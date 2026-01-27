<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                    
                TextColumn::make('start_time')
                    ->label('Дата начала')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                    
                TextColumn::make('location')
                    ->label('Место'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Пока пусто, проверяем работу таблицы
            ])
            ->bulkActions([
                // Пока пусто
            ]);
    }
}