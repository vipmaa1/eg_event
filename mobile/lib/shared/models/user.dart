class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? profilePhoto;
  final String? bio;
  final String status;
  final List<String> roles;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.profilePhoto,
    this.bio,
    required this.status,
    required this.roles,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      profilePhoto: json['profile_photo'] as String?,
      bio: json['bio'] as String?,
      status: json['status'] as String? ?? 'active',
      roles: (json['roles'] as List<dynamic>?)
              ?.map((r) => r is String ? r : (r as Map)['role'] as String)
              .toList() ??
          [],
    );
  }

  bool get isOrganizer => roles.contains('organizer');
  bool get isVenueOwner => roles.contains('venue_owner');
  bool get isAdmin => roles.contains('admin');
}

class AuthResponse {
  final User user;
  final String token;

  AuthResponse({required this.user, required this.token});

  factory AuthResponse.fromJson(Map<String, dynamic> json) {
    return AuthResponse(
      user: User.fromJson(json['user'] as Map<String, dynamic>),
      token: json['token'] as String,
    );
  }
}
