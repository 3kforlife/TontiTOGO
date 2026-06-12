// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'member.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Member _$MemberFromJson(Map<String, dynamic> json) => Member(
  id: (json['id'] as num).toInt(),
  memberCode: json['member_code'] as String,
  notebookNumber: json['notebook_number'] as String?,
  firstname: json['firstname'] as String,
  lastname: json['lastname'] as String,
  fullName: json['full_name'] as String?,
  phone: json['phone'] as String,
  gender: json['gender'] as String?,
  genderLabel: json['gender_label'] as String?,
  address: json['address'] as String?,
  status: json['status'] as String?,
  statusLabel: json['status_label'] as String?,
  createdAt: json['created_at'] as String?,
);

Map<String, dynamic> _$MemberToJson(Member instance) => <String, dynamic>{
  'id': instance.id,
  'member_code': instance.memberCode,
  'notebook_number': instance.notebookNumber,
  'firstname': instance.firstname,
  'lastname': instance.lastname,
  'full_name': instance.fullName,
  'phone': instance.phone,
  'gender': instance.gender,
  'gender_label': instance.genderLabel,
  'address': instance.address,
  'status': instance.status,
  'status_label': instance.statusLabel,
  'created_at': instance.createdAt,
};
