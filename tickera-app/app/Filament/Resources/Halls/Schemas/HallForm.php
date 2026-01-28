<?php

namespace App\Filament\Resources\Halls\Schemas;

use Filament\Schemas\Schema;
// 👇 ВОТ ЭТИ СТРОКИ ОБЯЗАТЕЛЬНО НУЖНЫ:
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class HallForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Название зала')
                ->required()
                ->maxLength(255),

            Section::make('Конструктор мест')
                ->description('Добавьте блоки мест (например: Партер, 10 рядов, 15 мест в ряду)')
                ->schema([
                    Repeater::make('seat_generators')
                        ->label('Блоки мест')
                        ->schema([
                            TextInput::make('section_name')
                                ->label('Название сектора (Партер, Балкон)')
                                ->required(),
                            
                            Grid::make(2)->schema([
                                TextInput::make('rows')
                                    ->label('Количество рядов')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                
                                TextInput::make('seats_per_row')
                                    ->label('Мест в ряду')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                            ]),
                        ])
                        ->dehydrated(false) 
                ]),
        ]);
    }
}