<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4 bg-white rounded-xl shadow">
            <h3 class="text-lg font-medium text-danger-600">Low Stock Alerts</h3>
            <ul class="mt-2 space-y-1">
                @foreach ($this->getLowStockItems() as $item)
                    <li class="text-sm">
                        {{ $item->variant->product->name }} ({{ $item->variant->sku }})
                        – <strong>{{ $item->quantity }}</strong> left in {{ $item->warehouse->name }}
                    </li>
                @endforeach
            </ul>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
