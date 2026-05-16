<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Illuminate\Support\Facades\Storage;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Invoice Details')
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! $state) {
                                    return;
                                }
                                $order = Order::find($state);
                                if ($order) {
                                    $set('subtotal', $order->subtotal);
                                    $set('tax_amount', $order->tax_amount);
                                    $set('discount_amount', $order->discount_amount);
                                    $set('grand_total', $order->grand_total);
                                    $set('invoice_number', 'INV-'.now()->format('Y').'-'.str_pad(Invoice::max('id') + 1, 5, '0', STR_PAD_LEFT));
                                }
                            }),
                        TextInput::make('invoice_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),
                        Select::make('status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'partially_paid' => 'Partially Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('unpaid')
                            ->required(),
                        DatePicker::make('due_date')
                            ->nullable(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Financials (from Order)')
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
                    ])->columns(2),

                Section::make('PDF File')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Invoice PDF')
                            ->directory('invoices')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->visibility('public'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'cancelled' => 'gray',
                    }),
                TextColumn::make('grand_total')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('file_path')
                    ->label('PDF')
                    ->formatStateUsing(fn ($state) => $state ? 'Download' : 'Not generated')
                    ->url(fn ($state) => $state ? Storage::disk('public')->url($state) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'partially_paid' => 'Partially Paid',
                        'cancelled' => 'Cancelled',
                    ]),
        ])
            ->actions([
            Tables\Actions\Action::make('generate_pdf')
                    ->label('Generate PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Invoice $record) {
                        static::generatePdf($record);
                        Notification::make()
                            ->title('PDF Generated')
                            ->body('The invoice PDF has been generated.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function generatePdf(Invoice $record): void
    {
        $invoice = $record->load('order.customer', 'order.items.variant');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        $invoice->update([
            'file_path' => $path,
            'issued_at' => $invoice->issued_at ?? now(),
        ]);
    }
}
