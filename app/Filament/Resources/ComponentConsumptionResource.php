<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentConsumptionResource\Pages;
use App\Models\Dimension;
use App\Models\Movement;
use App\Models\SettlementComponent;
use App\Services\MovementService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class ComponentConsumptionResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-down';

    protected static ?string $navigationLabel = 'კომპონენტის გახარჯვა';

    protected static ?string $slug = 'component-consumptions';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'კომპონენტის გახარჯვა';

    protected static ?string $pluralModelLabel = 'კომპონენტის გახარჯვები';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('operation_type', Movement::OPERATION_COMPONENT_CONSUMPTION)
            ->whereNull('source_movement_id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('organization_id')
                ->label('ობიექტი')
                ->relationship('organization', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),

            Repeater::make('componentItems')
                ->label('კომპონენტები')
                ->relationship('componentItems')
                ->schema([
                    Select::make('settlement_component_id')
                        ->label('კომპონენტი')
                        ->relationship('settlementComponent', 'name', fn ($q) => $q?->with('dimension'))
                        ->getOptionLabelFromRecordUsing(function (SettlementComponent $r) {
                            $stock    = app(MovementService::class)->getComponentStock($r->id);
                            $stockStr = rtrim(rtrim(number_format($stock, 4, '.', ''), '0'), '.') ?: '0';

                            return $r->name . ' — ' . ($r->dimension?->name ?? '') . ' | ნაშთი: ' . $stockStr;
                        })
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization.name')
                    ->label('ობიექტი')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
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
            'index'  => Pages\ListComponentConsumptions::route('/'),
            'create' => Pages\CreateComponentConsumption::route('/create'),
            'edit'   => Pages\EditComponentConsumption::route('/{record}/edit'),
        ];
    }
}
