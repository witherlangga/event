<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\URL;

class CustomerPanelController extends Controller
{
    public function index()
    {
        return redirect()->route('tickets');
    }

    public function tickets()
    {
        $events = Event::where('is_active', true)
            ->with(['ticketTypes' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('starts_at')
            ->get();

        return view('customer.tickets.index', compact('events'));
    }

    public function show(Event $event)
    {
        $ticketTypes = TicketType::where('event_id', $event->id)->where('is_active', true)->get();
        return view('customer.events.show', compact('event','ticketTypes'));
    }

    public function purchase(Request $request, Event $event)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please log in to purchase tickets.');
        }

        $data = $request->validate([
            'ticket_type_id' => 'required|integer|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticketType = TicketType::where('id', $data['ticket_type_id'])->lockForUpdate()->first();
        if (! $ticketType || $ticketType->event_id !== $event->id) {
            return redirect()->back()->with('error','Ticket type not found');
        }

        $qty = (int) $data['quantity'];

        // Create a pending order and redirect to a mock payment page (no real money)
        return DB::transaction(function () use ($user, $event, $ticketType, $qty) {
            $tt = TicketType::where('id', $ticketType->id)->lockForUpdate()->first();
            if (! is_null($tt->quota)) {
                $available = $tt->quota - $tt->sold;
                if ($available < $qty) {
                    return redirect()->back()->with('error', 'Not enough quota');
                }
            }

            // reserve quota but do not increment sold until payment completed

            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'total_price' => $tt->price * $qty,
                'status' => 'pending',
            ]);

            $item = OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $tt->id,
                'quantity' => $qty,
                'unit_price' => $tt->price,
                'line_total' => $tt->price * $qty,
            ]);

            // Redirect to mock payment page for this order
            return redirect()->route('customer.mockpay.show', ['order' => $order->id]);
        });
    }

    // Show mock payment page (simulate payment without real money)
    public function mockPaymentShow($orderId)
    {
        $user = Auth::user();
        $order = Order::with(['items.ticketType', 'refunds.requester', 'refunds.processor', 'user', 'event'])->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('customer.orders.show', ['order' => $order->id])->with('error', 'Order already processed');
        }

        $ticketCount = $order->ticket_count;

        $paymentMethods = [
            'card' => 'Credit / Debit Card',
            'e_wallet' => 'E-Wallet',
            'bank_transfer' => 'Bank Transfer',
            'qris' => 'QRIS Scan',
        ];

        $ewalletProviders = [
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'SHOPEEPAY' => 'ShopeePay',
        ];

        $banks = [
            'BCA' => 'BCA',
            'MANDIRI' => 'Mandiri',
            'BRI' => 'BRI',
            'BNI' => 'BNI',
        ];

        $qris = $this->generateQrisForOrder($order);

        return view('customer.mockpay.show', compact('order', 'ticketCount', 'paymentMethods', 'ewalletProviders', 'banks', 'qris'));
    }

    // Complete mock payment: mark order paid and generate tickets & QR
    public function mockPaymentComplete(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::with('items')->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('customer.orders.show', ['order' => $order->id])->with('error','Order already processed');
        }

        $data = $request->validate([
            'payment_method' => 'required|in:card,e_wallet,bank_transfer,qris',
            'payment_channel' => 'nullable|string',
        ]);

        $paymentMethod = $data['payment_method'];
        $paymentChannel = $data['payment_channel'] ?? null;
        $paymentReference = null;
        $paymentInstructions = null;

        if ($paymentMethod === 'bank_transfer') {
            $paymentChannel = $request->validate(['payment_channel' => 'required|in:BCA,MANDIRI,BRI,BNI'])['payment_channel'];
            $bankData = $this->getBankAccountForBank($paymentChannel);
            $paymentReference = $bankData['account'];
            $paymentInstructions = $bankData['instructions'];
        } elseif ($paymentMethod === 'e_wallet') {
            $paymentChannel = $request->validate(['payment_channel' => 'required|in:OVO,DANA,SHOPEEPAY'])['payment_channel'];
            $ewalletData = $this->getEwalletDestinationForProvider($paymentChannel);
            $paymentReference = $ewalletData['account'];
            $paymentInstructions = $ewalletData['instructions'];
        } elseif ($paymentMethod === 'qris') {
            $paymentChannel = 'QRIS';
            $qrisData = $this->generateQrisData($order->total_price);
            $paymentReference = $qrisData;
            $paymentInstructions = 'Scan the QRIS code with a QRIS-enabled app to complete the demo payment.';
        } else {
            $paymentChannel = 'VISA/MC';
            $paymentReference = 'CARD-4242';
            $paymentInstructions = 'Simulated card payment. No actual charge will occur.';
        }

        DB::transaction(function () use ($order, $paymentMethod, $paymentChannel, $paymentReference, $paymentInstructions) {
            $order->update([
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'payment_reference' => $paymentReference,
                'payment_instructions' => $paymentInstructions,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $writer = new PngWriter();
            foreach ($order->items as $item) {
                $tt = TicketType::where('id', $item->ticket_type_id)->lockForUpdate()->first();
                $qty = $item->quantity;
                if (! is_null($tt->quota)) {
                    $available = $tt->quota - $tt->sold;
                    if ($available < $qty) {
                        throw new \Exception('Not enough quota during payment');
                    }
                }
                $tt->sold += $qty;
                $tt->save();

                for ($i = 0; $i < $qty; $i++) {
                    $code = Str::uuid()->toString();
                    $ticket = Ticket::create([
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'ticket_type_id' => $tt->id,
                        'code' => $code,
                    ]);

                    $qr = new QrCode($code);
                    $result = $writer->write($qr);
                    $pngData = $result->getString();

                    $path = 'private/tickets/' . $code . '.png';
                    Storage::disk('local')->put($path, $pngData);

                    $ticket->qr_path = $path;
                    $ticket->save();
                }
            }
        });

        return redirect()->route('customer.orders.show', ['order' => $order->id])->with('success','Payment simulated and tickets generated');
    }

    private function generateQrisForOrder(Order $order): array
    {
        $qrisData = $this->generateQrisData($order->total_price);
        $writer = new PngWriter();
        $qr = new QrCode($qrisData);
        $result = $writer->write($qr);

        return [
            'qris_data' => $qrisData,
            'qris_image' => 'data:image/png;base64,' . base64_encode($result->getString()),
        ];
    }

    private function getBankAccountForBank(string $bank): array
    {
        $accounts = [
            'BCA' => [
                'account' => '7001234567',
                'instructions' => 'Transfer ke BCA 7001234567 a.n Neon Horizon. Masukkan nominal sesuai total pesanan.',
            ],
            'MANDIRI' => [
                'account' => '1122334455',
                'instructions' => 'Transfer ke Mandiri 1122334455 a.n Neon Horizon. Masukkan nominal sesuai total pesanan.',
            ],
            'BRI' => [
                'account' => '0023345566',
                'instructions' => 'Transfer ke BRI 0023345566 a.n Neon Horizon. Masukkan nominal sesuai total pesanan.',
            ],
            'BNI' => [
                'account' => '6509871234',
                'instructions' => 'Transfer ke BNI 6509871234 a.n Neon Horizon. Masukkan nominal sesuai total pesanan.',
            ],
        ];

        return $accounts[$bank] ?? [
            'account' => '0000000000',
            'instructions' => 'Pilih bank untuk melihat instruksi transfer.',
        ];
    }

    private function getEwalletDestinationForProvider(string $provider): array
    {
        $destinations = [
            'OVO' => [
                'account' => '081234567890',
                'instructions' => 'Buka aplikasi OVO, pilih Transfer ke OVO ID 081234567890, lalu bayar sesuai total pesanan.',
            ],
            'DANA' => [
                'account' => '081234567891',
                'instructions' => 'Buka aplikasi DANA, pilih Kirim ke nomor 081234567891, lalu bayar sesuai total pesanan.',
            ],
            'SHOPEEPAY' => [
                'account' => '081234567892',
                'instructions' => 'Buka aplikasi ShopeePay, pilih Transfer ke nomor 081234567892, lalu bayar sesuai total pesanan.',
            ],
        ];

        return $destinations[$provider] ?? [
            'account' => '081234567899',
            'instructions' => 'Pilih e-wallet untuk melihat instruksi pembayaran.',
        ];
    }

    private function generateQrisData($amount)
    {
        $amountCents = (int)($amount * 100);
        return '00020126360007ID.CO.MANDIRI01189370010300006154970208NMMID10254285957769'
            . '5204' . str_pad($amountCents, 4, '0', STR_PAD_LEFT)
            . '5303360' . '0';
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('items')->orderByDesc('created_at')->get();
        return view('customer.orders.index', compact('orders'));
    }

    public function orderShow($orderId)
    {
        $user = Auth::user();
        $order = Order::with(['items.ticketType', 'user', 'event', 'refunds'])->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        $tickets = Ticket::with('ticketType')->where('order_id', $order->id)->get();
        foreach ($tickets as $t) {
            $t->signed_download = URL::temporarySignedRoute('tickets.qr.download', now()->addMinutes(30), ['ticket' => $t->id]);
        }

        // pass refunds to view via $order->refunds
        return view('customer.orders.show', compact('order','tickets'));
    }

    // Customer requests refund for specific tickets
    public function requestRefund(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::with('items')->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        $data = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $ticketIds = $data['ticket_ids'];
        $tickets = Ticket::whereIn('id', $ticketIds)->where('order_id', $order->id)->get();
        if (count($tickets) !== count($ticketIds)) {
            return redirect()->back()->with('error','Some tickets not found in this order');
        }

        foreach ($tickets as $t) {
            if ($t->used) {
                return redirect()->back()->with('error','Cannot request refund: one or more tickets already used');
            }
        }

        $amount = 0;
        foreach ($tickets as $t) {
            $amount += $t->ticketType->price;
        }

        $refund = \App\Models\Refund::create([
            'order_id' => $order->id,
            'requested_by' => $user->id,
            'amount' => $amount,
            'reason' => $data['reason'] ?? null,
            'status' => 'requested',
            'ticket_ids' => $ticketIds,
        ]);

        return redirect()->route('customer.orders.show', ['order' => $order->id])->with('success','Refund requested');
    }
}
