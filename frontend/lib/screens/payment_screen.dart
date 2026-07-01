import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
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
  bool _loading = true;
  String? _error;
  DateTime? _deadline;
  Timer? _statusCheckTimer;
  int? _remainingMinutes;
  bool _paid = false;

  @override
  void initState() {
    super.initState();
    _generateQris();
  }

  @override
  void dispose() {
    _statusCheckTimer?.cancel();
    super.dispose();
  }

  Future<void> _generateQris() async {
    try {
      setState(() => _loading = true);
      final res = await ApiService.instance.generatePaymentQris(widget.orderId);

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final qrUrl = data['qr_url'] as String?;
        final deadline = data['payment_deadline'] as String?;

        if (qrUrl != null) {
          // Download QR image
          final qrRes = await ApiService.instance.get(qrUrl.replaceAll(RegExp(r'.*api/'), '/'));
          if (qrRes.statusCode == 200) {
            setState(() {
              _qrImage = qrRes.bodyBytes;
              _deadline = deadline != null ? DateTime.parse(deadline) : null;
            });
            // Start polling for payment status
            _startStatusCheck();
          } else {
            setState(() => _error = 'Failed to load QR image');
          }
        }
      } else {
        setState(() => _error = 'Failed to generate payment QR');
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
                        onPressed: _generateQris,
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
          ],
        ),
      ),
    );
  }
}
