<x-filament-panels::page>
    <style>
        .history-search-form {
            display: grid;
            grid-template-columns: minmax(16rem, 1fr) auto;
            align-items: end;
            gap: 1rem;
        }

        .history-search-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #f3f4f6;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .history-pincode-input {
            box-sizing: border-box;
            display: block;
            width: 100%;
            min-height: 3rem;
            border: 1px solid #4b5563;
            border-radius: 0.625rem;
            background: #111827;
            padding: 0.75rem 1rem;
            color: #f9fafb;
            font-size: 1rem;
            letter-spacing: 0.08em;
            outline: none;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .history-pincode-input::placeholder {
            color: #9ca3af;
            letter-spacing: normal;
        }

        .history-pincode-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgb(34 197 94 / 20%);
        }

        .history-search-form button.fi-btn {
            min-width: 9rem;
            min-height: 3rem;
            justify-content: center;
            color: #fff;
        }

        .history-results-scroll {
            overflow-x: auto;
            border: 1px solid rgb(255 255 255 / 10%);
            border-radius: 0.75rem;
        }

        .history-results-table {
            width: 100%;
            min-width: 1040px;
            border-collapse: collapse;
            table-layout: fixed;
            text-align: left;
        }

        .history-results-table th,
        .history-results-table td {
            padding: 0.875rem 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-results-table th:first-child,
        .history-results-table td:first-child {
            width: 60px;
            overflow: visible;
            text-overflow: clip;
        }

        .history-results-table td:nth-child(4) {
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
        }

        .history-results-table thead {
            background: rgb(255 255 255 / 4%);
        }

        .history-results-table th {
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .history-results-table tbody tr {
            border-top: 1px solid rgb(255 255 255 / 8%);
            color: #e5e7eb;
        }

        .history-results-table tbody tr:hover {
            background: rgb(255 255 255 / 4%);
        }

        .history-status {
            box-sizing: border-box;
            display: inline-block;
            max-width: 100%;
            border: 1px solid rgb(255 255 255 / 12%);
            border-radius: 9999px;
            background: rgb(255 255 255 / 6%);
            padding: 0.25rem 0.625rem;
            color: #e5e7eb;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.35;
            overflow-wrap: anywhere;
            text-overflow: clip;
            white-space: normal;
        }

        @media (max-width: 640px) {
            .history-search-form {
                grid-template-columns: minmax(0, 1fr);
            }

            .history-search-form button.fi-btn {
                width: 100%;
            }
        }
    </style>

    <x-filament::section>
        <form wire:submit="search" class="history-search-form">
            <div>
                <label for="pincode" class="history-search-label">
                    Pincode
                </label>
                <input
                    id="pincode"
                    type="text"
                    inputmode="numeric"
                    autocomplete="postal-code"
                    maxlength="6"
                    wire:model="pincode"
                    class="history-pincode-input"
                    placeholder="Enter 6-digit pincode"
                    aria-describedby="pincode-error"
                    aria-invalid="@error('pincode') true @else false @enderror"
                >
                @error('pincode')
                    <p id="pincode-error" class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="search">
                <span wire:loading.remove wire:target="search">Search</span>
                <span wire:loading wire:target="search">Searching...</span>
            </x-filament::button>
        </form>
    </x-filament::section>

    @if ($searchError)
        <x-filament::section>
            <p role="alert" class="py-4 text-sm text-danger-600 dark:text-danger-400">{{ $searchError }}</p>
        </x-filament::section>
    @elseif ($records)
        <x-filament::section>
            @if ($records->isEmpty())
                <div class="py-8 text-center">
                    <p class="text-base font-medium text-gray-950 dark:text-white">No booking history found</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        There are no bookings for pincode {{ $searchedPincode }} that you are authorized to view.
                    </p>
                </div>
            @else
                <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ $records->total() }} {{ \Illuminate\Support\Str::plural('record', $records->total()) }}
                    for pincode {{ $searchedPincode }}
                </div>

                <div class="history-results-scroll">
                    <table class="history-results-table">
                        <colgroup>
                            <col style="width: 60px">
                            <col style="width: 170px">
                            <col style="width: 150px">
                            <col style="width: 170px">
                            <col style="width: 130px">
                            <col style="width: 190px">
                            <col style="width: 170px">
                        </colgroup>
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-medium">#</th>
                                <th scope="col" class="px-4 py-3 font-medium">Agent Name</th>
                                <th scope="col" class="px-4 py-3 font-medium">Booking Date</th>
                                <th scope="col" class="px-4 py-3 font-medium">Status</th>
                                <th scope="col" class="px-4 py-3 font-medium">Pincode</th>
                                <th scope="col" class="px-4 py-3 font-medium">District</th>
                                <th scope="col" class="px-4 py-3 font-medium">State</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($records as $record)
                                <tr wire:key="booking-{{ $record->id }}" class="text-gray-700 dark:text-gray-200">
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ $records->firstItem() + $loop->index }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $record->agent?->name ?? 'Unavailable' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $record->booking_date ? \Illuminate\Support\Carbon::parse($record->booking_date)->format('d M Y') : '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="history-status">{{ $record->status ? \Illuminate\Support\Str::of($record->status)->replace(['_', '-'], ' ')->title() : '—' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $record->pincode }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $record->district ?: '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $record->state ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            @endif
        </x-filament::section>
    @elseif ($searchedPincode === null)
        <x-filament::section>
            <div class="py-8 text-center">
                <p class="text-base font-medium text-gray-950 dark:text-white">Search booking history by pincode</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Enter a six-digit pincode to find matching machine bookings.
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
