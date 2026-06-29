<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The purpose a tag serves (Search Demand Map, requirements B & C). Tags are the
 * lightweight, cross-cutting layer the SDM wants instead of season-led architecture:
 *
 *  - Occasion: permanent, recurring, search-driven event pages (Wedding Guest,
 *    Eid & Ramadan, Resort) — populated by tagging products, refreshed not rebuilt.
 *  - Season:   internal season codes (SS25, AW24) demoted from the SEO backbone
 *    to a simple tag.
 *  - Campaign: named editorial/campaign tags (Echoes of Time, Summer Voyage).
 *
 * Labels are English; the storefront wraps getLabel() in __() against lang/ar.json.
 */
enum TagGroup: string implements HasLabel
{
    case Occasion = 'occasion';
    case Season = 'season';
    case Campaign = 'campaign';

    public function getLabel(): string
    {
        return match ($this) {
            self::Occasion => 'Occasion',
            self::Season => 'Season',
            self::Campaign => 'Campaign',
        };
    }
}
