<x-filament-widgets::widget>

    {{-- =========================================================
         ORDER INACTIVITY ALERT
         ========================================================= --}}

    @if ($this->shouldShowOrderAlert())
        <div class="order-alert-card mb-6">
            <div class="order-alert-glow"></div>

            <div class="order-alert-content">

                {{-- Icon --}}
                <div class="order-alert-icon">
                    <x-heroicon-o-bell-alert class="h-7 w-7" />
                </div>

                {{-- Content --}}
                <div class="order-alert-main">

                    <div class="order-alert-title-row">
                        <div>
                            <h2 class="order-alert-title">
                                Order Activity Reminder
                            </h2>

                            <p class="order-alert-subtitle">
                                You haven't created an order in the last
                                <strong>72 hours</strong>.
                                It's time to follow up with your customers.
                            </p>
                        </div>

                        <span class="order-alert-badge">
                            ACTION NEEDED
                        </span>
                    </div>

                    @if ($this->getLastOrderDate())
                        <div class="order-alert-meta">
                            <span>
                                Last order:
                                <strong>{{ $this->getLastOrderDate() }}</strong>
                            </span>

                            <span class="order-alert-dot">•</span>

                            <span>
                                {{ $this->getTimeSinceLastOrder() }}
                            </span>
                        </div>
                    @else
                        <div class="order-alert-meta">
                            You haven't created your first order yet.
                        </div>
                    @endif

                    {{-- Button --}}
                    <div class="order-alert-action">
                        <button type="button" wire:click="acknowledgeOrderAlert" wire:loading.attr="disabled"
                            wire:target="acknowledgeOrderAlert" class="order-alert-button">
                            <span wire:loading.remove wire:target="acknowledgeOrderAlert">
                                <x-heroicon-o-clock class="h-5 w-5" />
                                I'll Make an Order Within 1 Hour
                            </span>

                            <span wire:loading wire:target="acknowledgeOrderAlert">
                                <svg class="order-alert-spinner" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"
                                        opacity="0.25" />
                                    <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3"
                                        stroke-linecap="round" />
                                </svg>

                                Saving...
                            </span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif


    @php
        $clampedPercentage = min((float) $percentage, 100);

        $statusConfig = match ($status) {
            'red' => [
                'theme' => 'is-red',
                'badge' => '🔴 BELOW TARGET',
                'heading' => 'Monthly Sales Alert',
                'subtitle' => "Your current month's sales are below the expected pace.",
            ],
            'yellow' => [
                'theme' => 'is-yellow',
                'badge' => '🟡 IN PROGRESS',
                'heading' => 'Target In Progress',
                'subtitle' => "Great! You are currently on track to achieve this month's target.",
            ],
            default => [
                'theme' => 'is-green',
                'badge' => '🟢 TARGET ACHIEVED',
                'heading' => 'Monthly Target Achieved',
                'subtitle' => 'Congratulations! Monthly target achieved successfully.',
            ],
        };
    @endphp

    <div class="apa-card {{ $statusConfig['theme'] }}">

        <div class="apa-glow"></div>
        <div class="apa-border-anim"></div>

        <div class="apa-inner">

            <div class="apa-header">
                <div class="apa-heading-group">
                    <h2 class="apa-heading">{{ $statusConfig['heading'] }}</h2>
                    <p class="apa-subtitle">{{ $statusConfig['subtitle'] }}</p>
                </div>

                <div class="apa-badge">
                    <span class="apa-badge-text">{{ $statusConfig['badge'] }}</span>
                </div>
            </div>

            <div class="apa-stats">

                <div class="apa-stat">
                    <span class="apa-stat-label">Current Sales</span>
                    <span class="apa-stat-value">₹{{ number_format($sales) }}</span>
                </div>

                <div class="apa-stat">
                    <span class="apa-stat-label">Monthly Target</span>
                    <span class="apa-stat-value">₹{{ number_format($target) }}</span>
                </div>

                @if ($status === 'green')
                    <div class="apa-stat">
                        <span class="apa-stat-label">Above Target</span>
                        <span class="apa-stat-value apa-stat-positive">
                            +₹{{ number_format(max(0, $sales - $target)) }}
                        </span>
                    </div>
                @else
                    <div class="apa-stat">
                        <span class="apa-stat-label">Remaining</span>
                        <span class="apa-stat-value">₹{{ number_format($remaining) }}</span>
                    </div>
                @endif

                <div class="apa-stat">
                    <span class="apa-stat-label">Performance Pace</span>
                    <span class="apa-stat-value apa-gradient-text">{{ number_format((float) $percentage, 1) }}%</span>
                </div>

            </div>

            <div class="apa-progress-wrapper">
                <div class="apa-progress-track">
                    <div class="apa-progress-fill" style="--apa-target-width: {{ $clampedPercentage }}%;">
                        <span class="apa-progress-shimmer"></span>
                    </div>
                </div>
                <div class="apa-progress-caption">
                    <span>0%</span>
                    <span>{{ number_format((float) $percentage, 1) }}% Achieved</span>
                    <span>100%</span>
                </div>
            </div>

            @if ($status === 'green')
                <div class="apa-confetti" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span>
                </div>
            @endif

        </div>
    </div>

    <style>
        .apa-card {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            padding: 2px;
            isolation: isolate;
            animation: apa-fade-in 0.6s ease-out, apa-slide-up 0.6s ease-out;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .apa-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 45px -12px rgba(0, 0, 0, 0.45);
        }

        .apa-inner {
            position: relative;
            z-index: 2;
            border-radius: 20px;
            padding: 32px;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            overflow: hidden;
        }

        .apa-glow {
            position: absolute;
            inset: -40%;
            z-index: 0;
            filter: blur(60px);
            opacity: 0.55;
            animation: apa-float 6s ease-in-out infinite;
            pointer-events: none;
        }

        .apa-border-anim {
            position: absolute;
            inset: 0;
            z-index: 1;
            border-radius: 22px;
            padding: 2px;
            background-size: 300% 300%;
            animation: apa-gradient-move 6s ease infinite;
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        /* ============ RED THEME ============ */
        .is-red .apa-inner {
            background: linear-gradient(145deg, rgba(76, 5, 5, 0.92), rgba(120, 15, 15, 0.85));
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.08), 0 18px 40px -14px rgba(120, 10, 10, 0.65);
            border: 1px solid rgba(255, 90, 90, 0.25);
        }

        .is-red .apa-glow {
            background: radial-gradient(circle, #ff4d4d 0%, transparent 70%);
        }

        .is-red .apa-border-anim {
            background-image: linear-gradient(120deg, #ff4d4d, #7a0e0e, #ff8080, #7a0e0e);
        }

        .is-red .apa-badge {
            background: linear-gradient(135deg, #ff4d4d, #b30000);
            box-shadow: 0 0 18px rgba(255, 77, 77, 0.55);
        }

        .is-red .apa-progress-fill {
            background: linear-gradient(90deg, #ff8080, #ff2b2b, #ff8080);
            background-size: 200% 100%;
        }

        .is-red .apa-stat-value,
        .is-red .apa-heading {
            color: #ffe5e5;
        }

        /* ============ YELLOW THEME ============ */
        .is-yellow .apa-inner {
            background: linear-gradient(145deg, rgba(95, 55, 5, 0.92), rgba(150, 90, 10, 0.85));
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.08), 0 18px 40px -14px rgba(150, 90, 10, 0.6);
            border: 1px solid rgba(255, 190, 90, 0.3);
        }

        .is-yellow .apa-glow {
            background: radial-gradient(circle, #ffb347 0%, transparent 70%);
        }

        .is-yellow .apa-border-anim {
            background-image: linear-gradient(120deg, #ffb347, #a35c00, #ffd27f, #a35c00);
        }

        .is-yellow .apa-badge {
            background: linear-gradient(135deg, #ffb347, #c97a00);
            box-shadow: 0 0 18px rgba(255, 179, 71, 0.55);
        }

        .is-yellow .apa-progress-fill {
            background: linear-gradient(90deg, #ffd27f, #ff9f1c, #ffd27f);
            background-size: 200% 100%;
        }

        .is-yellow .apa-stat-value,
        .is-yellow .apa-heading {
            color: #fff2df;
        }

        /* ============ GREEN THEME ============ */
        .is-green .apa-inner {
            background: linear-gradient(145deg, rgba(4, 60, 40, 0.92), rgba(8, 100, 65, 0.85));
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.08), 0 18px 40px -14px rgba(8, 100, 65, 0.6);
            border: 1px solid rgba(90, 255, 180, 0.28);
        }

        .is-green .apa-glow {
            background: radial-gradient(circle, #34d399 0%, transparent 70%);
        }

        .is-green .apa-border-anim {
            background-image: linear-gradient(120deg, #34d399, #046c4e, #6ee7b7, #046c4e);
        }

        .is-green .apa-badge {
            background: linear-gradient(135deg, #34d399, #047857);
            box-shadow: 0 0 18px rgba(52, 211, 153, 0.55);
        }

        .is-green .apa-progress-fill {
            background: linear-gradient(90deg, #6ee7b7, #10b981, #6ee7b7);
            background-size: 200% 100%;
        }

        .is-green .apa-stat-value,
        .is-green .apa-heading {
            color: #e3fff1;
        }

        /* ============ HEADER ============ */
        .apa-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 26px;
        }

        .apa-heading {
            margin: 0 0 6px 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .apa-subtitle {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.75);
            max-width: 42ch;
        }

        .apa-badge {
            flex-shrink: 0;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #fff;
            animation: apa-float-badge 3s ease-in-out infinite, apa-pulse 2.4s ease-in-out infinite;
            white-space: nowrap;
        }

        .apa-badge-text {
            display: inline-block;
        }

        /* ============ STATS ============ */
        .apa-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }

        .apa-stat {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px 14px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .apa-stat:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        .apa-stat-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.6);
        }

        .apa-stat-value {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .apa-stat-positive {
            color: #86efac !important;
        }

        .apa-gradient-text {
            background: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0.6), #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: apa-gradient-move 3s linear infinite;
        }

        /* ============ PROGRESS BAR ============ */
        .apa-progress-wrapper {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .apa-progress-track {
            position: relative;
            width: 100%;
            height: 12px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.3);
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        .apa-progress-fill {
            position: relative;
            height: 100%;
            width: 0;
            border-radius: 999px;
            animation:
                apa-progress-grow 1.4s cubic-bezier(0.16, 1, 0.3, 1) forwards,
                apa-gradient-move 2.5s linear infinite;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.35);
        }

        .apa-progress-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg,
                    transparent 20%,
                    rgba(255, 255, 255, 0.55) 50%,
                    transparent 80%);
            background-size: 200% 100%;
            animation: apa-shimmer 1.8s linear infinite;
        }

        .apa-progress-caption {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
        }

        /* ============ CONFETTI (GREEN ONLY) ============ */
        .apa-confetti {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .apa-confetti span {
            position: absolute;
            top: -10%;
            width: 6px;
            height: 12px;
            border-radius: 2px;
            background: #6ee7b7;
            opacity: 0.85;
            animation: apa-confetti-fall 3.2s linear infinite;
        }

        .apa-confetti span:nth-child(1) {
            left: 8%;
            background: #34d399;
            animation-delay: 0s;
        }

        .apa-confetti span:nth-child(2) {
            left: 20%;
            background: #fff;
            animation-delay: 0.4s;
        }

        .apa-confetti span:nth-child(3) {
            left: 34%;
            background: #10b981;
            animation-delay: 0.9s;
        }

        .apa-confetti span:nth-child(4) {
            left: 48%;
            background: #6ee7b7;
            animation-delay: 0.2s;
        }

        .apa-confetti span:nth-child(5) {
            left: 62%;
            background: #fff;
            animation-delay: 1.2s;
        }

        .apa-confetti span:nth-child(6) {
            left: 74%;
            background: #34d399;
            animation-delay: 0.6s;
        }

        .apa-confetti span:nth-child(7) {
            left: 86%;
            background: #10b981;
            animation-delay: 1.5s;
        }

        .apa-confetti span:nth-child(8) {
            left: 94%;
            background: #fff;
            animation-delay: 0.1s;
        }

        /* ============ KEYFRAMES ============ */
        @keyframes apa-fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes apa-slide-up {
            from {
                transform: translateY(18px);
            }

            to {
                transform: translateY(0);
            }
        }

        @keyframes apa-float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(3%, -3%) scale(1.06);
            }
        }

        @keyframes apa-float-badge {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        @keyframes apa-pulse {

            0%,
            100% {
                box-shadow: 0 0 10px rgba(255, 255, 255, 0.25);
            }

            50% {
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            }
        }

        @keyframes apa-gradient-move {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes apa-shimmer {
            from {
                background-position: 200% 0;
            }

            to {
                background-position: -200% 0;
            }
        }

        @keyframes apa-progress-grow {
            from {
                width: 0;
            }

            to {
                width: var(--apa-target-width, 0%);
            }
        }

        @keyframes apa-confetti-fall {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(340px) rotate(280deg);
                opacity: 0;
            }
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .apa-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .apa-inner {
                padding: 22px;
            }

            .apa-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .apa-heading {
                font-size: 20px;
            }

            .apa-stats {
                grid-template-columns: 1fr;
            }
        }



        /* =========================================================
        ORDER ACTIVITY ALERT — PREMIUM
        ========================================================= */

        .order-alert-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            margin-bottom: 20px;
            padding: 2px;
            border-radius: 18px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            animation: order-alert-enter 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .order-alert-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 38px -16px rgba(0, 0, 0, 0.5);
        }

        /* Animated gradient border — same technique as .apa-border-anim */
        .order-alert-border-anim {
            position: absolute;
            inset: 0;
            z-index: 1;
            border-radius: 18px;
            padding: 2px;
            background-image: linear-gradient(120deg, #fbbf24, #78350f, #fcd34d, #78350f);
            background-size: 300% 300%;
            animation: apa-gradient-move 7s ease infinite;
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .order-alert-glow {
            position: absolute;
            inset: -45%;
            z-index: 0;
            filter: blur(58px);
            opacity: 0.5;
            background: radial-gradient(circle at 25% 50%, #f59e0b 0%, transparent 65%);
            animation: apa-float 7s ease-in-out infinite;
            pointer-events: none;
        }

        .order-alert-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            border-radius: 16px;
            padding: 16px 20px;
            background: linear-gradient(145deg, rgba(69, 32, 4, 0.94), rgba(120, 60, 8, 0.86));
            border: 1px solid rgba(251, 191, 36, 0.24);
            box-shadow:
                inset 0 1px 1px rgba(255, 255, 255, 0.08),
                0 16px 36px -16px rgba(180, 83, 9, 0.6);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            overflow: hidden;
        }

        /* ---------- BELL ---------- */

        .order-alert-icon {
            position: relative;
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            margin-top: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            color: #fde68a;
            background: linear-gradient(140deg, rgba(251, 191, 36, 0.22), rgba(120, 53, 15, 0.55));
            border: 1px solid rgba(251, 191, 36, 0.35);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            animation: order-alert-bell-pulse 2.8s ease-in-out infinite;
        }

        /* Hard size cap — survives even if Tailwind h-6/w-6 never compiles */
        .order-alert-icon svg {
            width: 26px !important;
            height: 26px !important;
            display: block;
            flex-shrink: 0;
            stroke-width: 1.7;
            filter: drop-shadow(0 0 6px rgba(251, 191, 36, 0.5));
        }

        /* ---------- TEXT ---------- */

        .order-alert-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .order-alert-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 4px;
        }

        .order-alert-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.015em;
            background: linear-gradient(90deg, #fff7e6, rgba(253, 230, 138, 0.7), #fff7e6);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: apa-gradient-move 4s linear infinite;
        }

        .order-alert-badge {
            flex-shrink: 0;
            padding: 4px 11px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            line-height: 1.4;
            white-space: nowrap;
            color: #fff;
            background: linear-gradient(135deg, #fbbf24, #c2410c);
            box-shadow: 0 0 16px rgba(251, 191, 36, 0.5);
            animation: apa-pulse 2.6s ease-in-out infinite;
        }

        .order-alert-subtitle {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: rgba(255, 237, 213, 0.76);
            max-width: 52ch;
        }

        .order-alert-subtitle strong {
            font-weight: 700;
            color: #fcd34d;
        }

        .order-alert-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 8px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: rgba(253, 230, 138, 0.6);
        }

        .order-alert-meta strong {
            font-weight: 700;
            color: #fde68a;
        }

        .order-alert-dot {
            opacity: 0.45;
        }

        /* ---------- BUTTON ---------- */
        /* Detached from the text block above it with a hairline divider,
           and sized to its own content instead of stretching full width. */

        .order-alert-action {
            flex-shrink: 0;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(251, 191, 36, 0.16);
            display: flex;
        }

        .order-alert-button {
            position: relative;
            overflow: hidden;
            width: auto;
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border: 0;
            border-radius: 11px;
            cursor: pointer;
            white-space: nowrap;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.005em;
            color: #451a03;
            background: linear-gradient(135deg, #fcd34d, #f59e0b);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.45),
                0 8px 18px -8px rgba(245, 158, 11, 0.75);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        /* Sheen sweep on hover */
        .order-alert-button::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, transparent 20%, rgba(255, 255, 255, 0.55) 50%, transparent 80%);
            background-size: 200% 100%;
            background-position: 200% 0;
            pointer-events: none;
            transition: background-position 0.7s ease;
        }

        .order-alert-button:hover::after {
            background-position: -200% 0;
        }

        .order-alert-button:hover {
            filter: brightness(1.05);
            transform: translateY(-2px);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.45),
                0 12px 24px -8px rgba(245, 158, 11, 0.85);
        }

        .order-alert-button:active {
            transform: translateY(0);
        }

        .order-alert-button:focus-visible {
            outline: 2px solid #fcd34d;
            outline-offset: 3px;
        }

        .order-alert-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Targets the plain <span> wrappers around the button text/icon
           in the original Blade markup — no extra class required. This
           is what stops the clock icon wrapping onto its own line. */
        .order-alert-button>span {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .order-alert-button-inner {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .order-alert-button svg {
            width: 16px !important;
            height: 16px !important;
            display: block;
            flex-shrink: 0;
        }

        .order-alert-spinner {
            animation: order-alert-spin 0.8s linear infinite;
        }

        /* ---------- KEYFRAMES ---------- */

        @keyframes order-alert-enter {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes order-alert-bell-pulse {

            0%,
            100% {
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14), 0 0 0 0 rgba(251, 191, 36, 0.45);
            }

            50% {
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14), 0 0 0 8px rgba(251, 191, 36, 0);
            }
        }

        @keyframes order-alert-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .order-alert-card,
            .order-alert-glow,
            .order-alert-border-anim,
            .order-alert-icon,
            .order-alert-title,
            .order-alert-badge,
            .order-alert-spinner {
                animation: none !important;
            }

            .order-alert-button,
            .order-alert-button::after {
                transition: none;
            }

            .order-alert-card:hover,
            .order-alert-button:hover {
                transform: none;
            }
        }

        /* ---------- RESPONSIVE ---------- */

        @media (max-width: 767px) {
            .order-alert-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                padding: 16px;
            }

            .order-alert-action {
                width: 100%;
            }

            .order-alert-button {
                width: 100%;
            }
        }
    </style>

</x-filament-widgets::widget>
