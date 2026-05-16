<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers\AllocationsRelationManager;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Details')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('payment_number')
                            ->default(fn () => 'PAY-'.now()->format('Y').'-'.str_pad(Payment::max('id') + 1, 5, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->minValue(0.01),
                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'mobile_money' => 'Mobile Money',
                                'cheque' => 'Cheque',
                                'other' => 'Other',
                            ])
                            ->required(),
                        TextInput::make('reference')
                            ->maxLength(255),
                        DateTimePicker::make('payment_date')
                            ->default(now())
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge(),
                TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money' => 'Mobile Money',
                        'cheque' => 'Cheque',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirm_payment')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (Payment $record) {
                        static::confirmPayment($record);
                    })
                    ->visible(fn (Payment $record) => $record->allocations->isNotEmpty())
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
            AllocationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function confirmPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $totalAllocated = 0;
            foreach ($payment->allocations as $allocation) {
                $invoice = Invoice::findOrFail($allocation->invoice_id);
                // Refresh allocations to get the latest total
                $totalInvoicePaid = $invoice->allocations()->sum('amount');

                if ($totalInvoicePaid >= $invoice->grand_total) {
                    $invoice->update(['status' => 'paid']);
                } else {
                    $invoice->update(['status' => 'partially_paid']);
                }
                $totalAllocated += $allocation->amount;
            }

            // Update customer outstanding balance
            $customer = Customer::find($payment->customer_id);
            $customer->decrement('outstanding_balance', $totalAllocated);

            Notification::make()
                ->title('Payment Confirmed')
                ->body('Total allocated: $'.number_format($totalAllocated, 2))
                ->success()
                ->send();
        });
    }
}
