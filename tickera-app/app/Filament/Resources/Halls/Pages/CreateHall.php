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
        // Получаем состояние формы
        $state = $this->form->getState();

        if (!empty($state['seat_generators'])) {
            $hall = $this->record;
            
            foreach ($state['seat_generators'] as $block) {
                $section = $block['section_name'];
                $rowCount = (int) $block['rows'];
                $seatsCount = (int) $block['seats_per_row'];
                
                // 👇 ВАЖНО: Берем координаты, которые мы задали мышкой в редакторе
                // Если их нет (обычный ввод), будет 0
                $baseX = (int) ($block['x'] ?? 0);
                $baseY = (int) ($block['y'] ?? 0);
                
                // Шаг отрисовки (должен совпадать с фронтендом, например 30px)
                $seatSize = 30; 

                for ($r = 1; $r <= $rowCount; $r++) {
                    for ($s = 1; $s <= $seatsCount; $s++) {
                        Seat::create([
                            'hall_id' => $hall->id,
                            'section' => $section,
                            'row' => $r,
                            'number' => $s,
                            
                            // 👇 Считаем позицию: База блока + (Номер места * Размер)
                            'x' => $baseX + ($s * $seatSize), 
                            'y' => $baseY + ($r * $seatSize),
                        ]);
                    }
                }
            }
        }
    }
}