<?php

namespace App\Filament\Resources\Halls\Pages;

use App\Filament\Resources\Halls\HallResource;
use App\Models\Seat;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHall extends EditRecord
{
    protected static string $resource = HallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $hall = $this->record;

        // Если в зале настроена схема, перегенерируем места
        if (!empty($hall->schema_data)) {

            // 1. ВАЖНО: Удаляем все старые места этого зала, чтобы не было дублей
            $hall->seats()->delete();

            $currentY = 50;

            // 2. Генерируем новые места по сохраненной схеме
            foreach ($hall->schema_data as $block) {
                $section = $block['section_name'];
                $rowCount = (int) $block['rows'];
                $seatsCount = (int) $block['seats_per_row'];

                for ($r = 1; $r <= $rowCount; $r++) {
                    for ($s = 1; $s <= $seatsCount; $s++) {
                        Seat::create([
                            'hall_id' => $hall->id,
                            'section' => $section,
                            'row' => $r,
                            'number' => $s,
                            'x' => 50 + ($s * 35),
                            'y' => $currentY + ($r * 35),
                        ]);
                    }
                }
                // Сдвигаем позицию для следующего сектора
                $currentY += ($rowCount * 35) + 50;
            }
        }
    }
}
