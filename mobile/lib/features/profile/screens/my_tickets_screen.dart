import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'package:intl/intl.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/ticket.dart';

final myTicketsProvider = FutureProvider.autoDispose<List<Ticket>>((ref) async {
  final api = ref.read(apiClientProvider);
  final res = await api.get('/profile/tickets');
  final list = (res.data['data'] as List<dynamic>?)
          ?.map((e) => Ticket.fromJson(e as Map<String, dynamic>))
          .toList() ??
      [];
  return list;
});

class MyTicketsScreen extends ConsumerWidget {
  const MyTicketsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ticketsAsync = ref.watch(myTicketsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('تذاكري')),
      body: ticketsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('$e')),
        data: (tickets) => tickets.isEmpty
            ? const Center(child: Text('لا توجد تذاكر'))
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: tickets.length,
                itemBuilder: (_, i) => _TicketCard(ticket: tickets[i]),
              ),
      ),
    );
  }
}

class _TicketCard extends StatelessWidget {
  final Ticket ticket;
  const _TicketCard({required this.ticket});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isValid = ticket.isValid;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(ticket.eventTitle ?? 'فعالية',
                          style: theme.textTheme.titleMedium
                              ?.copyWith(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text('${ticket.holderName}',
                          style: theme.textTheme.bodySmall),
                      Text('${ticket.currency} ${ticket.pricePaid}',
                          style: TextStyle(
                              color: theme.colorScheme.primary,
                              fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isValid ? Colors.green.shade100 : Colors.red.shade100,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    isValid ? 'صالح' : 'مستخدم',
                    style: TextStyle(
                      color: isValid ? Colors.green : Colors.red,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (ticket.qrCodeUrl != null)
              Center(
                child: QrImageView(
                  data: ticket.ticketCode,
                  version: QrVersions.auto,
                  size: 120,
                  backgroundColor: Colors.white,
                ),
              ),
            const SizedBox(height: 4),
            Text(ticket.ticketCode,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: Colors.grey,
                  fontFamily: 'monospace',
                )),
          ],
        ),
      ),
    );
  }
}
