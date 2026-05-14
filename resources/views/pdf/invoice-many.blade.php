<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จรับเงิน</title>
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .invoice {
            background: white;
            padding: 15px;
            width: 210mm;
            max-width: 210mm;
        }
        .header {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section-container {
            width: 100%;
            margin-top: 8px;
        }
        .table-info {
            width: 100%;
            border-collapse: collapse;
        }
        .table-info td {
            vertical-align: top;
            font-size: 10px;
            padding: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }
        .table th {
            border-bottom: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }
        .table td {
            padding: 1px;
            font-size: 12px;
        }
        .table td:nth-child(1),
        .table th:nth-child(1) {
            text-align: center;
        }
        .table td:nth-child(2) {
            text-align: left;
        }
        .table td:nth-child(3),
        .table th:nth-child(3) {
            text-align: right;
        }
        .total-table {
            width: 100%;
            margin-top: 10px;
        }
        .total-table td {
            padding: 1px;
            font-size: 10px;
        }
        .total-table td:nth-child(1) {
            text-align: left;
        }
        .total-table td:nth-child(2) {
            text-align: left;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        .total-table td:nth-child(3) {
            text-align: right;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        .full-width {
            width: 100%;
            margin-top: 10px;
        }
        .signature {
            font-size: 10px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .signature-line {
            font-size: 10px;
            display: flex;
            justify-content: center;
            gap: 100px;
            margin-top: 10px;
        }
        .note {
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            margin-top: 10px;
        }
        .pdt-5px {
            padding-top: 7px !important;
        }
        .pdb-5px {
            padding-bottom: 7px !important;
        }

        /* ------------ PRINT STYLES ------------- */
        @media print {
            @page {
                size: A5 portrait;
                margin: 10mm;
            }
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
            }
            .invoice {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            .table td, .table th, .total-table td {
                font-size: 9px !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice">

        @foreach ($invoice_many as $invoice)

        <div class="header">ใบแจ้งหนี้ (Invoice)</div>
        <table class="table-info">
            <tr>
                <td>
                    <strong>{{ $setting_bill->company_name }}</strong><br>
                    {{ $setting_bill->address }}<br>
                    โทร. {{ $setting_bill->phone }}<br>
                    <strong>ลูกค้า (Customer)</strong><br>
                    {{ $invoice->room_for_rent->renter->prefix.' '.$invoice->room_for_rent->renter->name.' '.$invoice->room_for_rent->renter->surname }}<br>
                    {{ $invoice->room_for_rent->renter->address.' '.$invoice->room_for_rent->renter->fullThaiAddress() }}<br>
                    เลขประจำตัวผู้เสียภาษี {{ $invoice->room_for_rent->renter->id_card_number }}<br>
                    โทร {{ $invoice->room_for_rent->renter->phone }}
                </td>
                <td style="text-align: right;">
                    <strong>ต้นฉบับ (Original)</strong><br>
                    เลขที่(ID) {{ $invoice->invoice_number }}<br>
                    วันที่(Date) {{ date('d/m/Y',strtotime($invoice->created_at)) }}<br>
                    ห้อง(Room) {{ $invoice->room_for_rent->room->name }}<br>
                    พนักงาน(Staff) {{ $invoice->user->name }}<br>
                </td>
            </tr>
        </table>
        <div class="full-width">
            <table class="table">
                <tr>
                    <th width="1px">ลำดับ(#)</th>
                    <th>รายการชำระ (Description)</th>
                    <th>ราคา (Price)</th>
                </tr>
                @foreach ($invoice->payment_list as $key => $item_payment_list)
                    @php
                        $pd_5px = "";
                        if ($loop->first){
                            $pd_5px = "pdt-5px";
                        }
                        if ($loop->last){
                            $pd_5px .= " pdb-5px";
                        }
                    @endphp
                    <tr>
                        <td class="{{ $pd_5px }}"> {{ $key+1 }} </td>
                        <td class="{{ $pd_5px }}"> {{ $item_payment_list->title }}@if(strpos($item_payment_list->title, 'Water rate') !== false){{ number_format($item_payment_list->unit) }} - {{ $invoice->previous_water_unit ?? 0 }} = {{ $item_payment_list->unit-$invoice->previous_water_unit }} ยูนิต)@endif
                        </td>
                        <td class="{{ $pd_5px }}">{{  ($item_payment_list->discount == 1 ? "- " : '').number_format($item_payment_list->price, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <table class="total-table">
            <tr style="vertical-align: top;">
                <td style="font-size: large;">({{ $invoice->thai_total_amount }})</td>
                <td>จำนวนเงินรวมทั้งหมด <br>(Total amount)</td>
                <td style="font-size: large;">
                    {{ number_format($invoice->total_amount) }} บาท
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td width="150px" style="padding-top: 5px;">
                    <img src="/upload/qr-code/{{ $invoice->room_for_rent->room->floor->building->qr_code }}" alt="" width="70%" >
                </td>
                <td width="80%" style="padding: 0px 40px;">
                    <div class="note">หมายเหตุ(Note)</div>
                    <div class="signature" style="margin: auto 15px;">
                        <div class="signature-line">
                            <span>ลงชื่อ ................................................. ผู้รับเงิน</span>
                        </div>
                        <div class="signature-line">
                            <span>( ................................................. )</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        @if(count($invoice->payment_list) < 4)
            <hr style="border: 1px dashed #404040;margin:10px 0;">
        @else
            <div style="page-break-before: always;"></div>
            <div>&nbsp;</div>
        @endif

        <div class="header">ใบแจ้งหนี้ (Invoice)</div>
        <table class="table-info">
            <tr>
                <td>
                    <strong>{{ $setting_bill->company_name }}</strong><br>
                    {{ $setting_bill->address }}<br>
                    โทร. {{ $setting_bill->phone }}<br>
                    <strong>ลูกค้า (Customer)</strong><br>
                    {{ $invoice->room_for_rent->renter->prefix.' '.$invoice->room_for_rent->renter->name.' '.$invoice->room_for_rent->renter->surname }}<br>
                    {{ $invoice->room_for_rent->renter->address.' '.$invoice->room_for_rent->renter->fullThaiAddress() }}<br>
                    เลขประจำตัวผู้เสียภาษี {{ $invoice->room_for_rent->renter->id_card_number }}<br>
                    โทร {{ $invoice->room_for_rent->renter->phone }}
                </td>
                <td style="text-align: right;">
                    <strong>สำเนา (Copy)</strong><br>
                    เลขที่(ID) {{ $invoice->invoice_number }}<br>
                    วันที่(Date) {{ date('d/m/Y',strtotime($invoice->created_at)) }}<br>
                    ห้อง(Room) {{ $invoice->room_for_rent->room->name }}<br>
                    พนักงาน(Staff) {{ $invoice->user->name }}<br>
                </td>
            </tr>
        </table>
        <div class="full-width">
            <table class="table">
                <tr>
                    <th width="1px">ลำดับ(#)</th>
                    <th>รายการชำระ (Description)</th>
                    <th>ราคา (Price)</th>
                </tr>
                @foreach ($invoice->payment_list as $key => $item_payment_list)
                    @php
                        $pd_5px = "";
                        if ($loop->first){
                            $pd_5px = "pdt-5px";
                        }
                        if ($loop->last){
                            $pd_5px .= " pdb-5px";
                        }
                    @endphp
                    <tr>
                        <td class="{{ $pd_5px }}"> {{ $key+1 }} </td>
                        <td class="{{ $pd_5px }}"> {{ $item_payment_list->title }}@if(strpos($item_payment_list->title, 'Water rate') !== false){{ number_format($item_payment_list->unit) }} - {{ $invoice->previous_water_unit ?? 0 }} = {{ $item_payment_list->unit-$invoice->previous_water_unit }} ยูนิต)@endif
                        </td>
                        <td class="{{ $pd_5px }}">{{ (number_format($item_payment_list->discount) == 1 ? "- " : '').number_format($item_payment_list->price, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <table class="total-table">
            <tr style="vertical-align: top;">
                <td style="font-size: large;">({{ $invoice->thai_total_amount }})</td>
                <td>จำนวนเงินรวมทั้งหมด <br>(Total amount)</td>
                <td style="font-size: large;">
                    {{ number_format($invoice->total_amount) }} บาท
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td width="150px" style="padding-top: 5px;">
                    <img src="/upload/qr-code/{{ $invoice->room_for_rent->room->floor->building->qr_code }}" alt="" width="70%" >
                </td>
                <td width="80%" style="padding: 0px 40px;">
                    <div class="note">หมายเหตุ(Note) </div>
                    <div class="signature" style="margin: auto 15px;">
                        <div class="signature-line">
                            <span>ลงชื่อ ................................................. ผู้รับเงิน</span>
                        </div>
                        <div class="signature-line">
                            <span>( ................................................. )</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        @if (!$loop->last)
            <div style="page-break-before: always;"></div>
            <div>&nbsp;</div>
        @endif
        @endforeach
    </div>
</body>
</html>
