<?php

namespace App\Filament\Resources\Press;

use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Filament\Resources\Press\Pages\CreatePressPost;
use App\Filament\Resources\Press\Pages\EditPressPost;
use App\Filament\Resources\Press\Pages\ListPressPosts;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PressResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Press';

    protected static ?string $modelLabel = 'press release';

    protected static ?string $slug = 'press';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', Post::TYPE_PRESS);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPressPosts::route('/'),
            'create' => CreatePressPost::route('/create'),
            'edit' => EditPressPost::route('/{record}/edit'),
        ];
    }
}
