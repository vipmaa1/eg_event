class Organizer {
  final int id;
  final int userId;
  final String nameEn;
  final String? nameAr;
  final String slug;
  final String? bioEn;
  final String? bioAr;
  final String? logo;
  final bool verified;
  final String status;
  final int followerCount;

  Organizer({
    required this.id,
    required this.userId,
    required this.nameEn,
    this.nameAr,
    required this.slug,
    this.bioEn,
    this.bioAr,
    this.logo,
    required this.verified,
    required this.status,
    this.followerCount = 0,
  });

  factory Organizer.fromJson(Map<String, dynamic> json) {
    return Organizer(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      nameEn: json['name_en'] as String,
      nameAr: json['name_ar'] as String?,
      slug: json['slug'] as String,
      bioEn: json['bio_en'] as String?,
      bioAr: json['bio_ar'] as String?,
      logo: json['logo'] as String?,
      verified: json['verified'] as bool? ?? false,
      status: json['status'] as String? ?? 'active',
      followerCount: json['followers_count'] as int? ?? 0,
    );
  }

  String get displayName => nameAr ?? nameEn;
  String get displayBio => bioAr ?? bioEn ?? '';
}
