<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px;">
        <h2 style="margin-bottom: 20px; color: #333;">Password Reset OTP</h2>

        <p style="font-size: 16px; color: #555;">
            Hello,
        </p>

        <p style="font-size: 16px; color: #555;">
            You requested to reset your password. Use the following OTP code:
        </p>

        <div style="margin: 30px 0; text-align: center;">
            <span style="display: inline-block; font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #111;">
                {{ $otp }}
            </span>
        </div>

        <p style="font-size: 14px; color: #777;">
            This OTP will expire in a few minutes.
        </p>

        <p style="font-size: 14px; color: #777;">
            If you did not request a password reset, please ignore this email.
        </p>
    </div>
</body>
</html>
