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
        $hall = $this->record;

        if (!empty($hall->schema_data)) {
            
            // 👇 ИЗМЕНЕНИЕ ЗДЕСЬ: Тоже ставим 90 для новых залов.
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
                            'y' => $currentY + ($r * 35),
                        ]);
                    }
                }
                $currentY += ($rowCount * 35) + 50;
            }
        }
    }
}