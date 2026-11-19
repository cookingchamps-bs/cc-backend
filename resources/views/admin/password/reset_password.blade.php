<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Password · Cooking Champs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-theme.css') }}?v=20260821">
    <style>
        body.cc-login-page {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "DM Sans", system-ui, sans-serif;
            background: linear-gradient(145deg, #181544 0%, #48335C 45%, #5E3B70 100%);
        }
        .cc-login-wrap { width: 100%; max-width: 420px; padding: 1.5rem; }
        .cc-login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
            padding: 2rem 1.75rem;
        }
        .cc-login-card h2 {
            text-align: center;
            margin: 0 0 0.35rem;
            color: #181544;
            font-size: 1.35rem;
        }
        .cc-login-card > p {
            text-align: center;
            color: #6B7280;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.35rem; font-weight: 600; font-size: 0.9rem; color: #1C1C1C; }
        input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #E5E7EB;
            font-size: 0.95rem;
            font-family: inherit;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #5E3B70;
            box-shadow: 0 0 0 3px rgba(72, 51, 92, 0.15);
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #48335C;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            font-family: inherit;
        }
        .btn:hover { background: #5E3B70; }
        .error {
            background: #FEF2F2;
            color: #991B1B;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="cc-login-page">
<div class="cc-login-wrap">
    <div class="cc-login-card">
        <h2>Set your password</h2>
        <p>Create a new password to access your school dashboard</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label>New password</label>
                <input type="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Confirm password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn">Set password</button>
        </form>
    </div>
</div>
</body>
</html>
