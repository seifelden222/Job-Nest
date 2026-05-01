<?php

namespace App\Services\Translation;

use App\Contracts\MachineTranslator;

class FallbackMachineTranslator implements MachineTranslator
{
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): ?string
    {
        return $text;
    }
}
