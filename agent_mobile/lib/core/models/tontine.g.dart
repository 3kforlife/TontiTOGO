// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'tontine.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Tontine _$TontineFromJson(Map<String, dynamic> json) => Tontine(
  id: (json['id'] as num).toInt(),
  name: json['name'] as String,
  frequency: json['frequency'] as String,
  frequencyLabel: json['frequency_label'] as String?,
  minimumAmount: (json['minimum_amount'] as num).toDouble(),
);

Map<String, dynamic> _$TontineToJson(Tontine instance) => <String, dynamic>{
  'id': instance.id,
  'name': instance.name,
  'frequency': instance.frequency,
  'frequency_label': instance.frequencyLabel,
  'minimum_amount': instance.minimumAmount,
};

Participant _$ParticipantFromJson(Map<String, dynamic> json) => Participant(
  participantId: (json['participant_id'] as num).toInt(),
  tontine: Tontine.fromJson(json['tontine'] as Map<String, dynamic>),
  chosenAmount: (json['chosen_amount'] as num).toDouble(),
  minimumAmount: (json['minimum_amount'] as num).toDouble(),
);

Map<String, dynamic> _$ParticipantToJson(Participant instance) =>
    <String, dynamic>{
      'participant_id': instance.participantId,
      'tontine': instance.tontine,
      'chosen_amount': instance.chosenAmount,
      'minimum_amount': instance.minimumAmount,
    };
