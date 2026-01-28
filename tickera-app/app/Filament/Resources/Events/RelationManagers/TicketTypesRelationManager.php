<?php

namespace App\Filament\Resources\Events\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'ticketTypes';

    protected static ?string $title = 'Типы билетов и Цены';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название (VIP, Входной...)')
                    ->required()
                    ->maxLength(255),

                TextInput::make('price')
                    ->label('Цена')
                    ->numeric()
                    ->prefix('₴')
                    ->required(),

                TextInput::make('quantity')
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
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Цена')
                    ->money('UAH'),

                TextColumn::make('quantity')
                    ->label('Осталось мест'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
