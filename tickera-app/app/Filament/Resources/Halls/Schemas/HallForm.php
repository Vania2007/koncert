<?php

namespace App\Filament\Resources\Halls\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                ->schema([
                    // 👇 ИЗМЕНИЛИ ИМЯ НА schema_data И УБРАЛИ dehydrated(false)
                    Repeater::make('schema_data') 
                        ->label('Сектора')
                        ->schema([
                            TextInput::make('section_name')
                                ->label('Название сектора')
                                ->required(),
                            
                            Grid::make(2)->schema([
                                TextInput::make('rows')
                                    ->label('Рядов')
                                    ->numeric()
                                    ->required(),
                                
                                TextInput::make('seats_per_row')
                                    ->label('Мест в ряду')
                                    ->numeric()
                                    ->required(),
                            ]),
                        ])
                ]),
        ]);
    }
}