<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Http\Controllers\Api\ApiController;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class DashboardController extends ApiController
{
    #[OA\Get(
        path: '/api/responsible/dashboard',
        summary: 'Données complètes du tableau de bord responsable (KPIs + graphiques)',
        tags: ['Dashboard'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'KPIs, graphiques et activité récente')]
    )]
    public function index(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        // --- KPIs principaux ---
        $kpis = [
            // A. Caisse totale du jour
            'total_collected_today'  => (float) Contribution::whereHas('tontineParticipant.tontine', fn($q) => $q->where('organization_id', $orgId))
                ->whereDate('created_at', today())
                ->sum('amount'),

            // B. Agents actifs terrain aujourd'hui (ayant fait ≥ 1 collecte)
            'agents_active_today'    => User::where('organization_id', $orgId)
                ->where('role', 'agent')
                ->where('status', 'active')
                ->whereHas('contributions', fn($q) => $q->whereDate('created_at', today()))
                ->count(),

            // B². Total agents actifs (dénominateur pour "X / Y")
            'total_agents'           => User::where('organization_id', $orgId)->where('role', 'agent')->where('status', 'active')->count(),

            // C. Membres actifs uniquement
            'total_active_members'   => Member::where('organization_id', $orgId)->where('status', 'active')->count(),

            // D. Membres suspendus (proxy pour "en attente de traitement")
            'suspended_members'      => Member::where('organization_id', $orgId)->where('status', 'suspended')->count(),

            // E. Volume total du mois
            'total_collected_month'  => (float) Contribution::whereHas('tontineParticipant.tontine', fn($q) => $q->where('organization_id', $orgId))
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),

            // F. SMS échoués (depuis sms_logs)
            'sms_failed_count'       => \App\Models\SmsLog::where('organization_id', $orgId)
                ->where('status', 'failed')
                ->count(),

            // Conservé pour compatibilité
            'total_members'          => Member::where('organization_id', $orgId)->count(),
            'active_tontines'        => Tontine::where('organization_id', $orgId)->where('status', 'active')->count(),
            'pending_settlement'     => (float) Contribution::whereHas('tontineParticipant.tontine', fn($q) => $q->where('organization_id', $orgId))
                ->where('settlement_status', 'pending')
                ->sum('amount'),
        ];

        // --- Graphique Area : collecte journalière des 30 derniers jours ---
        $dailySeries = Contribution::whereHas(
            'tontineParticipant.tontine',
            fn($q) => $q->where('organization_id', $orgId)
        )
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Remplir les jours sans collecte avec 0
        $dailyCategories = [];
        $dailyData       = [];
        for ($i = 29; $i >= 0; $i--) {
            $date              = now()->subDays($i)->format('Y-m-d');
            $dailyCategories[] = now()->subDays($i)->format('d/m');
            $dailyData[]       = (float) ($dailySeries[$date]->total ?? 0);
        }

        // --- Graphique Bar : bilan mensuel des 6 derniers mois ---
        $monthlyData = Contribution::whereHas(
            'tontineParticipant.tontine',
            fn($q) => $q->where('organization_id', $orgId)
        )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyCategories = [];
        $monthlySeries     = [];
        for ($i = 5; $i >= 0; $i--) {
            $key                 = now()->subMonths($i)->format('Y-m');
            $label               = ucfirst(now()->subMonths($i)->translatedFormat('M Y'));
            $monthlyCategories[] = $label;
            $monthlySeries[]     = (float) ($monthlyData[$key]->total ?? 0);
        }

        // --- Activité récente (10 dernières contributions) ---
        $recentActivity = Contribution::whereHas(
            'tontineParticipant.tontine',
            fn($q) => $q->where('organization_id', $orgId)
        )
            ->with([
                'agent:id,firstname,lastname',
                'tontineParticipant.member:id,firstname,lastname',
                'tontineParticipant.tontine:id,name',
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'reference'  => $c->reference,
                'amount'     => $c->amount,
                'member'     => $c->tontineParticipant?->member?->full_name,
                'tontine'    => $c->tontineParticipant?->tontine?->name,
                'agent'      => $c->agent?->full_name,
                'created_at' => $c->created_at?->format('d/m/Y H:i'),
            ]);

        return $this->success([
            'kpis'               => $kpis,
            'charts'             => [
                'daily_collection' => [
                    'categories' => $dailyCategories,
                    'series'     => [['name' => 'Collecte (FCFA)', 'data' => $dailyData]],
                ],
                'monthly_summary'  => [
                    'categories' => $monthlyCategories,
                    'series'     => [['name' => 'Total mensuel (FCFA)', 'data' => $monthlySeries]],
                ],
            ],
            'recent_activity'    => $recentActivity,
        ]);
    }
}
