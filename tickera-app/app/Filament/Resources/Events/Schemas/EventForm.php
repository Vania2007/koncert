<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor; // Лучше чем Textarea для описания
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload; // Загрузка фото
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3) // Сетка на 3 колонки
                    ->schema([
                        
                        // ЛЕВАЯ КОЛОНКА (2/3 ширины)
                        Section::make('Детали события')
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Название события')
                                    ->required()
                                    ->maxLength(255),

                                // Используем RichEditor для красивого текста, или Textarea если хотите проще
                                RichEditor::make('description') 
                                    ->label('Описание')
                                    ->required()
                                    ->columnSpanFull(),

                                Select::make('hall_id')
                                    ->label('Выберите зал')
                                    ->relationship('hall', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        // ПРАВАЯ КОЛОНКА (1/3 ширины)
                        Section::make('Медиа и Локация')
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Афиша (Вертикальная)')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('events')
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('city')
                                    ->label('Город')
                                    ->placeholder('Киев')
                                    ->required(),

                                TextInput::make('location')
                                    ->label('Адрес')
                                    ->placeholder('ул. Крещатик, 1')
                                    ->required(),

                                DateTimePicker::make('start_time')
                                    ->label('Начало')
                                    ->required(),

                                DateTimePicker::make('end_time')
                                    ->label('Конец'),
                            ]),
                    ]),
            ]);
    }
}