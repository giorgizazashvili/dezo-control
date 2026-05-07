<?php

namespace App\Filament\Resources\ProductPlacementResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\ProductPlacementResource;
use App\Models\Movement;
use App\Models\MovementProductPlacementItem;
use App\Models\Organization;
use App\Models\ProductSettlement;
use App\Services\MovementService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class ListProductPlacements extends ListRecords
{
    protected static string $resource = ProductPlacementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ახალი განთავსება'),

            Action::make('downloadTemplate')
                ->label('შაბლონის ჩამოტვირთვა')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('export.product-placement-template'))
                ->openUrlInNewTab(),

            Action::make('importFromExcel')
                ->label('ექსელიდან იმპორტი')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('ექსელიდან განთავსების იმპორტი')
                ->modalSubmitActionLabel('იმპორტი')
                ->form([
                    Select::make('organization_id')
                        ->label('ორგანიზაცია')
                        ->options(Organization::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Textarea::make('comment')
                        ->label('კომენტარი')
                        ->rows(2)
                        ->nullable(),

                    FileUpload::make('file')
                        ->label('ექსელის ფაილი (.xlsx)')
                        ->disk('local')
                        ->directory('temp/imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $filePath = Storage::disk('local')->path($data['file']);

                    try {
                        $importer = new class implements ToCollection
                        {
                            public \Illuminate\Support\Collection $rows;

                            private bool $collected = false;

                            public function __construct()
                            {
                                $this->rows = collect();
                            }

                            public function collection(\Illuminate\Support\Collection $collection): void
                            {
                                if (! $this->collected) {
                                    $this->rows = $collection;
                                    $this->collected = true;
                                }
                            }
                        };

                        Excel::import($importer, $filePath);

                        $dataRows = $importer->rows
                            ->skip(1)
                            ->filter(fn ($row) => filled(trim((string) ($row[0] ?? ''))));

                        $productsByName = ProductSettlement::query()->pluck('id', 'name');

                        $errors = [];
                        $validRows = [];

                        foreach ($dataRows->values() as $index => $row) {
                            $productName = trim((string) ($row[0] ?? ''));

                            if (! $productsByName->has($productName)) {
                                $errors[] = 'მწკრივი '.($index + 2).": პროდუქტი \"{$productName}\" ვერ მოიძებნა";

                                continue;
                            }

                            $qty = (float) ($row[1] ?? 0);

                            if ($qty <= 0) {
                                $errors[] = 'მწკრივი '.($index + 2).': რაოდენობა უნდა იყოს 0-ზე მეტი';

                                continue;
                            }

                            $validRows[] = [
                                'product_settlement_id' => $productsByName[$productName],
                                'quantity' => $qty,
                                'unique_code' => filled($row[2] ?? '') ? trim((string) $row[2]) : null,
                                'zone' => filled($row[3] ?? '') ? trim((string) $row[3]) : null,
                                'location' => filled($row[4] ?? '') ? trim((string) $row[4]) : null,
                            ];
                        }

                        if (! empty($errors)) {
                            Notification::make()
                                ->title('იმპორტის შეცდომა')
                                ->body(implode("\n", $errors))
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        if (empty($validRows)) {
                            Notification::make()
                                ->title('ფაილი ცარიელია')
                                ->warning()
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($data, $validRows): void {
                            $movement = Movement::create([
                                'operation_type' => Movement::OPERATION_PRODUCT_PLACEMENT,
                                'organization_id' => $data['organization_id'],
                                'comment' => $data['comment'] ?? null,
                            ]);

                            foreach ($validRows as $row) {
                                MovementProductPlacementItem::create([
                                    'movement_id' => $movement->id,
                                    ...$row,
                                ]);
                            }

                            app(MovementService::class)->processProductPlacement($movement);
                        });

                        Notification::make()
                            ->title('წარმატებით შესრულდა')
                            ->body(count($validRows).' პროდუქტი განთავსდა')
                            ->success()
                            ->send();

                    } catch (InsufficientStockException $e) {
                        Notification::make()
                            ->title('ნაშთი არ არის საკმარისი')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }
                }),
        ];
    }
}
