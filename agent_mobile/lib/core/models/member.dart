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

  factory Member.fromJson(Map<String, dynamic> json) =>
      _$MemberFromJson(json);

  Map<String, dynamic> toJson() => _$MemberToJson(this);
}
