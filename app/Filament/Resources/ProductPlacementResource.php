<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPlacementResource\Pages;
use App\Models\Movement;
use App\Models\ProductSettlement;
use App\Services\MovementService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class ProductPlacementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'ობიექტზე განთავსება';

    protected static ?string $slug = 'product-placements';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'ობიექტზე განთავსება';

    protected static ?string $pluralModelLabel = 'ობიექტზე განთავსებები';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('operation_type', Movement::OPERATION_PRODUCT_PLACEMENT);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('organization_id')
                ->label('ორგანიზაცია')
                ->relationship('organization', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Textarea::make('comment')
                ->label('კომენტარი')
                ->rows(2)
                ->nullable(),

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
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization.name')
                    ->label('ორგანიზაცია')
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
            'index'  => Pages\ListProductPlacements::route('/'),
            'create' => Pages\CreateProductPlacement::route('/create'),
            'edit'   => Pages\EditProductPlacement::route('/{record}/edit'),
        ];
    }
}
