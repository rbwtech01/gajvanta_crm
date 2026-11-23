<x-filament-panels::page>
    <style>
        .admin-history-search-form {
            display: grid;
            grid-template-columns: minmax(16rem, 1fr) auto;
            align-items: end;
            gap: 1rem;
        }

        .admin-history-search-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #f3f4f6;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .admin-history-pincode-input {
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
        }

        .admin-history-pincode-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgb(34 197 94 / 20%);
        }

        .admin-history-search-form button.fi-btn {
            min-width: 9rem;
            min-height: 3rem;
            justify-content: center;
            color: #fff;
        }

        .admin-history-scroll {
            overflow-x: auto;
            border: 1px solid rgb(255 255 255 / 10%);
            border-radius: 0.75rem;
        }

        .admin-history-table {
            width: 100%;
            min-width: 2240px;
            border-collapse: collapse;
            table-layout: fixed;
            text-align: left;
        }

        .admin-history-table th,
        .admin-history-table td {
            padding: 0.75rem 0.875rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-history-table th {
            background: rgb(255 255 255 / 4%);
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .admin-history-table tbody tr {
            border-top: 1px solid rgb(255 255 255 / 8%);
            color: #e5e7eb;
        }

        .admin-history-table tbody tr:hover {
            background: rgb(255 255 255 / 4%);
        }

        .admin-history-table td.admin-history-address,
        .admin-history-table td.admin-history-hold-remark {
            overflow-wrap: anywhere;
            text-overflow: clip;
            white-space: normal;
        }

        .admin-history-status {
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
            white-space: normal;
        }

        @media (max-width: 640px) {
            .admin-history-search-form {
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-history-search-form button.fi-btn {
                width: 100%;
            }
        }
    </style>

    <x-filament::section>
        <form wire:submit="search" class="admin-history-search-form">
            <div>
                <label for="admin-history-pincode" class="admin-history-search-label">Pincode</label>
                <input
                    id="admin-history-pincode"
                    type="text"
                    inputmode="numeric"
                    autocomplete="postal-code"
                    maxlength="6"
                    wire:model="pincode"
                    class="admin-history-pincode-input"
                    placeholder="Enter 6-digit pincode"
                    aria-describedby="admin-history-pincode-error"
                    aria-invalid="@error('pincode') true @else false @enderror"
                >
                @error('pincode')
                    <p id="admin-history-pincode-error" class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
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
                        There are no machine bookings for pincode {{ $searchedPincode }}.
                    </p>
                </div>
            @else
                <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ $records->total() }} {{ \Illuminate\Support\Str::plural('record', $records->total()) }}
                    for pincode {{ $searchedPincode }}
                </div>

                <div class="admin-history-scroll">
                    <table class="admin-history-table">
                        <colgroup>
                            <col style="width: 60px">
                            <col style="width: 180px">
                            <col style="width: 145px">
                            <col style="width: 165px">
                            <col style="width: 180px">
                            <col style="width: 150px">
                            <col style="width: 150px">
                            <col style="width: 135px">
                            <col style="width: 135px">
                            <col style="width: 140px">
                            <col style="width: 190px">
                            <col style="width: 155px">
                            <col style="width: 145px">
                            <col style="width: 260px">
                            <col style="width: 90px">
                            <col style="width: 125px">
                            <col style="width: 135px">
                            <col style="width: 145px">
                            <col style="width: 220px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Customer Name</th>
                                <th scope="col">Contact Number</th>
                                <th scope="col">Agent</th>
                                <th scope="col">Machine Model</th>
                                <th scope="col">Category</th>
                                <th scope="col">Booking Date</th>
                                <th scope="col">Created Date</th>
                                <th scope="col">Delivery Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Address</th>
                                <th scope="col">Pincode</th>
                                <th scope="col">District</th>
                                <th scope="col">State</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Price</th>
                                <th scope="col">Advance Paid</th>
                                <th scope="col">Bill Number</th>
                                <th scope="col">Hold Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr wire:key="admin-booking-{{ $record->id }}">
                                    <td>{{ $records->firstItem() + $loop->index }}</td>
                                    <td>{{ $record->customer_name ?: '—' }}</td>
                                    <td>
                                        @if (filled($record->mobile))
                                            <a href="tel:{{ $record->mobile }}" class="text-primary-400 hover:underline">{{ $record->mobile }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $record->agent?->name ?? 'Unavailable' }}</td>
                                    <td>{{ $record->machineModel?->name ?? '—' }}</td>
                                    <td>{{ $record->category?->name ?? '—' }}</td>
                                    <td>{{ $record->booking_date ? \Illuminate\Support\Carbon::parse($record->booking_date)->format('d M Y') : '—' }}</td>
                                    <td>{{ $record->created_at?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $record->delivery_date ? \Illuminate\Support\Carbon::parse($record->delivery_date)->format('d M Y') : '—' }}</td>
                                    <td>
                                        <span class="admin-history-status">{{ $record->status ? \Illuminate\Support\Str::of($record->status)->replace(['_', '-'], ' ')->title() : '—' }}</span>
                                    </td>
                                    <td class="admin-history-address">{{ $record->address ?: '—' }}</td>
                                    <td>{{ $record->pincode }}</td>
                                    <td>{{ $record->district ?: '—' }}</td>
                                    <td>{{ $record->state ?: '—' }}</td>
                                    <td>{{ $record->quantity ?? '—' }}</td>
                                    <td>{{ $record->price !== null ? number_format((float) $record->price, 2) : '—' }}</td>
                                    <td>{{ $record->advance_payment !== null ? number_format((float) $record->advance_payment, 2) : '—' }}</td>
                                    <td>{{ $record->bill_number ?: '—' }}</td>
                                    <td class="admin-history-hold-remark">{{ $record->hold_remark ?: '—' }}</td>
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
                <p class="text-base font-medium text-gray-950 dark:text-white">Search machine history by pincode</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Enter a six-digit pincode to view matching machine bookings and customer details.
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
