<?php

namespace App\Filament\Resources\Orders;

use App\Actions\Orders\MarkOrderPaid;
use App\Actions\Orders\ReleaseOrderStock;
use App\Actions\Orders\RestockOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\StatusHistoryRelationManager;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    /** @return array<int, string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['order_number', 'customer_name', 'customer_email', 'customer_phone'];
    }

    /** Audited status transition (also restocks/charges via the dedicated actions). */
    protected static function transition(Order $order, OrderStatus $to, string $note, ?PaymentStatus $payment = null): void
    {
        $from = $order->status->value;

        $order->update(array_filter([
            'status' => $to,
            'payment_status' => $payment,
        ]));

        $order->statusHistories()->create([
            'from_status' => $from,
            'to_status' => $to->value,
            'note' => $note.' by '.(auth()->user()?->name ?? 'admin').'.',
        ]);
    }

    /** Queue a customer-facing status email ($headline/$body are translation keys). */
    protected static function notifyCustomer(Order $order, string $headline, string $body, bool $showTracking = false): void
    {
        if (! $order->customer_email) {
            return;
        }

        Mail::to($order->customer_email)->queue(
            new OrderStatusUpdateMail($order->refresh(), $headline, $body, $showTracking)
        );
    }

    public static function form(Schema $schema): Schema
    {
        $omr = fn ($state) => $state === null ? null : format_omr((int) $state);

        return $schema->components([
            Section::make('Order')->columns(2)->schema([
                TextInput::make('order_number')->disabled(),
                // Read-only: status & payment change only through the audited actions below.
                Select::make('status')->options(OrderStatus::class)->disabled(),
                Select::make('payment_status')->options(PaymentStatus::class)->disabled(),
                DateTimePicker::make('paid_at')->disabled(),
                TextInput::make('thawani_session_id')->label('Thawani session')->disabled(),
                TextInput::make('coupon_code')->disabled(),
            ]),
            Section::make('Totals')->columns(2)->schema([
                TextInput::make('subtotal_baisa')->label('Subtotal')->disabled()->formatStateUsing($omr),
                TextInput::make('shipping_baisa')->label('Shipping')->disabled()->formatStateUsing($omr),
                TextInput::make('discount_baisa')->label('Discount')->disabled()->formatStateUsing($omr),
                TextInput::make('total_baisa')->label('Total')->disabled()->formatStateUsing($omr),
            ]),
            Section::make('Customer & Shipping')->columns(2)->schema([
                TextInput::make('customer_name')->disabled(),
                TextInput::make('customer_email')->disabled(),
                TextInput::make('customer_phone')->disabled(),
                TextInput::make('shipping_address_line1')->label('Address line 1')->disabled(),
                TextInput::make('shipping_address_line2')->label('Address line 2')->disabled(),
                TextInput::make('shipping_city')->label('City')->disabled(),
                TextInput::make('shipping_region')->label('Region')->disabled(),
                TextInput::make('shipping_country')->label('Country')->disabled(),
                Textarea::make('notes')->disabled()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order')->searchable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('total_baisa')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => format_omr((int) $state))
                    ->sortable(),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->options(fn () => collect(PaymentStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()),
                SelectFilter::make('status')
                    ->options(fn () => collect(OrderStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Mark paid')->icon('heroicon-o-banknotes')->color('success')
                    ->visible(fn (Order $r) => $r->payment_status !== PaymentStatus::Paid && $r->status !== OrderStatus::Cancelled)
                    ->requiresConfirmation()
                    ->modalDescription('Confirms payment: decrements stock, records paid_at and writes the audit trail.')
                    ->action(fn (Order $r) => app(MarkOrderPaid::class)->handle($r))
                    ->successNotificationTitle('Order marked as paid'),
                Action::make('ship')
                    ->label('Ship')->icon('heroicon-o-truck')->color('warning')
                    ->visible(fn (Order $r) => $r->status === OrderStatus::Processing)
                    ->schema([
                        TextInput::make('carrier')->label('Carrier')->maxLength(120),
                        TextInput::make('tracking_number')->label('Tracking number')->maxLength(120),
                    ])
                    ->action(function (Order $r, array $data) {
                        $r->update([
                            'carrier' => $data['carrier'] ?? null,
                            'tracking_number' => $data['tracking_number'] ?? null,
                            'shipped_at' => now(),
                        ]);
                        static::transition($r, OrderStatus::Shipped, 'Marked shipped');
                        static::notifyCustomer($r, 'Your order has shipped', 'Good news — your order is on its way.', showTracking: true);
                    })
                    ->successNotificationTitle('Order marked as shipped — customer notified'),
                Action::make('complete')
                    ->label('Complete')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Order $r) => $r->status === OrderStatus::Shipped)
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => static::transition($r, OrderStatus::Completed, 'Marked completed'))
                    ->successNotificationTitle('Order completed'),
                Action::make('refund')
                    ->label('Mark as refunded')->icon('heroicon-o-arrow-uturn-left')->color('danger')
                    ->visible(fn (Order $r) => $r->payment_status === PaymentStatus::Paid && (bool) auth()->user()?->isAdmin())
                    ->requiresConfirmation()
                    ->modalHeading('Mark order as refunded')
                    // IMPORTANT: this is a bookkeeping action only — it updates the order to
                    // Refunded and restores stock. It does NOT move money back through
                    // Thawani. Issue the actual refund in the Thawani dashboard first.
                    ->modalDescription('This marks the order as Refunded and restores its stock for your records. It does NOT send money back to the customer — issue the actual refund in the Thawani dashboard first. This cannot be undone here.')
                    ->action(function (Order $r) {
                        app(RestockOrder::class)->handle($r);
                        static::notifyCustomer($r, 'Your order was refunded', 'Your order has been marked as refunded. Please allow a few business days for the funds to appear.');
                    })
                    ->successNotificationTitle('Order marked as refunded and restocked'),
                Action::make('cancel')
                    ->label('Cancel')->icon('heroicon-o-x-circle')->color('gray')
                    ->visible(fn (Order $r) => $r->status === OrderStatus::Pending && $r->payment_status !== PaymentStatus::Paid)
                    ->requiresConfirmation()
                    ->action(function (Order $r) {
                        app(ReleaseOrderStock::class)->handle(
                            $r, OrderStatus::Cancelled, PaymentStatus::Cancelled,
                            'Cancelled by '.(auth()->user()?->name ?? 'admin').' — reservation released.'
                        );
                        static::notifyCustomer($r, 'Your order was cancelled', 'Your order has been cancelled. If you have any questions, please contact us.');
                    })
                    ->successNotificationTitle('Order cancelled'),
                EditAction::make()->label('View'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Customer PII export and destructive delete are admin-only.
                    ExportBulkAction::make()->exporter(OrderExporter::class)
                        ->visible(fn () => (bool) auth()->user()?->isAdmin()),
                    DeleteBulkAction::make()
                        ->visible(fn () => (bool) auth()->user()?->isAdmin()),
                    RestoreBulkAction::make()
                        ->visible(fn () => (bool) auth()->user()?->isAdmin()),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => (bool) auth()->user()?->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            StatusHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
