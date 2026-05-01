<?php

namespace App\Contracts;

interface MachineTranslator
{
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): ?string;
}
