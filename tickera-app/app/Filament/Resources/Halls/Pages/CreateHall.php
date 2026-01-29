<?php

namespace App\Filament\Resources\Halls\Pages;

use App\Filament\Resources\Halls\HallResource;
use App\Models\Seat;
use Filament\Resources\Pages\CreateRecord;

class CreateHall extends CreateRecord
{
    protected static string $resource = HallResource::class;

    protected function afterCreate(): void
    {
        $hall = $this->record; // Зал уже создан и schema_data сохранена в нем

        // Проверяем, есть ли сохраненная схема
        if (!empty($hall->schema_data)) {
            
            $currentY = 50; 
            
            // 👇 Берем данные прямо из модели
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
                $currentY += ($rowCount * 35) + 50;
            }
        }
    }
}