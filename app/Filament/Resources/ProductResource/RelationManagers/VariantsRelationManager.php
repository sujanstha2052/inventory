<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('name')
                    ->maxLength(255),
                KeyValue::make('variant_attributes')
                    ->label('Attributes (e.g., color: Red, size: XL)')
                    ->keyLabel('Attribute')
                    ->valueLabel('Value')
                    ->addActionLabel('Add Attribute'),
                TextInput::make('purchase_price')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('selling_price')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('barcode')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_default')
                    ->label('Default Variant'),
                TextInput::make('weight')
                    ->numeric()
                    ->label('Weight (kg)'),
                TextInput::make('width')
                    ->numeric()
                    ->label('Width (cm)'),
                TextInput::make('height')
                    ->numeric()
                    ->label('Height (cm)'),
                TextInput::make('depth')
                    ->numeric()
                    ->label('Depth (cm)'),
                TextInput::make('low_stock_threshold')
                    ->numeric(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('variant_attributes')
                    ->formatStateUsing(fn($state) => json_encode($state))
                    ->limit(30),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
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
