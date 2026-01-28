<?php

namespace App\Filament\Resources\Tickets\Tables;

// 👇 ВАЖНО: Все действия теперь берем из Filament\Actions
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unique_code')
                    ->label('Код билета')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('ticketType.name')
                    ->label('Тип билета'),

                TextColumn::make('order.customer_email')
                    ->label('Email покупателя')
                    ->searchable(),

                IconColumn::make('is_checked_in')
                    ->label('Использован?')
                    ->boolean(),

                TextColumn::make('checked_in_at')
                    ->label('Время входа')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([ // 👇 В v5 это называется recordActions, а не actions
                // Наша кастомная кнопка "Пропустить"
                Action::make('checkIn')
                    ->label('Пропустить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => !$record->is_checked_in)
                    ->action(function ($record) {
                        $record->update([
                            'is_checked_in' => true,
                            'checked_in_at' => now(),
                        ]);
                    }),

                // Стандартные кнопки
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([ // 👇 В v5 это toolbarActions, а не bulkActions
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
