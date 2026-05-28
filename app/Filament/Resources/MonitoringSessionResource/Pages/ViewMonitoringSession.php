<?php

namespace App\Filament\Resources\MonitoringSessionResource\Pages;

use App\Filament\Resources\MonitoringResource;
use App\Filament\Resources\MonitoringSessionResource;
use App\Models\Monitoring;
use App\Models\MonitoringLog;
use App\Models\Movement;
use App\Models\MovementProductPlacementItem;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewMonitoringSession extends ViewRecord implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = MonitoringSessionResource::class;

    protected string $view = 'filament.resources.monitoring-session-resource.pages.view-monitoring-session';

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->record->organization->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('finish')
                ->label('დასრულება')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => ! $this->record->finished_at)
                ->modalHeading('სესიის დასრულება')
                ->schema([
                    DateTimePicker::make('finished_at')
                        ->label('დასრულების დრო')
                        ->default(now())
                        ->required()
                        ->seconds(false),
                ])
                ->action(function (array $data) {
                    $this->record->update(['finished_at' => $data['finished_at']]);
                    $this->record->refresh();
                }),

            Action::make('editNotes')
                ->label('კომენტარი / ფოტო')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->modalHeading('კომენტარი და ფოტოები')
                ->schema([
                    Textarea::make('notes')
                        ->label('კომენტარი')
                        ->rows(4)
                        ->nullable(),
                    FileUpload::make('photos')
                        ->label('ფოტოები')
                        ->multiple()
                        ->image()
                        ->disk('public')
                        ->directory('monitoring-sessions')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->fillForm(fn () => [
                    'notes' => $this->record->notes,
                    'photos' => $this->record->photos,
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'notes' => $data['notes'],
                        'photos' => $data['photos'] ?? [],
                    ]);
                    $this->record->refresh();
                }),

            Action::make('scan')
                ->label('სკანირება')
                ->icon('heroicon-o-camera')
                ->color('gray')
                ->modalContent(view('filament.modals.qr-scanner'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('დახურვა')
                ->modalWidth('sm'),

            Action::make('print')
                ->label('რეპორტი')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('print.service-report.session', $this->record))
                ->openUrlInNewTab(),

            Action::make('back')
                ->label('სია')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(MonitoringSessionResource::getUrl('index')),

            DeleteAction::make()
                ->label('წაშლა')
                ->modalHeading('სესიის წაშლა')
                ->modalDescription('დარწმუნებული ხართ? ამ სესიის ყველა მონიტორინგი წაიშლება.')
                ->modalSubmitActionLabel('წაშლა')
                ->successRedirectUrl(MonitoringSessionResource::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        $session = $this->record;

        $checkedMonitorings = Monitoring::query()
            ->where('monitoring_session_id', $session->id)
            ->with('movementProductItem:id,product_settlement_id')
            ->get()
            ->filter(fn ($m) => $m->movementProductItem)
            ->keyBy(fn ($m) => $m->movementProductItem->product_settlement_id);

        $checkedProductSettlementIds = $checkedMonitorings->keys()->all();

        $inspectedUniqueCodes = MonitoringLog::query()
            ->whereIn('monitoring_id', $checkedMonitorings->map->id->values())
            ->whereNotNull('unique_code')
            ->pluck('unique_code')
            ->flip();

        $monitoringIdByUniqueCode = MonitoringLog::query()
            ->whereIn('monitoring_id', $checkedMonitorings->map->id->values())
            ->whereNotNull('unique_code')
            ->pluck('monitoring_id', 'unique_code');

        return $table
            ->query(fn () => MovementProductPlacementItem::query()
                ->whereHas('movement', fn (Builder $q) => $q
                    ->where('organization_id', $session->organization_id)
                    ->where('operation_type', Movement::OPERATION_PRODUCT_PLACEMENT)
                )
                ->with(['productSettlement.dimension'])
                ->orderByDesc('id')
            )
            ->columns([
                TextColumn::make('productSettlement.name')
                    ->label('მოწყობილობა')
                    ->searchable(),

                TextColumn::make('productSettlement.dimension.name')
                    ->label('განზომილება')
                    ->placeholder('—'),

                TextColumn::make('unique_code')
                    ->label('კოდი')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('zone')
                    ->label('ზონა')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('location')
                    ->label('მდებარეობა')
                    ->placeholder('—')
                    ->searchable(),

                IconColumn::make('checked')
                    ->label('სტატუსი')
                    ->state(function (MovementProductPlacementItem $r) use ($inspectedUniqueCodes, $checkedProductSettlementIds) {
                        if ($r->unique_code) {
                            return $inspectedUniqueCodes->has($r->unique_code);
                        }

                        return in_array($r->product_settlement_id, $checkedProductSettlementIds);
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-ellipsis-horizontal-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->recordActions([
                Action::make('qr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->url(fn (MovementProductPlacementItem $r) => $r->unique_code
                        ? route('print.qr', $r->unique_code)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->hidden(fn (MovementProductPlacementItem $r) => ! $r->unique_code),

                Action::make('inspect')
                    ->label(function (MovementProductPlacementItem $r) use ($inspectedUniqueCodes, $checkedProductSettlementIds) {
                        $checked = $r->unique_code
                            ? $inspectedUniqueCodes->has($r->unique_code)
                            : in_array($r->product_settlement_id, $checkedProductSettlementIds);

                        return $checked ? 'რედაქტირება' : 'შემოწმება';
                    })
                    ->icon(function (MovementProductPlacementItem $r) use ($inspectedUniqueCodes, $checkedProductSettlementIds) {
                        $checked = $r->unique_code
                            ? $inspectedUniqueCodes->has($r->unique_code)
                            : in_array($r->product_settlement_id, $checkedProductSettlementIds);

                        return $checked ? 'heroicon-o-pencil' : 'heroicon-o-magnifying-glass';
                    })
                    ->color(function (MovementProductPlacementItem $r) use ($inspectedUniqueCodes, $checkedProductSettlementIds) {
                        $checked = $r->unique_code
                            ? $inspectedUniqueCodes->has($r->unique_code)
                            : in_array($r->product_settlement_id, $checkedProductSettlementIds);

                        return $checked ? 'gray' : 'primary';
                    })
                    ->url(function (MovementProductPlacementItem $r) use ($session, $checkedMonitorings, $checkedProductSettlementIds, $inspectedUniqueCodes, $monitoringIdByUniqueCode) {
                        $checked = $r->unique_code
                            ? $inspectedUniqueCodes->has($r->unique_code)
                            : in_array($r->product_settlement_id, $checkedProductSettlementIds);

                        if ($checked) {
                            if ($r->unique_code) {
                                $monitoringId = $monitoringIdByUniqueCode->get($r->unique_code);

                                return $monitoringId
                                    ? MonitoringResource::getUrl('edit', ['record' => $monitoringId])
                                    : null;
                            }

                            $monitoring = $checkedMonitorings->get($r->product_settlement_id);

                            return $monitoring
                                ? MonitoringResource::getUrl('edit', ['record' => $monitoring])
                                : null;
                        }

                        return MonitoringResource::getUrl('create').'?'.http_build_query([
                            'session_id' => $session->id,
                            'placement_item_id' => $r->id,
                        ]);
                    }),
            ]);
    }
}
