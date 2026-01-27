<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TicketTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketTypes';

    protected static ?string $title = 'Типы билетов и Цены';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Используем полные пути для полей формы
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Название (VIP, Входной...)')
                    ->required()
                    ->maxLength(255),

                \Filament\Forms\Components\TextInput::make('price')
                    ->label('Цена')
                    ->numeric()
                    ->prefix('₴')
                    ->required(),

                \Filament\Forms\Components\TextInput::make('quantity')
                    ->label('Количество мест')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // Используем полные пути для колонок таблицы
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Название'),

                \Filament\Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('UAH'),

                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->label('Осталось мест'),
            ])
            ->headerActions([
                // 👇 Вот здесь была ошибка. Теперь используем полный путь:
                \Filament\Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
