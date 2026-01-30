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

        if (!empty($hall->schema_data)) {
            // Удаляем старые места
            $hall->seats()->delete();

            // 👇 ИЗМЕНЕНИЕ ЗДЕСЬ: Было 50, стало 90. Сдвигаем начало отсчета вниз.
            $currentY = 90; 
            
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
                            // Y считается от нового базового $currentY
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