<?php

namespace App\Exports;

use App\Models\Contribution;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributionsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting
{
    public function __construct(
        private readonly int   $organizationId,
        private readonly array $filters = []
    ) {}

    // -------------------------------------------------------
    // Source de données
    // -------------------------------------------------------

    public function query()
    {
        $query = Contribution::forOrganization($this->organizationId)
            ->with([
                'agent:id,firstname,lastname',
                'tontineParticipant.member:id,firstname,lastname,phone,member_code',
                'tontineParticipant.tontine:id,name',
            ])
            ->orderByDesc('created_at');

        if (! empty($this->filters['date'])) {
            $query->filterByPeriod($this->filters['date']);
        }

        if (! empty($this->filters['agent_id'])) {
            $query->where('user_id', $this->filters['agent_id']);
        }

        if (! empty($this->filters['member_id'])) {
            $query->whereHas('tontineParticipant', fn($q) =>
                $q->where('member_id', $this->filters['member_id'])
            );
        }

        if (! empty($this->filters['tontine_id'])) {
            $query->whereHas('tontineParticipant', fn($q) =>
                $q->where('tontine_id', $this->filters['tontine_id'])
            );
        }

        return $query;
    }

    // -------------------------------------------------------
    // En-têtes des colonnes
    // -------------------------------------------------------

    public function headings(): array
    {
        return [
            'N°',
            'Référence',
            'Membre',
            'Code Membre',
            'Téléphone',
            'Tontine',
            'Agent',
            'Montant (FCFA)',
            'Statut versement',
            'Date & Heure',
        ];
    }

    // -------------------------------------------------------
    // Mappage ligne par ligne
    // -------------------------------------------------------

    public function map($contribution): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $contribution->reference,
            $contribution->tontineParticipant?->member?->full_name ?? '—',
            $contribution->tontineParticipant?->member?->member_code ?? '—',
            $contribution->tontineParticipant?->member?->phone ?? '—',
            $contribution->tontineParticipant?->tontine?->name ?? '—',
            $contribution->agent?->full_name ?? '—',
            (float) $contribution->amount,
            $contribution->settlement_status->label(),
            $contribution->created_at?->format('d/m/Y H:i'),
        ];
    }

    // -------------------------------------------------------
    // Styles & mise en forme
    // -------------------------------------------------------

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1B5E20'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF9E9E9E'],
                    ],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'Cotisations ' . now()->format('d-m-Y');
    }
}
