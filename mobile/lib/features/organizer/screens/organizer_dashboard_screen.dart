import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../auth/providers/auth_provider.dart';
import '../../auth/screens/login_screen.dart';
import 'create_event_screen.dart';
import '../../checkin/screens/scanner_screen.dart';

final dashboardProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/organizers/mine');
  return res.data as Map<String, dynamic>;
});

class OrganizerDashboardScreen extends ConsumerWidget {
  const OrganizerDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authStateProvider);
    final dashAsync = ref.watch(dashboardProvider);
    final theme = Theme.of(context);

    if (auth.status != AuthStatus.authenticated || !auth.user!.isOrganizer) {
      return Scaffold(
        appBar: AppBar(title: const Text('لوحة التحكم')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('يجب أن تكون منظم للوصول إلى لوحة التحكم'),
              ElevatedButton(
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                ),
                child: const Text('تسجيل الدخول'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('لوحة التحكم')),
      body: dashAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('$e')),
        data: (data) {
          final events = (data['events'] as List<dynamic>?) ?? [];
          final pending = data['pending_events'] ?? 0;

          return RefreshIndicator(
            onRefresh: () => ref.refresh(dashboardProvider.future),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Text('مرحباً!',
                    style: theme.textTheme.headlineSmall
                        ?.copyWith(fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(child: _StatCard(label: 'الفعاليات', value: '${data['total_events'] ?? 0}', icon: Icons.event, color: theme.colorScheme.primary)),
                    const SizedBox(width: 8),
                    Expanded(child: _StatCard(label: 'تذاكر مباعة', value: '${data['total_tickets_sold'] ?? 0}', icon: Icons.confirmation_number, color: Colors.orange)),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(child: _StatCard(label: 'بانتظار الموافقة', value: '$pending', icon: Icons.hourglass_empty, color: Colors.amber)),
                    const SizedBox(width: 8),
                    Expanded(child: _StatCard(label: 'الإيرادات', value: 'EGP ${data['total_revenue'] ?? 0}', icon: Icons.currency_pound, color: Colors.green)),
                  ],
                ),
                const SizedBox(height: 12),
                if (pending > 0)
                  Card(
                    color: Colors.amber.shade50,
                    child: ListTile(
                      leading: const Icon(Icons.hourglass_empty, color: Colors.amber),
                      title: Text('$pending فعالية بانتظار المراجعة'),
                      subtitle: const Text('بعد مراجعة المشرف سيتم النشر'),
                    ),
                  ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(child: _ActionCard(icon: Icons.add_circle, label: 'فعالية جديدة', onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CreateEventScreen())))),
                    const SizedBox(width: 8),
                    Expanded(child: _ActionCard(icon: Icons.qr_code_scanner, label: 'مسح QR', onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ScannerScreen())))),
                  ],
                ),
                const SizedBox(height: 24),
                if (events.isNotEmpty) ...[
                  Text('فعالياتي', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  ...events.map((e) => _EventListItem(event: e as Map<String, dynamic>, ref: ref)),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}

class _EventListItem extends ConsumerWidget {
  final Map<String, dynamic> event;
  final WidgetRef ref;
  const _EventListItem({required this.event, required this.ref});

  Color _statusColor(String status) {
    switch (status) {
      case 'published':
        return Colors.green;
      case 'pending_approval':
        return Colors.amber;
      case 'cancelled':
        return Colors.red;
      case 'completed':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  Future<void> _publish(int id) async {
    try {
      await ref.read(apiClientProvider).post('/events/$id/publish');
      ref.invalidate(dashboardProvider);
    } catch (_) {}
  }

  Future<void> _cancel(int id) async {
    try {
      await ref.read(apiClientProvider).post('/events/$id/cancel');
      ref.invalidate(dashboardProvider);
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final id = event['id'] as int;
    final status = event['status'] as String? ?? '';
    final tickets = event['tickets_count'] ?? 0;
    final checkedIn = event['checked_in_count'] ?? 0;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(event['title_en'] as String? ?? '',
                      style: const TextStyle(fontWeight: FontWeight.bold)),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: _statusColor(status).withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(status,
                      style: TextStyle(fontSize: 11, color: _statusColor(status))),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text('$tickets تذاكر · $checkedIn تم الدخول',
                style: const TextStyle(fontSize: 12, color: Colors.grey)),
            if (status == 'draft')
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Row(
                  children: [
                    Expanded(
                      child: SizedBox(
                        height: 32,
                        child: ElevatedButton.icon(
                          onPressed: () => _publish(id),
                          icon: const Icon(Icons.send, size: 14),
                          label: const Text('نشر', style: TextStyle(fontSize: 12)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: SizedBox(
                        height: 32,
                        child: OutlinedButton.icon(
                          onPressed: () => Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => Scaffold(
                                appBar: AppBar(title: const Text('تعديل الفعالية')),
                                body: const Center(child: Text('صفحة التعديل قيد التطوير')),
                              ),
                            ),
                          ),
                          icon: const Icon(Icons.edit, size: 14),
                          label: const Text('تعديل', style: TextStyle(fontSize: 12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            if (status == 'published')
              Padding(
                padding: const EdgeInsets.only(top: 8),
                child: SizedBox(
                  height: 32,
                  child: OutlinedButton.icon(
                    onPressed: () => _cancel(id),
                    icon: const Icon(Icons.cancel, size: 14),
                    label: const Text('إلغاء الفعالية', style: TextStyle(fontSize: 12, color: Colors.red)),
                    style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatCard({required this.label, required this.value, required this.icon, required this.color});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(height: 8),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
        ]),
      ),
    );
  }
}

class _ActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  const _ActionCard({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(children: [
            Icon(icon, size: 40, color: Theme.of(context).colorScheme.primary),
            const SizedBox(height: 8),
            Text(label),
          ]),
        ),
      ),
    );
  }
}
