<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal des Cotisations — TontiTOGO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #212121;
            background: #fff;
        }

        /* ── En-tête du document ── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #1B5E20;
            padding-bottom: 12px;
        }

        .header-logo {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
        }

        .header-logo h1 {
            font-size: 22px;
            color: #1B5E20;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .header-logo p {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }

        .header-meta {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: middle;
        }

        .header-meta p {
            font-size: 9px;
            color: #666;
            line-height: 1.6;
        }

        /* ── Titre de la section ── */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1B5E20;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Résumé statistique ── */
        .summary-box {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }

        .summary-item {
            display: table-cell;
            width: 25%;
            background: #F1F8E9;
            border: 1px solid #C8E6C9;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
        }

        .summary-item + .summary-item {
            margin-left: 6px;
        }

        .summary-item .label {
            font-size: 8px;
            color: #555;
            text-transform: uppercase;
        }

        .summary-item .value {
            font-size: 13px;
            font-weight: bold;
            color: #1B5E20;
            margin-top: 3px;
        }

        /* ── Tableau ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        thead tr {
            background-color: #1B5E20;
            color: #fff;
        }

        thead th {
            padding: 7px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        tbody tr:nth-child(even) {
            background-color: #F9FBE7;
        }

        tbody tr:hover {
            background-color: #DCEDC8;
        }

        tbody td {
            padding: 6px 6px;
            font-size: 9px;
            border-bottom: 1px solid #E8F5E9;
            vertical-align: middle;
        }

        .amount {
            text-align: right;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-pending {
            background: #FFF3E0;
            color: #E65100;
        }

        .badge-settled {
            background: #E8F5E9;
            color: #2E7D32;
        }

        /* ── Pied de page ── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #C8E6C9;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>

    {{-- En-tête --}}
    <div class="header">
        <div class="header-logo">
            <h1>TontiTOGO</h1>
            <p>La gestion numérique de votre tontine en toute simplicité.</p>
        </div>
        <div class="header-meta">
            <p><strong>Document :</strong> Journal des cotisations</p>
            <p><strong>Organisation :</strong> {{ $organization ?? '—' }}</p>
            <p><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
            <p><strong>Total lignes :</strong> {{ count($contributions) }}</p>
        </div>
    </div>

    {{-- Titre --}}
    <div class="section-title">Journal des cotisations</div>

    {{-- Résumé statistique --}}
    @php
        $totalAmount   = collect($contributions)->sum(fn($c) => $c->amount);
        $totalPending  = collect($contributions)->where('settlement_status.value', 'pending')->sum(fn($c) => $c->amount);
        $totalSettled  = collect($contributions)->where('settlement_status.value', 'settled')->sum(fn($c) => $c->amount);
        $countToday    = collect($contributions)->filter(fn($c) => $c->created_at?->isToday())->count();
    @endphp

    <div class="summary-box">
        <div class="summary-item">
            <div class="label">Total collecté</div>
            <div class="value">{{ number_format($totalAmount, 0, ',', ' ') }} F</div>
        </div>
        <div class="summary-item">
            <div class="label">En attente</div>
            <div class="value">{{ number_format($totalPending, 0, ',', ' ') }} F</div>
        </div>
        <div class="summary-item">
            <div class="label">Versé</div>
            <div class="value">{{ number_format($totalSettled, 0, ',', ' ') }} F</div>
        </div>
        <div class="summary-item">
            <div class="label">Nb. cotisations</div>
            <div class="value">{{ count($contributions) }}</div>
        </div>
    </div>

    {{-- Tableau des cotisations --}}
    <table>
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th style="width:12%">Référence</th>
                <th style="width:17%">Membre</th>
                <th style="width:9%">Code</th>
                <th style="width:12%">Téléphone</th>
                <th style="width:15%">Tontine</th>
                <th style="width:13%">Agent</th>
                <th style="width:10%">Montant</th>
                <th style="width:8%">Statut</th>
                <th style="width:11%">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contributions as $i => $contribution)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $contribution->reference }}</strong></td>
                    <td>{{ $contribution->tontineParticipant?->member?->full_name ?? '—' }}</td>
                    <td>{{ $contribution->tontineParticipant?->member?->member_code ?? '—' }}</td>
                    <td>{{ $contribution->tontineParticipant?->member?->phone ?? '—' }}</td>
                    <td>{{ $contribution->tontineParticipant?->tontine?->name ?? '—' }}</td>
                    <td>{{ $contribution->agent?->full_name ?? '—' }}</td>
                    <td class="amount">{{ number_format($contribution->amount, 0, ',', ' ') }} F</td>
                    <td>
                        <span class="badge badge-{{ $contribution->settlement_status->value }}">
                            {{ $contribution->settlement_status->label() }}
                        </span>
                    </td>
                    <td>{{ $contribution->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                        Aucune cotisation trouvée pour les filtres sélectionnés.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pied de page --}}
    <div class="footer">
        TontiTOGO &copy; {{ now()->year }} — Document généré automatiquement. Ne pas modifier manuellement.
    </div>

</body>
</html>
