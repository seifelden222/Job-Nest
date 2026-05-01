<?php

namespace App\Http\Middleware;

use App\Support\ApiLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        $request->attributes->set('resolved_language', $locale);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        $acceptLanguage = $request->getLanguages()[0] ?? null;

        if (is_string($acceptLanguage)) {
            $normalizedAcceptLanguage = mb_strtolower(explode('-', $acceptLanguage)[0]);

            if (ApiLocale::isSupported($normalizedAcceptLanguage)) {
                return $normalizedAcceptLanguage;
            }
        }

        $queryLanguage = $request->query('lang');

        if (is_string($queryLanguage) && ApiLocale::isSupported($queryLanguage)) {
            return $queryLanguage;
        }

        return ApiLocale::default();
    }
}
