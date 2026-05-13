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

    $deviceSummary = $session->monitorings
        ->groupBy(fn ($m) => $m->movementProductItem?->productSettlement?->name ?? '—')
        ->map(fn ($monitorings, $name) => [
            'name' => $name,
            'total' => $monitorings->count(),
            'inspected' => $monitorings->filter(fn ($m) => $m->logs->isNotEmpty())->count(),
            'active' => $monitorings->filter(fn ($m) => (float) $m->pest_quantity > 0)->count(),
            'replaced' => $monitorings->filter(fn ($m) => $m->componentReplacements->filter(fn ($r) => (float) $r->quantity > 0)->isNotEmpty())->count(),
        ])
        ->values();

    $pestLogs = $allLogs->filter(fn ($log) => $log->pest_type || (float) $log->pest_quantity > 0);

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

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('print.service-report', compact('session', 'deviceSummary', 'pestLogs', 'componentSummary'))
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
