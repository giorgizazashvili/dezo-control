<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoringResource\Pages;
use App\Models\Monitoring;
use App\Models\MonitoringOption;
use App\Models\MovementProductItem;
use App\Models\MovementProductPlacementItem;
use App\Models\ProductSettlementItem;
use App\Models\SettlementComponent;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoringResource extends Resource
{
    protected static ?string $model = Monitoring::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'მონიტორინგი';

    protected static ?string $pluralModelLabel = 'მონიტორინგები';

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('monitoring_session_id')
                ->default(fn () => request()->query('session_id')),

            Hidden::make('movement_product_placement_item_id')
                ->default(fn () => request()->query('placement_item_id')),

            Hidden::make('movement_product_item_id')
                ->default(function () {
                    $placementItemId = request()->query('placement_item_id');
                    if (! $placementItemId) {
                        return null;
                    }

                    $placementItem = MovementProductPlacementItem::find($placementItemId);

                    return $placementItem
                        ? MovementProductItem::where('product_settlement_id', $placementItem->product_settlement_id)->latest('id')->value('id')
                        : null;
                }),

            TextInput::make('_device_name')
                ->label('მოწყობილობა')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    $placementItemId = request()->query('placement_item_id');
                    if (! $placementItemId) {
                        return null;
                    }

                    $item = MovementProductPlacementItem::with('productSettlement.dimension')->find($placementItemId);

                    return $item
                        ? $item->productSettlement->name.' — '.($item->productSettlement->dimension?->name ?? '')
                        : null;
                })
                ->columnSpan(2),

            TextInput::make('_device_location')
                ->label('კოდი / ზონა / მდებარეობა')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    $placementItemId = request()->query('placement_item_id');
                    if (! $placementItemId) {
                        return null;
                    }

                    $item = MovementProductPlacementItem::find($placementItemId);

                    return $item
                        ? implode(' / ', array_filter([$item->unique_code, $item->zone, $item->location]))
                        : null;
                })
                ->columnSpan(2),

            Repeater::make('componentReplacements')
                ->label('ჩანაცვლებული კომპონენტები')
                ->relationship('componentReplacements')
                ->default(function (): array {
                    $placementItemId = request()->query('placement_item_id');
                    if (! $placementItemId) {
                        return [];
                    }

                    $placementItem = MovementProductPlacementItem::find($placementItemId);
                    if (! $placementItem) {
                        return [];
                    }

                    return ProductSettlementItem::where('product_settlement_id', $placementItem->product_settlement_id)
                        ->get()
                        ->map(fn (ProductSettlementItem $item) => [
                            'settlement_component_id' => $item->settlement_component_id,
                            'quantity' => null,
                        ])
                        ->toArray();
                })
                ->schema([
                    Select::make('settlement_component_id')
                        ->label('კომპონენტი')
                        ->relationship('settlementComponent', 'name', fn ($q) => $q?->with('dimension'))
                        ->getOptionLabelFromRecordUsing(
                            fn (SettlementComponent $r) => $r->name.' — '.($r->dimension?->name ?? '')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('quantity')
                        ->label('ჩასანაცვლებელი')
                        ->numeric()
                        ->minValue(0)
                        ->nullable()
                        ->placeholder('0')
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->addActionLabel('კომპონენტის დამატება')
                ->reorderable()
                ->columnSpanFull(),

            Section::make('ინსპექციის მონაცემები')
                ->schema([
                    Select::make('pest_type')
                        ->label('მავნებლის ტიპი')
                        ->options(fn () => MonitoringOption::where('type', 'pest_type')->pluck('name', 'name'))
                        ->searchable()
                        ->nullable()
                        ->createOptionForm([TextInput::make('name')->label('სახელი')->required()])
                        ->createOptionUsing(fn (array $data): string => MonitoringOption::firstOrCreate(['type' => 'pest_type', 'name' => $data['name']])->name),

                    TextInput::make('pest_quantity')
                        ->label('მავნებლის რაოდენობა')
                        ->numeric()
                        ->minValue(0)
                        ->nullable(),

                    Select::make('bait_status')
                        ->label('სატყუარას მდგომარეობა')
                        ->options(fn () => MonitoringOption::where('type', 'bait_status')->pluck('name', 'name'))
                        ->searchable()
                        ->nullable()
                        ->createOptionForm([TextInput::make('name')->label('სახელი')->required()])
                        ->createOptionUsing(fn (array $data): string => MonitoringOption::firstOrCreate(['type' => 'bait_status', 'name' => $data['name']])->name),

                    Select::make('risk_level')
                        ->label('რისკის დონე')
                        ->options(fn () => MonitoringOption::where('type', 'risk_level')->pluck('name', 'name'))
                        ->searchable()
                        ->nullable()
                        ->createOptionForm([TextInput::make('name')->label('სახელი')->required()])
                        ->createOptionUsing(fn (array $data): string => MonitoringOption::firstOrCreate(['type' => 'risk_level', 'name' => $data['name']])->name),

                    Select::make('action_taken')
                        ->label('მიღებული ზომა')
                        ->options(fn () => MonitoringOption::where('type', 'action_taken')->pluck('name', 'name'))
                        ->searchable()
                        ->nullable()
                        ->columnSpanFull()
                        ->createOptionForm([TextInput::make('name')->label('სახელი')->required()])
                        ->createOptionUsing(fn (array $data): string => MonitoringOption::firstOrCreate(['type' => 'action_taken', 'name' => $data['name']])->name),

                    Select::make('used_apparatus')
                        ->label('გამოყენებული აპარატი')
                        ->options(fn () => MonitoringOption::where('type', 'used_apparatus')->pluck('name', 'name'))
                        ->searchable()
                        ->nullable()
                        ->createOptionForm([TextInput::make('name')->label('სახელი')->required()])
                        ->createOptionUsing(fn (array $data): string => MonitoringOption::firstOrCreate(['type' => 'used_apparatus', 'name' => $data['name']])->name),

                    Select::make('pest_trace')
                        ->label('მავნებლის კვალის დაფიქსირება')
                        ->options(fn () => MonitoringOption::where('type', 'pest_trace')->pluck('name', 'name'))
                        ->searchable()
                        ->nullable()
                        ->createOptionForm([TextInput::make('name')->label('სახელი')->required()])
                        ->createOptionUsing(fn (array $data): string => MonitoringOption::firstOrCreate(['type' => 'pest_trace', 'name' => $data['name']])->name),

                    Textarea::make('inspection_note')
                        ->label('ინსპექციის შენიშვნა')
                        ->rows(2)
                        ->nullable()
                        ->columnSpanFull(),

                    FileUpload::make('inspection_photos')
                        ->label('ფოტოები')
                        ->image()
                        ->multiple()
                        ->disk('public')
                        ->directory('monitoring-photos')
                        ->imagePreviewHeight('80')
                        ->panelLayout('grid')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Textarea::make('notes')
                ->label('შენიშვნა')
                ->rows(2)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monitoringSession.organization.name')
                    ->label('ობიექტი'),

                TextColumn::make('movementProductItem.productSettlement.name')
                    ->label('მოწყობილობა'),

                TextColumn::make('created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make()->label('რედაქტირება'),
                DeleteAction::make()->label('წაშლა'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitorings::route('/'),
            'create' => Pages\CreateMonitoring::route('/create'),
            'edit' => Pages\EditMonitoring::route('/{record}/edit'),
        ];
    }
}
