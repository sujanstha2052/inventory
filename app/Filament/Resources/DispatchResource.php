<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DispatchResource\Pages;
use App\Filament\Resources\DispatchResource\RelationManagers\ItemsRelationManager;
use App\Models\Dispatch;
use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class DispatchResource extends Resource
{
    protected static ?string $model = Dispatch::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dispatch Details')
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),
                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('dispatch_number')
                            ->default(fn () => 'DISP-'.now()->format('Y').'-'.str_pad(Dispatch::max('id') + 1, 5, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_transit' => 'In Transit',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Timestamps')
                    ->schema([
                        DateTimePicker::make('dispatched_at'),
                        DateTimePicker::make('delivered_at'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dispatch_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_transit' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_transit' => 'In Transit',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirm_dispatch')
                    ->label('Confirm Dispatch')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (Dispatch $record) {
                        static::confirmDispatch($record);
                    })
                    ->visible(fn (Dispatch $record) => $record->status === 'pending')
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDispatches::route('/'),
            'create' => Pages\CreateDispatch::route('/create'),
            'edit' => Pages\EditDispatch::route('/{record}/edit'),
        ];
    }

    public static function confirmDispatch(Dispatch $dispatch): void
    {
        DB::transaction(function () use ($dispatch) {
            foreach ($dispatch->items as $item) {
                $stockBatch = Stock::findOrFail($item->stock_id);
                // Validate sufficient quantity
                if ($stockBatch->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock in batch {$stockBatch->batch_number}");
                }

                // Reduce stock (FIFO — we're already selecting the oldest batch in the form)
                $stockBatch->decrement('quantity', $item->quantity);

                // Create stock movement (out)
                StockMovement::create([
                    'stock_id' => $item->stock_id,
                    'product_variant_id' => $stockBatch->product_variant_id,
                    'warehouse_id' => $stockBatch->warehouse_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'unit_cost' => $stockBatch->unit_cost,
                    'reference_type' => get_class($dispatch),
                    'reference_id' => $dispatch->id,
                    'notes' => "Dispatch {$dispatch->dispatch_number}",
                    'created_by' => auth()->id(),
                ]);
            }

            // Update dispatch status
            $dispatch->update([
                'status' => 'in_transit',
                'dispatched_at' => now(),
            ]);

            Notification::make()
                ->title('Dispatch Confirmed')
                ->body('Stock has been deducted using FIFO batches.')
                ->success()
                ->send();
        });
    }
}
