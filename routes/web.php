<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('app');
});

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
