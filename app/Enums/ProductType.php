<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The descriptive garment type attached to every product (Search Demand Map,
 * requirement F). This is the evergreen vocabulary the SDM wants the catalogue
 * organised around — it drives the stable product-category landing pages and
 * search-led filtering, independent of the editorial look name or season.
 *
 * Labels are English; the storefront wraps getLabel() in __() against lang/ar.json
 * for Arabic, the same convention as CollectionType.
 */
enum ProductType: string implements HasLabel
{
    // --- SDM launch categories (garment types, in demand priority order) ----
    case Kaftan = 'kaftan';
    case EveningDress = 'evening_dress';
    case MaxiDress = 'maxi_dress';
    case Jumpsuit = 'jumpsuit';
    case SetCoord = 'set_coord';
    case Abaya = 'abaya';
    case Jalabiya = 'jalabiya';
    case ModestDress = 'modest_dress';

    // --- Supporting types (accessories — not SDM launch categories) ---------
    case Scarf = 'scarf';
    case Bag = 'bag';
    case Accessory = 'accessory';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kaftan => 'Kaftans',
            self::EveningDress => 'Evening & Occasion Dresses',
            self::MaxiDress => 'Maxi & Long Dresses',
            self::Jumpsuit => 'Jumpsuits',
            self::SetCoord => 'Sets & Co-ords',
            self::Abaya => 'Abayas',
            self::Jalabiya => 'Jalabiya',
            self::ModestDress => 'Modest Dresses',
            self::Scarf => 'Scarves',
            self::Bag => 'Bags',
            self::Accessory => 'Accessories',
        };
    }

    /**
     * Stable URL slug for the evergreen category landing page (e.g. /collections/kaftans).
     * These match the example URLs in the Search Demand Map.
     */
    public function slug(): string
    {
        return match ($this) {
            self::Kaftan => 'kaftans',
            self::EveningDress => 'evening-dresses',
            self::MaxiDress => 'maxi-dresses',
            self::Jumpsuit => 'jumpsuits',
            self::SetCoord => 'sets',
            self::Abaya => 'abayas',
            self::Jalabiya => 'jalabiya',
            self::ModestDress => 'modest-dresses',
            self::Scarf => 'scarves',
            self::Bag => 'bags',
            self::Accessory => 'accessories',
        };
    }

    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }

        return null;
    }

    /**
     * The SDM "launch backbone" categories — the five demand-validated families
     * that combine search demand, low difficulty and product support, and should
     * anchor navigation and SEO (SDM page 3, "How to read this"). Abaya, Jalabiya
     * and Modest Dresses are evergreen categories too, but the SDM explicitly does
     * NOT treat them as launch-backbone (higher difficulty / limited depth), so
     * they live in garmentCases() but not here.
     *
     * @return list<self>
     */
    public static function launchCases(): array
    {
        return [
            self::Kaftan,
            self::EveningDress,
            self::MaxiDress,
            self::Jumpsuit,
            self::SetCoord,
        ];
    }

    /**
     * All evergreen garment categories (the launch backbone plus the additional
     * evergreen families). Excludes accessory types. Demand-priority order, so
     * the launch families surface first wherever this drives nav/seed ordering.
     *
     * @return list<self>
     */
    public static function garmentCases(): array
    {
        return [
            ...self::launchCases(),
            self::Abaya,
            self::Jalabiya,
            self::ModestDress,
        ];
    }
}
