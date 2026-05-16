<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Order Information')
                    ->schema([
                        TextEntry::make('order_number'),
                        TextEntry::make('customer.name'),
                        TextEntry::make('status')
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
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                InfolistSection::make('Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('variant.sku')->label('SKU'),
                                TextEntry::make('quantity'),
                                TextEntry::make('unit_price')->money('USD'),
                                TextEntry::make('discount_amount')->money('USD'),
                                TextEntry::make('tax_rate')->suffix('%'),
                                TextEntry::make('tax_amount')->money('USD'),
                                TextEntry::make('total_price')->money('USD'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                InfolistSection::make('Financial Summary')
                    ->schema([
                        TextEntry::make('subtotal')->money('USD'),
                        TextEntry::make('discount_amount')->money('USD'),
                        TextEntry::make('tax_amount')->money('USD'),
                        TextEntry::make('grand_total')->money('USD'),
                    ])
                    ->columns(2),

                InfolistSection::make('Timestamps')
                    ->schema([
                        TextEntry::make('ordered_at')->dateTime(),
                        TextEntry::make('dispatched_at')->dateTime(),
                        TextEntry::make('delivered_at')->dateTime(),
                        TextEntry::make('cancelled_at')->dateTime(),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('updated_at')->dateTime(),
                    ])
                    ->collapsed()
                    ->columns(2),
            ]);
    }

    public function getTitle(): string
    {
        return __('Order') . ' ' . $this->record->order_number;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
