<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Created — CRM</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F0F4F8;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1A2332;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 48px 40px 40px;
            max-width: 440px;
            width: 100%;
            text-align: center;
        }

        .icon-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            background: #E6F4EA;
            border-radius: 50%;
            margin-bottom: 24px;
        }

        .icon-wrap svg {
            width: 36px;
            height: 36px;
            color: #2E7D32;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1A2332;
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }

        p {
            font-size: 0.95rem;
            color: #075530;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        p + p {
            margin-bottom: 32px;
        }

        .btn-login {
            display: inline-block;
            background: #21df84;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 13px 32px;
            border-radius: 8px;
            letter-spacing: 0.2px;
            transition: background 0.18s ease, transform 0.12s ease;
        }

        .btn-login:hover {
            background: #21df84;
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .card {
                margin: 16px;
                padding: 36px 24px 32px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .btn-login { transition: none; }
        }
    </style>
</head>
<body>

    <div class="card">

        {{-- Success Icon --}}
        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        {{-- Heading --}}
        <h1>Password Created Successfully</h1>

        {{-- Body copy --}}
        <p>Your password has been created.</p>
        <p>Please proceed to login to CRM.</p>

        {{-- CTA --}}
        <a href="/agent/login" class="btn-login">Go To Login</a>

    </div>

</body>
</html>