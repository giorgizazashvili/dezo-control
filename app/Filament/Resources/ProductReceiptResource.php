<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReceiptResource\Pages;
use App\Models\Dimension;
use App\Models\Movement;
use App\Models\ProductSettlement;
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
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class ProductReceiptResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationLabel = 'პროდუქტის მიღება';

    protected static ?string $slug = 'product-receipts';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'პროდუქტის მიღება';

    protected static ?string $pluralModelLabel = 'პროდუქტის მიღებები';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('operation_type', Movement::OPERATION_PRODUCT_RECEIPT);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('თარიღი')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
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
            'index'  => Pages\ListProductReceipts::route('/'),
            'create' => Pages\CreateProductReceipt::route('/create'),
            'edit'   => Pages\EditProductReceipt::route('/{record}/edit'),
        ];
    }
}
