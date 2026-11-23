<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0F1117;
            --surface:   #1A1D27;
            --border:    #2A2D3A;
            --border-focus: #3DD68C;
            --text:      #E8E9F0;
            --muted:     #3DD68C;
            --accent:    #3DD68C;
            --accent-hover: #3DD68C;
            --error:     #FF5370;
            --error-bg:  rgba(255, 83, 112, 0.08);
            --success:   #3DD68C;
            --radius:    12px;
            --font:      'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: var(--font);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-image:
                radial-gradient(ellipse 60% 50% at 50% -10%, rgba(108, 99, 255, 0.18) 0%, transparent 70%);
        }

        /* ── Card ── */
        .card {
            width: 100%;
            max-width: 440px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 24px 64px rgba(0,0,0,.45);
        }

        /* ── Icon badge ── */
        .icon-badge {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(108, 99, 255, 0.15);
            border: 1px solid rgba(108, 99, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .icon-badge svg { color: var(--accent); }

        /* ── Headings ── */
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .card-subtitle {
            margin-top: .4rem;
            font-size: .875rem;
            color: var(--muted);
            line-height: 1.5;
        }

        /* ── Global error banner ── */
        .alert-error {
            margin-top: 1.5rem;
            padding: .75rem 1rem;
            background: var(--error-bg);
            border: 1px solid rgba(255, 83, 112, 0.25);
            border-radius: var(--radius);
            color: var(--error);
            font-size: .8125rem;
            line-height: 1.5;
        }
        .alert-error ul { padding-left: 1.1rem; }
        .alert-error li + li { margin-top: .25rem; }

        /* ── Form ── */
        form { margin-top: 1.75rem; }

        .field { display: flex; flex-direction: column; gap: .4rem; }
        .field + .field { margin-top: 1.1rem; }

        label {
            font-size: .8125rem;
            font-weight: 500;
            color: var(--muted);
            letter-spacing: .02em;
        }

        /* wrapper for input + eye toggle */
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: .75rem 2.75rem .75rem 1rem;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-size: .9375rem;
            font-family: var(--font);
            transition: border-color .15s, box-shadow .15s;
            outline: none;
            -webkit-appearance: none;
        }
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, .18);
        }
        input.is-invalid {
            border-color: var(--error);
            box-shadow: 0 0 0 3px var(--error-bg);
        }
        input::placeholder { color: var(--muted); opacity: .6; }

        /* eye toggle button */
        .eye-btn {
            position: absolute;
            right: .75rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: var(--muted);
            display: flex;
            align-items: center;
            transition: color .15s;
        }
        .eye-btn:hover { color: var(--text); }

        /* field-level error message */
        .field-error {
            font-size: .775rem;
            color: var(--error);
            display: flex;
            align-items: center;
            gap: .3rem;
            line-height: 1.4;
        }
        .field-error svg { flex-shrink: 0; }

        /* password strength bar */
        .strength-wrap { margin-top: .3rem; }
        .strength-bars {
            display: flex;
            gap: .25rem;
        }
        .strength-bar {
            flex: 1;
            height: 3px;
            border-radius: 99px;
            background: var(--border);
            transition: background .3s;
        }
        .strength-label {
            font-size: .7rem;
            color: var(--muted);
            margin-top: .3rem;
        }

        /* ── Submit ── */
        .btn-submit {
            margin-top: 1.75rem;
            width: 100%;
            padding: .85rem;
            background: var(--accent);
            color: #fff;
            font-size: .9375rem;
            font-weight: 600;
            font-family: var(--font);
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            letter-spacing: .01em;
        }
        .btn-submit:hover {
            background: var(--accent-hover);
            box-shadow: 0 6px 24px rgba(108, 99, 255, .35);
        }
        .btn-submit:active { transform: scale(.98); }

        /* ── Footer link ── */
        .card-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: .8125rem;
            color: var(--muted);
        }
        .card-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        .card-footer a:hover { text-decoration: underline; }

        /* ── Mobile ── */
        @media (max-width: 480px) {
            body { padding: 1rem; align-items: flex-start; padding-top: 2rem; }
            .card { padding: 1.75rem 1.25rem; border-radius: 16px; }
            .card-title { font-size: 1.3rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; }
        }
    </style>
</head>
<body>

<div class="card" role="main">

    {{-- Icon --}}
    <div class="icon-badge" aria-hidden="true">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    <h1 class="card-title">Set new password</h1>
    <p class="card-subtitle">Choose a strong password to secure your account.</p>

    {{-- Global validation error banner --}}
    @if ($errors->any())
        <div class="alert-error" role="alert" aria-live="polite">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email --}}
        <div class="field">
            <label for="email">Email address</label>
            <div class="input-wrap">
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    placeholder="you@example.com"
                    autocomplete="email"
                    aria-describedby="email-error"
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    readonly
                >
            </div>
            @error('email')
                <span class="field-error" id="email-error" role="alert">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </span>
            @enderror
        </div>

        {{-- New password --}}
        <div class="field">
            <label for="password">New password</label>
            <div class="input-wrap">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Min. 8 characters"
                    autocomplete="new-password"
                    aria-describedby="password-error password-strength-label"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    oninput="updateStrength(this.value)"
                    required
                >
                <button type="button" class="eye-btn" onclick="togglePassword('password', this)"
                        aria-label="Show password">
                    <svg id="eye-password" width="18" height="18" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            {{-- Strength indicator --}}
            <div class="strength-wrap" aria-hidden="true">
                <div class="strength-bars">
                    <div class="strength-bar" id="bar-1"></div>
                    <div class="strength-bar" id="bar-2"></div>
                    <div class="strength-bar" id="bar-3"></div>
                    <div class="strength-bar" id="bar-4"></div>
                </div>
                <span class="strength-label" id="password-strength-label"></span>
            </div>

            @error('password')
                <span class="field-error" id="password-error" role="alert">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </span>
            @enderror
        </div>

        {{-- Confirm password --}}
        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <div class="input-wrap">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Repeat your password"
                    autocomplete="new-password"
                    aria-describedby="confirm-error"
                    class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                    required
                >
                <button type="button" class="eye-btn" onclick="togglePassword('password_confirmation', this)"
                        aria-label="Show confirm password">
                    <svg id="eye-password_confirmation" width="18" height="18" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <span class="field-error" id="confirm-error" role="alert">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </span>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
            Update password
        </button>
    </form>

</div>

<script>
    /* ── Show / hide password ── */
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

        const eye = document.getElementById('eye-' + fieldId);
        eye.innerHTML = isHidden
            ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8
                a18.45 18.45 0 0 1 5.06-5.94"/>
               <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8
                a18.5 18.5 0 0 1-2.16 3.19"/>
               <line x1="1" y1="1" x2="23" y2="23"/>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
               <circle cx="12" cy="12" r="3"/>`;
    }

    /* ── Password strength ── */
    function updateStrength(value) {
        let score = 0;
        if (value.length >= 8)  score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const colors  = ['', '#FF5370', '#FF9F43', '#FFD32A', '#3DD68C'];
        const labels  = ['', 'Weak', 'Fair', 'Good', 'Strong'];

        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('bar-' + i);
            bar.style.background = i <= score ? colors[score] : 'var(--border)';
        }
        const lbl = document.getElementById('password-strength-label');
        lbl.textContent = value.length ? labels[score] : '';
        lbl.style.color = colors[score] || 'var(--muted)';
    }
</script>

</body>
</html>