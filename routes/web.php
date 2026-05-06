<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('app');
});

Route::get('/print/service-report/{monitoring}', function (\App\Models\Monitoring $monitoring) {
    $monitoring->load([
        'organization',
        'logs.movementProductItem.productSettlement',
    ]);

    $deviceSummary = $monitoring->logs
        ->groupBy(fn ($log) => $log->movementProductItem?->productSettlement?->name ?? '—')
        ->map(fn ($logs, $name) => [
            'name' => $name,
            'total' => $logs->count(),
            'inspected' => $logs->where('type', 'inspection')->count(),
            'active' => $logs->where('inspection_status', 'active')->count(),
            'replaced' => $logs->where('type', 'replacement')->count(),
        ])
        ->values();

    $pestLogs = $monitoring->logs->filter(
        fn ($log) => $log->pest_type || $log->pest_quantity > 0
    );

    return view('print.service-report', compact('monitoring', 'deviceSummary', 'pestLogs'));
})->name('print.service-report');

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

Route::get('/print/qr/{productSettlementId}', function (int $productSettlementId) {
    $item = \App\Models\MovementProductItem::where('product_settlement_id', $productSettlementId)
        ->whereNotNull('qr_code')
        ->with('productSettlement.dimension')
        ->latest('id')
        ->firstOrFail();

    $placementItem = \App\Models\MovementProductPlacementItem::where('product_settlement_id', $productSettlementId)
        ->whereNotNull('unique_code')
        ->latest('id')
        ->first();

    $product = $item->productSettlement;
    $dimension = $product->dimension?->name ?? '';
    $quantity = rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.');

    return view('print.qr-label', [
        'qrCode' => $item->qr_code,
        'uniqueCode' => $placementItem?->unique_code,
        'qty' => $quantity.($dimension ? ' '.$dimension : ''),
    ]);
})->name('print.qr');
