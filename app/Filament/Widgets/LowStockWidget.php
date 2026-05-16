<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class LowStockWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static string $view = 'filament.widgets.low-stock-widget';

    public function getLowStockItems()
    {
        return Cache::remember('low-stock-items', 300, function () {
            return Stock::with('variant.product')
                ->where('quantity', '>', 0)
                ->get()
                ->filter(fn ($stock) => $stock->quantity <= ($stock->variant->low_stock_threshold ?? $stock->variant->product->low_stock_threshold ?? 10));
        });
    }
}
