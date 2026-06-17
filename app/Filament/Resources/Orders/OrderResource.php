<?php

namespace App\Filament\Resources\Orders;

use App\Actions\Orders\MarkOrderPaid;
use App\Actions\Orders\RestockOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\StatusHistoryRelationManager;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => static::transition($r, OrderStatus::Shipped, 'Marked shipped'))
                    ->successNotificationTitle('Order marked as shipped'),
                Action::make('complete')
                    ->label('Complete')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Order $r) => $r->status === OrderStatus::Shipped)
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => static::transition($r, OrderStatus::Completed, 'Marked completed'))
                    ->successNotificationTitle('Order completed'),
                Action::make('refund')
                    ->label('Refund')->icon('heroicon-o-arrow-uturn-left')->color('danger')
                    ->visible(fn (Order $r) => $r->payment_status === PaymentStatus::Paid)
                    ->requiresConfirmation()
                    ->modalDescription('Refunds this paid order and restores its stock. This cannot be undone here.')
                    ->action(fn (Order $r) => app(RestockOrder::class)->handle($r))
                    ->successNotificationTitle('Order refunded and restocked'),
                Action::make('cancel')
                    ->label('Cancel')->icon('heroicon-o-x-circle')->color('gray')
                    ->visible(fn (Order $r) => $r->status === OrderStatus::Pending && $r->payment_status !== PaymentStatus::Paid)
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => static::transition($r, OrderStatus::Cancelled, 'Cancelled', PaymentStatus::Cancelled))
                    ->successNotificationTitle('Order cancelled'),
                EditAction::make()->label('View'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()->exporter(OrderExporter::class),
                    DeleteBulkAction::make(),
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
