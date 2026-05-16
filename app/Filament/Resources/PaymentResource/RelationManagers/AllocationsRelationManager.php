<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(function () {
                        $payment = $this->getOwnerRecord();

                        return Invoice::whereHas('order', fn ($q) => $q->where('customer_id', $payment->customer_id))
                            ->where('status', '!=', 'paid')
                            ->get()
                            ->mapWithKeys(fn ($inv) => [
                                $inv->id => "{$inv->invoice_number} (Outstanding: \$".number_format($inv->grand_total - $inv->allocations->sum('amount'), 2).')',
                            ]);
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->minValue(0.01),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')
                    ->label('Invoice'),
                TextColumn::make('amount')
                    ->money('USD'),
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
