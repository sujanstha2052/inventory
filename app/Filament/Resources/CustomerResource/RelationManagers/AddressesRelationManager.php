<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'address_line_1';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->options([
                        'billing' => 'Billing',
                        'shipping' => 'Shipping',
                        'both' => 'Both',
                    ])
                    ->required()
                    ->default('billing'),
                TextInput::make('address_line_1')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line_2')
                    ->maxLength(255),
                TextInput::make('city')
                    ->maxLength(255),
                TextInput::make('state')
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->maxLength(20),
                TextInput::make('country')
                    ->maxLength(255)
                    ->default('US'),
                Toggle::make('is_default')
                    ->label('Default Address'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'billing' => 'info',
                        'shipping' => 'success',
                        'both' => 'warning',
                    }),
                TextColumn::make('address_line_1')
                    ->searchable(),
                TextColumn::make('city'),
                TextColumn::make('state'),
                TextColumn::make('country'),
                IconColumn::make('is_default')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
