<?php

namespace App\Enums;

enum SavedItemType: string
{
    case Job = 'job';
    case Course = 'course';
    case ServiceRequest = 'service_request';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function table(): string
    {
        return match ($this) {
            self::Job => 'jobs',
            self::Course => 'courses',
            self::ServiceRequest => 'service_requests',
        };
    }
}
