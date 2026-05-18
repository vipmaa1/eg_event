import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/event.dart';
import '../../../shared/models/ticket.dart';
import 'confirmation_screen.dart';
import '../../auth/providers/auth_provider.dart';
import '../../auth/screens/login_screen.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  final Event event;
  const CheckoutScreen({super.key, required this.event});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  int _quantity = 1;
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _promoCtrl = TextEditingController();
  bool _isLoading = false;
  String? _error;

  double get _unitPrice => widget.event.minPrice ?? 100;
  double get _subtotal => _unitPrice * _quantity;
  double get _fees => _subtotal * 0.03;
  double get _total => _subtotal + _fees;

  @override
  void initState() {
    super.initState();
    final auth = ref.read(authStateProvider);
    if (auth.user != null) {
      _nameCtrl.text = auth.user!.name;
      _emailCtrl.text = auth.user!.email;
      _phoneCtrl.text = auth.user!.phone ?? '';
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _promoCtrl.dispose();
    super.dispose();
  }

  Future<void> _purchase() async {
    final auth = ref.read(authStateProvider);
    if (auth.status != AuthStatus.authenticated) {
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiClientProvider);
      final res = await api.post('/tickets/purchase', data: {
        'event_id': widget.event.id,
        'ticket_type_id': widget.event.id,
        'quantity': _quantity,
        'customer_name': _nameCtrl.text.trim(),
        'customer_email': _emailCtrl.text.trim(),
        'customer_phone': _phoneCtrl.text.trim(),
        if (_promoCtrl.text.isNotEmpty) 'promo_code': _promoCtrl.text.trim(),
      });

      final orderData = res.data['order'] as Map<String, dynamic>;
      final paymentData = res.data['payment'] as Map<String, dynamic>;

      if (paymentData['success'] == true && paymentData['ifram_url'] != null) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => ConfirmationScreen(
              order: orderData,
              paymentUrl: paymentData['ifram_url'] as String,
            ),
          ),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => ConfirmationScreen(
              order: orderData,
              paymentUrl: null,
              orderNumber: orderData['order_number'] as String,
            ),
          ),
        );
      }
    } on Exception catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final currencyFormatter = NumberFormat('#,##0.00', 'ar');

    return Scaffold(
      appBar: AppBar(title: const Text('إتمام الحجز')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(widget.event.displayTitle,
                        style: theme.textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.confirmation_number, size: 16),
                        const SizedBox(width: 4),
                        Text('EGP ${currencyFormatter.format(_unitPrice)}'),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Text('العدد:'),
                        const Spacer(),
                        IconButton(
                          icon: const Icon(Icons.remove_circle_outline),
                          onPressed: _quantity > 1
                              ? () => setState(() => _quantity--)
                              : null,
                        ),
                        Text('$_quantity',
                            style: theme.textTheme.titleMedium),
                        IconButton(
                          icon: const Icon(Icons.add_circle_outline),
                          onPressed: _quantity < 10
                              ? () => setState(() => _quantity++)
                              : null,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _nameCtrl,
              decoration: const InputDecoration(labelText: 'الاسم الكامل'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: 'البريد الإلكتروني'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _phoneCtrl,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'رقم الهاتف'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _promoCtrl,
              decoration: const InputDecoration(
                labelText: 'كود الخصم (اختياري)',
                prefixIcon: Icon(Icons.discount_outlined),
              ),
            ),
            const SizedBox(height: 24),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    _SummaryRow('سعر التذكرة', _subtotal, currencyFormatter),
                    _SummaryRow('رسوم الخدمة', _fees, currencyFormatter),
                    const Divider(),
                    _SummaryRow('المجموع', _total, currencyFormatter,
                        bold: true),
                  ],
                ),
              ),
            ),
            if (_error != null)
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Text(_error!,
                    style: const TextStyle(color: Colors.red),
                    textAlign: TextAlign.center),
              ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _isLoading ? null : _purchase,
              child: _isLoading
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text('تأكيد الحجز - EGP ${currencyFormatter.format(_total)}'),
            ),
          ],
        ),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final double amount;
  final NumberFormat format;
  final bool bold;

  const _SummaryRow(this.label, this.amount, this.format, {this.bold = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
          Text('EGP ${format.format(amount)}',
              style: TextStyle(
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
        ],
      ),
    );
  }
}
