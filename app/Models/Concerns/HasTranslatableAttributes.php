<?php

namespace App\Models\Concerns;

use App\Support\ApiLocale;

trait HasTranslatableAttributes
{
    public function setAttribute($key, $value): static
    {
        if (in_array($key, $this->getTranslatableAttributes(), true) && is_string($value) && $value !== '') {
            $defaultLocale = ApiLocale::default();
            $alternateLocale = ApiLocale::alternate($defaultLocale);

            $value = [
                $defaultLocale => $value,
                $alternateLocale => $value,
            ];
        }

        return parent::setAttribute($key, $value);
    }

    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getTranslatableAttributes() as $attribute) {
            if (! array_key_exists($attribute, $attributes)) {
                continue;
            }

            $attributes[$attribute] = $this->resolveTranslatedValue($attributes[$attribute]);
        }

        return $attributes;
    }

    public function getAttributeValue($key): mixed
    {
        $value = parent::getAttributeValue($key);

        if (! in_array($key, $this->getTranslatableAttributes(), true)) {
            return $value;
        }

        return $this->resolveTranslatedValue($value);
    }

    /**
     * @return array<int, string>
     */
    public function getTranslatableAttributes(): array
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    /**
     * @return array<string, string>
     */
    public function getTranslations(string $attribute): array
    {
        if (! in_array($attribute, $this->getTranslatableAttributes(), true)) {
            return [];
        }

        $rawValue = $this->getRawOriginal($attribute);

        if (is_array($rawValue)) {
            return $rawValue;
        }

        if (! is_string($rawValue) || $rawValue === '') {
            return [];
        }

        $decoded = json_decode($rawValue, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function resolveTranslatedValue(mixed $value): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return (string) $value;
        }

        $locale = ApiLocale::current();
        $fallbackLocale = ApiLocale::default();

        foreach ([$locale, $fallbackLocale, ApiLocale::alternate($fallbackLocale)] as $candidate) {
            $candidateValue = $value[$candidate] ?? null;

            if (is_string($candidateValue) && $candidateValue !== '') {
                return $candidateValue;
            }
        }

        foreach ($value as $candidateValue) {
            if (is_string($candidateValue) && $candidateValue !== '') {
                return $candidateValue;
            }
        }

        return null;
    }
}
