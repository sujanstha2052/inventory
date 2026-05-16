<?php

namespace App\Filament\Resources\DispatchResource\RelationManagers;

use App\Models\OrderItem;
use App\Models\Stock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('order_item_id')
                    ->label('Order Item')
                    ->options(function (callable $get) {
                        $dispatch = $this->getOwnerRecord();
                        if (! $dispatch) {
                            return [];
                        }

                        return OrderItem::where('order_id', $dispatch->order_id)
                            ->with('variant')
                            ->get()
                            ->mapWithKeys(fn ($item) => [
                                $item->id => "SKU: {$item->variant->sku} (Qty ordered: {$item->quantity})",
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $this->updateStockOptions($state, $set)),

                Select::make('stock_id')
                    ->label('Stock Batch')
                    ->options(function (callable $get) {
                        $orderItemId = $get('order_item_id');
                        if (! $orderItemId) {
                            return [];
                        }
                        $orderItem = OrderItem::find($orderItemId);
                        if (! $orderItem) {
                            return [];
                        }
                        $dispatch = $this->getOwnerRecord();

                        return Stock::where('product_variant_id', $orderItem->product_variant_id)
                            ->where('warehouse_id', $dispatch->warehouse_id)
                            ->where('quantity', '>', 0)
                            ->orderBy('received_at')
                            ->get()
                            ->mapWithKeys(fn ($stock) => [
                                $stock->id => "Batch {$stock->batch_number} (Qty: {$stock->quantity})",
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->reactive(),

                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->label('Quantity to Dispatch'),
            ]);
    }

    private function updateStockOptions($orderItemId, callable $set): void
    {
        // Reset stock_id when order item changes
        $set('stock_id', null);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orderItem.variant.sku')
                    ->label('SKU'),
                TextColumn::make('stock.batch_number')
                    ->label('Batch'),
                TextColumn::make('quantity')
                    ->label('Qty'),
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
