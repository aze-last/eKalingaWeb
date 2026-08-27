<?php

namespace App\Http\Controllers;

use App\Enums\ReportType;
use App\Models\Setting;
use App\Services\ReportSnapshotService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    public function download(Request $request, ReportSnapshotService $reportService): Response
    {
        $request->validate([
            'type' => 'required|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'program_id' => 'nullable|integer',
        ]);

        $reportType = ReportType::from($request->type);
        $snapshot = $reportService->generateSnapshot(
            type: $reportType,
            dateFrom: $request->date_from,
            dateTo: $request->date_to,
            programId: $request->program_id ? (int) $request->program_id : null
        );

        $municipalSeal = Setting::get('municipal_seal_url', '/images/Site_logo.png');
        $municipalityName = Setting::get('municipality_name', 'MUNICIPALITY OF SULOP');
        $provinceName = Setting::get('province_name', 'PROVINCE OF DAVAO DEL SUR');

        $pdf = Pdf::loadView('reports.pdf-template', [
            'snapshot' => $snapshot,
            'municipalSeal' => $municipalSeal,
            'municipalityName' => $municipalityName,
            'provinceName' => $provinceName,
        ])->setPaper('a4', $snapshot->metadata['orientation'] ?? 'landscape');

        $filename = 'eKalinga_Report_'.$reportType->value.'_'.date('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }
}
