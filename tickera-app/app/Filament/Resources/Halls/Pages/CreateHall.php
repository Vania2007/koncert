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
        // Получаем данные формы
        $data = $this->data;

        if (!empty($data['seat_generators'])) {
            $hall = $this->record;
            
            foreach ($data['seat_generators'] as $block) {
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
                            'x' => $s * 40, 
                            'y' => $r * 40,
                        ]);
                    }
                }
            }
        }
    }
}