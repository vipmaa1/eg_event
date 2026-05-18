import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';

final adminSettingsProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/admin/settings');
  return res.data as List<dynamic>;
});

class AdminSettingsScreen extends ConsumerStatefulWidget {
  const AdminSettingsScreen({super.key});
  @override
  ConsumerState<AdminSettingsScreen> createState() => _AdminSettingsScreenState();
}

class _AdminSettingsScreenState extends ConsumerState<AdminSettingsScreen> {
  final Map<String, TextEditingController> _controllers = {};

  @override
  void dispose() {
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _save(String key, String value, String type) async {
    try {
      await ref.read(apiClientProvider).post('/admin/settings', data: {
        'key': key,
        'value': value,
        'type': type,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم الحفظ'), duration: Duration(seconds: 1)),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('$e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final settingsAsync = ref.watch(adminSettingsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('إعدادات الموقع')),
      body: settingsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('$e')),
        data: (settings) {
          for (final s in settings) {
            final key = s['key'] as String;
            if (!_controllers.containsKey(key)) {
              _controllers[key] = TextEditingController(text: s['value'] as String? ?? '');
            }
          }

          return ListView(
            padding: const EdgeInsets.all(16),
            children: settings.map((s) {
              final key = s['key'] as String;
              final type = s['type'] as String? ?? 'string';
              final desc = s['description'] as String?;

              return Card(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(key, style: const TextStyle(fontWeight: FontWeight.bold)),
                      if (desc != null) Text(desc, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                      const SizedBox(height: 8),
                      if (type == 'boolean')
                        DropdownButtonFormField<String>(
                          value: _controllers[key]?.text ?? '0',
                          decoration: const InputDecoration(isDense: true),
                          items: const [
                            DropdownMenuItem(value: '1', child: Text('فعال')),
                            DropdownMenuItem(value: '0', child: Text('معطل')),
                          ],
                          onChanged: (v) {
                            _controllers[key]?.text = v ?? '0';
                            _save(key, v ?? '0', type);
                          },
                        )
                      else
                        Row(
                          children: [
                            Expanded(
                              child: TextField(
                                controller: _controllers[key],
                                decoration: const InputDecoration(isDense: true),
                              ),
                            ),
                            const SizedBox(width: 8),
                            IconButton(
                              icon: const Icon(Icons.save),
                              onPressed: () => _save(key, _controllers[key]?.text ?? '', type),
                            ),
                          ],
                        ),
                    ],
                  ),
                ),
              );
            }).toList(),
          );
        },
      ),
    );
  }
}
