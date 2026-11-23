
<style>
    .meta-campaign-details {
        width: 100%;
        color: #f4f4f5;
        font-family: inherit;
    }

    .meta-campaign-details *,
    .meta-campaign-details *::before,
    .meta-campaign-details *::after {
        box-sizing: border-box;
    }

    .meta-campaign-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .meta-summary-card {
        padding: 22px;
        border: 1px solid #34343b;
        border-radius: 14px;
        background: #1c1c20;
        min-width: 0;
        transition: border-color 0.2s ease, transform 0.2s ease;
    }

    .meta-summary-card:hover {
        border-color: #52525b;
        transform: translateY(-2px);
    }

    .meta-summary-label {
        margin: 0 0 10px;
        color: #a1a1aa;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .meta-summary-value {
        margin: 0;
        color: #fafafa;
        font-size: clamp(20px, 2.2vw, 28px);
        font-weight: 750;
        line-height: 1.3;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .meta-summary-card.spent {
        background: linear-gradient(135deg, #10251e, #17221e);
        border-color: #176b4b;
    }

    .meta-summary-card.spent .meta-summary-label,
    .meta-summary-card.spent .meta-summary-value {
        color: #34d399;
    }

    .meta-summary-card.revenue {
        background: linear-gradient(135deg, #10251e, #17221e);
        border-color: #176b4b;
    }

    .meta-summary-card.revenue .meta-summary-label,
    .meta-summary-card.revenue .meta-summary-value {
        color: #34d399;
    }

    .meta-campaign-table-wrap {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #34343b;
        border-radius: 14px;
        background: #18181b;
        scrollbar-width: thin;
        scrollbar-color: #52525b #18181b;
    }

    .meta-campaign-table {
        width: 100%;
        min-width: 650px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    .meta-campaign-table th,
    .meta-campaign-table td {
        padding: 17px 20px;
        text-align: left;
        vertical-align: middle;
        border-bottom: 1px solid #2d2d33;
    }

    .meta-campaign-table thead th {
        background: #242428;
        color: #c4c4cc;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .meta-campaign-table tbody td {
        color: #e4e4e7;
        font-size: 14px;
        line-height: 1.6;
    }

    .meta-campaign-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .meta-campaign-table tbody tr:hover {
        background: #222227;
    }

    .meta-campaign-table tbody tr:last-child td {
        border-bottom: none;
    }

    .meta-campaign-table .meta-row-number {
        width: 60px;
        color: #a1a1aa;
    }

    .meta-campaign-table .meta-campaign-name {
        min-width: 220px;
        font-weight: 600;
        color: #fafafa;
        overflow-wrap: anywhere;
    }

    .meta-campaign-table .meta-amount {
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .meta-campaign-table .meta-spent {
        color: #34d399;
        font-weight: 650;
    }

    .meta-campaign-table tfoot th,
    .meta-campaign-table tfoot td {
        padding: 19px 20px;
        background: #202b27;
        border-top: 1px solid #176b4b;
        border-bottom: none;
        font-size: 14px;
        font-weight: 750;
    }

    .meta-campaign-table tfoot th {
        color: #fafafa;
        text-align: left;
    }

    .meta-campaign-table tfoot .meta-spent {
        color: #34d399;
    }

    .meta-campaign-empty {
        padding: 44px 20px !important;
        color: #a1a1aa !important;
        text-align: center !important;
    }

    .meta-campaign-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #303036;
    }

    .meta-campaign-footer p {
        margin: 0;
        color: #a1a1aa;
        font-size: 13px;
        line-height: 1.6;
    }

    @media (max-width: 700px) {
        .meta-campaign-summary {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .meta-summary-card {
            padding: 18px;
        }

        .meta-campaign-table th,
        .meta-campaign-table td {
            padding: 14px;
        }

        .meta-campaign-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="meta-campaign-details">
    {{-- Summary cards --}}
    <div class="meta-campaign-summary">
        <div class="meta-summary-card">
            <p class="meta-summary-label">Total Campaigns</p>
            <p class="meta-summary-value">{{ $campaigns->count() }}</p>
        </div>

        <div class="meta-summary-card">
            <p class="meta-summary-label">Total Budget</p>
            <p class="meta-summary-value">
                ₹{{ number_format((float) $campaigns->sum('budget'), 2) }}
            </p>
        </div>

        <div class="meta-summary-card spent">
            <p class="meta-summary-label">Total Amount Spent</p>
            <p class="meta-summary-value">
                ₹{{ number_format((float) $campaigns->sum('amount_spent'), 2) }}
            </p>
        </div>

        <div class="meta-summary-card revenue">
            <p class="meta-summary-label">Agent Revenue (Selected Date)</p>
            <p class="meta-summary-value">
                ₹{{ number_format((float) $agentRevenue, 2) }}
            </p>
        </div>

        <div class="meta-summary-card">
            <p class="meta-summary-label">Spend Share (All Agents)</p>
            <p class="meta-summary-value">
                {{ number_format((float) $spendShare, 2) }}%
            </p>
        </div>

        <div class="meta-summary-card">
            <p class="meta-summary-label">Salary / Day</p>
            <p class="meta-summary-value">
                ₹{{ number_format((float) $dailySalary, 2) }}
            </p>
        </div>

        <div class="meta-summary-card">
            <p class="meta-summary-label">Spend / Daily Salary</p>
            <p class="meta-summary-value">
                @if ($spendVsSalary === null)
                    —
                @else
                    {{ number_format((float) $spendVsSalary, 2) }}x daily salary
                @endif
            </p>
            <p class="meta-summary-label" style="margin-top: 10px; text-transform: none; letter-spacing: normal;">
                ₹{{ number_format((float) $campaigns->sum('amount_spent'), 2) }} ad spend ÷
                ₹{{ number_format((float) $dailySalary, 2) }} salary/day
            </p>
        </div>
    </div>

    {{-- Campaign table --}}
    <div class="meta-campaign-table-wrap">
        <table class="meta-campaign-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Agent Name</th>
                    <th>Campaign Name</th>
                    <th class="meta-amount">Campaign Budget</th>
                    <th class="meta-amount">Amount Spent</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td class="meta-row-number">
                            {{ $loop->iteration }}
                        </td>

                        <td class="meta-campaign-name">
                            {{ $campaign->agent?->name ?? 'Unknown Agent' }}
                        </td>

                        <td class="meta-campaign-name">
                            {{ $campaign->campaign_name }}
                        </td>

                        <td class="meta-amount">
                            @if ((float) $campaign->budget === 0.0)
                                —
                            @else
                                ₹{{ number_format((float) $campaign->budget, 2) }}
                                @if ($campaign->budget_type !== null)
                                    <small>/ {{ $campaign->budget_type }}</small>
                                @endif
                            @endif
                        </td>

                        <td class="meta-amount meta-spent">
                            ₹{{ number_format((float) $campaign->amount_spent, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="meta-campaign-empty">
                            No campaign records found for this date.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if ($campaigns->isNotEmpty())
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>

                        <td class="meta-amount">
                            ₹{{ number_format((float) $campaigns->sum('budget'), 2) }}
                        </td>

                        <td class="meta-amount meta-spent">
                            ₹{{ number_format((float) $campaigns->sum('amount_spent'), 2) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Footer --}}
    <div class="meta-campaign-footer">
        <p>
            Revenue is the agent’s total sales booked on this date; sales are not currently linked to individual Meta campaigns.
        </p>
        <p>
            Showing {{ $campaigns->count() }}
            {{ \Illuminate\Support\Str::plural('campaign', $campaigns->count()) }}
            for the selected date.
        </p>
    </div>
</div>
