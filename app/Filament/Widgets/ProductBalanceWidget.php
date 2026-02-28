<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use App\Models\MovementProductItem;
use App\Models\ProductSettlement;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ProductBalanceWidget extends TableWidget
{
    protected static ?string $heading = 'პროდუქტების ნაშთი';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getTableQuery(): Builder
    {
        $receipt   = Movement::OPERATION_PRODUCT_RECEIPT;
        $placement = Movement::OPERATION_PRODUCT_PLACEMENT;

        return ProductSettlement::query()
            ->select([
                'product_settlements.id',
                'product_settlements.name',
                DB::raw('(SELECT d.name FROM dimensions d WHERE d.id = product_settlements.dimension_id LIMIT 1) as dimension_name'),
                DB::raw("
                    COALESCE((
                        SELECT SUM(mpi.quantity)
                        FROM movement_product_items mpi
                        JOIN movements mr ON mr.id = mpi.movement_id AND mr.operation_type = '{$receipt}'
                        WHERE mpi.product_settlement_id = product_settlements.id
                    ), 0)
                    -
                    COALESCE((
                        SELECT SUM(mppi.quantity)
                        FROM movement_product_placement_items mppi
                        JOIN movements mp ON mp.id = mppi.movement_id AND mp.operation_type = '{$placement}'
                        WHERE mppi.product_settlement_id = product_settlements.id
                    ), 0)
                    as total_quantity
                "),
            ])
            ->groupBy('product_settlements.id', 'product_settlements.name');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading(static::$heading)
            ->columns([
                TextColumn::make('name')
                    ->label('პროდუქტი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dimension_name')
                    ->label('განზომილება')
                    ->sortable(),

                TextColumn::make('total_quantity')
                    ->label('ნაშთი')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            ])
            ->actions([
                Action::make('qrCodes')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->modalHeading('QR კოდები')
                    ->modalContent(fn ($record): HtmlString => new HtmlString(
                        $this->buildQrModalHtml((int) $record->id)
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('დახურვა'),
            ])
            ->paginated(false);
    }

    private function buildQrModalHtml(int $productSettlementId): string
    {
        $items = MovementProductItem::where('product_settlement_id', $productSettlementId)
            ->whereNotNull('qr_code')
            ->with('productSettlement.dimension')
            ->get();

        if ($items->isEmpty()) {
            return '<p class="text-center text-gray-500 py-4">QR კოდები არ მოიძებნა.</p>';
        }

        $html = '<div style="display:flex;flex-wrap:wrap;gap:24px;justify-content:center;padding:16px;">';

        foreach ($items as $item) {
            $product   = $item->productSettlement;
            $dimension = $product->dimension?->name ?? '';
            $quantity  = rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.');

            $html .= '<div style="text-align:center;border:1px solid #e5e7eb;border-radius:12px;padding:16px;min-width:200px;">';
            $html .= '<div style="width:180px;height:180px;margin:0 auto;">' . $item->qr_code . '</div>';
            $html .= '<p style="margin-top:8px;font-weight:600;font-size:14px;">' . e($product->name) . '</p>';
            $html .= '<p style="color:#6b7280;font-size:13px;">' . e($quantity) . ' ' . e($dimension) . '</p>';
            $html .= '</div>';
        }

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
