<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        StockMovement::create([
            'stock_id' => $record->id,
            'product_variant_id' => $record->product_variant_id,
            'warehouse_id' => $record->warehouse_id,
            'type' => 'in',
            'quantity' => $record->quantity,
            'unit_cost' => $record->unit_cost,
            'notes' => 'Manual stock addition',
            'created_by' => auth()->id(),
        ]);
    }
}
