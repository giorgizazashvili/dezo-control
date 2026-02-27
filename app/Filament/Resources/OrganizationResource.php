<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'ორგანიზაციები';

    protected static ?string $modelLabel = 'ორგანიზაცია';

    protected static ?string $pluralModelLabel = 'ორგანიზაციები';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('legal_form')
                ->label('იურიდიული ფორმა')
                ->options(Organization::legalForms())
                ->required()
                ->searchable(),

            TextInput::make('name')
                ->label('დასახელება')
                ->required()
                ->maxLength(255),

            TextInput::make('identification')
                ->label('საიდენტიფიკაციო კოდი')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            TextInput::make('address')
                ->label('მისამართი')
                ->required()
                ->maxLength(500),

            TextInput::make('director')
                ->label('დირექტორი')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legal_form')
                    ->label('იურიდიული ფორმა')
                    ->formatStateUsing(fn (string $state) => Organization::legalForms()[$state] ?? $state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('დასახელება')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('identification')
                    ->label('საიდენტიფიკაციო კოდი')
                    ->searchable(),

                TextColumn::make('address')
                    ->label('მისამართი')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('director')
                    ->label('დირექტორი')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('შექმნილია')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('legal_form')
                    ->label('იურიდიული ფორმა')
                    ->options(Organization::legalForms()),
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
            'index'  => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit'   => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
