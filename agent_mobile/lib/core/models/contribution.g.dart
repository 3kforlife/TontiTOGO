// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'contribution.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Contribution _$ContributionFromJson(Map<String, dynamic> json) => Contribution(
  id: (json['id'] as num).toInt(),
  reference: json['reference'] as String,
  amount: (json['amount'] as num).toDouble(),
  latitude: (json['latitude'] as num?)?.toDouble(),
  longitude: (json['longitude'] as num?)?.toDouble(),
  settlementStatus: json['settlement_status'] as String?,
  tontineParticipantId: (json['tontine_participant_id'] as num).toInt(),
  tontineParticipant:
      json['tontine_participant'] == null
          ? null
          : TontineParticipant.fromJson(
            json['tontine_participant'] as Map<String, dynamic>,
          ),
  createdAt: json['created_at'] as String?,
);

Map<String, dynamic> _$ContributionToJson(Contribution instance) =>
    <String, dynamic>{
      'id': instance.id,
      'reference': instance.reference,
      'amount': instance.amount,
      'latitude': instance.latitude,
      'longitude': instance.longitude,
      'settlement_status': instance.settlementStatus,
      'tontine_participant_id': instance.tontineParticipantId,
      'tontine_participant': instance.tontineParticipant,
      'created_at': instance.createdAt,
    };

TontineParticipant _$TontineParticipantFromJson(Map<String, dynamic> json) =>
    TontineParticipant(
      id: (json['id'] as num).toInt(),
      member:
          json['member'] == null
              ? null
              : Member.fromJson(json['member'] as Map<String, dynamic>),
      tontine:
          json['tontine'] == null
              ? null
              : Tontine.fromJson(json['tontine'] as Map<String, dynamic>),
      chosenAmount: (json['chosen_amount'] as num?)?.toDouble(),
    );

Map<String, dynamic> _$TontineParticipantToJson(TontineParticipant instance) =>
    <String, dynamic>{
      'id': instance.id,
      'member': instance.member,
      'tontine': instance.tontine,
      'chosen_amount': instance.chosenAmount,
    };
