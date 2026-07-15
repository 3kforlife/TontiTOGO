<?php

use App\Http\Controllers\Api\Agent\AgentAuthController;
use App\Http\Controllers\Api\Agent\AgentPasswordResetController;
use App\Http\Controllers\Api\Agent\ContributionController as AgentContributionController;
use App\Http\Controllers\Api\Agent\MobileMemberController;
use App\Http\Controllers\Api\Responsible\AgentManagementController;
use App\Http\Controllers\Api\Responsible\ContributionController as ResponsibleContributionController;
use App\Http\Controllers\Api\Responsible\DashboardController;
use App\Http\Controllers\Api\Responsible\EmailVerificationController;
use App\Http\Controllers\Api\Responsible\MapController;
use App\Http\Controllers\Api\Responsible\MemberManagementController;
use App\Http\Controllers\Api\Responsible\ResponsibleAuthController;
use App\Http\Controllers\Api\Responsible\ResponsiblePasswordController;
use App\Http\Controllers\Api\Responsible\SettingController;
use App\Http\Controllers\Api\Responsible\SettlementController;
use App\Http\Controllers\Api\Responsible\SmsController;
use App\Http\Controllers\Api\Responsible\TontineManagementController;
use Illuminate\Support\Facades\Route;


// =========================================================================
// DOMAINE RESPONSABLE — Plateforme Web Vue.js
// =========================================================================

Route::prefix('responsible')->name('responsible.')->group(function () {

    // --- Routes publiques ---
    Route::post('/register', [ResponsibleAuthController::class, 'register'])->name('register');
    Route::post('/login',    [ResponsibleAuthController::class, 'login'])->name('login');
    Route::post('/password/forgot', [ResponsiblePasswordController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/password/reset',  [ResponsiblePasswordController::class, 'resetPassword'])->name('password.reset');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    // --- Routes protégées (Sanctum + rôle responsible) ---
    Route::middleware(['auth:sanctum', 'role:responsible'])->group(function () {

        // Profil & Compte
        Route::get('/me',         [ResponsibleAuthController::class, 'me'])->name('me');
        Route::put('/profile',    [ResponsibleAuthController::class, 'updateProfile'])->name('profile.update');
        Route::delete('/account', [ResponsibleAuthController::class, 'deleteAccount'])->name('account.delete');
        Route::post('/logout',    [ResponsibleAuthController::class, 'logout'])->name('logout');

        // Vérification d'email (disponible mais non bloquante)
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail'])
            ->name('verification.send');
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware('signed')
            ->name('verification.verify');

        // ── Routes nécessitant un email vérifié ──────────────────────────────
        Route::middleware(['verified'])->group(function () {

            // Tableau de bord
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Gestion des Agents
            Route::apiResource('agents', AgentManagementController::class);
            Route::patch('agents/{agent}/toggle-status', [AgentManagementController::class, 'toggleStatus'])
                ->name('agents.toggle-status');

            // Gestion des Membres
            Route::apiResource('members', MemberManagementController::class);
            Route::patch('members/{member}/toggle-status', [MemberManagementController::class, 'toggleStatus'])
                ->name('members.toggle-status');

            // Gestion des Tontines
            Route::apiResource('tontines', TontineManagementController::class);
            Route::post('tontines/{tontine}/participants', [TontineManagementController::class, 'addParticipant'])
                ->name('tontines.participants.add');
            Route::delete('tontines/{tontine}/participants/{participant}', [TontineManagementController::class, 'removeParticipant'])
                ->name('tontines.participants.remove');

        // Cotisations (journal + exports)
        Route::get('/contributions',              [ResponsibleContributionController::class, 'index'])->name('contributions.index');
        Route::get('/contributions/{id}',         [ResponsibleContributionController::class, 'show'])->name('contributions.show');
        Route::get('/contributions/export/pdf',   [ResponsibleContributionController::class, 'exportPdf'])->name('contributions.export.pdf');
        Route::get('/contributions/export/excel', [ResponsibleContributionController::class, 'exportExcel'])->name('contributions.export.excel');

        // Règlements / Clôture de caisse
        Route::get('/settlements',                 [SettlementController::class, 'index'])->name('settlements.index');
        Route::post('/settlements/validate',       [SettlementController::class, 'validate'])->name('settlements.validate');
        Route::get('/settlements/pending-summary', [SettlementController::class, 'pendingSummary'])->name('settlements.pending-summary');

        // Module SMS
        Route::get('/sms',                 [SmsController::class, 'index'])->name('sms.index');
        Route::get('/sms/{id}',            [SmsController::class, 'show'])->name('sms.show');
        Route::post('/sms/send-reminders', [SmsController::class, 'sendReminders'])->name('sms.send-reminders');

        // Carte GPS
        Route::get('/map/markers', [MapController::class, 'markers'])->name('map.markers');

        // Paramètres
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        }); // fin groupe verified
    }); // fin groupe auth:sanctum + role:responsible
}); // fin prefix responsible

// =========================================================================
// DOMAINE AGENT MOBILE — Application Flutter
// =========================================================================

Route::prefix('agent')->name('agent.')->group(function () {

    // --- Routes publiques ---
    Route::post('/login',           [AgentAuthController::class, 'login'])->name('login');
    Route::post('/password/forgot', [AgentPasswordResetController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/password/reset',  [AgentPasswordResetController::class, 'resetPassword'])->name('password.reset');

    // --- Routes protégées (Sanctum + rôle agent) ---
    Route::middleware(['auth:sanctum', 'role:agent'])->group(function () {

        // Accessibles même si must_change_password = true
        Route::post('/logout',          [AgentAuthController::class, 'logout'])->name('logout');
        Route::get('/me',               [AgentAuthController::class, 'me'])->name('me');
        Route::post('/password/change', [AgentAuthController::class, 'changePassword'])->name('password.change');

        // Bloqué tant que le mot de passe temporaire n'est pas changé
        Route::middleware(['password.changed'])->group(function () {

            Route::get('/dashboard', [MobileMemberController::class, 'agentDashboard'])->name('dashboard');
            Route::get('/tontines',  [MobileMemberController::class, 'tontinesList'])->name('tontines.list');

            Route::get('/members/search',        [MobileMemberController::class, 'search'])->name('members.search');
            Route::get('/members/{id}/tontines', [MobileMemberController::class, 'tontines'])->name('members.tontines');
            Route::post('/members',              [MobileMemberController::class, 'store'])->name('members.store');
            Route::post('/members/{id}/enroll',  [MobileMemberController::class, 'enroll'])->name('members.enroll');

            Route::get('/contributions',      [AgentContributionController::class, 'index'])->name('contributions.index');
            Route::post('/contributions',     [AgentContributionController::class, 'store'])->name('contributions.store');
            Route::get('/contributions/{id}', [AgentContributionController::class, 'show'])->name('contributions.show');
        });
    });
});
