<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จรับเงิน</title>
    <style>
        body {
            font-family: "Sarabun", sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 210mm;
            height: 297mm; /* A4 */
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }
        .half {
            flex: 1; /* แบ่งครึ่ง */
            padding: 5mm;
            box-sizing: border-box;
            border-bottom: 1px dashed #000;
        }
        .half:last-child {
            border-bottom: none;
        }
        .header {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
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
            font-size: 14px; /* //p */
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
            font-size: 14px;
        }
        .table td {
            padding: 1px;
            font-size: 14px;
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
            font-size: 14px; /* //p */
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
        .signatures {
            padding: 0 40px;
            display: flex;
            justify-content: space-between; /* ให้ห่างกันซ้าย-ขวา */
            gap: 80px; /* ระยะห่างระหว่างช่องลายเซ็น */
            margin-top: 30px;
        }

        .signature {
            font-size: 14px;
            text-align: center;
        }
        .signature-line {
            margin: 5px 0;
        }
        .note {
            text-align: left;
            font-weight: bold;
            font-size: 14px; /* //p */
            margin-top: 10px;
        }
        .pdt-5px {
            padding-top: 7px !important;
        }
        .pdb-5px {
            padding-bottom: 7px !important;
        }
    </style>
</head>
<body>
    <div class="page">

        <!-- ครึ่งบน: ต้นฉบับ -->
        <div class="half">
            <div class="header">ใบเสร็จรับเงิน (Original)</div>
            @if($receipt->initial_bill == 1)
                <b style="float: right;margin-bottom: 10px;">
                    (แรกเข้า)
                </b>
            @else
                @if ($receipt->occupancy->booking_type == 1 && $receipt->ref_type_id == 1)
                    {{-- <span>&nbsp;</span> --}}
                    <div style="margin-bottom: -10px;">&nbsp;</div>
                @else
                <b style="float: right;margin-bottom: 10px;">
                    @if ($receipt->occupancy->booking_type == 1 && $receipt->ref_type_id == 2)
                        (ค่าประกันห้องพักรายวัน)
                    @else
                        {{ $title_bill[$receipt->initial_bill.$receipt->ref_type_id] ?? '' }}
                    @endif
                </b>
                @endif
            @endif
            <table class="table-info">
                <tr>
                    <td>
                        <strong>{{ $setting_bill->company_name }}</strong><br>
                        {{ $setting_bill->address }}<br>
                        โทร. {{ $setting_bill->phone }}<br>
                        <strong>ลูกค้า (Customer)</strong><br>
                        {{ $receipt->renter->fullName() }}<br>
                        {{ $renter->address.' '.$renter->fullThaiAddress() }}<br>
                        เลขประจำตัวผู้เสียภาษี {{ $receipt->renter->id_card_number }}<br>
                        โทร {{ $receipt->renter->phone }}
                    </td>
                    <td style="text-align: right;">
                        เลขที่(ID) {{ $receipt->receipt_number }}<br>
                        วันที่(Date) {{ date('d/m/Y',strtotime($receipt->created_at)) }}<br>
                        ห้อง(Room) {{ $receipt->room->name }}<br>
                        พนักงาน(Staff) {{ $receipt->user->name }}<br>
                        @if (@$receipt->invoice)
                            เลขที่อ้างอิง(Ref) {{ $receipt->invoice->invoice_number }}
                        @endif
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
                    @foreach ($receipt->payment_list as $key => $item_payment_list)
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
                            <td class="{{ $pd_5px }}"> {{ $item_payment_list->title }}</td>
                            <td class="{{ $pd_5px }}">{{ ($item_payment_list->discount == 1 ? "- " : '').number_format($item_payment_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <table class="total-table">
                <tr style="vertical-align: top;">
                    <td style="font-size: large;">({{ $amount_thai }})</td>
                    <td>จำนวนเงินรวมทั้งหมด <br>(Total amount)</td>
                    <td style="font-size: large;">
                        {{ number_format($receipt->total_amount) }} บาท
                    </td>
                </tr>
            </table>
            <div class="note">หมายเหตุ(Note): {{ $receipt->remark }}</div>
            <table width="100%" style="margin-top:30px;">
                <tr>
                    <td width="50%" align="center" style="vertical-align: top;">
                        ลงชื่อ ............................................ ลูกค้า<br>
                        ( ............................................ )
                    </td>
                    <td width="50%" align="center" style="vertical-align: top;">
                        ลงชื่อ ............................................ ผู้รับเงิน<br>
                        ( ............................................ )
                        <br><br>
                        @if (in_array($receipt->payment_channel, [1,2,3]))
                            <input type="checkbox" {{ $receipt->payment_channel == 1 ? 'checked':''; }}> เงินสด
                            <input type="checkbox" {{ $receipt->payment_channel == 2 ? 'checked':''; }}> โอนเงิน
                            <input type="checkbox" {{ $receipt->payment_channel == 3 ? 'checked':''; }}> บัตรเครดิต
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- ครึ่งล่าง: สำเนา -->
        <div class="half">
            <div class="header">ใบเสร็จรับเงิน (Copy)</div>
                @if($receipt->initial_bill == 1)
                    <b style="float: right;margin-bottom: 10px;">
                        (แรกเข้า)
                    </b>
                @else
                    @if ($receipt->occupancy->booking_type == 1 && $receipt->ref_type_id == 1)
                        {{-- <span>&nbsp;</span> --}}
                        <div style="margin-bottom: -10px;">&nbsp;</div>
                    @else
                    <b style="float: right;margin-bottom: 10px;">
                        @if ($receipt->occupancy->booking_type == 1 && $receipt->ref_type_id == 2)
                            (ค่าประกันห้องพักรายวัน)
                        @else
                            {{ $title_bill[$receipt->initial_bill.$receipt->ref_type_id] ?? '' }}
                        @endif
                    </b>
                    @endif
                @endif
            <table class="table-info">
                <tr>
                    <td>
                        <strong>{{ $setting_bill->company_name }}</strong><br>
                        {{ $setting_bill->address }}<br>
                        โทร. {{ $setting_bill->phone }}<br>
                        <strong>ลูกค้า (Customer)</strong><br>
                        {{ $receipt->renter->fullName() }}<br>
                        {{ $renter->address.' '.$renter->fullThaiAddress() }}<br>
                        เลขประจำตัวผู้เสียภาษี {{ $receipt->renter->id_card_number }}<br>
                        โทร {{ $receipt->renter->phone }}
                    </td>
                    <td style="text-align: right;">
                        เลขที่(ID) {{ $receipt->receipt_number }}<br>
                        วันที่(Date) {{ date('d/m/Y',strtotime($receipt->created_at)) }}<br>
                        ห้อง(Room) {{ $receipt->room->name }}<br>
                        พนักงาน(Staff) {{ $receipt->user->name }}<br>
                        @if (@$receipt->invoice)
                            เลขที่อ้างอิง(Ref) {{ $receipt->invoice->invoice_number }}
                        @endif
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
                    @foreach ($receipt->payment_list as $key => $item_payment_list)
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
                            <td class="{{ $pd_5px }}"> {{ $item_payment_list->title }}</td>
                            <td class="{{ $pd_5px }}">{{ ($item_payment_list->discount == 1 ? "- " : '').number_format($item_payment_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <table class="total-table">
                <tr style="vertical-align: top;">
                    <td style="font-size: large;">({{ $amount_thai }})</td>
                    <td>จำนวนเงินรวมทั้งหมด <br>(Total amount)</td>
                    <td style="font-size: large;">
                        {{ number_format($receipt->total_amount) }} บาท
                    </td>
                </tr>
            </table>
            <div class="note">หมายเหตุ (Note): {{ $receipt->remark }}</div>
                <table width="100%" style="margin-top:30px;">
                    <tr>
                        <td width="50%" align="center" style="vertical-align: top;">
                            ลงชื่อ ............................................ ลูกค้า<br>
                            ( ............................................ )
                        </td>
                        <td width="50%" align="center" style="vertical-align: top;">
                            ลงชื่อ ............................................ ผู้รับเงิน<br>
                            ( ............................................ )
                            <br><br>
                            @if (in_array($receipt->payment_channel, [1,2,3]))
                                <input type="checkbox" {{ $receipt->payment_channel == 1 ? 'checked':''; }}> เงินสด
                                <input type="checkbox" {{ $receipt->payment_channel == 2 ? 'checked':''; }}> โอนเงิน
                                <input type="checkbox" {{ $receipt->payment_channel == 3 ? 'checked':''; }}> บัตรเครดิต
                            @endif
                        </td>
                    </tr>
                </table>
        </div>
    </div>
</body>
</html>
