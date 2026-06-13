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
      Contribution(
        id: (json['id'] as num?)?.toInt() ?? 0,
        reference: json['reference'] as String? ?? '',
        amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
        latitude: (json['latitude'] as num?)?.toDouble(),
        longitude: (json['longitude'] as num?)?.toDouble(),
        settlementStatus: json['settlement_status'] as String?,
        tontineParticipantId: (json['tontine_participant_id'] as num?)?.toInt() ?? 0,
        tontineParticipant: json['tontine_participant'] == null
            ? null
            : TontineParticipant.fromJson(
              json['tontine_participant'] as Map<String, dynamic>,
            ),
        createdAt: json['created_at'] as String?,
      );

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
      TontineParticipant(
        id: (json['id'] as num?)?.toInt() ?? 0,
        member: json['member'] == null
            ? null
            : Member.fromJson(json['member'] as Map<String, dynamic>),
        tontine: json['tontine'] == null
            ? null
            : Tontine.fromJson(json['tontine'] as Map<String, dynamic>),
        chosenAmount: (json['chosen_amount'] as num?)?.toDouble(),
      );

  Map<String, dynamic> toJson() => _$TontineParticipantToJson(this);
}
