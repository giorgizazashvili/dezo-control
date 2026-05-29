<?php

namespace App\Filament\Resources\MonitoringOptions;

use App\Filament\Resources\MonitoringOptions\Pages\CreateMonitoringOption;
use App\Filament\Resources\MonitoringOptions\Pages\EditMonitoringOption;
use App\Filament\Resources\MonitoringOptions\Pages\ListMonitoringOptions;
use App\Filament\Resources\MonitoringOptions\Schemas\MonitoringOptionForm;
use App\Filament\Resources\MonitoringOptions\Tables\MonitoringOptionsTable;
use App\Models\MonitoringOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MonitoringOptionResource extends Resource
{
    protected static ?string $model = MonitoringOption::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static \UnitEnum|string|null $navigationGroup = 'ცნობარები';

    protected static ?string $navigationLabel = 'მონიტორინგის ოფციები';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'ოფცია';

    protected static ?string $pluralModelLabel = 'მონიტორინგის ოფციები';

    public static function typeOptions(): array
    {
        return [
            'pest_type' => 'მავნებლის ტიპი',
            'bait_status' => 'სატყუარას მდგომარეობა',
            'risk_level' => 'რისკის დონე',
            'action_taken' => 'მიღებული ზომა',
            'used_apparatus' => 'გამოყენებული აპარატი',
            'pest_trace' => 'მავნებლის კვალის დაფიქსირება',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return MonitoringOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonitoringOptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonitoringOptions::route('/'),
            'create' => CreateMonitoringOption::route('/create'),
            'edit' => EditMonitoringOption::route('/{record}/edit'),
        ];
    }
}
