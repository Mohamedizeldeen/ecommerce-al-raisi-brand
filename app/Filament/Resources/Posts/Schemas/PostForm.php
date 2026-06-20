<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Shared form for Blog posts and Press releases (both stored in `posts`,
 * distinguished by `type`). English + Arabic inputs are packed into the
 * translatable JSON by the Create/Edit pages (HandlesTranslations).
 */
class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('title_ar')
                            ->label('Title (العربية)')
                            ->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the page URL.'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower shows first when no publish date ordering applies.'),
                        Textarea::make('excerpt')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Short summary shown on cards and link previews.'),
                        Textarea::make('excerpt_ar')
                            ->label('Excerpt (العربية)')
                            ->rows(2)
                            ->extraInputAttributes(['dir' => 'rtl'])
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->columnSpanFull(),
                        RichEditor::make('body_ar')
                            ->label('Body (العربية)')
                            ->extraInputAttributes(['dir' => 'rtl'])
                            ->columnSpanFull(),
                    ]),

                Section::make('Cover image')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->image()
                            ->maxSize(4096)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->directory('posts')
                            ->helperText('Used as the article header and the social/link preview image.'),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->helperText('Leave blank to publish immediately. A future date schedules it.'),
                        Toggle::make('is_active')
                            ->label('Published')
                            ->default(true),
                    ]),

                Section::make('SEO (optional)')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title'),
                        TextInput::make('meta_title_ar')
                            ->label('Meta title (العربية)')
                            ->extraInputAttributes(['dir' => 'rtl']),
                        Textarea::make('meta_description')
                            ->rows(2),
                        Textarea::make('meta_description_ar')
                            ->label('Meta description (العربية)')
                            ->rows(2)
                            ->extraInputAttributes(['dir' => 'rtl']),
                    ]),
            ]);
    }
}
