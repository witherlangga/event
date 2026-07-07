import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import 'order_result_screen.dart';

class PaymentScreen extends StatefulWidget {
  final int orderId;
  final double totalAmount;
  final List<Map<String, dynamic>> tickets;

  const PaymentScreen({
    super.key,
    required this.orderId,
    required this.totalAmount,
    required this.tickets,
  });

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  Uint8List? _qrImage;
  String? _checkoutUrl;
  bool _loading = true;
  bool _confirming = false;
  String? _error;
  Timer? _statusCheckTimer;
  int? _remainingMinutes;
  bool _paid = false;
  String _selectedMethod = 'qris';
  String? _selectedBank;

  @override
  void initState() {
    super.initState();
    _initializePayment();
  }

  @override
  void dispose() {
    _statusCheckTimer?.cancel();
    super.dispose();
  }

  Future<void> _initializePayment() async {
    try {
      setState(() {
        _loading = true;
        _qrImage = null;
        _checkoutUrl = null;
        _error = null;
      });

      final res = await ApiService.instance.initializePayment(
        widget.orderId,
        _selectedMethod,
        bankCode: _selectedBank,
      );

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final paymentData = data['payment_data'] as Map<String, dynamic>?;

        setState(() {
          _remainingMinutes = data['remaining_time'] as int?;
        });

        if (paymentData != null) {
          final qrUrl = paymentData['qr_code_url'] as String?;
          final checkoutUrl = paymentData['payment_url'] as String?;

          if (qrUrl != null) {
            final qrRes = await ApiService.instance.get(qrUrl);
            if (qrRes.statusCode == 200) {
              setState(() {
                _qrImage = qrRes.bodyBytes;
              });
              _startStatusCheck();
              return;
            }
            final message = _extractErrorMessage(qrRes.body) ?? 'Failed to load QR image (${qrRes.statusCode})';
            setState(() => _error = message);
          } else if (checkoutUrl != null) {
            setState(() {
              _checkoutUrl = checkoutUrl;
            });
            _startStatusCheck();
            return;
          }
        }

        setState(() => _error = 'Gateway response did not include payment link or QR code');
      } else {
        final message = _extractErrorMessage(res.body) ?? 'Failed to initialize payment (${res.statusCode})';
        setState(() => _error = message);
      }
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _loading = false);
    }
  }

  void _startStatusCheck() {
    _statusCheckTimer = Timer.periodic(const Duration(seconds: 10), (_) async {
      try {
        final res = await ApiService.instance.getPaymentStatus(widget.orderId);
        if (res.statusCode == 200) {
          final data = jsonDecode(res.body);
          final status = data['status'] as String?;
          final remaining = data['remaining_time'] as int?;

          setState(() => _remainingMinutes = remaining);

          if (status == 'paid') {
            _statusCheckTimer?.cancel();
            setState(() => _paid = true);
            // Auto-navigate to order result after 2 seconds
            Future.delayed(const Duration(seconds: 2), () {
              if (mounted) {
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(
                    builder: (_) => OrderResultScreen(tickets: widget.tickets),
                  ),
                );
              }
            });
          } else if (remaining != null && remaining <= 0) {
            _statusCheckTimer?.cancel();
            setState(() => _error = 'Payment deadline expired');
          }
        }
      } catch (e) {
        // Continue polling despite errors
      }
    });
  }

  Future<void> _confirmPayment() async {
    if (_confirming || _paid) return;

    setState(() {
      _confirming = true;
      _error = null;
    });

    try {
      final res = await ApiService.instance.confirmPayment(widget.orderId);
      if (res.statusCode == 200) {
        setState(() {
          _paid = true;
          _statusCheckTimer?.cancel();
        });
        Future.delayed(const Duration(seconds: 2), () {
          if (mounted) {
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(
                builder: (_) => OrderResultScreen(tickets: widget.tickets),
              ),
            );
          }
        });
      } else {
        final message = _extractErrorMessage(res.body) ?? 'Failed to confirm payment (${res.statusCode})';
        setState(() => _error = message);
      }
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _confirming = false);
    }
  }

  String _formatRemainingTime(int? minutes) {
    if (minutes == null || minutes <= 0) return 'Expired';
    if (minutes >= 60) {
      final hours = minutes ~/ 60;
      final mins = minutes % 60;
      return '$hours hour${hours > 1 ? 's' : ''} ${mins}m';
    }
    return '${minutes}m';
  }

  String _formatCurrency(double amount) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return formatter.format(amount.toInt());
  }

  Widget _buildPaymentMethodButton(BuildContext context, String method) {
    final selected = _selectedMethod == method;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedMethod = method;
          if (method != 'bank_transfer') {
            _selectedBank = null;
          }
        });
      },
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected ? Colors.deepPurple : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? Colors.deepPurple : Colors.grey.shade300,
            width: selected ? 1.8 : 1,
          ),
          boxShadow: selected
              ? [
                  BoxShadow(
                    color: Colors.deepPurple.withAlpha(41),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ]
              : null,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              _paymentMethodLabel(method),
              style: TextStyle(
                color: selected ? Colors.white : Colors.black87,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              _paymentMethodDescription(method),
              style: TextStyle(
                color: selected ? Colors.white70 : Colors.grey.shade600,
                fontSize: 12,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _paymentMethodLabel(String method) {
    switch (method) {
      case 'card':
        return 'Credit / Debit Card';
      case 'qris':
        return 'QRIS Scan';
      case 'bank_transfer':
        return 'Bank Transfer';
      case 'dana':
      case 'gopay':
      case 'ovo':
        return 'E-Wallet';
      default:
        return method;
    }
  }

  String _paymentMethodDescription(String method) {
    switch (method) {
      case 'card':
        return 'Use the simulated card details below to complete payment.';
      case 'qris':
        return 'A QRIS code will be generated. Scan it using your banking app.';
      case 'bank_transfer':
        return 'Choose your bank and follow the virtual account instructions.';
      case 'dana':
        return 'Use DANA app to complete a demo transfer.';
      case 'gopay':
        return 'Use GoPay app to complete a demo transfer.';
      case 'ovo':
        return 'Use OVO app to complete a demo transfer.';
      default:
        return 'Select a payment option to continue.';
    }
  }

  String? _extractErrorMessage(String body) {
    try {
      final data = jsonDecode(body);
      if (data is Map<String, dynamic>) {
        if (data['message'] is String) return data['message'] as String;
        if (data['errors'] != null) return data['errors'].toString();
      }
    } catch (_) {
      // ignore invalid JSON
    }
    return null;
  }

  Future<void> _openCheckoutUrl() async {
    if (_checkoutUrl == null) return;
    final uri = Uri.tryParse(_checkoutUrl!);
    if (uri == null) {
      setState(() => _error = 'Invalid payment URL');
      return;
    }

    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      setState(() => _error = 'Unable to open payment link');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Payment'),
        leading: _loading || _paid ? null : IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Amount Card
            Card(
              elevation: 2,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Total Amount',
                      style: TextStyle(color: Colors.grey, fontSize: 12),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _formatCurrency(widget.totalAmount),
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            Container(
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withAlpha(15),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Simulasi Pembayaran',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Pilih metode yang ingin kamu gunakan untuk menyelesaikan pembayaran pesanan ini. Ini adalah simulasi yang menyerupai proses dari versi web.',
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                    ),
                    const SizedBox(height: 20),
                    _buildPaymentMethodButton(context, 'qris'),
                    const SizedBox(height: 10),
                    _buildPaymentMethodButton(context, 'dana'),
                    const SizedBox(height: 10),
                    _buildPaymentMethodButton(context, 'gopay'),
                    const SizedBox(height: 10),
                    _buildPaymentMethodButton(context, 'ovo'),
                    const SizedBox(height: 10),
                    _buildPaymentMethodButton(context, 'bank_transfer'),
                    if (_selectedMethod == 'bank_transfer') ...[
                      const SizedBox(height: 14),
                      DropdownButtonFormField<String>(
                        initialValue: _selectedBank,
                        decoration: const InputDecoration(
                          labelText: 'Pilih Bank',
                          border: OutlineInputBorder(),
                          filled: true,
                          fillColor: Colors.white,
                        ),
                        items: const [
                          DropdownMenuItem(value: 'bca', child: Text('BCA')),
                          DropdownMenuItem(value: 'bri', child: Text('BRI')),
                          DropdownMenuItem(value: 'bni', child: Text('BNI')),
                          DropdownMenuItem(value: 'mandiri', child: Text('Mandiri')),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedBank = value;
                          });
                        },
                      ),
                    ],
                    const SizedBox(height: 18),
                    Text(
                      _paymentMethodDescription(_selectedMethod),
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                    ),
                    const SizedBox(height: 18),
                    ElevatedButton(
                      onPressed: _loading ? null : _initializePayment,
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        backgroundColor: Colors.deepPurple,
                      ),
                      child: Text(
                        _loading ? 'Memulai...' : 'Mulai Simulasi Pembayaran',
                        style: const TextStyle(fontSize: 15),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Timer
            if (!_paid && _remainingMinutes != null)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: _remainingMinutes! <= 5
                      ? Colors.red.shade50
                      : Colors.blue.shade50,
                  border: Border.all(
                    color: _remainingMinutes! <= 5 ? Colors.red : Colors.blue,
                  ),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  children: [
                    const Text(
                      'Time Remaining',
                      style: TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _formatRemainingTime(_remainingMinutes),
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color:
                            _remainingMinutes! <= 5 ? Colors.red : Colors.blue,
                      ),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 24),

            // QR Code Section
            if (_paid)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.green.shade50,
                  border: Border.all(color: Colors.green),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  children: [
                    const Icon(Icons.check_circle,
                        color: Colors.green, size: 48),
                    const SizedBox(height: 8),
                    const Text(
                      'Payment Confirmed!',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Redirecting to tickets...',
                      style: TextStyle(color: Colors.grey, fontSize: 12),
                    ),
                  ],
                ),
              )
            else if (_error != null)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  border: Border.all(color: Colors.red),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  children: [
                    const Icon(Icons.error, color: Colors.red, size: 48),
                    const SizedBox(height: 8),
                    Text(
                      _error!,
                      style: const TextStyle(
                        color: Colors.red,
                        fontWeight: FontWeight.w500,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: _initializePayment,
                        icon: const Icon(Icons.refresh),
                        label: const Text('Retry'),
                      ),
                    ),
                  ],
                ),
              )
            else if (_loading)
              Center(
                child: Column(
                  children: [
                    const CircularProgressIndicator(),
                    const SizedBox(height: 12),
                    const Text(
                      'Generating QR Code...',
                      style: TextStyle(color: Colors.grey),
                    ),
                  ],
                ),
              )
            else if (_checkoutUrl != null)
              Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Pembayaran siap dilakukan:',
                    style: TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _openCheckoutUrl,
                      child: const Text('Buka Aplikasi Pembayaran'),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Jika aplikasi tidak terbuka otomatis, gunakan tombol di atas untuk melanjutkan pembayaran.',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                    textAlign: TextAlign.center,
                  ),
                ],
              )
            else if (_qrImage != null)
              Column(
                children: [
                  const Text(
                    'Scan with your mobile banking app:',
                    style: TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.grey.shade300),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Image.memory(
                      _qrImage!,
                      width: 250,
                      height: 250,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.amber.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Column(
                      children: [
                        const Text(
                          '📱 How to pay:',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          '1. Open your banking app\n'
                          '2. Select "Scan QRIS" or "Transfer"\n'
                          '3. Scan this QR code\n'
                          '4. Review and confirm payment\n'
                          '5. Your tickets will appear automatically',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            if ((_qrImage != null || _checkoutUrl != null) && !_paid) ...[
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _confirming ? null : _confirmPayment,
                  icon: _confirming
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(Icons.check_circle_outline),
                  label: Text(_confirming ? 'Confirming payment...' : 'Already paid, finish transaction'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
