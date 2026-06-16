<?php

namespace App\Filament\Pages;

use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PestActivityReportPage extends Page
{
    protected static \UnitEnum|string|null $navigationGroup = 'რეპორტები';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'ტრენდ ანალიზი';

    protected static ?string $title = 'ტრენდ ანალიზი';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.pest-activity-report-page';

    public ?int $organization_id = null;

    public ?string $date_from = null;

    public ?string $date_to = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isAdmin() || $user?->isOfficeManager();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Select::make('organization_id')
                        ->label('ობიექტი')
                        ->options(Organization::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live(),

                    DatePicker::make('date_from')
                        ->label('თარიღიდან')
                        ->format('Y-m-d')
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->live(),

                    DatePicker::make('date_to')
                        ->label('თარიღამდე')
                        ->format('Y-m-d')
                        ->displayFormat('d/m/Y')
                        ->required()
                        ->live(),
                ])
                ->columns(3),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel-ში ჩამოტვირთვა')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => ($this->organization_id && $this->date_from && $this->date_to)
                    ? route('export.pest-activity-report', [
                        'organization_id' => $this->organization_id,
                        'date_from' => $this->date_from,
                        'date_to' => $this->date_to,
                    ])
                    : null
                )
                ->disabled(fn () => ! $this->organization_id || ! $this->date_from || ! $this->date_to)
                ->openUrlInNewTab(),
        ];
    }
}
