<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Machine QR Code - {{ $serial->serial_number }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .qr-card {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.10);
            overflow: hidden;
        }

        .header {
            background: #111827;
            color: #ffffff;
            padding: 24px 28px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .header p {
            margin: 7px 0 0;
            font-size: 14px;
            color: #d1d5db;
        }

        .machine-info {
            padding: 24px 28px 10px;
        }

        .machine-name {
            margin: 0 0 18px;
            font-size: 22px;
            font-weight: 700;
            color: #111827;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #6b7280;
            font-size: 14px;
        }

        .value {
            color: #111827;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
        }

        .qr-section {
            padding: 20px 28px 30px;
            text-align: center;
        }

        .qr-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
        }

        .qr-wrapper svg {
            display: block;
            width: 280px;
            height: 280px;
        }

        .scan-title {
            margin: 20px 0 6px;
            font-size: 17px;
            font-weight: 700;
            color: #111827;
        }

        .scan-text {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: #6b7280;
        }

        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 14px 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .header {
                padding: 20px;
            }

            .machine-info {
                padding: 20px 20px 5px;
            }

            .qr-section {
                padding: 15px 20px 25px;
            }

            .qr-wrapper svg {
                width: 230px;
                height: 230px;
            }

            .machine-name {
                font-size: 19px;
            }
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .qr-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>

<div class="qr-card">

    <div class="header">
        <h1>Machine Identification</h1>
        <p>Scan QR code to view machine details</p>
    </div>

    <div class="machine-info">

        <h2 class="machine-name">
            {{ $serial->machineryItem->name ?? 'Machine' }}
        </h2>

        <div class="info-row">
            <span class="label">Serial Number</span>
            <span class="value">
                {{ $serial->serial_number }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Serial Code</span>
            <span class="value">
                {{ $serial->serial_code }}
            </span>
        </div>

        <div class="info-row">
            <span class="label">Category</span>
            <span class="value">
                {{ $serial->machineryItem->category->name ?? 'N/A' }}
            </span>
        </div>

    </div>

    <div class="qr-section">

        <div class="qr-wrapper">
            {!! QrCode::size(280)->margin(1)->generate($machineUrl) !!}
        </div>

        <h3 class="scan-title">
            Scan to View Machine
        </h3>

        <p class="scan-text">
            Use your mobile camera to scan this QR code.
            <br>
            Machine details will open automatically.
        </p>

    </div>

    <div class="footer">
        Machine Serial: {{ $serial->serial_number }}
    </div>

</div>

</body>
</html>