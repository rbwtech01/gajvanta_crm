<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business System — Application Control Center</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-deep: #3b0a0a;
            --bg-mid: #641313;
            --panel: #7f1d1d;
            --panel-hover: #991b1b;
            --border: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 160, 160, 0.45);
            --text-primary: #eef2fb;
            --text-secondary: #94a3c0;
            --accent: #ff8585;
            --accent-soft: rgba(255, 110, 110, 0.18);
            --shadow-color: rgba(32, 0, 0, 0.55);
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Inter', Roboto, Helvetica, Arial, sans-serif;
            background:
                radial-gradient(circle at 15% 15%, rgba(255, 140, 140, 0.16), transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(170, 35, 35, 0.18), transparent 45%),
                linear-gradient(180deg, var(--bg-deep) 0%, var(--bg-mid) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 56px 24px 72px;
        }

        .header {
            text-align: center;
            margin-bottom: 56px;
            max-width: 720px;
        }

        .header .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            letter-spacing: 3px;
            font-weight: 600;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .header .brand::before,
        .header .brand::after {
            content: "";
            width: 28px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent));
        }

        .header .brand::after {
            background: linear-gradient(90deg, var(--accent), transparent);
        }

        .header h1 {
            font-size: 38px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            background: linear-gradient(180deg, #ffffff, #c3cee6);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            font-size: 15.5px;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(220px, 300px));
            gap: 28px;
            width: 100%;
            max-width: 1000px;
            justify-content: center;
            perspective: 1600px;
        }

        .grid .card:nth-child(5) {
            grid-column: 2;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            background: linear-gradient(160deg, var(--panel) 0%, var(--bg-mid) 100%);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 30px 26px 26px;
            text-decoration: none;
            color: var(--text-primary);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.04) inset,
                0 18px 40px -20px var(--shadow-color);
            transition: transform 0.35s cubic-bezier(0.2, 0.8, 0.2, 1),
                        box-shadow 0.35s cubic-bezier(0.2, 0.8, 0.2, 1),
                        border-color 0.35s ease,
                        background 0.35s ease;
            transform-style: preserve-3d;
            will-change: transform;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(420px circle at var(--mx, 50%) var(--my, 0%), var(--accent-soft), transparent 60%);
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
        }

        .card:hover {
            transform: translateY(-10px) rotateX(4deg) rotateY(-3deg);
            border-color: var(--border-hover);
            background: linear-gradient(160deg, var(--panel-hover) 0%, #741717 100%);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.06) inset,
                0 30px 60px -24px rgba(0, 0, 0, 0.75),
                0 0 0 1px rgba(255, 120, 120, 0.16);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card:active {
            transform: translateY(-4px) scale(0.99);
        }

        .card:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 4px;
            border-color: var(--border-hover);
        }

        @media (prefers-reduced-motion: reduce) {
            .card,
            .card .cta svg {
                transition: none !important;
            }
            .card:hover {
                transform: none;
            }
        }

        .icon-wrap {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, rgba(255, 130, 130, 0.2), rgba(255, 130, 130, 0.07));
            border: 1px solid rgba(255, 160, 160, 0.24);
            margin-bottom: 20px;
            font-size: 24px;
            box-shadow: 0 8px 18px -8px rgba(255, 100, 100, 0.4);
        }

        .card h2 {
            font-size: 19px;
            font-weight: 650;
            letter-spacing: -0.2px;
            margin-bottom: 8px;
        }

        .card p {
            font-size: 13.5px;
            line-height: 1.5;
            color: var(--text-secondary);
            flex-grow: 1;
            margin-bottom: 22px;
        }

        .card .cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.2px;
        }

        .card .cta svg {
            width: 15px;
            height: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover .cta svg {
            transform: translateX(4px);
        }

        footer {
            margin-top: 64px;
            font-size: 12.5px;
            color: #58698c;
            text-align: center;
        }

        @media (max-width: 860px) {
            .grid {
                grid-template-columns: repeat(2, minmax(200px, 1fr));
                max-width: 620px;
            }
            .grid .card:nth-child(5) {
                grid-column: span 2;
                max-width: 300px;
                margin: 0 auto;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 40px 18px 56px;
            }
            .header h1 {
                font-size: 28px;
            }
            .grid {
                grid-template-columns: 1fr;
                max-width: 380px;
                gap: 18px;
            }
            .grid .card:nth-child(5) {
                grid-column: 1;
                max-width: 100%;
            }
            .card:hover {
                transform: translateY(-4px);
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">Business System</div>
        <h1>Application Control Center</h1>
        <p>Select an application to continue</p>
    </div>

    <div class="grid" role="list" aria-label="Available applications">

        <a href="{{ url('/crm/login') }}" class="card" role="listitem" aria-label="Open CRM application">
            <div class="icon-wrap" aria-hidden="true">1</div>
            <h2>CRM</h2>
            <p>Manage customers, leads, and relationship records across the business.</p>
            <span class="cta">Open Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </span>
        </a>

        <a href="{{ url('/agent/login') }}" class="card" role="listitem" aria-label="Open Agent application">
            <div class="icon-wrap" aria-hidden="true">2</div>
            <h2>Agent</h2>
            <p>Access agent tools, assignments, and day-to-day operational workflows.</p>
            <span class="cta">Open Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </span>
        </a>

        <a href="{{ url('/supervisor/login') }}" class="card" role="listitem" aria-label="Open supervisor application">
            <div class="icon-wrap" aria-hidden="true">3</div>
            <h2>Supervisor</h2>
            <p>Access suprvisor access, & review orders.</p>
            <span class="cta">Open Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </span>
        </a>

        <a href="{{ url('/order-confirming/login') }}" class="card" role="listitem" aria-label="Open Operations application">
            <div class="icon-wrap" aria-hidden="true">4</div>
            <h2>Order Confirming</h2>
            <p>Oversee internal operations, workflows, and cross-team coordination.</p>
            <span class="cta">Open Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </span>
        </a>

        <a href="{{ url('/packing/login') }}" class="card" role="listitem" aria-label="Open Packing application">
            <div class="icon-wrap" aria-hidden="true">5</div>
            <h2>Packing</h2>
            <p>Coordinate packing operations, order preparation, and warehouse tasks.</p>
            <span class="cta">Open Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </span>
        </a>

        <a href="{{ url('/shipment/login') }}" class="card" role="listitem" aria-label="Open Shipment application">
            <div class="icon-wrap" aria-hidden="true">6</div>
            <h2>Shipment</h2>
            <p>Track, dispatch, and manage outgoing and incoming shipments.</p>
            <span class="cta">Open Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </span>
        </a>

        

    </div>

    <footer>Business System · Application Launcher</footer>

    <script>
        // Subtle cursor-tracked glow. Purely cosmetic — cards are plain <a href>
        // links, so navigation works with or without this script running.
        (function () {
            try {
                var supportsFinePointer = window.matchMedia && window.matchMedia('(pointer: fine)').matches;
                var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (!supportsFinePointer || reducedMotion) return;

                document.querySelectorAll('.card').forEach(function (card) {
                    card.addEventListener('mousemove', function (e) {
                        var rect = card.getBoundingClientRect();
                        var x = ((e.clientX - rect.left) / rect.width) * 100;
                        var y = ((e.clientY - rect.top) / rect.height) * 100;
                        card.style.setProperty('--mx', x + '%');
                        card.style.setProperty('--my', y + '%');
                    });
                });
            } catch (err) {
                // Fail silently — cosmetic enhancement only, never blocks navigation.
            }
        })();
    </script>

</body>
</html>