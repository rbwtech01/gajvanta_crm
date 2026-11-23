
@php
    $steps = [
        'created'    => ['label' => 'Unconfirmed', 'desc' => 'Order placed'],
        'hold'       => ['label' => 'Hold', 'desc' => 'Awaiting action'],
        'processing' => ['label' => 'Processing', 'desc' => 'Being prepared'],
        'confirmed'  => ['label' => 'Confirmed', 'desc' => 'Order confirmed'],
        'packed'     => ['label' => 'Packed', 'desc' => 'Ready to ship'],
        'dispatched' => ['label' => 'Dispatched', 'desc' => 'On the way'],
        'delivered'  => ['label' => 'Delivered', 'desc' => 'Order completed'],
    ];

    $statusKeys   = array_keys($steps);
    $currentStatus = strtolower($record->status ?? 'created');
    $currentIndex = array_search($currentStatus, $statusKeys, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
    $totalSteps   = count($statusKeys);
    $isDelivered  = $currentStatus === 'delivered';
    $shipment = $record->shipment;

    $progressPercent = $totalSteps > 1
        ? ($currentIndex / ($totalSteps - 1)) * 100
        : 0;

    $trackingId = $shipment?->tracking_id;
    $courier = $shipment?->courier?->name;
    $dispatchDate = $shipment?->dispatch_date;
    $remarks = $record->hold_remark;

    $formatDate = function ($value) {
        if (blank($value)) return null;
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $hasDetails = true;
    $currentLabel = $steps[$statusKeys[$currentIndex]]['label'] ?? ucfirst($currentStatus);
@endphp

<style>
    .fst-wrapper * { box-sizing: border-box; }

    .fst-wrapper {
        --fst-primary: #6366f1;
        --fst-primary-soft: rgba(99, 102, 241, 0.12);
        --fst-success: #10b981;
        --fst-success-soft: rgba(16, 185, 129, 0.12);
        --fst-border: #e5e7eb;
        --fst-muted: #9ca3af;
        --fst-text: #111827;
        --fst-subtext: #6b7280;
        --fst-card-bg: #ffffff;
        font-family: inherit;
        width: 100%;
    }

    html.dark .fst-wrapper {
        --fst-border: rgba(255, 255, 255, 0.1);
        --fst-muted: #6b7280;
        --fst-text: #f9fafb;
        --fst-subtext: #9ca3af;
        --fst-card-bg: rgba(255, 255, 255, 0.03);
    }

    .fst-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 20px;
    }

    .fst-title {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--fst-text);
    }

    .fst-subtitle {
        margin: 2px 0 0;
        font-size: 12px;
        color: var(--fst-subtext);
    }

    .fst-header-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .fst-courier {
        font-size: 12px;
        font-weight: 600;
        color: var(--fst-text);
    }

    .fst-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: var(--fst-primary-soft);
        color: var(--fst-primary);
    }

    .fst-badge.is-delivered {
        background: var(--fst-success-soft);
        color: var(--fst-success);
    }

    .fst-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--fst-primary);
    }

    .fst-badge.is-delivered .fst-badge-dot {
        background: var(--fst-success);
    }

    .fst-badge:not(.is-delivered) .fst-badge-dot {
        animation: fst-pulse 1.6s infinite;
    }

    .fst-card {
        border: 1px solid var(--fst-border);
        background: var(--fst-card-bg);
        border-radius: 14px;
        padding: 24px 16px 16px;
    }

    .fst-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .fst-track {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        min-width: 640px;
        padding: 0 8px;
    }

    .fst-line-bg,
    .fst-line-progress {
        position: absolute;
        top: 20px;
        left: 0;
        height: 2px;
        border-radius: 2px;
    }

    .fst-line-bg {
        width: 100%;
        background: var(--fst-border);
    }

    .fst-line-progress {
        width: {{ $progressPercent }}%;
        background: {{ $isDelivered ? 'var(--fst-success)' : 'var(--fst-primary)' }};
        transition: width 0.7s ease;
    }

    .fst-step {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
    }

    .fst-circle {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--fst-border);
        background: var(--fst-card-bg);
        color: var(--fst-muted);
        transition: all 0.3s ease;
    }

    .fst-circle svg {
        width: 18px;
        height: 18px;
    }

    .fst-circle.is-completed {
        background: var(--fst-primary);
        border-color: var(--fst-primary);
        color: #fff;
    }

    /* Current step: solid fill (like completed) + extra glow ring so it's
       visually distinguished from both completed and pending steps. */
    .fst-circle.is-current {
        background: var(--fst-primary);
        border-color: var(--fst-primary);
        color: #fff;
        box-shadow: 0 0 0 4px var(--fst-primary-soft);
    }

    .fst-circle.is-current.is-delivered-final {
        background: var(--fst-success);
        border-color: var(--fst-success);
        color: #fff;
        box-shadow: 0 0 0 4px var(--fst-success-soft);
    }

    .fst-label {
        margin-top: 10px;
        font-size: 13px;
        font-weight: 600;
        color: var(--fst-muted);
    }

    .fst-label.is-completed,
    .fst-label.is-current {
        color: var(--fst-text);
    }

    .fst-label.is-current-active {
        color: var(--fst-primary);
    }

    .fst-label.is-current-active.is-delivered-final {
        color: var(--fst-success);
    }

    .fst-desc {
        margin-top: 2px;
        font-size: 11px;
        color: var(--fst-subtext);
    }

    .fst-desc.is-pending {
        color: var(--fst-muted);
    }

    .fst-details {
        margin-top: 16px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        border: 1px solid var(--fst-border);
        background: var(--fst-card-bg);
        border-radius: 14px;
        padding: 16px;
    }

    .fst-detail-label {
        margin: 0;
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--fst-subtext);
    }

    .fst-detail-value {
        margin: 4px 0 0;
        font-size: 13px;
        font-weight: 500;
        color: var(--fst-text);
    }

    @keyframes fst-pulse {
        0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.5); }
        70% { box-shadow: 0 0 0 6px rgba(99, 102, 241, 0); }
        100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    }

    @media (max-width: 640px) {
        .fst-details { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="fst-wrapper">

    {{-- Summary header --}}
    <div class="fst-header">
        <div>
            <p class="fst-title">Shipment Progress</p>
            <p class="fst-subtitle">Track the current stage of this shipment</p>
        </div>

        <div class="fst-header-meta">
            <span class="fst-badge {{ $isDelivered ? 'is-delivered' : '' }}">
                <span class="fst-badge-dot"></span>
                {{ $currentLabel }}
            </span>
            <span class="fst-courier">{{ $courier ?: 'Not Assigned' }}</span>
        </div>
    </div>

    {{-- Timeline card --}}
    <div class="fst-card">
        <div class="fst-scroll">
            <div class="fst-track">
                <div class="fst-line-bg"></div>
                <div class="fst-line-progress"></div>

                @foreach ($steps as $key => $step)
                    @php
                        $index = array_search($key, $statusKeys, true);
                        $isCompleted = $index < $currentIndex;
                        $isCurrent   = $index === $currentIndex;
                        $isPending   = $index > $currentIndex;
                        $isFinalDelivered = $isCurrent && $isDelivered;
                    @endphp

                    <div class="fst-step">
                        <div class="fst-circle
                            {{ $isCompleted ? 'is-completed' : '' }}
                            {{ $isCurrent ? 'is-current' : '' }}
                            {{ $isFinalDelivered ? 'is-delivered-final' : '' }}
                        ">
                            @if ($isCompleted || $isCurrent)
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                            @elseif ($key === 'created')
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.75 2A2.75 2.75 0 0 0 2 4.75v10.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-6.5a.75.75 0 0 0-1.5 0v6.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25V4.75c0-.69.56-1.25 1.25-1.25h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd" /><path d="M13.03 2.47a.75.75 0 0 1 1.06 0l3.44 3.44a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-.53.22H6.5a.75.75 0 0 1-.75-.75v-3.25a.75.75 0 0 1 .22-.53l7.25-7.25Z" /></svg>
                            @elseif ($key === 'processing')
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.34 1.804A1 1 0 0 1 9.32 1h1.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l.681 1.18a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.226l1.267 1.113a1 1 0 0 1 .205 1.251l-.681 1.18a1 1 0 0 1-1.186.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H9.32a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-.681-1.18a1 1 0 0 1 .205-1.251l1.267-1.113a7.05 7.05 0 0 1 0-2.226L2.82 6.909a1 1 0 0 1-.205-1.251l.681-1.18a1 1 0 0 1 1.186-.447l1.598.54A6.993 6.993 0 0 1 8.01 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" /></svg>
                            @elseif ($key === 'hold')
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM7.25 7.5A.75.75 0 0 1 8 6.75h.01a.75.75 0 0 1 .75.75v5a.75.75 0 0 1-.75.75H8a.75.75 0 0 1-.75-.75v-5Zm4 0a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v5a.75.75 0 0 1-.75.75H12a.75.75 0 0 1-.75-.75v-5Z" clip-rule="evenodd" /></svg>
                            @elseif ($key === 'confirmed')
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.28-9.78a.75.75 0 0 0-1.06-1.06L9 10.38 7.78 9.16a.75.75 0 0 0-1.06 1.06l1.75 1.75a.75.75 0 0 0 1.06 0l3.75-3.75Z" clip-rule="evenodd" /></svg>
                            @elseif ($key === 'packed')
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0V4.5H4.5A2.75 2.75 0 0 0 1.75 7.25v7.5A2.75 2.75 0 0 0 4.5 17.5h11a2.75 2.75 0 0 0 2.75-2.75v-7.5A2.75 2.75 0 0 0 15.25 4.5h-4.5V2.75ZM3.25 8h13.5v6.75c0 .69-.56 1.25-1.25 1.25h-11c-.69 0-1.25-.56-1.25-1.25V8Z" /><path d="M3.25 6.25c0-.69.56-1.25 1.25-1.25h11c.69 0 1.25.56 1.25 1.25v.25H3.25v-.25Z" /></svg>
                            @elseif ($key === 'dispatched')
                                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M8.5 4a.5.5 0 0 0-.5.5v6a.5.5 0 0 0 .5.5h6.44a1.5 1.5 0 0 0 1.256-.679l1.06-1.605a1.5 1.5 0 0 0 .244-.82V6.5A1.5 1.5 0 0 0 16 5h-1.5V4.5a.5.5 0 0 0-.5-.5h-5.5Z" /><path fill-rule="evenodd" d="M1.75 4A1.75 1.75 0 0 0 0 5.75v6.5c0 .966.784 1.75 1.75 1.75h.263a2.001 2.001 0 0 0 3.974 0h4.026a2.001 2.001 0 0 0 3.974 0h.263A1.75 1.75 0 0 0 16 12.25v-1.1a1 1 0 0 0-.09-.417l-1.06-2.298A1.5 1.5 0 0 0 13.5 7.5H8.5a1.5 1.5 0 0 1-1.5-1.5v-2A1.75 1.75 0 0 0 5.25 2.25h-3.5A1.75 1.75 0 0 0 0 4v0Z" clip-rule="evenodd" /><path d="M4.5 15.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" /></svg>
                            @else
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                            @endif
                        </div>

                        <p class="fst-label
                            {{ $isCompleted ? 'is-completed' : '' }}
                            {{ $isCurrent ? 'is-current is-current-active' : '' }}
                            {{ $isFinalDelivered ? 'is-delivered-final' : '' }}
                        ">{{ $step['label'] }}</p>

                        <p class="fst-desc {{ $isPending ? 'is-pending' : '' }}">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Optional shipment details --}}
    @if ($hasDetails)
        <div class="fst-details">
            <div>
                <p class="fst-detail-label">Status</p>
                <p class="fst-detail-value">{{ $currentLabel }}</p>
            </div>
            <div>
                <p class="fst-detail-label">Tracking ID</p>
                <p class="fst-detail-value">{{ $trackingId ?: 'Not Available' }}</p>
            </div>
            @if (filled($dispatchDate))
                <div>
                    <p class="fst-detail-label">Dispatch Date</p>
                    <p class="fst-detail-value">{{ $formatDate($dispatchDate) }}</p>
                </div>
            @endif
            <div>
                <p class="fst-detail-label">Remarks</p>
                <p class="fst-detail-value">{{ $remarks ?: 'No remarks' }}</p>
            </div>
        </div>
    @endif

</div>