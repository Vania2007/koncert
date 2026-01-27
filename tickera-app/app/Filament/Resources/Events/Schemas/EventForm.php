<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Название события')
                    ->required()
                    ->maxLength(255),
                    
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
                    
                TextInput::make('location')
                    ->label('Место проведения')
                    ->required(),
                    
                DateTimePicker::make('start_time')
                    ->label('Начало')
                    ->required(),
                    
                DateTimePicker::make('end_time')
                    ->label('Конец'),
            ]);
    }
}