<?php

namespace App\Filament\Concerns;

/**
 * Bridges spatie/laravel-translatable fields to Filament forms: each translatable
 * field <f> is edited via the English input <f> plus an Arabic input <f>_ar.
 *
 * A Create/Edit page using this trait declares the fields it manages:
 *   protected array $arFields = ['name', 'description'];
 * and the resource form provides matching "<field>_ar" inputs.
 */
trait HandlesTranslations
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        foreach ($this->arFields as $f) {
            $data[$f] = $record->getTranslation($f, 'en', false);
            $data[$f.'_ar'] = $record->getTranslation($f, 'ar', false);
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->packTranslations($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->packTranslations($data);
    }

    /**
     * Collapse the <f> (English) and <f>_ar (Arabic) inputs back into the JSON
     * translation array spatie expects, dropping empty locales.
     */
    protected function packTranslations(array $data): array
    {
        foreach ($this->arFields as $f) {
            $translations = array_filter(
                ['en' => $data[$f] ?? null, 'ar' => $data[$f.'_ar'] ?? null],
                static fn ($value) => filled($value),
            );

            $data[$f] = $translations;
            unset($data[$f.'_ar']);
        }

        return $data;
    }
}
