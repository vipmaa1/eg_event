import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/event.dart';

final eventsProvider = FutureProvider.autoDispose<List<Event>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/events', queryParameters: {'upcoming': 'true'});
  final list = (res.data['data'] as List<dynamic>?)
          ?.map((e) => Event.fromJson(e as Map<String, dynamic>))
          .toList() ??
      [];
  return list;
});

final searchProvider =
    StateNotifierProvider<SearchNotifier, AsyncValue<List<Event>>>((ref) {
  return SearchNotifier(ref.read(apiClientProvider));
});

class SearchNotifier extends StateNotifier<AsyncValue<List<Event>>> {
  final ApiClient _api;

  SearchNotifier(this._api) : super(const AsyncValue.loading());

  Future<void> search(String query) async {
    if (query.isEmpty) {
      state = const AsyncValue.data([]);
      return;
    }
    state = const AsyncValue.loading();
    try {
      final res = await _api.get('/events', queryParameters: {'search': query});
      final list = (res.data['data'] as List<dynamic>?)
              ?.map((e) => Event.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [];
      state = AsyncValue.data(list);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }
}

final eventDetailProvider =
    FutureProvider.autoDispose.family<Event, int>((ref, id) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/events/$id');
  return Event.fromJson(res.data as Map<String, dynamic>);
});
