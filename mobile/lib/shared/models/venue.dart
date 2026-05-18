class Venue {
  final int id;
  final String nameEn;
  final String? nameAr;
  final String slug;
  final String city;
  final String? district;
  final int? capacity;
  final String? coverImage;
  final List<String>? amenities;

  Venue({
    required this.id,
    required this.nameEn,
    this.nameAr,
    required this.slug,
    required this.city,
    this.district,
    this.capacity,
    this.coverImage,
    this.amenities,
  });

  factory Venue.fromJson(Map<String, dynamic> json) {
    return Venue(
      id: json['id'] as int,
      nameEn: json['name_en'] as String,
      nameAr: json['name_ar'] as String?,
      slug: json['slug'] as String,
      city: json['city'] as String,
      district: json['district'] as String?,
      capacity: json['capacity'] as int?,
      coverImage: json['cover_image'] as String?,
      amenities: (json['amenities'] as List<dynamic>?)
          ?.map((a) => a as String)
          .toList(),
    );
  }

  String get displayName => nameAr ?? nameEn;
}
