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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

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
            ->filters([
                SelectFilter::make('operation_type')
                    ->label('ოპერაციის ტიპი')
                    ->options([
                        Movement::OPERATION_COMPONENT_RECEIPT => 'კომპონენტის მიღება',
                        Movement::OPERATION_PRODUCT_RECEIPT   => 'პროდუქტის მიღება',
                        Movement::OPERATION_PRODUCT_PLACEMENT => 'ობიექტზე განთავსება',
                    ]),

                SelectFilter::make('organization_id')
                    ->label('ობიექტი')
                    ->relationship('organization', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_from')
                    ->label('თარიღიდან')
                    ->form([
                        DatePicker::make('created_from')->label('თარიღიდან'),
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['created_from'],
                        fn ($q) => $q->whereDate('created_at', '>=', $data['created_from'])
                    )),

                Filter::make('created_until')
                    ->label('თარიღამდე')
                    ->form([
                        DatePicker::make('created_until')->label('თარიღამდე'),
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['created_until'],
                        fn ($q) => $q->whereDate('created_at', '<=', $data['created_until'])
                    )),
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
            'index'  => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit'   => Pages\EditMovement::route('/{record}/edit'),
        ];
    }
}
