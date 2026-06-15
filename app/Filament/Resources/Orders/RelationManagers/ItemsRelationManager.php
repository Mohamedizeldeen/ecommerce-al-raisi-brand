<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->disabled(),
            TextInput::make('variant_label')->label('Variant')->disabled(),
            TextInput::make('quantity')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('variant_label')->label('Variant'),
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('price_baisa')
                    ->label('Unit price')
                    ->getStateUsing(fn ($record) => format_omr((int) $record->price_baisa)),
                TextColumn::make('quantity'),
                TextColumn::make('line_total_baisa')
                    ->label('Line total')
                    ->getStateUsing(fn ($record) => format_omr((int) $record->line_total_baisa)),
            ]);
    }
}
