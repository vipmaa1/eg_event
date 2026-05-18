import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import 'package:flutter_slidable/flutter_slidable.dart';

final adminEventsProvider = FutureProvider.autoDispose.family<List<dynamic>, String>((ref, status) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/admin/events?status=$status');
  return (res.data['data'] as List<dynamic>?) ?? [];
});

class AdminEventsScreen extends ConsumerStatefulWidget {
  const AdminEventsScreen({super.key});
  @override
  ConsumerState<AdminEventsScreen> createState() => _AdminEventsScreenState();
}

class _AdminEventsScreenState extends ConsumerState<AdminEventsScreen> {
  String _statusFilter = '';

  @override
  Widget build(BuildContext context) {
    final eventsAsync = ref.watch(adminEventsProvider(_statusFilter));

    return Scaffold(
      appBar: AppBar(title: const Text('إدارة الفعاليات')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: DropdownButtonFormField<String>(
              value: _statusFilter,
              decoration: const InputDecoration(labelText: 'الحالة', isDense: true),
              items: const [
                DropdownMenuItem(value: '', child: Text('الكل')),
                DropdownMenuItem(value: 'draft', child: Text('مسودة')),
                DropdownMenuItem(value: 'pending_approval', child: Text('بانتظار الموافقة')),
                DropdownMenuItem(value: 'published', child: Text('منشور')),
                DropdownMenuItem(value: 'cancelled', child: Text('ملغي')),
                DropdownMenuItem(value: 'completed', child: Text('مكتمل')),
              ],
              onChanged: (v) => setState(() => _statusFilter = v ?? ''),
            ),
          ),
          Expanded(
            child: eventsAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('$e')),
              data: (events) => events.isEmpty
                  ? const Center(child: Text('لا توجد فعاليات', style: TextStyle(color: Colors.grey)))
                  : ListView.builder(
                      itemCount: events.length,
                      itemBuilder: (_, i) {
                        final e = events[i] as Map<String, dynamic>;
                        final status = e['status'] as String? ?? '';
                        final isPending = status == 'pending_approval';

                        return Slidable(
                          endActionPane: isPending
                              ? ActionPane(motion: const DrawerMotion(), children: [
                                  SlidableAction(onPressed: (_) => _approve(e['id'] as int), backgroundColor: Colors.green, icon: Icons.check, label: 'موافقة'),
                                  SlidableAction(onPressed: (_) => _reject(e['id'] as int), backgroundColor: Colors.red, icon: Icons.close, label: 'رفض'),
                                ])
                              : ActionPane(motion: const DrawerMotion(), children: [
                                  SlidableAction(onPressed: (_) => _delete(e['id'] as int), backgroundColor: Colors.red, icon: Icons.delete, label: 'حذف'),
                                ]),
                          child: ListTile(
                            title: Text(e['title_en'] as String? ?? ''),
                            subtitle: Text('$status · ${e['category'] ?? ''}'),
                            trailing: _statusBadge(status),
                          ),
                        );
                      },
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _statusBadge(String status) {
    final colors = {
      'draft': Colors.grey,
      'pending_approval': Colors.amber,
      'published': Colors.green,
      'cancelled': Colors.red,
      'completed': Colors.blue,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(color: (colors[status] ?? Colors.grey).withValues(alpha: 0.15), borderRadius: BorderRadius.circular(8)),
      child: Text(status, style: TextStyle(fontSize: 11, color: colors[status] ?? Colors.grey)),
    );
  }

  Future<void> _approve(int id) async {
    try {
      await ref.read(apiClientProvider).post('/admin/events/$id/approve');
      ref.invalidate(adminEventsProvider);
    } catch (_) {}
  }

  Future<void> _reject(int id) async {
    try {
      await ref.read(apiClientProvider).post('/admin/events/$id/reject', data: {'reason': 'مرفوض'});
      ref.invalidate(adminEventsProvider);
    } catch (_) {}
  }

  Future<void> _delete(int id) async {
    try {
      await ref.read(apiClientProvider).delete<dynamic>('/admin/events/$id');
      ref.invalidate(adminEventsProvider);
    } catch (_) {}
  }
}
