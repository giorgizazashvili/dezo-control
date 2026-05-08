<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\RestrictedToOfficeManager;
use App\Filament\Resources\ProductSettlementResource\Pages;
use App\Models\Dimension;
use App\Models\ProductSettlement;
use App\Models\SettlementComponent;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductSettlementResource extends Resource
{
    use RestrictedToOfficeManager;

    protected static ?string $model = ProductSettlement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static \UnitEnum|string|null $navigationGroup = 'ცნობარები';

    protected static ?string $navigationLabel = 'პროდუქტების დას.';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'დასახლება';

    protected static ?string $pluralModelLabel = 'პროდუქტების დას.';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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

            Repeater::make('items')
                ->label('კომპონენტები')
                ->relationship('items')
                ->schema([
                    Select::make('settlement_component_id')
                        ->label('კომპონენტი')
                        ->options(
                            SettlementComponent::with('dimension')
                                ->get()
                                ->mapWithKeys(fn (SettlementComponent $c) => [
                                    $c->id => $c->name.' — '.($c->dimension?->name ?? ''),
                                ])
                        )
                        ->searchable()
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
                ->addActionLabel('კომპონენტის დამატება')
                ->defaultItems(0)
                ->reorderable()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('დასახელება')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('კომპონენტები')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('შექმნილია')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make()->label('რედაქტირება'),
                DeleteAction::make()
                    ->label('წაშლა')
                    ->before(function (DeleteAction $action, ProductSettlement $record): void {
                        if ($record->movementProductItems()->exists() || $record->movementProductPlacementItems()->exists()) {
                            Notification::make()
                                ->title('წაშლა შეუძლებელია')
                                ->body('ეს ჩანაწერი გამოყენებულია და ვერ წაიშლება.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('წაშლა')
                        ->before(function (\Filament\Actions\DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records): void {
                            $blocked = $records->filter(fn (ProductSettlement $r) => $r->movementProductItems()->exists() || $r->movementProductPlacementItems()->exists());
                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->title('წაშლა შეუძლებელია')
                                    ->body('შერჩეული ჩანაწერ(ებ)ი გამოყენებულია და ვერ წაიშლება.')
                                    ->danger()
                                    ->send();
                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductSettlements::route('/'),
            'create' => Pages\CreateProductSettlement::route('/create'),
            'edit' => Pages\EditProductSettlement::route('/{record}/edit'),
        ];
    }
}
