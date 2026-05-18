import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../../features/events/screens/home_screen.dart';

class ConfirmationScreen extends StatelessWidget {
  final Map<String, dynamic> order;
  final String? paymentUrl;
  final String? orderNumber;

  const ConfirmationScreen({
    super.key,
    required this.order,
    this.paymentUrl,
    this.orderNumber,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    if (paymentUrl != null) {
      return Scaffold(
        appBar: AppBar(title: const Text('الدفع')),
        body: WebViewWidget(
          controller: WebViewController()
            ..loadRequest(Uri.parse(paymentUrl!))
            ..setJavaScriptMode(JavaScriptMode.unrestricted)
            ..setNavigationDelegate(
              NavigationDelegate(
                onUrlChange: (change) {
                  if (change.url?.contains('success') == true ||
                      change.url?.contains('callback') == true) {
                    Navigator.of(context).pushAndRemoveUntil(
                      MaterialPageRoute(
                        builder: (_) => _SuccessScreen(order: order),
                      ),
                      (_) => false,
                    );
                  }
                },
              ),
            ),
        ),
      );
    }

    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.check_circle,
                  size: 80, color: theme.colorScheme.primary),
              const SizedBox(height: 16),
              Text('تم تأكيد الحجز!',
                  style: theme.textTheme.headlineSmall
                      ?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text('رقم الحجز: ${orderNumber ?? order['order_number']}'),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const HomeScreen()),
                  (_) => false,
                ),
                child: const Text('العودة للرئيسية'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SuccessScreen extends StatelessWidget {
  final Map<String, dynamic> order;
  const _SuccessScreen({required this.order});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.check_circle, size: 80, color: Colors.green),
              const SizedBox(height: 16),
              Text('تم الدفع بنجاح!',
                  style: Theme.of(context)
                      .textTheme
                      .headlineSmall
                      ?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const HomeScreen()),
                  (_) => false,
                ),
                child: const Text('العودة للرئيسية'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
