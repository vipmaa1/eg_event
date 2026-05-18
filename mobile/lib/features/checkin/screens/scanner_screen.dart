import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../providers/checkin_provider.dart';
import '../../../core/network/api_client.dart';

final organizerEventsProvider =
    FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/my-events');
  final list = (res.data['data'] as List<dynamic>?)
          ?.map((e) => e as Map<String, dynamic>)
          .toList() ??
      [];
  return list;
});

class ScannerScreen extends ConsumerStatefulWidget {
  const ScannerScreen({super.key});

  @override
  ConsumerState<ScannerScreen> createState() => _ScannerScreenState();
}

class _ScannerScreenState extends ConsumerState<ScannerScreen> {
  MobileScannerController? _scannerCtrl;
  int? _selectedEventId;
  String? _lastScannedCode;

  @override
  void initState() {
    super.initState();
    _scannerCtrl = MobileScannerController();
  }

  @override
  void dispose() {
    _scannerCtrl?.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (capture.barcodes.isEmpty) return;
    final code = capture.barcodes.first.rawValue;
    if (code == null || code == _lastScannedCode) return;
    if (_selectedEventId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('اختر الفعالية أولاً')),
      );
      return;
    }
    _lastScannedCode = code;
    ref.read(checkinProvider.notifier).scan(code, _selectedEventId!);
  }

  @override
  Widget build(BuildContext context) {
    final eventsAsync = ref.watch(organizerEventsProvider);
    final checkinResult = ref.watch(checkinProvider);
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('مسح QR')),
      body: Column(
        children: [
          eventsAsync.when(
            loading: () => const LinearProgressIndicator(),
            error: (e, _) => Text('$e'),
            data: (events) => Padding(
              padding: const EdgeInsets.all(16),
              child: DropdownButtonFormField<int>(
                value: _selectedEventId,
                decoration: const InputDecoration(labelText: 'اختر الفعالية'),
                items: events
                    .map((e) => DropdownMenuItem(
                          value: e['id'] as int,
                          child: Text(e['title_en'] as String),
                        ))
                    .toList(),
                onChanged: (v) => setState(() => _selectedEventId = v),
              ),
            ),
          ),
          Expanded(
            child: MobileScanner(
              controller: _scannerCtrl,
              onDetect: _onDetect,
            ),
          ),
          checkinResult.when(
            loading: () => const LinearProgressIndicator(),
            error: (e, _) => Padding(
              padding: const EdgeInsets.all(16),
              child: Text('خطأ: $e', style: const TextStyle(color: Colors.red)),
            ),
            data: (result) {
              if (result == null) return const SizedBox.shrink();
              return Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                color: result.success ? Colors.green.shade50 : Colors.red.shade50,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          result.success ? Icons.check_circle : Icons.error,
                          color: result.success ? Colors.green : Colors.red,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            result.success ? 'تم الدخول!' : 'فشل الدخول',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: result.success ? Colors.green : Colors.red,
                            ),
                          ),
                        ),
                        TextButton(
                          onPressed: () =>
                              ref.read(checkinProvider.notifier).reset(),
                          child: const Text('مسح جديد'),
                        ),
                      ],
                    ),
                    Text(result.message),
                    if (result.holderName != null)
                      Text('الحامل: ${result.holderName}'),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
