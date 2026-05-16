<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Details')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->email(),
                                TextInput::make('phone'),
                            ]),
                        TextInput::make('order_number')
                            ->default(fn() => 'ORD-' . now()->format('Y') . '-' . str_pad((Order::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing',
                                'dispatched' => 'Dispatched',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                                'returned' => 'Returned',
                            ])
                            ->default('draft')
                            ->required()
                            ->reactive(),
                        Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Order Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_variant_id')
                                    ->label('Product Variant')
                                    ->relationship('variant', 'sku')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $variant = ProductVariant::find($state);
                                            if ($variant) {
                                                $set('unit_price', $variant->selling_price);
                                                $set('quantity', 1);
                                                $set('discount_amount', 0);
                                                $set('tax_rate', 15.00);
                                                $set('total_price', $variant->selling_price);
                                                $set('tax_amount', $variant->selling_price * 0.15);
                                            }
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        self::updateLineTotals($set, $get);
                                    }),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        self::updateLineTotals($set, $get);
                                    }),
                                TextInput::make('discount_amount')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('$')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        self::updateLineTotals($set, $get);
                                    }),
                                TextInput::make('tax_rate')
                                    ->numeric()
                                    ->default(15)
                                    ->suffix('%')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        self::updateLineTotals($set, $get);
                                    }),
                                TextInput::make('tax_amount')
                                    ->numeric()
                                    ->disabled()
                                    ->default(0)
                                    ->prefix('$'),
                                TextInput::make('total_price')
                                    ->label('Line Total')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateOrderTotals($get, $set);
                            }),
                    ]),

                Section::make('Financials')
                    ->schema([
                        TextInput::make('subtotal')
                            ->numeric()
                            ->disabled()
                            ->prefix('$'),
                        TextInput::make('discount_amount')
                            ->numeric()
                            ->disabled()
                            ->prefix('$'),
                        TextInput::make('tax_amount')
                            ->numeric()
                            ->disabled()
                            ->prefix('$'),
                        TextInput::make('grand_total')
                            ->numeric()
                            ->disabled()
                            ->prefix('$'),
                    ])
                    ->columns(2),

                Section::make('Timestamps')
                    ->schema([
                        DateTimePicker::make('ordered_at'),
                        DateTimePicker::make('dispatched_at'),
                        DateTimePicker::make('delivered_at'),
                        DateTimePicker::make('cancelled_at'),
                    ])
                    ->collapsed()
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'warning',
                        'processing' => 'info',
                        'dispatched' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'returned' => 'danger',
                    }),
                TextColumn::make('grand_total')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('ordered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer')
                    ->relationship('customer', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'dispatched' => 'Dispatched',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'returned' => 'Returned',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn(Order $record) => $record->update([
                        'status' => 'confirmed',
                        'ordered_at' => now(),
                    ]))
                    ->visible(fn(Order $record) => $record->status === 'draft')
                    ->requiresConfirmation(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    // Helper methods for auto-calculation
    protected static function updateLineTotals(Set $set, Get $get): void
    {
        $unitPrice = (float) $get('unit_price');
        $quantity = (float) $get('quantity');
        $discount = (float) $get('discount_amount');
        $taxRate = (float) $get('tax_rate');

        $lineTotal = ($unitPrice * $quantity) - $discount;
        $taxAmount = $lineTotal * ($taxRate / 100);

        $set('total_price', $lineTotal);
        $set('tax_amount', $taxAmount);
    }

    protected static function updateOrderTotals(Get $get, Set $set): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            if (empty($item)) continue;
            $subtotal += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
            $totalDiscount += $item['discount_amount'] ?? 0;
            $totalTax += $item['tax_amount'] ?? 0;
        }

        $set('subtotal', $subtotal);
        $set('discount_amount', $totalDiscount);
        $set('tax_amount', $totalTax);
        $set('grand_total', $subtotal - $totalDiscount + $totalTax);
    }
}
