<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('app');
});

Route::get('/print/service-report/session/{session}', function (\App\Models\MonitoringSession $session) {
    $session->load([
        'organization',
        'monitorings.movementProductItem.productSettlement',
        'monitorings.componentReplacements.settlementComponent.dimension',
        'monitorings.logs.movementProductItem.productSettlement',
    ]);

    $allLogs = $session->monitorings->flatMap(fn ($m) => $m->logs);

    $inspectedUniqueCodes = $allLogs->pluck('unique_code')->filter()->unique();

    $pestQuantityByCode = $allLogs
        ->filter(fn ($l) => $l->unique_code)
        ->groupBy('unique_code')
        ->map(fn ($logs) => $logs->sum(fn ($l) => (float) $l->pest_quantity));

    $allPlacementItems = \App\Models\MovementProductPlacementItem::query()
        ->whereHas('movement', fn ($q) => $q
            ->where('organization_id', $session->organization_id)
            ->where('operation_type', \App\Models\Movement::OPERATION_PRODUCT_PLACEMENT)
        )
        ->whereNotNull('unique_code')
        ->with('productSettlement')
        ->get();

    $deviceSummary = $allPlacementItems
        ->groupBy(fn ($item) => $item->productSettlement?->name ?? '—')
        ->map(function ($items, $name) use ($inspectedUniqueCodes, $pestQuantityByCode) {
            $total = $items->count();
            $inspectedCount = $items->filter(fn ($item) => $inspectedUniqueCodes->contains($item->unique_code))->count();
            $pestQuantity = $items->sum(fn ($item) => (float) ($pestQuantityByCode->get($item->unique_code) ?? 0));
            $inspectedPct = $total > 0 ? round($inspectedCount / $total * 100, 1) : 0;

            return [
                'name' => $name,
                'total' => $total,
                'inspected_pct' => $inspectedPct,
                'pest_quantity' => $pestQuantity,
                'missed_pct' => round(100 - $inspectedPct, 1),
            ];
        })
        ->values();

    $pestLogs = $allLogs->filter(fn ($log) => $log->pest_type || (float) $log->pest_quantity > 0);

    $pestSummary = $session->monitorings
        ->filter(fn ($m) => $m->pest_type)
        ->map(fn ($m) => [
            'device_name' => $m->movementProductItem?->productSettlement?->name ?? '—',
            'unique_code' => $m->logs->first()?->unique_code ?? '—',
            'pest_type' => $m->pest_type,
            'pest_quantity' => (float) $m->pest_quantity,
        ])
        ->values();

    $monitoringByUniqueCode = $session->monitorings
        ->filter(fn ($m) => $m->logs->isNotEmpty() && $m->logs->first()?->unique_code)
        ->keyBy(fn ($m) => $m->logs->first()->unique_code);

    $inspectionDetails = $allPlacementItems
        ->sortBy('unique_code')
        ->map(function ($item) use ($monitoringByUniqueCode) {
            $m = $monitoringByUniqueCode->get($item->unique_code);
            $pq = $m && $m->pest_quantity ? rtrim(rtrim(number_format((float) $m->pest_quantity, 4, '.', ''), '0'), '.') : '';

            return [
                'unique_code' => $item->unique_code,
                'location' => implode(', ', array_filter([$item->zone, $item->location])),
                'device_name' => $item->productSettlement?->name ?? '—',
                'bait_status' => $m?->bait_status ?? '',
                'scan_time' => $m ? $m->created_at->format('H:i') : '',
                'scan_status' => $m ? 'შემოწმდა' : 'არ შემოწმდა',
                'device_condition' => $m?->action_taken ?? '',
                'pest_quantity' => $pq,
                'risk_level' => $m?->risk_level ?? '',
            ];
        })
        ->values();

    $componentSummary = $session->monitorings
        ->flatMap(fn ($m) => $m->componentReplacements)
        ->filter(fn ($r) => (float) $r->quantity > 0)
        ->groupBy('settlement_component_id')
        ->map(fn ($items) => [
            'name' => $items->first()->settlementComponent?->name ?? '—',
            'dimension' => $items->first()->settlementComponent?->dimension?->name ?? '',
            'quantity' => $items->sum(fn ($r) => (float) $r->quantity),
        ])
        ->values();

    $filename = 'service-report-'.$session->id.'.pdf';

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('print.service-report', compact('session', 'deviceSummary', 'pestLogs', 'componentSummary', 'pestSummary', 'inspectionDetails'))
        ->setPaper('a4')
        ->stream($filename);
})->name('print.service-report.session');

Route::get('/export/monitoring-report', function () {
    $filename = 'monitoring-report-'.now()->format('Y-m-d').'.xlsx';

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\MonitoringReportExport,
        $filename
    );
})->name('export.monitoring-report');

Route::get('/export/pesticide-report', function (\Illuminate\Http\Request $request) {
    $filename = 'pesticide-report-'.now()->format('Y-m-d').'.xlsx';

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\PesticideReportExport(
            organizationId: $request->integer('organization_id') ?: null,
            from: $request->string('from')->toString() ?: null,
            until: $request->string('until')->toString() ?: null,
        ),
        $filename
    );
})->name('export.pesticide-report');

Route::get('/export/unscanned-devices-report', function (\Illuminate\Http\Request $request) {
    $filename = 'unscanned-devices-report-'.now()->format('Y-m-d').'.xlsx';

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\UnscannedDevicesReportExport(
            organizationId: $request->integer('organization_id') ?: null,
            unscannedOnly: $request->boolean('unscanned_only', true),
        ),
        $filename
    );
})->name('export.unscanned-devices-report');

Route::get('/export/product-placement-template', function () {
    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\ProductPlacementTemplateExport,
        'product-placement-template.xlsx'
    );
})->name('export.product-placement-template');

Route::get('/print/qr/{uniqueCode}', function (string $uniqueCode) {
    $movementService = app(\App\Services\MovementService::class);

    return view('print.qr-label', [
        'qrCode' => $movementService->generateQrSvg($uniqueCode),
        'uniqueCode' => $uniqueCode,
    ]);
})->name('print.qr');

Route::get('/print/qr-placement/{movement}', function (\App\Models\Movement $movement) {
    $movementService = app(\App\Services\MovementService::class);

    $items = $movement->placementItems()
        ->whereNotNull('unique_code')
        ->with('productSettlement')
        ->get()
        ->map(fn ($item) => [
            'uniqueCode' => $item->unique_code,
            'name' => $item->productSettlement?->name ?? '',
            'zone' => $item->zone,
            'location' => $item->location,
            'qrCode' => $movementService->generateQrSvg($item->unique_code),
        ]);

    return view('print.qr-placement', compact('items'));
})->name('print.qr-placement');
