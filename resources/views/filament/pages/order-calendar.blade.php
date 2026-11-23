<x-filament-panels::page>

    <style>
        .order-calendar-content {
            width: 100%;
            margin: 0 auto;
            max-width: 1000px;
        }

        #calendar-wrapper {
            width: 100%;
        }

        #monthly-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .monthly-summary-card {
            padding: 1rem;
            border: 1px solid #374151;
            border-radius: 0.75rem;
            background: #1f2937;
        }

        .monthly-summary-label {
            display: block;
            color: #9ca3af;
            font-size: 0.875rem;
        }

        .monthly-summary-value {
            display: block;
            margin-top: 0.25rem;
            color: #f9fafb;
            font-size: 1.25rem;
            font-weight: 700;
        }

        #calendar {
            width: 100%;
            min-width: 0;
        }

        .fc {
            width: 100%;
        }

        .fc .fc-toolbar {
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .fc-daygrid-event,
        .fc-daygrid-event-harness {
            display: none !important;
        }

        .fc-daygrid-day-frame {
            min-height: clamp(3.25rem, 5vw, 4.25rem);
        }

        .fc-daygrid-day {
            transition: background-color 0.2s;
        }

        .order-count {
            display: block;
            margin: 0.25rem;
            padding: 0.2rem;
            border-radius: 0.5rem;
            background: rgb(255 255 255 / 92%);
            color: #065f46;
            font-size: 0.625rem;
            font-weight: 700;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .fc .fc-toolbar-title {
            color: #f3f4f6;
            font-size: clamp(1rem, 2vw, 1.35rem);
            font-weight: 600;
        }

        .fc .fc-button {
            border-color: #374151;
            background: #1f2937;
            padding: 0.45rem 0.7rem;
            font-size: 0.875rem;
        }

        .fc .fc-button:hover,
        .fc .fc-button:focus {
            border-color: #059669;
            background: #065f46;
            box-shadow: none;
        }

        .fc .fc-button:disabled {
            border-color: #374151;
            background: #111827;
        }

        .fc .fc-col-header-cell-cushion,
        .fc .fc-daygrid-day-number {
            color: #e5e7eb;
            font-size: 0.875rem;
        }

        @media (max-width: 640px) {
            .fc .fc-toolbar-title {
                font-size: 1rem;
            }

            .fc .fc-button {
                padding: 0.35rem 0.5rem;
                font-size: 0.75rem;
            }

            .fc-daygrid-day-frame {
                min-height: 3rem;
            }

            .order-count {
                margin: 0.125rem;
                padding: 0.2rem;
                font-size: 0.6rem;
            }

            .fc .fc-col-header-cell-cushion,
            .fc .fc-daygrid-day-number {
                font-size: 0.75rem;
            }
        }

        #agent-selector-wrapper {
            width: 100%;
            margin: 0 0 1rem;
        }

        #agent-selector-wrapper label {
            display: block;
            margin-bottom: 0.375rem;
            color: #d1d5db;
            font-size: 0.875rem;
            font-weight: 600;
        }

        #agent-selector {
            display: block;
            width: 100%;
            max-width: 20rem;
            min-height: 2.625rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #4b5563;
            border-radius: 0.5rem;
            background: #111827;
            color: #f9fafb;
            font-size: 0.875rem;
            cursor: pointer;
        }

        #agent-selector:focus-visible {
            border-color: #10b981;
            outline: 2px solid #10b981;
            outline-offset: 2px;
        }

        #agent-selector option {
            background: #111827;
            color: #f9fafb;
        }
    </style>

    <div class="order-calendar-content">
        @if(auth()->user()?->hasRole('Admin'))
        <div id="agent-selector-wrapper">
            <label for="agent-selector">
                Select Agent
            </label>

            <select id="agent-selector" @disabled($agents->isEmpty())>
                @forelse ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected((string) $selectedAgentId === (string) $agent->id)>
                        {{ $agent->name }}
                    </option>
                @empty
                    <option value="">No agents available</option>
                @endforelse
            </select>
        </div>
        @endif

        <div id="monthly-summary" aria-live="polite">
            <div class="monthly-summary-card">
                <span class="monthly-summary-label">
                    Total orders for <span id="summary-month"></span>
                </span>
                <span class="monthly-summary-value" id="summary-orders">0</span>
            </div>
            <div class="monthly-summary-card">
                <span class="monthly-summary-label">
                    Total revenue for <span class="summary-month-name"></span>
                </span>
                <span class="monthly-summary-value" id="summary-revenue">₹0.00</span>
            </div>
        </div>

        <div id="calendar-wrapper">
            <div id="calendar"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', async function() {

                const calendarEl = document.getElementById('calendar');
                const agentSelector = document.getElementById('agent-selector');
                const agent = @js($selectedAgentId);
                const currentUrl = new URL(window.location.href);

                if (agent) {
                    currentUrl.searchParams.set('agent', agent);
                } else {
                    currentUrl.searchParams.delete('agent');
                }

                if (currentUrl.href !== window.location.href) {
                    window.history.replaceState({}, '', currentUrl);
                }

                if (agentSelector) {
                    agentSelector.addEventListener('change', function() {
                        const nextUrl = new URL(window.location.href);

                        if (agentSelector.value) {
                            nextUrl.searchParams.set('agent', agentSelector.value);
                        } else {
                            nextUrl.searchParams.delete('agent');
                        }

                        window.location.assign(nextUrl);
                    });
                }

                let events = [];

                if (agent) {
                    const eventParams = new URLSearchParams({
                        start: '2026-01-01',
                        end: '2027-12-31',
                        agent: String(agent),
                    });
                    const response = await fetch(`/calendar-events?${eventParams}`);

                    if (!response.ok) {
                        throw new Error(`Calendar events request failed (${response.status}).`);
                    }

                    events = await response.json();
                }

                const orderDates = {};
                const orderCounts = {};
                const orderAmounts = {};
                const orderStatuses = {};
                const orderStates = {};

                events.forEach(event => {

                    orderDates[event.start] = true;
                    orderCounts[event.start] = event.count;
                    orderAmounts[event.start] = event.amount;
                    orderStatuses[event.start] = event.statuses || [];
                    orderStates[event.start] = event.states || [];

                });

                const calendar = new FullCalendar.Calendar(calendarEl, {

                    plugins: [
                        FullCalendar.dayGridPlugin,
                        FullCalendar.interactionPlugin,
                    ],

                    initialView: 'dayGridMonth',

                    showNonCurrentDates: false,
                    fixedWeekCount: false,

                    /*
                     * Responsive monthly calendar.
                     */
                    height: 'auto',
                    contentHeight: 'auto',
                    aspectRatio: 2,

                    expandRows: true,
                    handleWindowResize: true,

                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: ''
                    },

                    events: events,

                    datesSet(dateInfo) {
                        const monthStart = dateInfo.view.currentStart;
                        const monthKey = [
                            monthStart.getFullYear(),
                            String(monthStart.getMonth() + 1).padStart(2, '0'),
                        ].join('-');
                        const monthEvents = events.filter(event => event.start.startsWith(monthKey));
                        const monthlyOrders = monthEvents.reduce(
                            (total, event) => total + Number(event.count || 0),
                            0
                        );
                        const monthlyRevenue = monthEvents.reduce(
                            (total, event) => total + Number(event.amount || 0),
                            0
                        );
                        const monthName = new Intl.DateTimeFormat('en-IN', {
                            month: 'long',
                            year: 'numeric',
                        }).format(monthStart);

                        document.getElementById('summary-month').textContent = monthName;
                        document.querySelectorAll('.summary-month-name').forEach(element => {
                            element.textContent = monthName;
                        });
                        document.getElementById('summary-orders').textContent =
                            monthlyOrders.toLocaleString('en-IN');
                        document.getElementById('summary-revenue').textContent =
                            new Intl.NumberFormat('en-IN', {
                                style: 'currency',
                                currency: 'INR',
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            }).format(monthlyRevenue);

                        document
                            .querySelectorAll('.fc-daygrid-day')
                            .forEach(cell => {

                                const date = cell.dataset.date;

                                if (!date) {
                                    return;
                                }

                                /*
                                 * Green = orders
                                 * Red = no orders
                                 */
                                if (orderCounts[date]) {

                                    cell.style.background = '#034719';
                                    cell.style.color = '#fff';

                                } else {

                                    cell.style.background = '#5e0707';
                                    cell.style.color = '#fff';

                                }

                                const frame = cell.querySelector(
                                    '.fc-daygrid-day-frame'
                                );

                                if (!frame) {
                                    return;
                                }

                                /*
                                 * Remove old badges
                                 */
                                frame
                                    .querySelectorAll('.order-count')
                                    .forEach(e => e.remove());

                                /*
                                 * Add badge only when orders exist
                                 */
                                if (orderCounts[date]) {

                                    const badge =
                                        document.createElement('div');

                                    badge.className = 'order-count';

                                    let badgeContent = `
                                        📦 ${orderCounts[date]} Orders
                                        <br>
                                        💰 ₹${Number(
                                            orderAmounts[date]
                                        ).toLocaleString('en-IN')}
                                    `;

                                    // Add shipment statuses
                                    const statuses = orderStatuses[date] || [];
                                    if (statuses.length > 0) {
                                        const statusLines = statuses.map(status => {
                                            if (status === 'Delivered') {
                                                return '✓ Delivered';
                                            } else if (status === 'Shipped') {
                                                return '🚚 Shipped';
                                            }
                                            return status;
                                        }).join('<br>');

                                        badgeContent += `<br>${statusLines}`;
                                    }

                                    const states = orderStates[date] || [];
                                    if (states.length > 0) {
                                        badgeContent += `<br>📍 ${states.join(', ')}`;
                                    }

                                    badge.innerHTML = badgeContent;

                                    frame.appendChild(badge);

                                }

                            });

                    },

                    dateClick(info) {

                        /*
                         * Don't navigate if
                         * there are no orders.
                         */
                        if (!orderDates[info.dateStr]) {
                            return;
                        }

                        const orderListPath = @js(
                            filament()->getCurrentPanel()?->getId() === 'agent'
                                ? '/agent/orders-by-date'
                                : '/crm/orders-by-date'
                        );
                        const orderListUrl = new URL(orderListPath, window.location.origin);
                        orderListUrl.searchParams.set('date', info.dateStr);
                        orderListUrl.searchParams.set('agent', String(agent));

                        window.location.assign(orderListUrl);

                    }

                });

                calendar.render();

                /*
                 * Recalculate calendar dimensions
                 */
                setTimeout(() => {
                    calendar.updateSize();
                }, 100);

                window.addEventListener('resize', () => {
                    calendar.updateSize();
                });

            });
        </script>
    @endpush

</x-filament-panels::page>
