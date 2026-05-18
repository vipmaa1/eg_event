class Ticket {
  final int id;
  final String ticketCode;
  final int orderId;
  final int eventId;
  final String? eventTitle;
  final String holderName;
  final String holderEmail;
  final double pricePaid;
  final String currency;
  final String? qrCodeUrl;
  final String status;
  final bool checkedIn;
  final DateTime? checkedInAt;

  Ticket({
    required this.id,
    required this.ticketCode,
    required this.orderId,
    required this.eventId,
    this.eventTitle,
    required this.holderName,
    required this.holderEmail,
    required this.pricePaid,
    required this.currency,
    this.qrCodeUrl,
    required this.status,
    required this.checkedIn,
    this.checkedInAt,
  });

  factory Ticket.fromJson(Map<String, dynamic> json) {
    final event = json['event'] as Map<String, dynamic>?;
    return Ticket(
      id: json['id'] as int,
      ticketCode: json['ticket_code'] as String,
      orderId: json['order_id'] as int,
      eventId: json['event_id'] as int,
      eventTitle: event?['title_en'] as String?,
      holderName: json['holder_name'] as String,
      holderEmail: json['holder_email'] as String,
      pricePaid: (json['price_paid'] as num).toDouble(),
      currency: json['currency'] as String? ?? 'EGP',
      qrCodeUrl: json['qr_code_url'] as String?,
      status: json['status'] as String,
      checkedIn: json['checked_in'] as bool? ?? false,
      checkedInAt: json['checked_in_at'] != null
          ? DateTime.parse(json['checked_in_at'] as String)
          : null,
    );
  }

  bool get isValid => status == 'valid' && !checkedIn;
}
