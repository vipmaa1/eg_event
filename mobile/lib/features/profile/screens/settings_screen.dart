import 'package:flutter/material.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('الإعدادات')),
      body: ListView(
        children: [
          SwitchListTile(
            title: const Text('اللغة العربية'),
            subtitle: const Text('تغيير لغة التطبيق'),
            value: true,
            onChanged: (_) {},
          ),
          SwitchListTile(
            title: const Text('إشعارات الفعاليات'),
            subtitle: const Text('تنبيهات بالفعاليات القادمة'),
            value: true,
            onChanged: (_) {},
          ),
          SwitchListTile(
            title: const Text('إشعارات التذاكر'),
            subtitle: const Text('تنبيهات بتأكيد الحجز'),
            value: true,
            onChanged: (_) {},
          ),
          const Divider(),
          ListTile(
            title: const Text('عن التطبيق'),
            subtitle: const Text('EventHub v1.0.0'),
          ),
        ],
      ),
    );
  }
}
