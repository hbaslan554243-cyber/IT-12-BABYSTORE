<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Aizen Baby Store</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --clr-bg: #EAF6FC;
            --clr-primary: #8FD3F4;
            --clr-primary-dark: #6EC1E4;
            --clr-primary-darker: #55B0D6;
            --clr-powder: #CDEBF7;
            --clr-text: #33475B;
            --clr-text-muted: #83A0B4;
            --clr-border: #DFF0F8;
            --clr-danger-soft: #FBE1E1;
            --clr-danger-text: #B15757;
            --shadow-md: 0 16px 40px rgba(120, 170, 210, 0.18);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(180deg, #EAF6FC 0%, #F7FBFD 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-shell {
            display: flex;
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            min-height: 560px;
        }
        .illustration-side {
            flex: 1;
            background: linear-gradient(160deg, var(--clr-primary) 0%, var(--clr-primary-dark) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .illustration-side .cloud { position: absolute; opacity: .35; }
        .illustration-side .cloud.c1 { top: 40px; left: 30px; width: 70px; }
        .illustration-side .cloud.c2 { bottom: 60px; right: 30px; width: 50px; }
        .illustration-side .cloud.c3 { top: 120px; right: 60px; width: 34px; }
        .illustration-side h2 { font-weight: 800; font-size: 22px; margin: 20px 0 8px; }
        .illustration-side p { font-size: 14px; opacity: .9; max-width: 260px; }

        .form-side {
            flex: 1.1;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .logo-row { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; }
        .logo-bubble {
            width: 46px; height: 46px;
            background: linear-gradient(160deg, var(--clr-primary), var(--clr-primary-dark));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .logo-row strong { display: block; font-size: 17px; color: var(--clr-text); }
        .logo-row span { font-size: 12px; color: var(--clr-text-muted); }

        .form-side h1 { font-size: 24px; font-weight: 800; color: var(--clr-text); margin-bottom: 4px; }
        .form-side .sub { color: var(--clr-text-muted); font-size: 14px; margin-bottom: 28px; }

        .form-label { font-weight: 700; font-size: 13.5px; color: var(--clr-text); }
        .form-control {
            width: 100%;
            display: block;
            border-radius: 12px;
            border: 1px solid var(--clr-border);
            padding: 11px 14px;
            font-size: 14.5px;
            font-family: inherit;
        }
        .form-control:focus {
            border-color: var(--clr-primary);
            box-shadow: 0 0 0 .2rem rgba(143, 211, 244, .25);
            outline: none;
        }
        .password-wrap { position: relative; width: 100%; }
        .password-wrap .form-control { padding-right: 44px; }
        .password-wrap .toggle-eye {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: var(--clr-text-muted); background: none; border: none; padding: 4px;
        }
        .form-check-input:checked { background-color: var(--clr-primary-dark); border-color: var(--clr-primary-dark); }

        .btn-login {
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark));
            border: none;
            color: #fff;
            font-weight: 800;
            font-size: 15px;
            border-radius: 12px;
            padding: 12px;
            width: 100%;
            transition: filter .15s ease;
        }
        .btn-login:hover { filter: brightness(0.96); }
        .btn-login:disabled { opacity: .75; }

        .alert-error {
            background: var(--clr-danger-soft);
            color: var(--clr-danger-text);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13.5px;
            margin-bottom: 18px;
        }
        .alert-status {
            background: #DCF6E7;
            color: #2C8558;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13.5px;
            margin-bottom: 18px;
        }
        .forgot-link { font-size: 13px; color: var(--clr-primary-darker); text-decoration: none; font-weight: 700; }
        .spinner { width: 16px; height: 16px; border: 2.5px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%; display: none; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 767px) {
            .illustration-side { display: none; }
            .form-side { padding: 40px 28px; }
        }

        /* Fallback modal styling — keeps the Forgot Password dialog centered
           and dimmed even if the Bootstrap CSS CDN fails to load (only its
           JS loading would otherwise leave the modal content sitting
           inline, undimmed, unstyled). */
        .modal:not(.show) { display: none !important; }
        .modal.show { display: block !important; position: fixed; inset: 0; z-index: 1055; }
        .modal-dialog { max-width: 420px; margin: 90px auto; position: relative; z-index: 1056; }
        .modal-content { background: #fff; border-radius: 16px; box-shadow: var(--shadow-md); }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(51, 71, 91, 0.45); z-index: 1050; }

        .forgot-modal-content { border: none; border-radius: 20px; position: relative; overflow: visible; }
        .forgot-modal-close {
            position: absolute; top: 14px; right: 14px;
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--clr-bg); border: none; color: var(--clr-text-muted);
            display: flex; align-items: center; justify-content: center;
            transition: background .15s ease;
        }
        .forgot-modal-close:hover { background: var(--clr-powder); }
        .forgot-modal-icon {
            width: 56px; height: 56px; margin: 4px auto 18px auto;
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark));
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: var(--shadow-sm);
        }
        .forgot-modal-title { font-weight: 800; font-size: 18px; color: var(--clr-text); margin-bottom: 10px; }
        .forgot-modal-text { font-size: 13.5px; color: var(--clr-text-muted); line-height: 1.6; margin-bottom: 22px; }
    </style>
</head>
<body>

<div class="login-shell">
    <div class="illustration-side">
        <svg class="cloud c1" viewBox="0 0 64 40" fill="currentColor"><path d="M16 30a12 12 0 0 1-1-24 14 14 0 0 1 27-4 10 10 0 0 1 6 19Z"/></svg>
        <svg class="cloud c2" viewBox="0 0 64 40" fill="currentColor"><path d="M16 30a12 12 0 0 1-1-24 14 14 0 0 1 27-4 10 10 0 0 1 6 19Z"/></svg>
        <svg class="cloud c3" viewBox="0 0 64 40" fill="currentColor"><path d="M16 30a12 12 0 0 1-1-24 14 14 0 0 1 27-4 10 10 0 0 1 6 19Z"/></svg>

        <!-- Simple teddy bear illustration -->
        <svg width="150" height="150" viewBox="0 0 200 200" fill="none">
            <circle cx="60" cy="55" r="18" fill="#fff" opacity=".9"/>
            <circle cx="140" cy="55" r="18" fill="#fff" opacity=".9"/>
            <circle cx="100" cy="95" r="55" fill="#fff" opacity=".95"/>
            <circle cx="78" cy="88" r="7" fill="#6EC1E4"/>
            <circle cx="122" cy="88" r="7" fill="#6EC1E4"/>
            <ellipse cx="100" cy="108" rx="16" ry="12" fill="#DCEFF8"/>
            <circle cx="100" cy="100" r="6" fill="#6EC1E4"/>
            <path d="M85 118q15 12 30 0" stroke="#6EC1E4" stroke-width="3" stroke-linecap="round" fill="none"/>
            <circle cx="55" cy="140" r="20" fill="#fff" opacity=".9"/>
            <circle cx="145" cy="140" r="20" fill="#fff" opacity=".9"/>
        </svg>

        <h2>Welcome Back!</h2>
        <p>Sign in to Aizen Baby Store.</p>
    </div>

    <div class="form-side">
        <div class="logo-row">
            <div class="logo-bubble">🍼</div>
            <div>
                <strong>Aizen Baby Store</strong>
                <span>Management System</span>
            </div>
        </div>

        <h1>Sign In</h1>
        <div class="sub">Enter your credentials to access your dashboard.</div>

        @if(session('status'))
            <div class="alert-status">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error)
                    {{ $error }}@if(!$loop->last)<br>@endif
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" id="login-form">
            @csrf
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="login" value="{{ old('login') }}" class="form-control" placeholder="owner or owner@aizenbabystore.test" required autofocus>
            </div>
            <div class="mb-2">
                <label class="form-label">Password</label>
                <div class="password-wrap">
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="toggle-eye" onclick="togglePassword()" aria-label="Show or hide password">
                        <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:13.5px; color: var(--clr-text-muted);">Remember me</label>
                </div>
                <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot password?</a>
            </div>
            <button type="submit" class="btn-login d-flex align-items-center justify-content-center gap-2" id="login-btn">
                <span class="spinner" id="login-spinner"></span>
                <span id="login-btn-text">Login</span>
            </button>
        </form>
    </div>
</div>

<!-- Forgot Password info modal (no self-service reset; owner/staff use a two-person store) -->
<div class="modal fade" id="forgotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content forgot-modal-content">
            <button type="button" class="forgot-modal-close" data-bs-dismiss="modal" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <div class="modal-body text-center px-4 pt-4 pb-4">
                <div class="forgot-modal-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                </div>
                <h5 class="forgot-modal-title">Forgot your password?</h5>
                <p class="forgot-modal-text">For account security, password resets are handled directly by the store owner. Please ask them to reset it for you from the <strong>Manage Users</strong> page.</p>
                <button class="btn-login" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
    }

    document.getElementById('login-form').addEventListener('submit', function () {
        document.getElementById('login-spinner').style.display = 'inline-block';
        document.getElementById('login-btn-text').textContent = 'Signing in...';
        document.getElementById('login-btn').disabled = true;
    });
</script>
</body>
</html>
