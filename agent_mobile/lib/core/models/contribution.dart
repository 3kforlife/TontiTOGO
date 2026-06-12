import 'package:json_annotation/json_annotation.dart';
import 'member.dart';
import 'tontine.dart';

part 'contribution.g.dart';

@JsonSerializable()
class Contribution {
  final int id;
  final String reference;
  final double amount;
  final double? latitude;
  final double? longitude;
  @JsonKey(name: 'settlement_status')
  final String? settlementStatus;
  @JsonKey(name: 'tontine_participant_id')
  final int tontineParticipantId;
  @JsonKey(name: 'tontine_participant')
  final TontineParticipant? tontineParticipant;
  @JsonKey(name: 'created_at')
  final String? createdAt;

  Contribution({
    required this.id,
    required this.reference,
    required this.amount,
    this.latitude,
    this.longitude,
    this.settlementStatus,
    required this.tontineParticipantId,
    this.tontineParticipant,
    this.createdAt,
  });

  factory Contribution.fromJson(Map<String, dynamic> json) =>
      _$ContributionFromJson(json);

  Map<String, dynamic> toJson() => _$ContributionToJson(this);
}

@JsonSerializable()
class TontineParticipant {
  final int id;
  final Member? member;
  final Tontine? tontine;
  @JsonKey(name: 'chosen_amount')
  final double? chosenAmount;

  TontineParticipant({
    required this.id,
    this.member,
    this.tontine,
    this.chosenAmount,
  });

  factory TontineParticipant.fromJson(Map<String, dynamic> json) =>
      _$TontineParticipantFromJson(json);

  Map<String, dynamic> toJson() => _$TontineParticipantToJson(this);
}
