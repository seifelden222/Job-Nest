<?php

use App\Mail\Auth\SendOtp;
use App\Models\User;
use App\Notifications\Auth\mailotpnotfication;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

it('sends the SendOtp mailable and fires the notification', function () {
    Mail::fake();
    Notification::fake();

    $user = User::factory()->create(['email' => 'test@example.com']);

    $service = app(ForgotPasswordService::class);

    $service->sendResetOtp([
        'method' => 'email',
        'email_or_phone' => $user->email,
    ]);

    Mail::assertSent(SendOtp::class, fn($mail) => $mail->hasTo($user->email));

    Notification::assertSentTo($user, mailotpnotfication::class);
});
