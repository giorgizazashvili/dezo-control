<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovementResource\Pages;
use App\Models\Dimension;
use App\Models\Movement;
use App\Models\Organization;
use App\Models\ProductSettlement;
use App\Models\SettlementComponent;
use App\Services\MovementService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'მოძრაობა';

    protected static ?string $modelLabel = 'მოძრაობა';

    protected static ?string $pluralModelLabel = 'მოძრაობები';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('operation_type')
                ->label('ოპერაციის ტიპი')
                ->options(Movement::operationTypes())
                ->required()
                ->live()
                ->columnSpanFull(),

            // კომპონენტის მიღება
            Repeater::make('componentItems')
                ->label('კომპონენტები')
                ->relationship('componentItems')
                ->schema([
                    Select::make('settlement_component_id')
                        ->label('კომპონენტი')
                        ->relationship('settlementComponent', 'name', fn ($q) => $q?->with('dimension'))
                        ->getOptionLabelFromRecordUsing(
                            fn (SettlementComponent $r) => $r->name . ' — ' . ($r->dimension?->name ?? '')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2)
                        ->createOptionModalHeading('ახალი კომპონენტი')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('დასახელება')
                                ->required()
                                ->maxLength(255),
                            Select::make('dimension_id')
                                ->label('განზომილება')
                                ->relationship('dimension', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionModalHeading('ახალი განზომილება')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('დასახელება')
                                        ->required()
                                        ->unique(Dimension::class, 'name')
                                        ->maxLength(100),
                                ])
                                ->createOptionUsing(fn (array $data): int => Dimension::create($data)->id),
                        ])
                        ->createOptionUsing(fn (array $data): int => SettlementComponent::create($data)->id),

                    TextInput::make('quantity')
                        ->label('რაოდენობა')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->addActionLabel('კომპონენტის დამატება')
                ->reorderable()
                ->visible(fn (Get $get) => $get('operation_type') === Movement::OPERATION_COMPONENT_RECEIPT)
                ->columnSpanFull(),

            // პროდუქტის მიღება
            Repeater::make('productItems')
                ->label('პროდუქტები')
                ->relationship('productItems')
                ->schema([
                    Select::make('product_settlement_id')
                        ->label('პროდუქტი')
                        ->relationship('productSettlement', 'name', fn ($q) => $q?->with('dimension'))
                        ->getOptionLabelFromRecordUsing(
                            fn (ProductSettlement $r) => $r->name . ' — ' . ($r->dimension?->name ?? '')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2)
                        ->createOptionModalHeading('ახალი პროდუქტი')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('დასახელება')
                                ->required()
                                ->maxLength(255),
                            Select::make('dimension_id')
                                ->label('განზომილება')
                                ->relationship('dimension', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionModalHeading('ახალი განზომილება')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('დასახელება')
                                        ->required()
                                        ->unique(Dimension::class, 'name')
                                        ->maxLength(100),
                                ])
                                ->createOptionUsing(fn (array $data): int => Dimension::create($data)->id),
                        ])
                        ->createOptionUsing(fn (array $data): int => ProductSettlement::create($data)->id),

                    TextInput::make('quantity')
                        ->label('რაოდენობა')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->addActionLabel('პროდუქტის დამატება')
                ->reorderable()
                ->visible(fn (Get $get) => $get('operation_type') === Movement::OPERATION_PRODUCT_RECEIPT)
                ->columnSpanFull(),

            // ობიექტზე განთავსება — დამატებითი ველები
            Select::make('organization_id')
                ->label('ორგანიზაცია')
                ->relationship('organization', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->visible(fn (Get $get) => $get('operation_type') === Movement::OPERATION_PRODUCT_PLACEMENT),

            Textarea::make('comment')
                ->label('კომენტარი')
                ->rows(2)
                ->nullable()
                ->visible(fn (Get $get) => $get('operation_type') === Movement::OPERATION_PRODUCT_PLACEMENT),

            // ობიექტზე განთავსება
            Repeater::make('placementItems')
                ->label('პროდუქტები')
                ->relationship('placementItems')
                ->schema([
                    Select::make('product_settlement_id')
                        ->label('პროდუქტი')
                        ->relationship('productSettlement', 'name', fn ($q) => $q?->with('dimension'))
                        ->getOptionLabelFromRecordUsing(function (ProductSettlement $r) {
                            $stock = app(MovementService::class)->getProductStock($r->id);

                            return $r->name
                                . ' — ' . ($r->dimension?->name ?? '')
                                . ' | ნაშთი: ' . number_format($stock, 2, '.', '');
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('quantity')
                        ->label('რაოდენობა')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->addActionLabel('პროდუქტის დამატება')
                ->reorderable()
                ->visible(fn (Get $get) => $get('operation_type') === Movement::OPERATION_PRODUCT_PLACEMENT)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('operation_type', '!=', Movement::OPERATION_COMPONENT_CONSUMPTION))
            ->columns([
                TextColumn::make('operation_type')
                    ->label('ოპერაციის ტიპი')
                    ->formatStateUsing(fn (string $state) => [
                    Movement::OPERATION_COMPONENT_RECEIPT     => 'კომპონენტის მიღება',
                    Movement::OPERATION_PRODUCT_RECEIPT       => 'პროდუქტის მიღება',
                    Movement::OPERATION_COMPONENT_CONSUMPTION => 'კომპონენტის ჩამოწერა',
                    Movement::OPERATION_PRODUCT_PLACEMENT     => 'ობიექტზე განთავსება',
                ][$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Movement::OPERATION_COMPONENT_RECEIPT     => 'info',
                        Movement::OPERATION_PRODUCT_RECEIPT       => 'success',
                        Movement::OPERATION_COMPONENT_CONSUMPTION => 'danger',
                        Movement::OPERATION_PRODUCT_PLACEMENT     => 'warning',
                        default                                   => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('organization.name')
                    ->label('ორგანიზაცია')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('qrCodes')
                    ->label('QR კოდები')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->visible(fn (Movement $record) => $record->operation_type === Movement::OPERATION_PRODUCT_RECEIPT)
                    ->modalHeading('QR კოდები')
                    ->modalContent(fn (Movement $record): HtmlString => new HtmlString(
                        self::buildQrModalHtml($record)
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('დახურვა'),

                EditAction::make()->label('რედაქტირება'),
                DeleteAction::make()->label('წაშლა'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()->label('წაშლა'),
                ]),
            ]);
    }

    private static function buildQrModalHtml(Movement $record): string
    {
        $record->load('productItems.productSettlement.dimension');

        $items = $record->productItems->filter(fn ($i) => ! empty($i->uuid));

        if ($items->isEmpty()) {
            return '<p class="text-center text-gray-500 py-4">QR კოდები არ მოიძებნა.</p>';
        }

        $service = app(MovementService::class);

        $html = '<div style="display:flex;flex-wrap:wrap;gap:24px;justify-content:center;padding:16px;">';

        foreach ($items as $item) {
            $product   = $item->productSettlement;
            $dimension = $product->dimension?->name ?? '';
            $quantity  = rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.');
            $qrSvg     = $service->generateQrSvg($item->uuid);

            $html .= '<div style="text-align:center;border:1px solid #e5e7eb;border-radius:12px;padding:16px;min-width:200px;">';
            $html .= '<div style="width:180px;height:180px;margin:0 auto;">' . $qrSvg . '</div>';
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit'   => Pages\EditMovement::route('/{record}/edit'),
        ];
    }
}
