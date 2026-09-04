<?php

namespace App\Http\Controllers;

use App\Actions\BuildReportData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request, BuildReportData $buildReportData): View
    {
        $validated = $request->validate([
            'board' => ['nullable', 'integer'],
            'days' => ['nullable', 'integer', 'in:7,30,90'],
        ]);

        $report = $buildReportData->execute(
            $request->user(),
            isset($validated['board']) ? (int) $validated['board'] : null,
            (int) ($validated['days'] ?? 30),
        );

        return view('reports.index', compact('report'));
    }
}
