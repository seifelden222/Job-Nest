<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TranslatableJson
{
    public static function localePath(string $column, ?string $locale = null): string
    {
        return sprintf('%s->%s', $column, $locale ?? ApiLocale::current());
    }

    public static function extractExpression(string $column, ?string $locale = null): string
    {
        $resolvedLocale = $locale ?? ApiLocale::current();
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return sprintf(
                'json_extract("%s", \'$."%s"\')',
                $column,
                $resolvedLocale,
            );
        }

        return sprintf(
            'json_unquote(json_extract(`%s`, \'$."%s"\'))',
            $column,
            $resolvedLocale,
        );
    }

    public static function whereLike(Builder $query, string $column, string $term, ?string $locale = null): Builder
    {
        return $query->whereRaw(self::extractExpression($column, $locale).' like ?', ['%'.$term.'%']);
    }

    public static function orWhereLike(Builder $query, string $column, string $term, ?string $locale = null): Builder
    {
        return $query->whereRaw(self::extractExpression($column, $locale).' like ?', ['%'.$term.'%'], 'or');
    }
}
