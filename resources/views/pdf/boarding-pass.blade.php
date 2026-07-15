<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boarding Pass - {{ $transaction->code }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            color: #18181b;
            font-size: 12px;
        }
        .page {
            padding: 20px;
        }
        .boarding-pass {
            border: 1.5px solid #18181b;
            border-radius: 6px;
            margin-bottom: 18px;
            overflow: hidden;
        }
        .bp-header {
            background-color: #18181b;
            color: #ffffff;
            padding: 12px 16px;
        }
        .bp-header table {
            width: 100%;
        }
        .bp-header .airline-name {
            font-size: 16px;
            font-weight: bold;
        }
        .bp-header .boarding-label {
            font-size: 10px;
            letter-spacing: 1px;
            text-align: right;
            opacity: 0.85;
        }
        .bp-body {
            padding: 16px;
        }
        .route-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .route-table td {
            vertical-align: top;
        }
        .city-code {
            font-size: 26px;
            font-weight: bold;
        }
        .city-name {
            font-size: 10px;
            color: #71717a;
        }
        .route-arrow {
            text-align: center;
            font-size: 14px;
            color: #71717a;
            padding-top: 6px;
        }
        .detail-grid {
            width: 100%;
            border-top: 1px dashed #d4d4d8;
            padding-top: 10px;
        }
        .detail-grid td {
            padding: 4px 8px 4px 0;
            font-size: 11px;
        }
        .detail-label {
            color: #71717a;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            font-weight: bold;
            font-size: 12px;
        }
        .passenger-strip {
            background-color: #fafafa;
            border-top: 1px dashed #d4d4d8;
            padding: 10px 16px;
        }
        .footer-note {
            text-align: center;
            color: #a1a1aa;
            font-size: 9px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="page">

        <h3 style="text-align:center; margin-bottom: 18px;">
            Boarding Pass &mdash; {{ $transaction->code }}
        </h3>

        @foreach ($transaction->passengers as $passenger)
            @php
                $segments = $transaction->flight->segments->sortBy('sequence');
                $origin = $segments->first();
                $destination = $segments->last();
            @endphp

            <div class="boarding-pass">
                <div class="bp-header">
                    <table>
                        <tr>
                            <td class="airline-name">
                                {{ $transaction->flight->airline->name ?? '-' }}
                                ({{ $transaction->flight->flight_number }})
                            </td>
                            <td class="boarding-label">BOARDING PASS</td>
                        </tr>
                    </table>
                </div>

                <div class="bp-body">
                    <table class="route-table">
                        <tr>
                            <td width="40%">
                                <div class="city-code">{{ $origin->airport->iata_code ?? '-' }}</div>
                                <div class="city-name">{{ $origin->airport->city ?? '-' }}</div>
                            </td>
                            <td width="20%" class="route-arrow">&#9992;</td>
                            <td width="40%" style="text-align:right;">
                                <div class="city-code">{{ $destination->airport->iata_code ?? '-' }}</div>
                                <div class="city-name">{{ $destination->airport->city ?? '-' }}</div>
                            </td>
                        </tr>
                    </table>

                    <table class="detail-grid">
                        <tr>
                            <td width="25%">
                                <div class="detail-label">Passenger</div>
                                <div class="detail-value">{{ $passenger->name }}</div>
                            </td>
                            <td width="25%">
                                <div class="detail-label">Class</div>
                                <div class="detail-value">{{ ucfirst($transaction->class->class_type ?? '-') }}</div>
                            </td>
                            <td width="25%">
                                <div class="detail-label">Seat</div>
                                <div class="detail-value">{{ $passenger->seat->name ?? '-' }}</div>
                            </td>
                            <td width="25%">
                                <div class="detail-label">Departure</div>
                                <div class="detail-value">
                                    {{ $origin?->time ? \Carbon\Carbon::parse($origin->time)->format('d M Y, H:i') : '-' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="passenger-strip">
                    <table style="width:100%;">
                        <tr>
                            <td>
                                <div class="detail-label">Booking Code</div>
                                <div class="detail-value">{{ $transaction->code }}</div>
                            </td>
                            <td>
                                <div class="detail-label">Date of Birth</div>
                                <div class="detail-value">
                                    {{ $passenger->date_of_birth ? \Carbon\Carbon::parse($passenger->date_of_birth)->format('d M Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="detail-label">Nationality</div>
                                <div class="detail-value">{{ $passenger->nationality ?? '-' }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="footer-note">
            Please arrive at the airport at least 2 hours before departure &mdash; Garuda
        </div>

    </div>
</body>
</html>