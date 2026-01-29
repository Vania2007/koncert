<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Schemas\Schema;
// 👇 Импорты полей ввода (Forms)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select; // <--- Добавили Select

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
                    
                // 👇 Добавляем выбор зала
                Select::make('hall_id')
                    ->label('Выберите зал')
                    ->relationship('hall', 'name') // Связь с моделью Hall, показываем поле name
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('location')
                    ->label('Адрес (текстом)')
                    ->required(),
                    
                DateTimePicker::make('start_time')
                    ->label('Начало')
                    ->required(),
                    
                DateTimePicker::make('end_time')
                    ->label('Конец'),
                    
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
            ]);
    }
}