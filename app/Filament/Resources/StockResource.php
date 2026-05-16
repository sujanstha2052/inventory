<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_variant_id')
                    ->relationship('variant', 'sku')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('batch_number')
                    ->required()
                    ->maxLength(255),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0.01),
                TextInput::make('unit_cost')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                DatePicker::make('received_at')
                    ->default(now())
                    ->required(),
                DatePicker::make('expiry_date')
                    ->nullable(),
                Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.sku')->label('SKU')->searchable(),
                TextColumn::make('warehouse.name')->label('Warehouse'),
                TextColumn::make('batch_number')->searchable(),
                TextColumn::make('quantity'),
                TextColumn::make('unit_cost')->money('USD'),
                TextColumn::make('received_at')->date(),
                TextColumn::make('expiry_date')->date(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
