import 'package:json_annotation/json_annotation.dart';

part 'tontine.g.dart';

@JsonSerializable()
class Tontine {
  final int id;
  final String name;
  final String frequency;
  @JsonKey(name: 'frequency_label')
  final String? frequencyLabel;
  @JsonKey(name: 'minimum_amount')
  final double minimumAmount;

  Tontine({
    required this.id,
    required this.name,
    required this.frequency,
    this.frequencyLabel,
    required this.minimumAmount,
  });

  factory Tontine.fromJson(Map<String, dynamic> json) => Tontine(
        id: (json['id'] as num?)?.toInt() ?? 0,
        name: json['name'] as String? ?? '',
        frequency: json['frequency'] as String? ?? '',
        frequencyLabel: json['frequency_label'] as String?,
        minimumAmount: (json['minimum_amount'] as num? ?? 0.0).toDouble(),
      );

  Map<String, dynamic> toJson() => _$TontineToJson(this);
}

@JsonSerializable()
class Participant {
  @JsonKey(name: 'participant_id')
  final int participantId;
  final Tontine tontine;
  @JsonKey(name: 'chosen_amount')
  final double chosenAmount;
  @JsonKey(name: 'minimum_amount')
  final double minimumAmount;

  Participant({
    required this.participantId,
    required this.tontine,
    required this.chosenAmount,
    required this.minimumAmount,
  });

  factory Participant.fromJson(Map<String, dynamic> json) => Participant(
        participantId: (json['participant_id'] as num?)?.toInt() ?? 0,
        tontine: Tontine.fromJson(json['tontine'] as Map<String, dynamic>),
        chosenAmount: (json['chosen_amount'] as num? ?? 0.0).toDouble(),
        minimumAmount: (json['minimum_amount'] as num? ?? 0.0).toDouble(),
      );

  Map<String, dynamic> toJson() => _$ParticipantToJson(this);
}
