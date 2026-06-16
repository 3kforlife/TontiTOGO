import 'package:json_annotation/json_annotation.dart';

part 'user.g.dart';

@JsonSerializable()
class User {
  final int id;
  @JsonKey(name: 'firstname')
  final String? firstName;
  @JsonKey(name: 'lastname')
  final String? lastName;
  final String phone;
  final String? email;
  final String role;
  @JsonKey(name: 'organization_id')
  final int? organizationId;
  final Organization? organization;

  User({
    required this.id,
    this.firstName,
    this.lastName,
    required this.phone,
    this.email,
    required this.role,
    this.organizationId,
    this.organization,
  });

  factory User.fromJson(Map<String, dynamic> json) =>
      _$UserFromJson(json);

  Map<String, dynamic> toJson() => _$UserToJson(this);
}

@JsonSerializable()
class Organization {
  final int id;
  final String name;

  Organization({
    required this.id,
    required this.name,
  });

  factory Organization.fromJson(Map<String, dynamic> json) =>
      _$OrganizationFromJson(json);

  Map<String, dynamic> toJson() => _$OrganizationToJson(this);
}
