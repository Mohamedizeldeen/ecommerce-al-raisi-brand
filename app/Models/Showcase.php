<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Showcase extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /** @var array<string, string> */
    public const TYPES = [
        'fashion_show' => 'Fashion Show',
        'behind_the_scenes' => 'Behind the Scenes',
        'design' => 'The Designs',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? Str::headline((string) $this->type);
    }

    /**
     * Cover image: uploaded file, else a deterministic editorial fallback so
     * every card always has a backdrop.
     */
    public function coverImageUrl(int $offset = 0): string
    {
        if ($this->cover_image) {
            return Str::startsWith($this->cover_image, ['http://', 'https://'])
                ? $this->cover_image
                : asset('storage/'.$this->cover_image);
        }

        static $pool = null;

        if ($pool === null) {
            $files = glob(public_path('images/collections/*.jpg')) ?: [];
            sort($files);
            $pool = array_map(fn ($file) => asset('images/collections/'.basename($file)), $files);
        }

        if ($pool === []) {
            return asset('images/heroes/hero.jpg');
        }

        return $pool[((int) $this->id + $offset) % count($pool)];
    }

    /**
     * Normalise a YouTube/Vimeo link into an embeddable URL. Returns null when
     * there is no video (the entry then displays as an image story).
     */
    public function embedUrl(): ?string
    {
        $url = trim((string) $this->video_url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]+)~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }
}
