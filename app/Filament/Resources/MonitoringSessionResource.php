<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoringSessionResource\Pages;
use App\Models\MonitoringSession;
use Filament\Actions\Action as TableRowAction;
use Filament\Actions\DeleteAction as TableRowDeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitoringSessionResource extends Resource
{
    protected static ?string $model = MonitoringSession::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'მონიტორინგი';

    protected static ?string $modelLabel = 'მონიტორინგი';

    protected static ?string $pluralModelLabel = 'მონიტორინგები';

    protected static ?int $navigationSort = 9;

    public static function canViewAny(): bool
    {
        return auth()->check();
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

            TextInput::make('technician')
                ->label('ტექნიკოსი')
                ->default(fn () => auth()->user()?->name)
                ->disabled()
                ->dehydrated()
                ->required()
                ->columnSpanFull(),

            DateTimePicker::make('started_at')
                ->label('დაწყების დრო')
                ->default(now())
                ->required()
                ->seconds(false)
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

                TextColumn::make('technician')
                    ->label('ტექნიკოსი')
                    ->searchable(),

                TextColumn::make('started_at')
                    ->label('დაწყება')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('finished_at')
                    ->label('დასრულება')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('მიმდინარე')
                    ->sortable(),

                TextColumn::make('monitorings_count')
                    ->label('შემოწმებული')
                    ->counts('monitorings')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                TableRowAction::make('open')
                    ->label('გახსნა')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn (MonitoringSession $record) => Pages\ViewMonitoringSession::getUrl(['record' => $record])),

                TableRowDeleteAction::make()
                    ->label('წაშლა')
                    ->modalHeading('სესიის წაშლა')
                    ->modalDescription('დარწმუნებული ხართ? ამ სესიის ყველა მონიტორინგი წაიშლება.')
                    ->modalSubmitActionLabel('წაშლა'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringSessions::route('/'),
            'create' => Pages\CreateMonitoringSession::route('/create'),
            'view' => Pages\ViewMonitoringSession::route('/{record}'),
        ];
    }
}
