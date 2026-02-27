<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettlementComponentResource\Pages;
use App\Models\Dimension;
use App\Models\SettlementComponent;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettlementComponentResource extends Resource
{
    protected static ?string $model = SettlementComponent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'კომპონენტების დას.';

    protected static ?string $modelLabel = 'კომპონენტი';

    protected static ?string $pluralModelLabel = 'კომპონენტების დას.';

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
                ->createOptionUsing(function (array $data): int {
                    return Dimension::create($data)->id;
                }),
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

                TextColumn::make('dimension.name')
                    ->label('განზომილება')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('შექმნილია')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index'  => Pages\ListSettlementComponents::route('/'),
            'create' => Pages\CreateSettlementComponent::route('/create'),
            'edit'   => Pages\EditSettlementComponent::route('/{record}/edit'),
        ];
    }
}
