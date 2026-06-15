<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

    public static function form(Schema $schema): Schema
    {
        $omr = fn ($state) => $state === null ? null : format_omr((int) $state);

        return $schema->components([
            Section::make('Order')->columns(2)->schema([
                TextInput::make('order_number')->disabled(),
                Select::make('status')->options(OrderStatus::class)->required(),
                Select::make('payment_status')->options(PaymentStatus::class)->required(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
