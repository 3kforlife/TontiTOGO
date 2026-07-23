<?php

/*
|--------------------------------------------------------------------------
| Lignes de langue pour la validation — TontiTOGO
|--------------------------------------------------------------------------
|
| Centralisation ABSOLUE de tous les messages de validation de l'API.
| Aucune chaîne d'erreur ne doit être codée en dur dans les contrôleurs
| ou les Form Requests. Tout passe par ce fichier.
|
*/

return [

    // -------------------------------------------------------
    // Règles génériques Laravel (traduites en français)
    // -------------------------------------------------------

    'accepted'             => 'Le champ :attribute doit être accepté.',
    'accepted_if'          => 'Le champ :attribute doit être accepté quand :other vaut :value.',
    'active_url'           => 'Le champ :attribute n\'est pas une URL valide.',
    'after'                => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal'       => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha'                => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash'           => 'Le champ :attribute ne doit contenir que des lettres, chiffres et tirets.',
    'alpha_num'            => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array'                => 'Le champ :attribute doit être un tableau.',
    'ascii'                => 'Le champ :attribute ne doit contenir que des caractères alphanumériques ASCII.',
    'before'               => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal'      => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between'              => [
        'array'   => 'Le tableau :attribute doit contenir entre :min et :max éléments.',
        'file'    => 'Le fichier :attribute doit peser entre :min et :max kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être comprise entre :min et :max.',
        'string'  => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password'     => 'Le mot de passe actuel est incorrect.',
    'date'                 => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals'          => 'Le champ :attribute doit être une date égale à :date.',
    'date_format'          => 'Le champ :attribute ne correspond pas au format :format.',
    'decimal'              => 'Le champ :attribute doit avoir :decimal décimales.',
    'declined'             => 'Le champ :attribute doit être refusé.',
    'different'            => 'Les champs :attribute et :other doivent être différents.',
    'digits'               => 'Le champ :attribute doit contenir exactement :digits chiffres.',
    'digits_between'       => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions'           => 'Les dimensions de l\'image :attribute ne sont pas conformes.',
    'distinct'             => 'Le champ :attribute contient une valeur en double.',
    'doesnt_end_with'      => 'Le champ :attribute ne doit pas se terminer par : :values.',
    'doesnt_start_with'    => 'Le champ :attribute ne doit pas commencer par : :values.',
    'email'                => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with'            => 'Le champ :attribute doit se terminer par : :values.',
    'enum'                 => 'La valeur sélectionnée pour :attribute est invalide.',
    'exists'               => 'La valeur sélectionnée pour :attribute est introuvable.',
    'extensions'           => 'Le champ :attribute doit avoir l\'une des extensions suivantes : :values.',
    'file'                 => 'Le champ :attribute doit être un fichier.',
    'filled'               => 'Le champ :attribute doit avoir une valeur.',
    'gt'                   => [
        'array'   => 'Le tableau :attribute doit contenir plus de :value éléments.',
        'file'    => 'Le fichier :attribute doit peser plus de :value kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être supérieure à :value.',
        'string'  => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte'                  => [
        'array'   => 'Le tableau :attribute doit contenir au moins :value éléments.',
        'file'    => 'Le fichier :attribute doit peser au moins :value kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être supérieure ou égale à :value.',
        'string'  => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],
    'hex_color'            => 'Le champ :attribute doit être une couleur hexadécimale valide.',
    'image'                => 'Le champ :attribute doit être une image.',
    'in'                   => 'La valeur sélectionnée pour :attribute est invalide.',
    'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'              => 'Le champ :attribute doit être un entier.',
    'ip'                   => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4'                 => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6'                 => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json'                 => 'Le champ :attribute doit être une chaîne JSON valide.',
    'lowercase'            => 'Le champ :attribute doit être en minuscules.',
    'lt'                   => [
        'array'   => 'Le tableau :attribute doit contenir moins de :value éléments.',
        'file'    => 'Le fichier :attribute doit peser moins de :value kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être inférieure à :value.',
        'string'  => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],
    'lte'                  => [
        'array'   => 'Le tableau :attribute ne doit pas contenir plus de :value éléments.',
        'file'    => 'Le fichier :attribute doit peser au plus :value kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être inférieure ou égale à :value.',
        'string'  => 'Le champ :attribute doit contenir au plus :value caractères.',
    ],
    'mac_address'          => 'Le champ :attribute doit être une adresse MAC valide.',
    'max'                  => [
        'array'   => 'Le tableau :attribute ne doit pas contenir plus de :max éléments.',
        'file'    => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
        'numeric' => 'La valeur de :attribute ne doit pas dépasser :max.',
        'string'  => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'max_digits'           => 'Le champ :attribute ne doit pas avoir plus de :max chiffres.',
    'mimes'                => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes'            => 'Le champ :attribute doit être un fichier de type : :values.',
    'min'                  => [
        'array'   => 'Le tableau :attribute doit contenir au moins :min éléments.',
        'file'    => 'Le fichier :attribute doit peser au moins :min kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être d\'au moins :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'min_digits'           => 'Le champ :attribute doit avoir au moins :min chiffres.',
    'missing'              => 'Le champ :attribute doit être absent.',
    'missing_if'           => 'Le champ :attribute doit être absent quand :other vaut :value.',
    'missing_unless'       => 'Le champ :attribute doit être absent sauf si :other vaut :value.',
    'missing_with'         => 'Le champ :attribute doit être absent quand :values est présent.',
    'missing_with_all'     => 'Le champ :attribute doit être absent quand :values sont présents.',
    'multiple_of'          => 'La valeur de :attribute doit être un multiple de :value.',
    'not_in'               => 'La valeur sélectionnée pour :attribute est invalide.',
    'not_regex'            => 'Le format du champ :attribute est invalide.',
    'numeric'              => 'Le champ :attribute doit être un nombre.',
    'password'             => [
        'letters'       => 'Le :attribute doit contenir au moins une lettre.',
        'mixed'         => 'Le :attribute doit contenir au moins une majuscule (A-Z) et une minuscule (a-z).',
        'numbers'       => 'Le :attribute doit contenir au moins un chiffre (0-9).',
        'symbols'       => 'Le :attribute doit contenir au moins un caractère spécial ( @ $ ! % * # ? & _ - + = ^ ~ ).',
        'uncompromised' => 'Ce :attribute est apparu dans une fuite de données. Veuillez en choisir un autre.',
    ],
    'present'              => 'Le champ :attribute doit être présent.',
    'present_if'           => 'Le champ :attribute doit être présent quand :other vaut :value.',
    'present_unless'       => 'Le champ :attribute doit être présent sauf si :other vaut :value.',
    'present_with'         => 'Le champ :attribute doit être présent quand :values est présent.',
    'present_with_all'     => 'Le champ :attribute doit être présent quand :values sont présents.',
    'prohibited'           => 'Le champ :attribute est interdit.',
    'prohibited_if'        => 'Le champ :attribute est interdit quand :other vaut :value.',
    'prohibited_unless'    => 'Le champ :attribute est interdit à moins que :other soit dans :values.',
    'prohibits'            => 'Le champ :attribute interdit la présence de :other.',
    'regex'                => 'Le format du champ :attribute est invalide.',
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_array_keys'  => 'Le champ :attribute doit contenir des entrées pour : :values.',
    'required_if'          => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_if_accepted' => 'Le champ :attribute est obligatoire quand :other est accepté.',
    'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with'        => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_with_all'    => 'Le champ :attribute est obligatoire quand :values sont présents.',
    'required_without'     => 'Le champ :attribute est obligatoire quand :values est absent.',
    'required_without_all' => 'Le champ :attribute est obligatoire quand aucun de :values n\'est présent.',
    'same'                 => 'Les champs :attribute et :other doivent être identiques.',
    'size'                 => [
        'array'   => 'Le tableau :attribute doit contenir :size éléments.',
        'file'    => 'Le fichier :attribute doit peser :size kilo-octets.',
        'numeric' => 'La valeur de :attribute doit être :size.',
        'string'  => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with'          => 'Le champ :attribute doit commencer par une des valeurs suivantes : :values.',
    'string'               => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone'             => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique'               => 'Cette valeur est déjà utilisée pour :attribute.',
    'uploaded'             => 'Le téléversement du fichier :attribute a échoué.',
    'uppercase'            => 'Le champ :attribute doit être en majuscules.',
    'url'                  => 'Le champ :attribute doit être une URL valide.',
    'ulid'                 => 'Le champ :attribute doit être un ULID valide.',
    'uuid'                 => 'Le champ :attribute doit être un UUID valide.',

    // -------------------------------------------------------
    // Règles personnalisées par champ (custom)
    // Syntaxe : 'champ.règle' => 'message'
    // -------------------------------------------------------

    'custom' => [

        // Organisation
        'organization_name' => [
            'required' => 'Le nom de l\'organisation est obligatoire.',
            'min'      => 'Le nom de l\'organisation doit contenir au moins 3 caractères.',
            'max'      => 'Le nom de l\'organisation ne doit pas dépasser 50 caractères.',
            'regex'    => 'Le nom de l\'organisation ne peut contenir que des lettres, espaces, apostrophes ou tirets (ex: Tontine Solidarité, Koffi d\'Almeida).',
        ],

        // Identité
        'firstname' => [
            'required' => 'Le prénom est obligatoire.',
            'min'      => 'Le prénom doit contenir au moins 3 caractères.',
            'max'      => 'Le prénom ne doit pas dépasser 50 caractères.',
            'regex'    => 'Le prénom ne peut contenir que des lettres, espaces, apostrophes ou tirets (ex: Abalo-Koffi, Koffi d\'Almeida).',
        ],
        'lastname' => [
            'required' => 'Le nom de famille est obligatoire.',
            'min'      => 'Le nom de famille doit contenir au moins 3 caractères.',
            'max'      => 'Le nom de famille ne doit pas dépasser 50 caractères.',
            'regex'    => 'Le nom de famille ne peut contenir que des lettres, espaces, apostrophes ou tirets.',
        ],

        // Contact
        'phone' => [
            'required' => 'Le numéro de téléphone est obligatoire.',
            'unique'   => 'Ce numéro de téléphone est déjà utilisé.',
        ],
        'email' => [
            'required' => 'L\'adresse e-mail est obligatoire.',
            'email'    => 'L\'adresse e-mail n\'est pas valide.',
            'unique'   => 'Cette adresse e-mail est déjà utilisée.',
        ],

        // Mot de passe
        'password' => [
            'required'  => 'Le mot de passe est obligatoire.',
            'confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        ],
        'current_password' => [
            'required'         => 'Le mot de passe actuel est obligatoire.',
            'current_password' => 'Le mot de passe actuel est incorrect.',
        ],

        // Photo
        'avatar' => [
            'required' => 'La photo de profil est obligatoire.',
            'image'    => 'Le fichier doit être une image.',
            'mimes'    => 'La photo doit être au format JPEG, JPG, PNG ou WEBP.',
            'max'      => 'La photo ne doit pas dépasser 2 Mo.',
        ],

        // Membre
        'notebook_number' => [
            'required' => 'Le numéro de carnet est obligatoire.',
            'min'      => 'Le numéro de carnet doit contenir au moins 3 caractères.',
            'max'      => 'Le numéro de carnet ne doit pas dépasser 50 caractères.',
            'regex'    => 'Le numéro de carnet ne peut contenir que des lettres, chiffres, espaces et tirets.',
            'unique'   => 'Ce numéro de carnet est déjà attribué à un autre membre.',
        ],
        'gender' => [
            'required' => 'Le genre est obligatoire.',
            'in'       => 'Le genre doit être M (Masculin) ou F (Féminin).',
        ],
        'address' => [
            'min'   => 'L\'adresse doit contenir au moins 3 caractères.',
            'max'   => 'L\'adresse ne doit pas dépasser 150 caractères.',
            'regex' => 'L\'adresse ne peut contenir que des lettres, chiffres, espaces, tirets, virgules et points (ex: Quartier Adidogomé, Rue 145).',
        ],
        'status' => [
            'required' => 'Le statut est obligatoire.',
            'in'       => 'Le statut sélectionné n\'est pas valide.',
        ],

        // Tontine
        'name' => [
            'required' => 'Le nom de la tontine est obligatoire.',
            'min'      => 'Le nom de la tontine doit contenir au moins 3 caractères.',
            'max'      => 'Le nom de la tontine ne doit pas dépasser 50 caractères.',
            'regex'    => 'Le nom de la tontine ne peut contenir que des lettres, espaces, apostrophes ou tirets.',
        ],
        'minimum_amount' => [
            'required' => 'Le montant minimum de cotisation est obligatoire.',
            'numeric'  => 'Le montant minimum doit être un nombre.',
            'min'      => 'Le montant minimum doit être d\'au moins 100 FCFA.',
        ],
        'frequency' => [
            'required' => 'La fréquence de cotisation est obligatoire.',
            'in'       => 'La fréquence doit être : daily (journalière), weekly (hebdomadaire) ou monthly (mensuelle).',
        ],
        'start_date' => [
            'required'       => 'La date de début est obligatoire.',
            'date'           => 'La date de début n\'est pas valide.',
            'after_or_equal' => 'La date de début ne peut pas être dans le passé.',
        ],
        'end_date' => [
            'date'  => 'La date de fin n\'est pas valide.',
            'after' => 'La date de fin doit être postérieure à la date de début.',
        ],
        'chosen_amount' => [
            'required' => 'Le montant choisi est obligatoire.',
            'numeric'  => 'Le montant choisi doit être un nombre.',
            'min'      => 'Le montant choisi doit être supérieur au montant minimum de la tontine.',
        ],
        'joined_at' => [
            'required'        => 'La date d\'adhésion est obligatoire.',
            'date'            => 'La date d\'adhésion n\'est pas valide.',
            'after_or_equal'  => 'La date d\'adhésion ne peut pas être antérieure à la date de début de la tontine.',
            'before_or_equal' => 'La date d\'adhésion ne peut pas être dans le futur.',
        ],

        // Cotisation
        'amount' => [
            'required' => 'Le montant est obligatoire.',
            'numeric'  => 'Le montant doit être un nombre.',
            'min'      => 'Le montant doit être positif.',
        ],
        'latitude' => [
            'numeric'  => 'La latitude doit être un nombre.',
            'between'  => 'La latitude doit être comprise entre -90 et 90.',
        ],
        'longitude' => [
            'numeric'  => 'La longitude doit être un nombre.',
            'between'  => 'La longitude doit être comprise entre -180 et 180.',
        ],
        'tontine_participant_id' => [
            'required' => 'La participation à la tontine est obligatoire.',
            'exists'   => 'Cette participation à la tontine est introuvable.',
        ],

        // Règlement
        'agent_id' => [
            'required' => 'L\'agent est obligatoire.',
            'exists'   => 'Cet agent n\'existe pas dans le système.',
        ],
        'date_settled' => [
            'required'        => 'La date de règlement est obligatoire.',
            'date'            => 'La date de règlement n\'est pas valide.',
            'before_or_equal' => 'La date de règlement ne peut pas être dans le futur.',
        ],
        'received_amount' => [
            'required' => 'Le montant reçu est obligatoire.',
            'numeric'  => 'Le montant reçu doit être un nombre.',
            'min'      => 'Le montant reçu ne peut pas être négatif.',
        ],
        'notes' => [
            'min' => 'Les notes doivent contenir au moins 3 caractères.',
            'max' => 'Les notes ne doivent pas dépasser 500 caractères.',
        ],

        // Auth agent
        'login' => [
            'required' => 'L\'identifiant (email ou téléphone) est obligatoire.',
        ],
        'otp' => [
            'required' => 'Le code de réinitialisation est obligatoire.',
            'digits'   => 'Le code doit contenir exactement 6 chiffres.',
        ],
        'query' => [
            'required' => 'Le terme de recherche est obligatoire.',
            'min'      => 'La recherche doit contenir au moins 2 caractères.',
        ],

        // Paramètres SMS
        'sms_template_confirmation' => [
            'max' => 'Le modèle de SMS de confirmation ne doit pas dépasser 500 caractères.',
        ],
        'sms_template_reminder' => [
            'max' => 'Le modèle de SMS de rappel ne doit pas dépasser 500 caractères.',
        ],
        'sms_reminder_time' => [
            'regex' => 'L\'heure de rappel doit être au format HH:MM (ex: 17:30).',
        ],
    ],

    // -------------------------------------------------------
    // Noms lisibles des attributs (affichés dans :attribute)
    // -------------------------------------------------------

    'attributes' => [
        'organization_name'      => 'nom de l\'organisation',
        'firstname'              => 'prénom',
        'lastname'               => 'nom de famille',
        'phone'                  => 'numéro de téléphone',
        'email'                  => 'adresse e-mail',
        'password'               => 'mot de passe',
        'current_password'       => 'mot de passe actuel',
        'password_confirmation'  => 'confirmation du mot de passe',
        'avatar'                 => 'photo de profil',
        'notebook_number'        => 'numéro de carnet',
        'member_code'            => 'code membre',
        'gender'                 => 'genre',
        'address'                => 'adresse',
        'status'                 => 'statut',
        'name'                   => 'nom',
        'minimum_amount'         => 'montant minimum',
        'frequency'              => 'fréquence',
        'start_date'             => 'date de début',
        'end_date'               => 'date de fin',
        'chosen_amount'          => 'montant choisi',
        'joined_at'              => 'date d\'adhésion',
        'amount'                 => 'montant',
        'latitude'               => 'latitude',
        'longitude'              => 'longitude',
        'agent_id'               => 'agent',
        'member_id'              => 'membre',
        'tontine_id'             => 'tontine',
        'date_settled'           => 'date de règlement',
        'received_amount'        => 'montant reçu',
        'notes'                  => 'notes',
        'tontine_participant_id' => 'participation à la tontine',
        'login'                  => 'identifiant',
        'query'                  => 'terme de recherche',
        'otp'                    => 'code de réinitialisation',
        'token'                  => 'token de réinitialisation',
        'sms_template_confirmation' => 'modèle SMS de confirmation',
        'sms_template_reminder'     => 'modèle SMS de rappel',
        'sms_reminder_time'         => 'heure d\'envoi des rappels',
    ],
];
