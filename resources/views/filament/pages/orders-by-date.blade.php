<x-filament-panels::page>

    <style>
        .receipt-wrapper {
            width: 100%;
            max-width: 760px;
            margin: 20px auto;
            padding: 10px;
        }

        .total-box {
            display: flex;
            width: 380px;
            margin: 0 auto 25px;
            border: 1px solid #ccc;
        }

        .total-left {
            width: 50%;
            background: #555;
            color: #fff;
            text-align: center;
            padding: 14px;
            font-size: 22px;
            font-weight: 600;
        }

        .total-right {
            width: 50%;
            background: #fff;
            color: #a32020;
            text-align: center;
            padding: 14px;
            font-size: 34px;
            font-weight: bold;
        }

        .card {
            width: 680px;
            margin: 0 auto 18px;
            background: #fff;
            margin-bottom: 18px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
        }

        .card-body {
            padding: 24px 30px;
        }

        .row {
            display: flex;
            margin-bottom: 12px;
        }

        .label {
            width: 180px;
            color: #777;
            font-size: 18px;
        }

        .value {
            font-size: 18px;
            font-weight: 600;
            color: #111;
        }

        .footer {
            display: flex;
        }

        .amount {
            width: 50%;
            background: #b52323;
            color: #fff;
            text-align: center;
            padding: 14px;
            font-size: 24px;
            font-weight: bold;
        }

        .receipt-btn {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            color: #b52323;
            text-decoration: none;
            font-size: 24px;
            font-weight: bold;
            border-left: 1px solid #ddd;
        }

        .receipt-btn:hover {
            background: #f8f8f8;
        }

        .receipt-btn {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;

            background: #fff;
            color: #b52323;

            text-decoration: none;
            font-size: 18px;
            font-weight: 700;

            border-left: 1px solid #ddd;
            cursor: pointer;
            transition: all .2s ease;
        }

        .receipt-btn:hover {
            background: #b52323;
            color: #fff;
        }

        .receipt-icon {
            width: 22px;
            height: 22px;
        }
    </style>

    <div class="receipt-wrapper">

        <div class="max-w-4xl mx-auto mb-4">
            <a
                href="javascript:history.back()"
                class="fi-btn fi-btn-size-md fi-btn-color-gray inline-flex items-center gap-2"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
                <span>Back</span>
            </a>
        </div>

        <div class="total-box">

            <div class="total-left">
                Total Orders
            </div>

            <div class="total-right">
                {{ $this->getOrders()->count() }}
            </div>

        </div>

        @foreach ($this->getOrders() as $order)
            <div class="card">

                <div class="card-body">

                    <div class="row">
                        <div class="label">Receipt No.</div>
                        <div class="value">{{ $order->id }}</div>
                    </div>

                    <div class="row">
                        <div class="label">Date of Receipt</div>
                        <div class="value">
                            {{ \Carbon\Carbon::parse($order->booking_date)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="label">Mode</div>
                        <div class="value">
                            Online Payment
                        </div>
                    </div>

                </div>

                <div class="footer">

                    <div class="amount">
                        ₹ {{ number_format($order->price, 2) }}
                    </div>

                    <a href="{{ route('invoice.download', $order) }}" class="receipt-btn">
                        <x-heroicon-o-arrow-down-tray class="receipt-icon" />
                        <span>INVOICE</span>
                    </a>

                </div>

            </div>
        @endforeach

    </div>

</x-filament-panels::page>
