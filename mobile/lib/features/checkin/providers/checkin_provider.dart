import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';

class CheckInResult {
  final bool success;
  final String message;
  final String? holderName;
  final String? ticketType;
  final String? eventTitle;

  CheckInResult({
    required this.success,
    required this.message,
    this.holderName,
    this.ticketType,
    this.eventTitle,
  });

  factory CheckInResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>?;
    return CheckInResult(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? '',
      holderName: data?['holder_name'] as String?,
      ticketType: data?['ticket_type'] as String?,
      eventTitle: data?['event_title'] as String?,
    );
  }
}

final checkinProvider = StateNotifierProvider<CheckinNotifier, AsyncValue<CheckInResult?>>((ref) {
  return CheckinNotifier(ref.read(apiClientProvider));
});

class CheckinNotifier extends StateNotifier<AsyncValue<CheckInResult?>> {
  final ApiClient _api;

  CheckinNotifier(this._api) : super(const AsyncValue.data(null));

  Future<void> scan(String ticketCode, int eventId) async {
    state = const AsyncValue.loading();
    try {
      final res = await _api.post('/check-in/scan', data: {
        'ticket_code': ticketCode,
        'event_id': eventId,
      });
      state = AsyncValue.data(
        CheckInResult.fromJson(res.data as Map<String, dynamic>),
      );
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  void reset() => state = const AsyncValue.data(null);
}
