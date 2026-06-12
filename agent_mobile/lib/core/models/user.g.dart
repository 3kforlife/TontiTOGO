// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'user.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

User _$UserFromJson(Map<String, dynamic> json) => User(
  id: (json['id'] as num).toInt(),
  firstName: json['firstName'] as String?,
  lastName: json['lastName'] as String?,
  phone: json['phone'] as String,
  email: json['email'] as String?,
  role: json['role'] as String,
  organizationId: (json['organizationId'] as num?)?.toInt(),
  organization:
      json['organization'] == null
          ? null
          : Organization.fromJson(json['organization'] as Map<String, dynamic>),
);

Map<String, dynamic> _$UserToJson(User instance) => <String, dynamic>{
  'id': instance.id,
  'firstName': instance.firstName,
  'lastName': instance.lastName,
  'phone': instance.phone,
  'email': instance.email,
  'role': instance.role,
  'organizationId': instance.organizationId,
  'organization': instance.organization,
};

Organization _$OrganizationFromJson(Map<String, dynamic> json) =>
    Organization(id: (json['id'] as num).toInt(), name: json['name'] as String);

Map<String, dynamic> _$OrganizationToJson(Organization instance) =>
    <String, dynamic>{'id': instance.id, 'name': instance.name};
