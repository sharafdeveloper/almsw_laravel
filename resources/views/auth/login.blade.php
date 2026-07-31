<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>POS - Login</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 36px 32px 28px;
            text-align: center;
            color: #ffffff;
        }

        .login-header .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .login-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .login-header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }

        .login-body {
            padding: 32px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 24px;
        }

        .welcome-text h2 {
            font-size: 20px;
            color: #1f2937;
            margin: 0 0 4px;
            font-weight: 600;
        }

        .welcome-text p {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #9ca3af;
        }

        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            color: #111827;
            background: #f9fafb;
            transition: all 0.2s ease;
            box-sizing: border-box;
            outline: none;
        }

        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .error-msg {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            margin: 16px 0 22px;
        }

        .remember-row label {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #4b5563;
            cursor: pointer;
            user-select: none;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: #9ca3af;
        }

        .session-status {
            background: #d1fae5;
            color: #065f46;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
            <h1>AL-MAKKAH-STEEL WORKS</h1>
            <!--<p>Point of Sale System</p>-->
        </div>

        <div class="login-body">
            <div class="welcome-text">
                <h2>Welcome Back</h2>
                <p>Sign in to continue to your dashboard</p>
            </div>

            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">
                    </div>
                    @error('email')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    </div>
                    @error('password')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-row">
                    <label for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="footer-text">
                &copy; {{ date('Y') }} Al-Makkah-Steel Works. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
