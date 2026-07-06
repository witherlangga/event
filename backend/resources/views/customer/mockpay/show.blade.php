@extends('layouts.panel')

@section('content')
    <div class="section" style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 200px);">
        <div class="card" style="max-width: 500px; width: 100%; overflow: hidden;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #FF6B9D, #64C8FF); padding: 40px 24px; text-align: center; color: white;">
                <h2 style="margin: 0 0 8px 0; background: none; -webkit-text-fill-color: unset;">🛒 Payment Checkout</h2>
                <p style="margin: 0; color: rgba(255,255,255,0.9);">Order #{{ $order->id }}</p>
            </div>

            <!-- Body -->
            <div class="card-body" style="padding: 40px 24px;">
                <!-- Order Summary -->
                <div style="background: rgba(100, 200, 255, 0.1); padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: var(--text-secondary);">Items Count</span>
                        <span style="color: white; font-weight: 600;">{{ $order->order_items_count ?? 0 }} Ticket(s)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: var(--text-secondary);">Subtotal</span>
                        <span style="color: white; font-weight: 600;">Rp {{ number_format($order->total_price) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(100, 200, 255, 0.2); padding-top: 12px; margin-top: 12px;">
                        <span style="color: var(--text-secondary); font-weight: 600;">Total Amount</span>
                        <span style="color: #00FF88; font-weight: 700; font-size: 1.5rem;">Rp {{ number_format($order->total_price) }}</span>
                    </div>
                </div>

                <!-- Payment Method Info -->
                <div style="background: rgba(0, 255, 136, 0.1); border: 1px solid rgba(0, 255, 136, 0.3); padding: 16px; border-radius: 12px; margin-bottom: 30px;">
                    <p style="margin: 0; color: #00FF88; font-weight: 600;">ℹ️ Demo Mode</p>
                    <p style="margin: 8px 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                        Ini adalah halaman pembayaran simulasi untuk memudahkan pesanan Anda. Tidak ada uang sungguhan yang ditarik. Klik "Confirm Payment" untuk menyelesaikan proses dan membuat e-ticket.
                    </p>
                </div>

                <!-- Payment Form -->
                <form method="POST" action="{{ route('customer.mockpay.complete', ['order' => $order->id]) }}" style="display: flex; flex-direction: column; gap: 12px;">
                    @csrf

                    <!-- Card Details (Visual Only) -->
                    <div style="background: linear-gradient(135deg, #1a2550, #16213e); border: 2px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 20px;">
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 16px;">CARD NUMBER</div>
                        <div style="font-family: monospace; color: white; font-size: 1.5rem; letter-spacing: 8px; margin-bottom: 24px;">•••• •••• •••• 4242</div>
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 8px;">VALID THRU</div>
                                <div style="color: white; font-weight: 600;">12/28</div>
                            </div>
                            <div>
                                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 8px;">CVV</div>
                                <div style="color: white; font-weight: 600;">•••</div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmation Checkbox -->
                    <label style="display: flex; align-items: center; padding: 12px; background: rgba(100, 200, 255, 0.05); border-radius: 8px; border: 1px solid var(--border-color); cursor: pointer; margin-bottom: 12px;">
                        <input type="checkbox" required style="width: 18px; height: 18px; margin-right: 12px; accent-color: #FF6B9D; cursor: pointer;">
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">
                            Saya setuju dengan syarat dan ketentuan pembayaran
                        </span>
                    </label>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 1.1rem; font-weight: 700;">
                        ✓ Confirm Payment
                    </button>
                </form>

                <!-- Back Link -->
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('customer.orders.show', ['order' => $order->id]) }}" style="color: var(--secondary); text-decoration: none; font-size: 0.9rem;">
                        ← Back to Order
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div style="background: rgba(0,0,0,0.3); padding: 16px 24px; text-align: center; border-top: 1px solid var(--border-color);">
                <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">
                    🔒 Your payment is secure and encrypted
                </p>
            </div>
        </div>
    </div>
@endsection
