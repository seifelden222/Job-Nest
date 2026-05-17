<?php

namespace App\Filament\Auth;

use App\Models\Admin;
use App\Support\AdminPanelPasswordValidator;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class AdminLogin extends Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        $credentials = $this->getCredentialsFromFormData($data);
        $user = $authGuard->getProvider()->retrieveByCredentials($credentials); /** @phpstan-ignore-line */
        if (! $user instanceof Admin) {
            $this->fireAdminLoginFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if (! app(AdminPanelPasswordValidator::class)->validate($user, (string) ($credentials['password'] ?? ''))) {
            $this->fireAdminLoginFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if (($user instanceof FilamentUser) && (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))) {
            $this->fireAdminLoginFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        $authGuard->login($user, $data['remember'] ?? false);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    protected function fireAdminLoginFailedEvent(SessionGuard $guard, ?Authenticatable $user, #[SensitiveParameter] array $credentials): void
    {
        event(app(Failed::class, [
            'guard' => property_exists($guard, 'name') ? $guard->name : '',
            'user' => $user,
            'credentials' => $credentials,
        ]));
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
