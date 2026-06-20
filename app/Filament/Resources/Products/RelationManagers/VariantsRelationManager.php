<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('sku')->label('SKU')->required(),
                TextInput::make('size'),
                TextInput::make('color'),
                ColorPicker::make('color_hex')->label('Colour'),
                TextInput::make('price_override_baisa')
                    ->label('Price override (OMR)')
                    ->prefix('OMR')
                    ->numeric()
                    ->step(0.001)
                    ->helperText('Leave blank to use the product base price.')
                    ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 1000 : null)
                    ->dehydrateStateUsing(fn ($state) => ($state === null || $state === '') ? null : (int) round(((float) $state) * 1000)),
                TextInput::make('stock_qty')->label('Stock')->numeric()->default(0)->required(),
                Toggle::make('is_active')->default(true),
                FileUpload::make('image_path')
                    ->label('Colour photo')
                    ->image()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->directory('variants')
                    ->columnSpanFull()
                    ->helperText('Optional. Shown on the product page when this colour is selected.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                ImageColumn::make('image_path')->label('Photo'),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('label')
                    ->label('Variant')
                    ->getStateUsing(fn ($record) => $record->label),
                TextColumn::make('price_baisa')
                    ->label('Price')
                    ->getStateUsing(fn ($record) => format_omr((int) $record->price_baisa)),
                TextColumn::make('stock_qty')->label('Stock')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
