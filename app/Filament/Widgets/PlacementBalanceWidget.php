<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use App\Models\MovementProductItem;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class PlacementBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'ობიექტზე განთავსებული პროდუქტები';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getTableQuery(): Builder
    {
        return Organization::query()
            ->select([
                'organizations.id',
                'organizations.name as organization_name',
                'product_settlements.id as product_settlement_id',
                'product_settlements.name as product_name',
                DB::raw('MAX(dimensions.name) as dimension_name'),
                DB::raw('SUM(mppi.quantity) as total_quantity'),
            ])
            ->join('movements', 'movements.organization_id', '=', 'organizations.id')
            ->join('movement_product_placement_items as mppi', 'mppi.movement_id', '=', 'movements.id')
            ->join('product_settlements', 'product_settlements.id', '=', 'mppi.product_settlement_id')
            ->leftJoin('dimensions', 'dimensions.id', '=', 'product_settlements.dimension_id')
            ->where('movements.operation_type', Movement::OPERATION_PRODUCT_PLACEMENT)
            ->groupBy(
                'organizations.id',
                'organizations.name',
                'product_settlements.id',
                'product_settlements.name',
            );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading(static::$heading)
            ->columns([
                TextColumn::make('organization_name')
                    ->label('ორგანიზაცია')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_name')
                    ->label('პროდუქტი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dimension_name')
                    ->label('განზომილება'),

                TextColumn::make('total_quantity')
                    ->label('რაოდენობა')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->actions([
                Action::make('qrCodes')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->modalHeading('QR კოდები')
                    ->modalContent(fn ($record): HtmlString => new HtmlString(
                        $this->buildQrModalHtml((int) $record->product_settlement_id)
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('დახურვა'),
            ])
            ->defaultSort('organization_name')
            ->paginated(false);
    }

    private function buildQrModalHtml(int $productSettlementId): string
    {
        $item = MovementProductItem::where('product_settlement_id', $productSettlementId)
            ->whereNotNull('qr_code')
            ->with('productSettlement.dimension')
            ->latest('id')
            ->first();

        if (! $item) {
            return '<p class="text-center text-gray-500 py-4">QR კოდები არ მოიძებნა.</p>';
        }

        $product   = $item->productSettlement;
        $dimension = $product->dimension?->name ?? '';
        $quantity  = rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.');

        $html = '<div style="display:flex;justify-content:center;padding:16px;">';
        $html .= '<div style="text-align:center;border:1px solid #e5e7eb;border-radius:12px;padding:16px;min-width:200px;">';
        $html .= '<div style="width:180px;height:180px;margin:0 auto;">' . $item->qr_code . '</div>';
        $html .= '<p style="margin-top:8px;font-weight:600;font-size:14px;">' . e($product->name) . '</p>';
        $html .= '<p style="color:#6b7280;font-size:13px;">' . e($quantity) . ' ' . e($dimension) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '
        <div style="text-align:center;margin-top:12px;">
            <button onclick="window.print()"
                style="background:#3b82f6;color:#fff;border:none;padding:8px 24px;border-radius:8px;cursor:pointer;font-size:14px;">
                ბეჭდვა
            </button>
        </div>';

        return $html;
    }
}
