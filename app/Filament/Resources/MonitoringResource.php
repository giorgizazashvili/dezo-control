<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoringResource\Pages;
use App\Models\Dimension;
use App\Models\Monitoring;
use App\Models\Organization;
use App\Models\SettlementComponent;
use App\Services\MonitoringService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoringResource extends Resource
{
    protected static ?string $model = Monitoring::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'მონიტორინგი';

    protected static ?string $modelLabel = 'მონიტორინგი';

    protected static ?string $pluralModelLabel = 'მონიტორინგები';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // ── ობიექტი ────────────────────────────────────────────────
            Select::make('organization_id')
                ->label('ობიექტი')
                ->relationship('organization', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            // ── ბოქსის QR სკანი ────────────────────────────────────────
            TextInput::make('qr_data')
                ->label('QR კოდი (სკანი)')
                ->placeholder('სკანერი ჩასვამს მონაცემებს...')
                ->live()
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if (! $state) {
                        $set('movement_product_item_id', null);
                        $set('_box_product', null);
                        $set('_box_quantity', null);
                        $set('_box_date', null);
                        return;
                    }

                    $item = app(MonitoringService::class)->findBoxFromQr($state);

                    if ($item) {
                        $product = $item->productSettlement;
                        $set('movement_product_item_id', $item->id);
                        $set('_box_product', $product->name . ' — ' . ($product->dimension?->name ?? ''));
                        $set('_box_quantity', rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.'));
                        $set('_box_date', $item->movement->created_at->format('d.m.Y H:i'));
                    } else {
                        $set('movement_product_item_id', null);
                        $set('_box_product', 'ბოქსი ვერ მოიძებნა');
                        $set('_box_quantity', null);
                        $set('_box_date', null);
                    }
                })
                ->columnSpanFull(),

            Hidden::make('movement_product_item_id')
                ->required(),

            // ── ბოქსის მონაცემები (ავტო-შევსება) ─────────────────────
            TextInput::make('_box_product')
                ->label('პროდუქტი')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('—')
                ->visible(fn (Get $get) => (bool) $get('qr_data')),

            TextInput::make('_box_quantity')
                ->label('რაოდენობა')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('—')
                ->visible(fn (Get $get) => (bool) $get('qr_data')),

            TextInput::make('_box_date')
                ->label('მიღების თარიღი')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('—')
                ->visible(fn (Get $get) => (bool) $get('qr_data')),

            // ── ჩანაცვლებული კომპონენტები ─────────────────────────────
            Repeater::make('componentReplacements')
                ->label('ჩანაცვლებული კომპონენტები')
                ->relationship('componentReplacements')
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
                ->columnSpanFull(),

            // ── შენიშვნა ───────────────────────────────────────────────
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
                TextColumn::make('organization.name')
                    ->label('ობიექტი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movementProductItem.productSettlement.name')
                    ->label('პროდუქტი')
                    ->placeholder('—'),

                TextColumn::make('componentReplacements_count')
                    ->label('ჩანაცვლება')
                    ->counts('componentReplacements')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make()->label('რედაქტირება'),
                DeleteAction::make()->label('წაშლა'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()->label('წაშლა'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMonitorings::route('/'),
            'create' => Pages\CreateMonitoring::route('/create'),
            'edit'   => Pages\EditMonitoring::route('/{record}/edit'),
        ];
    }
}
