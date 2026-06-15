<?php

namespace App\Filament\Resources\Showcases\Schemas;

use App\Models\Showcase;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShowcaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('subtitle'),
                Select::make('type')
                    ->options(Showcase::TYPES)
                    ->default('behind_the_scenes')
                    ->required(),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('video_url')
                    ->url()
                    ->label('Video URL')
                    ->helperText('YouTube or Vimeo link (optional). Leave blank for an image-only story.'),
                FileUpload::make('cover_image')
                    ->image()
                    ->directory('showcases')
                    ->helperText('Cover / poster image. Used as the video poster or the story image.'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
