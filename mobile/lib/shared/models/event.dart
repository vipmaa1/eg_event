class Event {
  final int id;
  final String titleEn;
  final String? titleAr;
  final String slug;
  final String descriptionEn;
  final String? descriptionAr;
  final DateTime startDate;
  final DateTime endDate;
  final String category;
  final String? coverImage;
  final List<String>? tags;
  final String status;
  final bool hasTickets;
  final bool isFree;
  final int viewCount;
  final int attendingCount;
  final int saveCount;
  final String? venueName;
  final String? venueCity;
  final String? organizerName;
  final double? minPrice;

  Event({
    required this.id,
    required this.titleEn,
    this.titleAr,
    required this.slug,
    required this.descriptionEn,
    this.descriptionAr,
    required this.startDate,
    required this.endDate,
    required this.category,
    this.coverImage,
    this.tags,
    required this.status,
    required this.hasTickets,
    required this.isFree,
    required this.viewCount,
    required this.attendingCount,
    required this.saveCount,
    this.venueName,
    this.venueCity,
    this.organizerName,
    this.minPrice,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    final venue = json['venue'] as Map<String, dynamic>?;
    final organizer = json['organizer'] as Map<String, dynamic>?;
    double? minPrice;
    final ticketTypes = json['ticket_types'] as List<dynamic>?;
    if (ticketTypes != null && ticketTypes.isNotEmpty) {
      minPrice = (ticketTypes.map((t) => (t as Map)['price'] as num).reduce(
            (a, b) => a < b ? a : b,
          ) as num)
          .toDouble();
    }

    return Event(
      id: json['id'] as int,
      titleEn: json['title_en'] as String,
      titleAr: json['title_ar'] as String?,
      slug: json['slug'] as String,
      descriptionEn: json['description_en'] as String,
      descriptionAr: json['description_ar'] as String?,
      startDate: DateTime.parse(json['start_date'] as String),
      endDate: DateTime.parse(json['end_date'] as String),
      category: json['category'] as String,
      coverImage: json['cover_image'] as String?,
      tags: (json['tags'] as List<dynamic>?)?.map((t) => t as String).toList(),
      status: json['status'] as String? ?? 'published',
      hasTickets: json['has_tickets'] as bool? ?? false,
      isFree: json['is_free'] as bool? ?? false,
      viewCount: json['view_count'] as int? ?? 0,
      attendingCount: json['attending_count'] as int? ?? 0,
      saveCount: json['save_count'] as int? ?? 0,
      venueName: venue?['name_en'] as String?,
      venueCity: venue?['city'] as String?,
      organizerName: organizer?['name_en'] as String?,
      minPrice: minPrice,
    );
  }

  String get displayTitle => titleAr ?? titleEn;
  String get displayDescription => descriptionAr ?? descriptionEn;
}
