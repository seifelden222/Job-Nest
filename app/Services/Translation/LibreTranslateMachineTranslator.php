<?php

namespace App\Services\Translation;

use App\Contracts\MachineTranslator;
use Illuminate\Support\Facades\Http;
use Throwable;

class LibreTranslateMachineTranslator implements MachineTranslator
{
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): ?string
    {
        $baseUrl = config('translation.drivers.libretranslate.base_url');

        if (! is_string($baseUrl) || $baseUrl === '') {
            return null;
        }

        $payload = [
            'q' => $text,
            'source' => $sourceLanguage,
            'target' => $targetLanguage,
            'format' => 'text',
        ];

        $apiKey = config('translation.drivers.libretranslate.api_key');

        if (is_string($apiKey) && $apiKey !== '') {
            $payload['api_key'] = $apiKey;
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->asForm()
                ->acceptJson()
                ->timeout((int) config('translation.drivers.libretranslate.timeout', 10))
                ->post('/translate', $payload)
                ->throw();

            $translatedText = $response->json('translatedText');

            return is_string($translatedText) && $translatedText !== ''
                ? $translatedText
                : null;
        } catch (Throwable $throwable) {
            report($throwable);

            return null;
        }
    }
}
