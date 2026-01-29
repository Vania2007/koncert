<?php

namespace App\Filament\Resources\Halls\Schemas;

use Filament\Schemas\Schema;
// 👇 В v5 разметка (Section, Grid) лежит в Schemas
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
// 👇 А поля ввода (TextInput, Repeater) остались в Forms
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class HallForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название зала')
                ->required()
                ->maxLength(255),

            Section::make('Конструктор мест')
                ->description('Добавьте сектора, укажите количество рядов и мест')
                ->schema([
                    Repeater::make('seat_generators')
                        ->label('Сектора')
                        ->schema([
                            TextInput::make('section_name')
                                ->label('Название сектора (Партер)')
                                ->required(),
                            
                            Grid::make(2)->schema([
                                TextInput::make('rows')
                                    ->label('Рядов')
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
                        // 👇 Важно: эти данные не идут напрямую в таблицу halls
                        ->dehydrated(false) 
                ]),
        ]);
    }
}