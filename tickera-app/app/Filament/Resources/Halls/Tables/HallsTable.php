<?php

namespace App\Filament\Resources\Halls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
// 👇 Добавляем импорт колонки
use Filament\Tables\Columns\TextColumn;

class HallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название зала')
                    ->searchable(),

                // 👇 Эта колонка покажет, сколько мест создалось в базе
                TextColumn::make('seats_count')
                    ->counts('seats')
                    ->label('Количество мест')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}