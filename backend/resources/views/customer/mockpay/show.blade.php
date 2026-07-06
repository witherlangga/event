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
                        <span style="color: white; font-weight: 600;">{{ $order->ticket_count }} Ticket(s)</span>
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
                <form method="POST" action="{{ route('customer.mockpay.complete', ['order' => $order->id]) }}" style="display: flex; flex-direction: column; gap: 16px;">
                    @csrf

                    <div style="display: grid; gap: 16px;">
                        <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 20px;">
                            <p style="margin: 0 0 12px 0; font-weight: 700; color: white;">Choose payment method</p>
                            <div style="display: grid; gap: 12px;">
                                @foreach($paymentMethods as $key => $label)
                                    <label style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); cursor: pointer; background: rgba(255,255,255,0.02);">
                                        <span style="color: white;">{{ $label }}</span>
                                        <input type="radio" name="payment_method" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} style="accent-color: #64C8FF;">
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div id="payment-options" style="display: grid; gap: 12px;">
                            <div id="card-panel" class="payment-panel" style="display: none; background: rgba(100, 200, 255, 0.04); border: 1px solid rgba(100, 200, 255, 0.15); border-radius: 16px; padding: 20px;">
                                <h4 style="margin: 0 0 12px 0; color: white;">Credit / Debit Card</h4>
                                <p style="margin: 0; color: var(--text-secondary);">This is a demo card payment. Use the simulated card details below to complete payment.</p>
                                <div style="margin-top: 16px; padding: 16px; background: linear-gradient(135deg, #1a2550, #16213e); border-radius: 14px; color: white;">
                                    <div style="font-family: monospace; letter-spacing: 4px; margin-bottom: 14px;">4242 4242 4242 4242</div>
                                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                                        <div>
                                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">VALID THRU</div>
                                            <div style="font-weight: 700;">12/28</div>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">CVV</div>
                                            <div style="font-weight: 700;">123</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="ewallet-panel" class="payment-panel" style="display: none; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; padding: 20px;">
                                <h4 style="margin: 0 0 12px 0; color: white;">E-Wallet</h4>
                                <p style="margin: 0 0 16px 0; color: var(--text-secondary);">Choose an e-wallet provider and follow the mocked instructions below.</p>
                                <div style="display: grid; gap: 10px;">
                                    @foreach($ewalletProviders as $providerKey => $providerLabel)
                                        <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border-radius: 14px; background: rgba(255,255,255,0.05);">
                                            <span>{{ $providerLabel }}</span>
                                            <input type="radio" name="payment_channel" value="{{ $providerKey }}" {{ $loop->first ? 'checked' : '' }} style="accent-color: #64C8FF;">
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div id="bank-panel" class="payment-panel" style="display: none; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 20px;">
                                <h4 style="margin: 0 0 12px 0; color: white;">Bank Transfer</h4>
                                <p style="margin: 0 0 16px 0; color: var(--text-secondary);">Select a bank and follow the virtual account instructions.</p>
                                <div style="display: grid; gap: 10px;">
                                    @foreach($banks as $bankKey => $bankLabel)
                                        <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border-radius: 14px; background: rgba(255,255,255,0.05);">
                                            <span>{{ $bankLabel }}</span>
                                            <input type="radio" name="payment_channel" value="{{ $bankKey }}" {{ $loop->first ? 'checked' : '' }} style="accent-color: #64C8FF;">
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div id="qris-panel" class="payment-panel" style="display: none; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; padding: 20px;">
                                <h4 style="margin: 0 0 14px 0; color: white;">QRIS Scan</h4>
                                <p style="margin: 0 0 16px 0; color: var(--text-secondary);">Scan this QRIS code using your favorite Indonesian payment app.</p>
                                <div style="background: rgba(0,0,0,0.2); padding: 18px; border-radius: 16px; text-align: center;">
                                    <img src="{{ $qris['qris_image'] }}" alt="QRIS code" style="width: 220px; max-width: 100%; border-radius: 16px; background: white; padding: 12px;" />
                                    <p style="margin: 14px 0 0 0; color: #00FF88; font-weight: 700;">Scan to pay Rp {{ number_format($order->total_price) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 18px;">
                        <p style="margin: 0 0 8px 0; color: #64C8FF; font-weight: 700;">Payment details</p>
                        <p style="margin: 0; color: var(--text-secondary);">Method: <span style="color: white; font-weight: 600;" id="selected-method-label">Credit / Debit Card</span></p>
                        <p style="margin: 8px 0 0 0; color: var(--text-secondary);">Channel: <span style="color: white; font-weight: 600;" id="selected-channel-label">VISA/MC</span></p>
                    </div>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                        <label style="flex: 1; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius:16px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); cursor: pointer;">
                            <input type="checkbox" required style="width: 18px; height: 18px; accent-color: #64C8FF;">
                            <span style="color: var(--text-secondary);">Saya setuju dengan syarat dan ketentuan pembayaran</span>
                        </label>
                        <button type="submit" class="btn btn-primary" style="padding: 16px 24px; font-size: 1rem; font-weight: 700;">✓ Confirm Payment</button>
                    </div>
                </form>

                <!-- Back Link -->
                <div style="text-align: center; margin-top: 18px;">
                    <a href="{{ route('customer.orders.show', ['order' => $order->id]) }}" style="color: var(--secondary); text-decoration: none; font-size: 0.9rem;">← Back to Order</a>
                </div>

                <script>
                    const methodLabels = {
                        card: 'Credit / Debit Card',
                        e_wallet: 'E-Wallet',
                        bank_transfer: 'Bank Transfer',
                        qris: 'QRIS Scan',
                    };

                    const channelLabels = {
                        card: 'VISA/MC',
                        e_wallet: 'E-Wallet Provider',
                        bank_transfer: 'Bank Virtual Account',
                        qris: 'QRIS',
                    };

                    const panels = {
                        card: document.getElementById('card-panel'),
                        e_wallet: document.getElementById('ewallet-panel'),
                        bank_transfer: document.getElementById('bank-panel'),
                        qris: document.getElementById('qris-panel'),
                    };

                    const methodInputs = Array.from(document.querySelectorAll('input[name="payment_method"]'));
                    const channelInputs = Array.from(document.querySelectorAll('input[name="payment_channel"]'));
                    const selectedMethodLabel = document.getElementById('selected-method-label');
                    const selectedChannelLabel = document.getElementById('selected-channel-label');

                    function updatePaymentView() {
                        const selectedMethod = methodInputs.find(input => input.checked).value;
                        Object.values(panels).forEach(panel => panel.style.display = 'none');
                        panels[selectedMethod].style.display = 'block';
                        selectedMethodLabel.textContent = methodLabels[selectedMethod] || selectedMethod;
                        selectedChannelLabel.textContent = channelLabels[selectedMethod] || selectedMethod;

                        if (selectedMethod !== 'qris' && selectedMethod !== 'card') {
                            const selectedChannelInput = channelInputs.find(input => input.checked);
                            if (selectedChannelInput) {
                                selectedChannelLabel.textContent = selectedChannelInput.value;
                            }
                        }
                    }

                    methodInputs.forEach(input => input.addEventListener('change', updatePaymentView));
                    channelInputs.forEach(input => input.addEventListener('change', updatePaymentView));
                    updatePaymentView();
                </script>
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
