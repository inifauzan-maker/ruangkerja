<?php

namespace App\Http\Controllers;

use App\Actions\BuildReportData;
use App\Support\SimplePdfReport;
use App\Support\XlsxReport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    public function pdf(
        Request $request,
        BuildReportData $buildReportData,
        SimplePdfReport $pdfReport,
    ): Response {
        $report = $this->report($request, $buildReportData);
        $lines = [
            'RUANG KERJA _ VILLA MERAH - LAPORAN PROYEK',
            'Dibuat: '.$report['generated_at']->format('d M Y H:i'),
            'Periode aktivitas: '.$report['days'].' hari',
            '',
            'RINGKASAN',
            'Total tugas: '.$report['metrics']['total'],
            'Selesai: '.$report['metrics']['completed'],
            'Dikerjakan: '.$report['metrics']['in_progress'],
            'Terlambat: '.$report['metrics']['overdue'],
            '',
            'BEBAN KERJA',
        ];

        foreach ($report['workload']->take(20) as $member) {
            $lines[] = sprintf(
                '%s - total %d, aktif %d, selesai %d, terlambat %d',
                $member['name'],
                $member['total'],
                $member['active'],
                $member['completed'],
                $member['overdue'],
            );
        }

        $lines[] = '';
        $lines[] = 'AKTIVITAS PER PROYEK';
        foreach ($report['activity_by_project']->take(15) as $project) {
            $lines[] = $project['name'].': '.$project['count'].' aktivitas';
        }

        return response($pdfReport->make($lines), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-villa-merah.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function excel(
        Request $request,
        BuildReportData $buildReportData,
        XlsxReport $xlsxReport,
    ): Response {
        $report = $this->report($request, $buildReportData);
        $rows = [
            ['LAPORAN RUANG KERJA _ VILLA MERAH'],
            ['Dibuat', $report['generated_at']->format('Y-m-d H:i')],
            ['Periode', $report['days'].' hari'],
            [],
            ['RINGKASAN'],
            ['Total tugas', $report['metrics']['total']],
            ['Selesai', $report['metrics']['completed']],
            ['Dikerjakan', $report['metrics']['in_progress']],
            ['Terlambat', $report['metrics']['overdue']],
            [],
            ['BEBAN KERJA'],
            ['Anggota', 'Total', 'Aktif', 'Selesai', 'Terlambat'],
        ];

        foreach ($report['workload'] as $member) {
            $rows[] = [$member['name'], $member['total'], $member['active'], $member['completed'], $member['overdue']];
        }

        $rows[] = [];
        $rows[] = ['AKTIVITAS PER PROYEK'];
        $rows[] = ['Proyek', 'Jumlah aktivitas', 'Aktivitas terakhir'];
        foreach ($report['activity_by_project'] as $project) {
            $rows[] = [$project['name'], $project['count'], $project['latest_at']?->format('Y-m-d H:i') ?? '-'];
        }

        return response($xlsxReport->make($rows), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="laporan-villa-merah.xlsx"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function report(Request $request, BuildReportData $buildReportData): array
    {
        $validated = $request->validate([
            'board' => ['nullable', 'integer'],
            'days' => ['nullable', 'integer', 'in:7,30,90'],
        ]);

        return $buildReportData->execute(
            $request->user(),
            isset($validated['board']) ? (int) $validated['board'] : null,
            (int) ($validated['days'] ?? 30),
        );
    }
}
