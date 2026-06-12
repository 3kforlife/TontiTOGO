<?php

/*
|--------------------------------------------------------------------------
| Messages HTTP de l'API — TontiTOGO
|--------------------------------------------------------------------------
|
| Centralisation des messages de réponse HTTP (succès, erreurs, accès).
| Utilisés dans les middlewares et les contrôleurs via __('http.clé').
|
*/

return [

    // -------------------------------------------------------
    // Authentification & Autorisation
    // -------------------------------------------------------
    'unauthenticated'          => 'Non authentifié. Veuillez vous connecter.',
    'unauthorized'             => 'Action non autorisée.',
    'forbidden_role_responsible' => 'Accès réservé aux responsables. Veuillez utiliser l\'application mobile.',
    'forbidden_role_agent'     => 'Accès réservé aux agents. Veuillez utiliser la plateforme web.',
    'account_suspended'        => 'Votre compte a été suspendu. Contactez votre responsable.',
    'account_suspended_admin'  => 'Votre compte a été suspendu. Contactez l\'administrateur.',

    // -------------------------------------------------------
    // Vérification email
    // -------------------------------------------------------
    'email_not_verified'       => 'Votre adresse e-mail n\'est pas encore vérifiée. Veuillez consulter votre boîte de réception.',
    'email_already_verified'   => 'Votre adresse e-mail est déjà vérifiée.',
    'email_verified'           => 'Adresse e-mail vérifiée avec succès. Vous pouvez maintenant accéder à toutes les fonctionnalités.',
    'email_verification_sent'  => 'Un nouvel e-mail de vérification vous a été envoyé.',

    // -------------------------------------------------------
    // Changement de mot de passe obligatoire (agent)
    // -------------------------------------------------------
    'must_change_password'     => 'Vous devez définir votre mot de passe avant de continuer.',

    // -------------------------------------------------------
    // Ressources
    // -------------------------------------------------------
    'not_found'                => 'La ressource demandée est introuvable.',
    'already_exists'           => 'Cette ressource existe déjà.',

    // -------------------------------------------------------
    // Opérations CRUD
    // -------------------------------------------------------
    'created'                  => 'Ressource créée avec succès.',
    'updated'                  => 'Ressource mise à jour avec succès.',
    'deleted'                  => 'Ressource supprimée avec succès.',
    'operation_success'        => 'Opération réussie.',

    // -------------------------------------------------------
    // Connexion / Déconnexion
    // -------------------------------------------------------
    'login_success'            => 'Connexion réussie.',
    'logout_success'           => 'Déconnexion réussie.',
    'login_failed'             => 'Identifiant ou mot de passe incorrect.',
    'password_changed'         => 'Mot de passe modifié avec succès.',
    'password_reset_success'   => 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.',

    // -------------------------------------------------------
    // Inscription & Compte
    // -------------------------------------------------------
    'register_success'         => 'Compte créé avec succès. Veuillez vérifier votre adresse e-mail pour activer votre compte.',
    'account_deleted'          => 'Votre compte et toutes les données associées ont été supprimés définitivement.',

    // -------------------------------------------------------
    // SMS
    // -------------------------------------------------------
    'otp_sent'                 => 'Si ce numéro est enregistré, vous recevrez un SMS avec votre code de réinitialisation.',
    'otp_expired'              => 'Le code de réinitialisation a expiré ou est invalide. Veuillez en demander un nouveau.',
    'otp_invalid'              => 'Le code de réinitialisation est incorrect.',
    'reminders_dispatched'     => 'Envoi des rappels de retard lancé en arrière-plan.',
    'password_reset_email_sent'=> 'Un lien de réinitialisation vous a été envoyé par e-mail.',
    'password_reset_throttled' => 'Trop de demandes. Veuillez patienter avant d\'en faire une nouvelle.',

    // -------------------------------------------------------
    // Métier TontiTOGO
    // -------------------------------------------------------
    'settlement_validated'     => 'Règlement validé avec succès.',
    'settlement_discrepancy'   => 'Règlement enregistré avec écart signalé.',
    'settlement_exists'        => 'Un règlement existe déjà pour cet agent à cette date.',
    'tontine_inactive'         => 'Cette tontine est inactive. La cotisation ne peut pas être enregistrée.',
    'tontine_wrong_org'        => 'Cette tontine n\'appartient pas à votre organisation.',
    'amount_below_minimum'     => 'Le montant est inférieur au minimum requis de :minimum FCFA.',
    'participant_exists'       => 'Ce membre est déjà inscrit à cette tontine.',
    'participant_add_inactive' => 'Impossible d\'ajouter un participant à une tontine inactive.',
    'chosen_below_minimum'     => 'Le montant choisi (:chosen FCFA) est inférieur au minimum de la tontine (:minimum FCFA).',
    'enroll_success'           => 'Membre inscrit à la tontine avec succès.',
    'contribution_sms_sent'    => 'Cotisation enregistrée avec succès. Le membre sera notifié par SMS.',
    'settings_updated'         => 'Paramètres mis à jour avec succès.',
    'profile_updated'          => 'Profil mis à jour avec succès.',
    'agent_not_found'          => 'Aucun compte agent trouvé pour ce numéro de téléphone.',
];
