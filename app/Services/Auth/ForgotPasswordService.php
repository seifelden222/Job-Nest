<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ForgotPasswordService
{
    private string $OTP_TYPE;

    private string $USER_TYPE;

    private int $OTP_LENGTH;

    private int $OTP_EXPIRES_IN_MINUTES;

    public function __construct()
    {
        $this->OTP_EXPIRES_IN_MINUTES = (int) config('services.otp.expires_in_minutes', 10);
        $this->OTP_LENGTH = (int) config('services.otp.length', 6);
        $this->OTP_TYPE = config('services.otp.otp_type', 'reset_password');
        $this->USER_TYPE = config('services.otp.user_type', 'user');
    }
    public function sendResetOtp(array $validated): void
    {
        $method = $validated['method'];
        $identifier = trim((string) $validated['email_or_phone']);
        $user = $this->resolveUserByMethod($identifier, $method);

        DB::transaction(function () use ($user, $method, $identifier): void {
            $this->invalidateExistingOtps($user, $method, $identifier);

            $this->createOtp($user, $method, $identifier);
        });
    }

    public function verifyResetOtp(array $validated): void
    {
        $identifier = trim((string) $validated['email_or_phone']);
        $otp = trim((string) $validated['otp']);

        [$user, $method] = $this->resolveUserAndMethod($identifier);
        $otpCode = $this->findActiveOtp($user, $method, $identifier, $otp);

        $otpCode->update([
            'verified_at' => now(),
        ]);
    }

    public function resetPassword(array $validated): void
    {
        $identifier = trim((string) $validated['email_or_phone']);
        $otp = trim((string) $validated['otp']);

        [$user, $method] = $this->resolveUserAndMethod($identifier);

        $otpCode = OtpCode::query()
            ->where('user_type', $this->USER_TYPE)
            ->where('user_id', $user->id)
            ->where('type', $this->OTP_TYPE)
            ->where('code', $otp)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->when(
                $method === 'email',
                fn($query) => $query->where('email', $identifier),
                fn($query) => $query->where('phone', $identifier)
            )
            ->latest('id')
            ->first();

        if (! $otpCode) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or unverified OTP.'],
            ]);
        }

        DB::transaction(function () use ($user, $validated, $method, $identifier): void {
            $user->update([
                'password' => $validated['password'],
            ]);

            $this->consumeOtps($user, $method, $identifier);
        });
    }

    private function resolveUserByMethod(string $identifier, string $method): User
    {
        $user = User::query()
            ->where($method === 'email' ? 'email' : 'phone', $identifier)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email_or_phone' => ['User not found.'],
            ]);
        }

        return $user;
    }

    private function resolveUserAndMethod(string $identifier): array
    {
        $method = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::query()
            ->where($method === 'email' ? 'email' : 'phone', $identifier)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email_or_phone' => ['User not found.'],
            ]);
        }

        return [$user, $method];
    }

    private function findActiveOtp(User $user, string $method, string $identifier, string $otp): OtpCode
    {
        $otpCode = OtpCode::query()
            ->where('user_type', $this->USER_TYPE)
            ->where('user_id', $user->id)
            ->where('type', $this->OTP_TYPE)
            ->where('code', $otp)
            ->where('expires_at', '>', now())
            ->when(
                $method === 'email',
                fn($query) => $query->where('email', $identifier),
                fn($query) => $query->where('phone', $identifier)
            )
            ->latest('id')
            ->first();

        if (! $otpCode) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        return $otpCode;
    }
    public function resendResetOtp(array $validated): void
    {
        $method = $validated['method'];
        $identifier = trim((string) $validated['email_or_phone']);
        $user = $this->resolveUserByMethod($identifier, $method);

        DB::transaction(function () use ($user, $method, $identifier): void {
            $this->invalidateExistingOtps($user, $method, $identifier);

            $this->createOtp($user, $method, $identifier);
        });
    }

    private function invalidateExistingOtps(User $user, string $method, string $identifier): void
    {
        OtpCode::query()
            ->where('user_type', $this->USER_TYPE)
            ->where('user_id', $user->id)
            ->where('type', $this->OTP_TYPE)
            ->whereNull('verified_at')
            ->when(
                $method === 'email',
                fn($query) => $query->where('email', $identifier),
                fn($query) => $query->where('phone', $identifier)
            )
            ->delete();
    }

    private function consumeOtps(User $user, string $method, string $identifier): void
    {
        OtpCode::query()
            ->where('user_type', $this->USER_TYPE)
            ->where('user_id', $user->id)
            ->where('type', $this->OTP_TYPE)
            ->when(
                $method === 'email',
                fn($query) => $query->where('email', $identifier),
                fn($query) => $query->where('phone', $identifier)
            )
            ->delete();
    }

    private function generateOtp(): string
    {
        $min = 10 ** ($this->OTP_LENGTH - 1);
        $max = (10 ** $this->OTP_LENGTH) - 1;

        return (string) random_int($min, $max);
    }
    private function createOtp(User $user, string $method, string $identifier): void
    {

        OtpCode::create([
            'user_type' => $this->USER_TYPE,
            'user_id' => $user->id,
            'email' => $method === 'email' ? $identifier : null,
            'phone' => $method === 'phone' ? $identifier : null,
            'code' => $this->generateOtp(),
            'type' => $this->OTP_TYPE,
            'expires_at' => now()->addMinutes($this->OTP_EXPIRES_IN_MINUTES),
            'verified_at' => null,
        ]);
    }
}
