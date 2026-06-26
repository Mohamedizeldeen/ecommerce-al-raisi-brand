<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $pending = Review::where('is_approved', false)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Schema $schema): Schema
    {
        // Review content is read-only; the only editable control is approval.
        return $schema->components([
            TextInput::make('author_name')->disabled(),
            TextInput::make('author_email')->disabled(),
            TextInput::make('rating')->disabled(),
            TextInput::make('title')->disabled(),
            Textarea::make('body')->disabled()->columnSpanFull(),
            Toggle::make('is_verified_purchase')->label('Verified purchase')->disabled(),
            Toggle::make('is_approved')->label('Approved (visible on the storefront)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')->label('Product')->limit(30)->searchable(),
                TextColumn::make('author_name')->label('Author')->searchable(),
                TextColumn::make('rating')->badge(),
                IconColumn::make('is_verified_purchase')->label('Verified')->boolean(),
                IconColumn::make('is_approved')->label('Approved')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label('Approval'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (Review $r) => ! $r->is_approved)
                    ->action(fn (Review $r) => $r->update(['is_approved' => true]))
                    ->successNotificationTitle('Review approved'),
                Action::make('unapprove')
                    ->label('Unapprove')->icon('heroicon-o-eye-slash')->color('gray')
                    ->visible(fn (Review $r) => $r->is_approved)
                    ->action(fn (Review $r) => $r->update(['is_approved' => false]))
                    ->successNotificationTitle('Review hidden'),
                EditAction::make()->label('View'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
