<?php

namespace App\Support;

class ApiLocale
{
    public static function supported(): array
    {
        $configuredLocales = array_keys(config('laravellocalization.supportedLocales', []));

        if ($configuredLocales !== []) {
            return $configuredLocales;
        }

        return config('translation.supported_locales', ['ar', 'en']);
    }

    public static function default(): string
    {
        return config('translation.default_locale', 'en');
    }

    public static function current(): string
    {
        $locale = app()->getLocale();

        return in_array($locale, static::supported(), true)
            ? $locale
            : static::default();
    }

    public static function isSupported(?string $locale): bool
    {
        return is_string($locale) && in_array($locale, static::supported(), true);
    }

    public static function alternate(string $locale): string
    {
        return $locale === 'ar' ? 'en' : 'ar';
    }

    public static function normalize(?string $locale): string
    {
        return static::isSupported($locale) ? $locale : static::default();
    }
}
