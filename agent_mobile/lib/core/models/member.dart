import 'package:json_annotation/json_annotation.dart';

part 'member.g.dart';

@JsonSerializable()
class Member {
  final int id;
  @JsonKey(name: 'member_code')
  final String memberCode;
  @JsonKey(name: 'notebook_number')
  final String? notebookNumber;
  final String firstname;
  final String lastname;
  @JsonKey(name: 'full_name')
  final String? fullName;
  final String phone;
  final String? gender;
  @JsonKey(name: 'gender_label')
  final String? genderLabel;
  final String? address;
  final String? status;
  @JsonKey(name: 'status_label')
  final String? statusLabel;
  @JsonKey(name: 'created_at')
  final String? createdAt;

  Member({
    required this.id,
    required this.memberCode,
    this.notebookNumber,
    required this.firstname,
    required this.lastname,
    this.fullName,
    required this.phone,
    this.gender,
    this.genderLabel,
    this.address,
    this.status,
    this.statusLabel,
    this.createdAt,
  });

  String get displayName => fullName ?? '$firstname $lastname';

  factory Member.fromJson(Map<String, dynamic> json) => Member(
        id: (json['id'] as num?)?.toInt() ?? 0,
        memberCode: json['member_code'] as String? ?? '',
        notebookNumber: json['notebook_number'] as String?,
        firstname: json['firstname'] as String? ?? '',
        lastname: json['lastname'] as String? ?? '',
        fullName: json['full_name'] as String?,
        phone: json['phone'] as String? ?? '',
        gender: json['gender'] as String?,
        genderLabel: json['gender_label'] as String?,
        address: json['address'] as String?,
        status: json['status'] as String?,
        statusLabel: json['status_label'] as String?,
        createdAt: json['created_at'] as String?,
      );

  Map<String, dynamic> toJson() => _$MemberToJson(this);
}
