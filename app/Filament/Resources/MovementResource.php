<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovementResource\Pages;
use App\Models\Dimension;
use App\Models\Movement;
use App\Models\ProductSettlement;
use App\Models\SettlementComponent;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('operation_type')
                    ->label('ოპერაციის ტიპი')
                    ->formatStateUsing(fn (string $state) => Movement::operationTypes()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        Movement::OPERATION_COMPONENT_RECEIPT => 'info',
                        Movement::OPERATION_PRODUCT_RECEIPT   => 'success',
                        default                               => 'gray',
                    })
                    ->sortable(),

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
            'index'  => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit'   => Pages\EditMovement::route('/{record}/edit'),
        ];
    }
}
