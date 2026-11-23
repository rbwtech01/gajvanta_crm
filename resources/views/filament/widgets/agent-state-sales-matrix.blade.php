<x-filament-widgets::widget>
    <x-filament::section heading="Agent & State Wise Sales"
        description="Sales revenue by state and agent for the selected dashboard filters.">

        <style>
            .agent-state-sales-wrapper {
                width: 100%;
                min-width: 0;
                max-width: 100%;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            .agent-state-sales-table {
                width: max-content;
                min-width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                table-layout: auto;
                font-size: 0.875rem;
            }

            .agent-state-sales-table th,
            .agent-state-sales-table td {
                white-space: nowrap;
                padding: 14px 20px;
                vertical-align: middle;
            }

            .agent-state-sales-table th {
                font-weight: 600;
                color: rgb(243 244 246);
                background: rgb(31 41 55);
                border-bottom: 1px solid rgb(55 65 81);
                text-align: right;
            }

            .agent-state-sales-table td {
                color: rgb(209 213 219);
                border-bottom: 1px solid rgb(31 41 55);
                text-align: right;
            }

            /* State column */
            .agent-state-sales-table .state-column {
                min-width: 180px;
                width: 180px;
                text-align: left;
                position: sticky;
                left: 0;
            }

            .agent-state-sales-table thead .state-column {
                z-index: 10;
                background: rgb(31 41 55);
            }

            .agent-state-sales-table tbody .state-column {
                z-index: 5;
                background: rgb(24 24 27);
            }

            .agent-state-sales-table tfoot .state-column {
                z-index: 10;
                background: rgb(39 39 42);
            }

            /* Agent columns */
            .agent-state-sales-table .agent-column {
                min-width: 160px;
                width: 160px;
            }

            /* Total Sales column */
            .agent-state-sales-table .total-column {
                min-width: 180px;
                width: 180px;
                font-weight: 600;
                background: rgb(39 39 42);
            }

            .agent-state-sales-table thead .total-column {
                background: rgb(31 41 55);
            }

            /* Footer (grand total row) */
            .agent-state-sales-table tfoot th {
                background: rgb(39 39 42);
                font-weight: 700;
                color: rgb(255 255 255);
                border-top: 2px solid rgb(75 85 99);
                border-bottom: none;
                text-align: right;
            }

            .agent-state-sales-table tfoot th.state-column {
                text-align: left;
            }

            .agent-state-sales-table tbody tr:last-child td {
                border-bottom: none;
            }

            .agent-state-sales-table .empty-row td {
                text-align: center;
                padding: 32px;
                color: rgb(156 163 175);
            }

            /* Scrollbar */
            .agent-state-sales-wrapper::-webkit-scrollbar {
                height: 8px;
            }

            .agent-state-sales-wrapper::-webkit-scrollbar-track {
                background: rgb(39 39 42);
                border-radius: 999px;
            }

            .agent-state-sales-wrapper::-webkit-scrollbar-thumb {
                background: rgb(82 82 91);
                border-radius: 999px;
            }

            .agent-state-sales-wrapper::-webkit-scrollbar-thumb:hover {
                background: rgb(113 113 122);
            }


            .agent-header {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .agent-name {
                font-weight: 600;
                white-space: nowrap;
            }

            .view-orders-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 5px 9px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                line-height: 1;
                text-decoration: none;
                white-space: nowrap;

                color: rgb(34 197 94);
                border: 1px solid rgb(34 197 94 / 0.35);
                background: rgb(34 197 94 / 0.08);
            }

            .view-orders-button:hover {
                background: rgb(34 197 94 / 0.16);
            }

            .view-orders-icon {
                width: 14px;
                height: 14px;
            }
        </style>

        <div class="agent-state-sales-wrapper">

            <table class="agent-state-sales-table">

                <thead>
                    <tr>

                        {{-- STATE --}}
                        <th class="state-column">
                            State
                        </th>

                        {{-- AGENTS --}}
                        {{-- @foreach ($agents as $agent)
                            <th class="agent-column">
                                {{ $agent['name'] }}
                            </th>
                        @endforeach --}}


                        @foreach ($agents as $agent)
                            <th class="agent-column">
                                <div class="agent-header">

                                    <div class="agent-name">
                                        {{ $agent['name'] }}
                                    </div>

                                    <a href="{{ url('/crm/order-calendar') . '?agent=' . $agent['id'] }}"
                                        class="view-orders-button">
                                        <x-filament::icon icon="heroicon-o-calendar-days" class="view-orders-icon" />

                                        <span>View Orders</span>
                                    </a>

                                </div>
                            </th>
                        @endforeach

                        {{-- TOTAL --}}
                        <th class="total-column">
                            Total Sales
                        </th>

                    </tr>
                </thead>

                <tbody>

                    @forelse ($salesMatrix as $state => $agentSales)

                        <tr>

                            {{-- STATE --}}
                            <td class="state-column">
                                {{ $state }}
                            </td>

                            {{-- AGENT SALES --}}
                            @foreach ($agents as $agent)
                                @php
                                    $value = $agentSales[$agent['id']] ?? 0;
                                @endphp

                                <td class="agent-column">
                                    ₹{{ number_format($value, 2) }}
                                </td>
                            @endforeach

                            {{-- STATE TOTAL --}}
                            <td class="total-column">
                                ₹{{ number_format($stateTotals[$state] ?? 0, 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr class="empty-row">
                            <td colspan="{{ $agents->count() + 2 }}">
                                No sales data found for the selected filters.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

                {{-- GRAND TOTAL --}}
                @if ($salesMatrix)

                    <tfoot>
                        <tr>

                            <th class="state-column">
                                Total Sales
                            </th>

                            @foreach ($agents as $agent)
                                <th class="agent-column">
                                    ₹{{ number_format($agentTotals[$agent['id']] ?? 0, 2) }}
                                </th>
                            @endforeach

                            <th class="total-column">
                                ₹{{ number_format($grandTotal, 2) }}
                            </th>

                        </tr>
                    </tfoot>

                @endif

            </table>

        </div>

    </x-filament::section>
</x-filament-widgets::widget>
