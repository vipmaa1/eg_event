import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/events_provider.dart';
import '../../../shared/models/event.dart';
import '../../../shared/widgets/loading.dart';
import '../../booking/screens/checkout_screen.dart';

class EventDetailScreen extends ConsumerWidget {
  final int eventId;
  const EventDetailScreen({super.key, required this.eventId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventAsync = ref.watch(eventDetailProvider(eventId));
    final theme = Theme.of(context);

    return Scaffold(
      body: eventAsync.when(
        loading: () => const Center(child: AppLoading()),
        error: (e, _) => Center(child: Text('$e')),
        data: (event) => CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: 250,
              pinned: true,
              flexibleSpace: FlexibleSpaceBar(
                background: event.coverImage != null
                    ? CachedNetworkImage(
                        imageUrl: event.coverImage!,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Container(color: Colors.grey[200]),
                      )
                    : Container(
                        color: theme.colorScheme.primaryContainer,
                        child: const Icon(Icons.event, size: 80),
                      ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: theme.colorScheme.primaryContainer,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            event.category,
                            style: TextStyle(
                              color: theme.colorScheme.onPrimaryContainer,
                              fontSize: 12,
                            ),
                          ),
                        ),
                        if (event.isFree)
                          Padding(
                            padding: const EdgeInsets.only(right: 8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.green.shade100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text('مجاني',
                                  style: TextStyle(
                                      color: Colors.green, fontSize: 12)),
                            ),
                          ),
                        const Spacer(),
                        Icon(Icons.remove_red_eye_outlined,
                            size: 16, color: Colors.grey),
                        const SizedBox(width: 4),
                        Text('${event.viewCount}',
                            style: const TextStyle(color: Colors.grey)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      event.displayTitle,
                      style: theme.textTheme.headlineSmall
                          ?.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.calendar_today, size: 16),
                        const SizedBox(width: 4),
                        Text(
                          DateFormat('EEEE, MMMM d, yyyy', 'ar')
                              .format(event.startDate),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.access_time, size: 16),
                        const SizedBox(width: 4),
                        Text(
                          '${DateFormat('HH:mm').format(event.startDate)} - ${DateFormat('HH:mm').format(event.endDate)}',
                        ),
                      ],
                    ),
                    if (event.venueName != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.location_on, size: 16),
                          const SizedBox(width: 4),
                          Text(event.venueName!),
                        ],
                      ),
                    ],
                    if (event.organizerName != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.person, size: 16),
                          const SizedBox(width: 4),
                          Text('منظم: ${event.organizerName}'),
                        ],
                      ),
                    ],
                    const Divider(height: 24),
                    Text(
                      'عن الفعالية',
                      style: theme.textTheme.titleMedium
                          ?.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      event.displayDescription,
                      style: theme.textTheme.bodyLarge,
                    ),
                    const SizedBox(height: 24),
                    if (!event.isFree && event.hasTickets) ...[
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () => Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) =>
                                  CheckoutScreen(event: event),
                            ),
                          ),
                          child: Text(
                            event.minPrice != null
                                ? 'احجز الآن - EGP ${NumberFormat('#,##0').format(event.minPrice)}'
                                : 'احجز الآن',
                          ),
                        ),
                      ),
                    ] else if (event.isFree) ...[
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () {},
                          child: const Text('سجل حضورك مجاناً'),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
