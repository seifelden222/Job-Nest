<?php

namespace App\Services\Translation;

use App\Contracts\MachineTranslator;
use App\Support\ApiLocale;

class ContentTranslationService
{
    public function __construct(private MachineTranslator $translator) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $translatableFields
     * @return array<string, mixed>
     */
    public function translatePayload(array $payload, array $translatableFields, string $sourceLanguage): array
    {
        $resolvedSourceLanguage = ApiLocale::normalize($sourceLanguage);
        $targetLanguage = ApiLocale::alternate($resolvedSourceLanguage);

        foreach ($translatableFields as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];

            if ($value === null) {
                $payload[$field] = null;

                continue;
            }

            $sourceText = trim((string) $value);

            $payload[$field] = [
                $resolvedSourceLanguage => $sourceText,
                $targetLanguage => $this->translator->translate($sourceText, $resolvedSourceLanguage, $targetLanguage) ?? $sourceText,
            ];
        }

        return $payload;
    }
}
