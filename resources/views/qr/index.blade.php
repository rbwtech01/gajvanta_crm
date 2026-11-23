<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $machineryItem->name }} - Machine Details
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            color: #111827;
        }

        .page {
            min-height: 100vh;
            padding: 30px 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .container {
            width: 100%;
            max-width: 650px;
        }

        /* Header */

        .top-header {
            background: #111827;
            color: white;
            border-radius: 18px 18px 0 0;
            padding: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
            color: #d1d5db;
            letter-spacing: 0.4px;
        }

        .brand-text p {
            margin: 5px 0 0;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }

        /* Main Card */

        .card {
            background: #ffffff;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .status-section {
            padding: 22px 28px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .section-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #111827;
        }

        .section-subtitle {
            margin: 5px 0 0;
            font-size: 13px;
            color: #6b7280;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
        }

        /* Information */

        .details {
            padding: 8px 28px 18px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            padding: 18px 0;
            border-bottom: 1px solid #f0f1f3;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 13px;
            color: #6b7280;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            text-align: right;
            word-break: break-word;
        }

        .serial-value {
            font-family: monospace;
            letter-spacing: 0.4px;
        }

        /* Footer */

        .footer {
            padding: 18px 28px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .footer p {
            margin: 0;
            font-size: 12px;
            color: #9ca3af;
        }

        /* Mobile */

        @media (max-width: 520px) {

            .page {
                padding: 15px;
            }

            .top-header {
                padding: 22px 20px;
            }

            .brand-icon {
                width: 42px;
                height: 42px;
                font-size: 20px;
            }

            .brand-text p {
                font-size: 19px;
            }

            .status-section {
                padding: 18px 20px;
                align-items: flex-start;
            }

            .details {
                padding: 5px 20px 15px;
            }

            .detail-row {
                padding: 16px 0;
            }

            .footer {
                padding: 16px 20px;
            }
        }
    </style>
</head>

<body>

<div class="page">

    <div class="container">

        <!-- Header -->

        <div class="top-header">

            <div class="brand">

                <div class="brand-icon">
                    ⚙
                </div>

                <div class="brand-text">

                    <h1>Machine Information</h1>

                    <p>
                        {{ $machineryItem->name }}
                    </p>

                </div>

            </div>

        </div>


        <!-- Details Card -->

        <div class="card">

            <div class="status-section">

                <div>

                    <h2 class="section-title">
                        Machine Details
                    </h2>

                    <p class="section-subtitle">
                        Registered machine information
                    </p>

                </div>

                <div class="status">

                    <span class="status-dot"></span>

                    Active

                </div>

            </div>


            <div class="details">

                <!-- Serial Number -->

                <div class="detail-row">

                    <span class="detail-label">
                        Serial Number
                    </span>

                    <span class="detail-value serial-value">
                        {{ $serial->serial_number }}
                    </span>

                </div>


                <!-- Serial Code -->

                <div class="detail-row">

                    <span class="detail-label">
                        Serial Code
                    </span>

                    <span class="detail-value serial-value">
                        {{ $serial->serial_code }}
                    </span>

                </div>


                <!-- Category -->

                <div class="detail-row">

                    <span class="detail-label">
                        Category
                    </span>

                    <span class="detail-value">
                        {{ $machineryItem->category->name ?? 'N/A' }}
                    </span>

                </div>

            </div>


            <!-- Footer -->

            <div class="footer">

                <p>
                    Machine identification verified via QR code
                </p>

            </div>

        </div>

    </div>

</div>

</body>

</html>