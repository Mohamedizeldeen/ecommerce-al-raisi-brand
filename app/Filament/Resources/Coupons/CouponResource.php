<?php

namespace App\Filament\Resources\Coupons;

use App\Enums\CouponType;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                Select::make('type')
                    ->options(CouponType::class)
                    ->default('percent')
                    ->live()
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->live()
                    ->label(fn (Get $get) => $get('type') === CouponType::Fixed->value ? 'Amount (OMR)' : 'Percentage (%)')
                    ->prefix(fn (Get $get) => $get('type') === CouponType::Fixed->value ? 'OMR' : null)
                    ->suffix(fn (Get $get) => $get('type') === CouponType::Percent->value ? '%' : null)
                    ->maxValue(fn (Get $get) => $get('type') === CouponType::Percent->value ? 100 : null)
                    // Fixed coupons store integer baisa (OMR × 1000); percent stores 0–100.
                    ->formatStateUsing(fn ($state, Get $get) => $get('type') === CouponType::Fixed->value && $state !== null ? $state / 1000 : $state)
                    ->dehydrateStateUsing(fn ($state, Get $get) => $get('type') === CouponType::Fixed->value ? (int) round(((float) $state) * 1000) : (int) $state),
                TextInput::make('min_total_baisa')
                    ->label('Minimum order (OMR)')
                    ->prefix('OMR')
                    ->numeric()
                    ->default(0)
                    ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 1000 : 0)
                    ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 1000)),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                TextInput::make('usage_limit')
                    ->numeric()
                    ->helperText('Leave blank for unlimited.'),
                TextInput::make('used_count')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Managed automatically when orders are paid.'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_total_baisa')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('used_count')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
