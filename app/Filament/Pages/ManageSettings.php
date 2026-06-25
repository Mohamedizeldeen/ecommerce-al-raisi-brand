<?php

namespace App\Filament\Pages;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Friendly, labelled editor for the site-wide key/value Settings — so the
 * client manages social links, contact details and shipping without touching
 * raw key/value rows. Money fields are shown in OMR and stored as baisa.
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?string $slug = 'site-settings';

    protected string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'social_instagram_url' => Setting::get('social_instagram_url'),
            'social_facebook_url' => Setting::get('social_facebook_url'),
            'social_tiktok_url' => Setting::get('social_tiktok_url'),
            'social_youtube_url' => Setting::get('social_youtube_url'),
            'social_pinterest_url' => Setting::get('social_pinterest_url'),
            'address_line' => Setting::get('address_line'),
            'contact_email' => Setting::get('contact_email'),
            'contact_phone' => Setting::get('contact_phone'),
            'free_shipping_threshold_omr' => (int) Setting::get('free_shipping_threshold_baisa', 100000) / 1000,
            'shipping_flat_omr' => (int) Setting::get('shipping_flat_baisa', 2000) / 1000,
            'newsletter_discount_percent' => (int) Setting::get('newsletter_discount_percent', 10),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Social media links')
                    ->description('Paste each profile\'s full URL. Leave a field blank to hide that icon in the footer.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('social_instagram_url')->label('Instagram')->url()->placeholder('https://instagram.com/...'),
                        TextInput::make('social_facebook_url')->label('Facebook')->url()->placeholder('https://facebook.com/...'),
                        TextInput::make('social_tiktok_url')->label('TikTok')->url()->placeholder('https://tiktok.com/@...'),
                        TextInput::make('social_youtube_url')->label('YouTube')->url()->placeholder('https://youtube.com/@...'),
                        TextInput::make('social_pinterest_url')->label('Pinterest')->url()->placeholder('https://pinterest.com/...'),
                    ]),

                Section::make('Contact details')
                    ->description('Shown on the contact page and in the footer.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('address_line')->label('Showroom address')->columnSpanFull(),
                        TextInput::make('contact_email')->label('Contact email')->email(),
                        TextInput::make('contact_phone')->label('Contact phone'),
                    ]),

                Section::make('Shipping & offers')
                    ->columns(3)
                    ->schema([
                        TextInput::make('free_shipping_threshold_omr')
                            ->label('Free shipping over')
                            ->prefix('OMR')
                            ->numeric()
                            ->step(0.001)
                            ->helperText('Orders above this qualify for free delivery.'),
                        TextInput::make('shipping_flat_omr')
                            ->label('Flat shipping')
                            ->prefix('OMR')
                            ->numeric()
                            ->step(0.001),
                        TextInput::make('newsletter_discount_percent')
                            ->label('Newsletter discount')
                            ->suffix('%')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ([
            'social_instagram_url', 'social_facebook_url', 'social_tiktok_url',
            'social_youtube_url', 'social_pinterest_url',
            'address_line', 'contact_email', 'contact_phone',
        ] as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        Setting::put('free_shipping_threshold_baisa', (int) round(((float) ($data['free_shipping_threshold_omr'] ?? 0)) * 1000));
        Setting::put('shipping_flat_baisa', (int) round(((float) ($data['shipping_flat_omr'] ?? 0)) * 1000));
        $percent = (int) ($data['newsletter_discount_percent'] ?? 0);
        Setting::put('newsletter_discount_percent', $percent);

        // Keep the advertised welcome code in sync with a real coupon. The storefront
        // shows "WELCOME{percent}" everywhere, so ensure that exact code exists and is a
        // valid percent coupon — otherwise changing the percentage would advertise a code
        // that applyCoupon() rejects. Existing customisation (usage limits, dates) is
        // preserved; only a freshly created code gets a zero minimum.
        if ($percent > 0) {
            $coupon = Coupon::firstOrNew(['code' => 'WELCOME'.$percent]);
            $coupon->type = CouponType::Percent;
            $coupon->value = $percent;
            $coupon->is_active = true;

            if (! $coupon->exists) {
                $coupon->min_total_baisa = 0;
            }

            $coupon->save();
        }

        Notification::make()->title('Settings saved')->success()->send();
    }
}
