import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/events_provider.dart';
import '../../auth/providers/auth_provider.dart';
import '../../auth/screens/login_screen.dart';
import 'event_detail_screen.dart';
import 'search_screen.dart';
import '../../../shared/widgets/event_card.dart';
import '../../../shared/widgets/loading.dart';
import '../../../shared/widgets/app_bottom_nav.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsync = ref.watch(eventsProvider);
    final authState = ref.watch(authStateProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('EventHub'),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const SearchScreen()),
            ),
          ),
          if (authState.status != AuthStatus.authenticated)
            IconButton(
              icon: const Icon(Icons.person_outline),
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
              ),
            )
          else
            IconButton(
              icon: const Icon(Icons.bookmark_border),
              onPressed: () {},
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(eventsProvider.future),
        child: eventsAsync.when(
          loading: () => const Center(child: AppLoading()),
          error: (e, _) => Center(child: Text('$e')),
          data: (events) => events.isEmpty
              ? const Center(child: Text('لا توجد فعاليات حالياً'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: events.length + 1,
                  itemBuilder: (_, i) {
                    if (i == 0) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'الفعاليات القادمة',
                              style:
                                  Theme.of(context).textTheme.titleLarge?.copyWith(
                                        fontWeight: FontWeight.bold,
                                      ),
                            ),
                            const SizedBox(height: 8),
                            SizedBox(
                              height: 40,
                              child: ListView(
                                scrollDirection: Axis.horizontal,
                                children: [
                                  _FilterChip('الكل', selected: true),
                                  _FilterChip('حفلات'),
                                  _FilterChip('رياضة'),
                                  _FilterChip('أعمال'),
                                  _FilterChip('ثقافة'),
                                  _FilterChip('عائلي'),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }
                    final event = events[i - 1];
                    return EventCard(
                      event: event,
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => EventDetailScreen(eventId: event.id),
                        ),
                      ),
                    );
                  },
                ),
        ),
      ),
      bottomNavigationBar: AppBottomNav(currentIndex: 0),
    );
  }
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool selected;
  const _FilterChip(this.label, {this.selected = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) {},
      ),
    );
  }
}
