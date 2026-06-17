<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /** @return array<int, string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Details')->columns(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set, $operation) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('name_ar')
                    ->label('Name (العربية)')
                    ->extraInputAttributes(['dir' => 'rtl']),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                Textarea::make('description_ar')
                    ->label('Description (العربية)')
                    ->rows(4)
                    ->extraInputAttributes(['dir' => 'rtl'])
                    ->columnSpanFull(),
                TextInput::make('fabric'),
                TextInput::make('fabric_ar')
                    ->label('Fabric (العربية)')
                    ->extraInputAttributes(['dir' => 'rtl']),
                TextInput::make('base_price_baisa')
                    ->label('Base price (OMR)')
                    ->prefix('OMR')
                    ->numeric()
                    ->required()
                    ->step(0.001)
                    ->formatStateUsing(fn (?int $state) => $state !== null ? $state / 1000 : null)
                    ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 1000)),
                KeyValue::make('specs')->keyLabel('Spec')->valueLabel('Detail')->columnSpanFull(),
            ]),
            Section::make('Organisation')->columns(2)->schema([
                Select::make('categories')->relationship('categories', 'name')->multiple()->preload(),
                Select::make('collections')->relationship('collections', 'name')->multiple()->preload(),
                Select::make('pairings')
                    ->relationship('pairings', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->label('Style it with')
                    ->helperText('Matching pieces shown on the product page — belts, bags, scarves, jewellery.')
                    ->columnSpanFull(),
                Toggle::make('is_active')->default(true),
                Toggle::make('is_featured'),
                TextInput::make('sort_order')->numeric()->default(0),
                DateTimePicker::make('published_at'),
            ]),
            Section::make('Gallery')->schema([
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->collection('gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->columnSpanFull(),
            ]),
            Section::make('SEO')->columns(2)->collapsed()->schema([
                TextInput::make('meta_title'),
                TextInput::make('meta_description'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->collection('gallery')
                    ->limit(1)
                    ->label('Image'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('base_price_baisa')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => format_omr((int) $state))
                    ->sortable(),
                TextColumn::make('variants')
                    ->label('Variants')
                    ->getStateUsing(fn (Product $record) => $record->variants()->count()),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => (int) $state === 0 ? 'danger' : ((int) $state <= 5 ? 'warning' : 'success'))
                    ->getStateUsing(fn (Product $record) => (int) $record->variants()->sum('stock_qty')),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_featured'),
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
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
