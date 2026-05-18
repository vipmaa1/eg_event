import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../auth/providers/auth_provider.dart';
import '../../auth/screens/login_screen.dart';
import 'admin_events_screen.dart';
import 'admin_settings_screen.dart';

final adminDashboardProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/admin/dashboard');
  return res.data as Map<String, dynamic>;
});

class AdminDashboardScreen extends ConsumerWidget {
  const AdminDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authStateProvider);
    final dashAsync = ref.watch(adminDashboardProvider);
    final theme = Theme.of(context);

    if (auth.status != AuthStatus.authenticated || !auth.user!.isAdmin) {
      return Scaffold(
        appBar: AppBar(title: const Text('لوحة الإدارة')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('غير مصرح بالوصول'),
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
      appBar: AppBar(title: const Text('لوحة الإدارة')),
      body: dashAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('$e')),
        data: (data) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Row(
              children: [
                Expanded(child: _StatCard(label: 'الفعاليات', value: '${data['total_events'] ?? 0}', icon: Icons.event, color: Colors.blue)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'المستخدمين', value: '${data['total_users'] ?? 0}', icon: Icons.people, color: Colors.green)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _StatCard(label: 'المنظمين', value: '${data['total_organizers'] ?? 0}', icon: Icons.store, color: Colors.purple)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'المواقع', value: '${data['total_venues'] ?? 0}', icon: Icons.location_city, color: Colors.orange)),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: _StatCard(label: 'بانتظار الموافقة', value: '${data['pending_approval'] ?? 0}', icon: Icons.hourglass_empty, color: Colors.amber)),
                const SizedBox(width: 8),
                Expanded(child: _StatCard(label: 'الإيرادات', value: 'EGP ${data['total_revenue'] ?? 0}', icon: Icons.monetization_on, color: Colors.teal)),
              ],
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(child: _ActionCard(icon: Icons.event, label: 'الفعاليات', onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AdminEventsScreen())))),
                const SizedBox(width: 8),
                Expanded(child: _ActionCard(icon: Icons.settings, label: 'الإعدادات', onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AdminSettingsScreen())))),
              ],
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
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
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
            Icon(icon, size: 36, color: Theme.of(context).colorScheme.primary),
            const SizedBox(height: 8),
            Text(label),
          ]),
        ),
      ),
    );
  }
}
