<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Step 1 - Job Nest</title>
</head>
<body>
    <h1>Welcome to Job Nest, {{ $name }}!</h1>
    <p>Thank you for registering. Please complete your registration by clicking the link below:</p>
    <a href="{{ route('register.complete', ['token' => $user->verification_token]) }}">Complete Registration</a>
</body>
</html>
