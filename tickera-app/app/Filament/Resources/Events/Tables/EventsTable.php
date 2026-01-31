<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\EditAction;     // <--- ВАЖНО: Добавлен импорт
use Filament\Tables\Actions\DeleteAction;   // <--- ВАЖНО: Добавлен импорт
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Постер')
                    ->height(80)
                    ->circular(false),

                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('city')
                    ->label('Город')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('hall.name') // Показываем зал
                    ->label('Зал')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Начало')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_time', 'asc');
    }
}