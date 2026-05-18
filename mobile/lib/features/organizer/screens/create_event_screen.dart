import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';

final venuesProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/venues');
  return (res.data['data'] as List<dynamic>?) ?? [];
});

class CreateEventScreen extends ConsumerStatefulWidget {
  const CreateEventScreen({super.key});

  @override
  ConsumerState<CreateEventScreen> createState() => _CreateEventScreenState();
}

class _CreateEventScreenState extends ConsumerState<CreateEventScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleEnCtrl = TextEditingController();
  final _titleArCtrl = TextEditingController();
  final _descEnCtrl = TextEditingController();
  final _descArCtrl = TextEditingController();
  final _coverCtrl = TextEditingController();
  String _category = 'concerts';
  int? _venueId;
  bool _isFree = false;
  DateTime _startDate = DateTime.now().add(const Duration(days: 30));
  DateTime _endDate = DateTime.now().add(const Duration(days: 32));
  bool _isLoading = false;
  bool _isSuccess = false;

  @override
  void dispose() {
    _titleEnCtrl.dispose();
    _titleArCtrl.dispose();
    _descEnCtrl.dispose();
    _descArCtrl.dispose();
    _coverCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiClientProvider);
      await api.post('/events', data: {
        'title_en': _titleEnCtrl.text.trim(),
        'title_ar': _titleArCtrl.text.trim(),
        'description_en': _descEnCtrl.text.trim(),
        'description_ar': _descArCtrl.text.trim(),
        'start_date': _startDate.toIso8601String(),
        'end_date': _endDate.toIso8601String(),
        'category': _category,
        'venue_id': _venueId,
        'cover_image': _coverCtrl.text.trim().isEmpty ? null : _coverCtrl.text.trim(),
        'is_free': _isFree,
      });
      setState(() => _isSuccess = true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('$e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isSuccess) {
      return Scaffold(
        appBar: AppBar(title: const Text('إنشاء فعالية')),
        body: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.check_circle, size: 80, color: Colors.green),
              SizedBox(height: 16),
              Text('تم إنشاء الفعالية بنجاح!'),
              SizedBox(height: 8),
              Text('بإنتظار مراجعة المشرف', style: TextStyle(color: Colors.grey)),
            ],
          ),
        ),
      );
    }

    final venuesAsync = ref.watch(venuesProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('إنشاء فعالية جديدة')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _titleEnCtrl,
                decoration: const InputDecoration(labelText: 'عنوان الفعالية (إنجليزي)'),
                validator: (v) => v == null || v.trim().isEmpty ? 'مطلوب' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _titleArCtrl,
                decoration: const InputDecoration(labelText: 'عنوان الفعالية (عربي)'),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _descEnCtrl,
                maxLines: 4,
                decoration: const InputDecoration(labelText: 'الوصف (إنجليزي)'),
                validator: (v) => v == null || v.trim().isEmpty ? 'مطلوب' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _descArCtrl,
                maxLines: 4,
                decoration: const InputDecoration(labelText: 'الوصف (عربي)'),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: _category,
                decoration: const InputDecoration(labelText: 'التصنيف'),
                items: const [
                  DropdownMenuItem(value: 'concerts', child: Text('حفلات')),
                  DropdownMenuItem(value: 'sports', child: Text('رياضة')),
                  DropdownMenuItem(value: 'business', child: Text('أعمال')),
                  DropdownMenuItem(value: 'cultural', child: Text('ثقافة')),
                  DropdownMenuItem(value: 'food', child: Text('طعام')),
                  DropdownMenuItem(value: 'arts', child: Text('فن')),
                  DropdownMenuItem(value: 'family', child: Text('عائلي')),
                  DropdownMenuItem(value: 'nightlife', child: Text('حياة ليلية')),
                  DropdownMenuItem(value: 'other', child: Text('أخرى')),
                ],
                onChanged: (v) => setState(() => _category = v ?? 'other'),
              ),
              const SizedBox(height: 16),
              venuesAsync.when(
                loading: () => const SizedBox(height: 40, child: Center(child: CircularProgressIndicator(strokeWidth: 2))),
                error: (_) => const SizedBox.shrink(),
                data: (venues) => DropdownButtonFormField<int?>(
                  value: _venueId,
                  decoration: const InputDecoration(labelText: 'الموقع (اختياري)'),
                  items: [
                    const DropdownMenuItem(value: null, child: Text('اختر موقع')),
                    ...venues.map((v) => DropdownMenuItem(
                      value: v['id'] as int,
                      child: Text('${v['name_en']} - ${v['city'] ?? ''}'),
                    )),
                  ],
                  onChanged: (v) => setState(() => _venueId = v),
                ),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _coverCtrl,
                decoration: const InputDecoration(labelText: 'رابط صورة الغلاف (اختياري)'),
              ),
              const SizedBox(height: 12),
              CheckboxListTile(
                value: _isFree,
                onChanged: (v) => setState(() => _isFree = v ?? false),
                title: const Text('فعالية مجانية'),
                controlAffinity: ListTileControlAffinity.leading,
              ),
              ListTile(
                leading: const Icon(Icons.calendar_today),
                title: const Text('تاريخ البداية'),
                subtitle: Text('${_startDate.year}-${_startDate.month}-${_startDate.day}'),
                trailing: const Icon(Icons.edit),
                onTap: () async {
                  final date = await showDatePicker(
                    context: context,
                    initialDate: _startDate,
                    firstDate: DateTime.now(),
                    lastDate: DateTime.now().add(const Duration(days: 365)),
                  );
                  if (date != null) setState(() => _startDate = date);
                },
              ),
              ListTile(
                leading: const Icon(Icons.calendar_today),
                title: const Text('تاريخ النهاية'),
                subtitle: Text('${_endDate.year}-${_endDate.month}-${_endDate.day}'),
                trailing: const Icon(Icons.edit),
                onTap: () async {
                  final date = await showDatePicker(
                    context: context,
                    initialDate: _endDate,
                    firstDate: _startDate,
                    lastDate: DateTime.now().add(const Duration(days: 365)),
                  );
                  if (date != null) setState(() => _endDate = date);
                },
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _submit,
                child: _isLoading
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Text('إنشاء الفعالية'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
