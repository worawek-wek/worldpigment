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
            /* margin-top: 10px; */
        }
        .total-table td {
            padding: 7px;
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
        .pt-5 {
            /* padding-top: 4px !important; */
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
        
        {{-- //////// สำเนา --}}
        {{-- //////// สำเนา --}}

        <div class="header">ใบย้ายออก(ผู้เช่าหนี)
            {{-- (ผู้เช่าหนี) --}}
        </div>
        <table class="table-info">
            <tr>
                <td>
                    <strong>{{ $setting_bill->company_name }}</strong><br>
                    {{ $setting_bill->address }}<br>
                    โทร. {{ $setting_bill->phone }}<br>
                    <strong>ลูกค้า (Customer)</strong><br>
                    {{ $invoice_contract->room_for_rent->renter->prefix.' '.$invoice_contract->room_for_rent->renter->name.' '.$invoice_contract->room_for_rent->renter->surname }}<br>
                    {{ $renter->address.' '.$renter->fullThaiAddress() }}<br>
                    เลขประจำตัวผู้เสียภาษี {{ $invoice_contract->room_for_rent->renter->id_card_number }}<br>
                    โทร {{ $invoice_contract->room_for_rent->renter->phone }}
                </td>
                <td style="text-align: right;">
                    <strong>ต้นฉบับ (Original)</strong><br>
                    เลขที่(ID) {{ $invoice_contract->invoice_number }}<br>
                    วันที่(Date) {{ date('d/m/Y',strtotime($invoice_contract->created_at)) }}<br>
                    ห้อง(Room) {{ $invoice_contract->room_for_rent->room->name }}<br>
                    พนักงาน(Staff) {{ $invoice_contract->user->name }}<br>
                </td>
            </tr>
        </table>
            
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

        <div class="full-width">
            @if (@$receipt_rent_room_not_deducted || @$receipt_bad_debt_not_deducted)
            <table class="table">
                <tr>
                    <th width="1px">ลำดับ</th>
                    <th>รายการรับ </th>
                    <th>ราคา (Price)</th>
                </tr>
                <tr>
                    <td style=padding-top:2px;></td>
                </tr>
                @php
                    $num = 1;
                @endphp
                @if (@$receipt_rent_room_not_deducted)
                    @foreach ($receipt_rent_room_not_deducted->payment_list as $key => $rrrd_list)
                        <tr>
                            <td class="pt-5"> {{ $num++ }} </td>
                            <td class="pt-5">{{ $rrrd_list->title }}</td>
                            <td class="pt-5"> {{ number_format($rrrd_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                @if (@$receipt_bad_debt_not_deducted)
                    @foreach ($receipt_bad_debt_not_deducted->payment_list as $key => $rmond_list)
                        <tr>
                            <td class="pt-5"> {{ $num++ }} </td>
                            <td class="pt-5">{{ $rmond_list->title }}</td>
                            <td class="pt-5"> {{ number_format($rmond_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                    <tr>
                        <td style=padding-bottom:3px;></td>
                    </tr>
                    <tr>
                        <td class="pt-5"> </td>
                        <th class="pt-5" align="center">
                            รวมจำนวนเงินรับ
                        </th>
                        <td class="pt-5" width="90px"> {{ number_format(($receipt_bad_debt_not_deducted->total_amount ?? 0.00)+($receipt_rent_room_not_deducted->total_amount ?? 0.00) , 2) }}</td>
                    </tr>
            </table>
        @endif
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

            <table class="table">
                <tr>
                    <th width="1px">ลำดับ</th>
                    <th>รายการคืน/หักเงินประกัน </th>
                    <th>ราคา (Price)</th>
                </tr>
                <tr>
                    <td style=padding-top:2px;></td>
                </tr>
                @php 
                $key_count2 = 1;
                @endphp
                @foreach ($invoice_contract->payment_not_discount as $key2 => $ic_list)
                    <tr>
                        <td class="pt-5"> {{ $key_count2++; }} </td>
                        <td class="pt-5">{{ $ic_list->title }}</td>
                        <td class="pt-5"> {{ number_format($ic_list->price, 2) }}</td>
                    </tr>
                @endforeach

                @if (@$receipt_rent_room_deducted)
                    @foreach ($receipt_rent_room_deducted->payment_list as $key2 => $rmod_list)
                        <tr>
                            <td class="pt-5"> {{ $key_count2++ }} </td>
                            <td class="pt-5"> {{ $rmod_list->title }}
                            </td>
                            <td class="pt-5">{{  ($rmod_list->discount == 1 ? "" : "-").number_format($rmod_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                
                @if (@$receipt_bad_debt_deducted)
                    @foreach ($receipt_bad_debt_deducted->payment_list as $key2 => $rmod_list)
                        <tr>
                            <td class="pt-5"> {{ $key_count2++ }} </td>
                            <td class="pt-5"> {{ $rmod_list->title }}
                            </td>
                            <td class="pt-5">{{  ($rmod_list->discount == 1 ? "" : "-").number_format($rmod_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                    <tr>
                        <th style="padding: 14px 0;"></th>
                        <th style="padding-left: 124px;">รวม</th>
                        <th>{{ number_format($cal, 2) }}</th>
                    </tr>
            </table>
        </div>

{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

        <table class="total-table">
            <tr style="vertical-align: top;">
                <td style="">({{ $amount_thai }})</td>
                @if ($cal > 0)
                    <td>สรุป หอพักได้รับเงินประกัน</td>
                    <td style="">
                        {{ number_format(abs($cal)) }} บาท
                    </td>
                @else
                    <td>สรุป หนี้สูญ</td>
                    <td style="">
                        {{ number_format(abs($cal)) }} บาท
                    </td>
                @endif
            </tr>
        </table>
        <table>
            <tr>
                <td width="150px" style="padding-top: 5px;">
                    <img src="/upload/qr-code/{{ $invoice_contract->room_for_rent->room->floor->building->qr_code }}" alt="" width="70%" >
                </td>
                <td width="80%" style="padding: 0px 40px;">
                    <div class="note">หมายเหตุ(Note): <span style="font-weight: 400;">{{ @$invoice->receipt[0]->remark }}</span></div>
                    <div class="signature" style="margin: auto 15px;">
                        <div class="signature-line">
                            <span>ลงชื่อ ................................................. ผู้รับเงิน</span>
                        </div>
                        {{-- <div class="signature-line">
                            <span>( ................................................. )</span>
                        </div> --}}
                    </div>
                </td>
            </tr>
        </table>

{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

        @if(count($invoice_contract->payment_list) < 6)
            <hr style="border: 1px dashed #404040;margin:10px 0;">
        @else
            <div style="page-break-before: always;"></div>
            <div>&nbsp;</div>
        @endif

        {{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

        {{-- //////// สำเนา --}}
        {{-- //////// สำเนา --}}

        <div class="header">ใบย้ายออก(ผู้เช่าหนี)
            {{-- (ผู้เช่าหนี) --}}
        </div>
        <table class="table-info">
            <tr>
                <td>
                    <strong>{{ $setting_bill->company_name }}</strong><br>
                    {{ $setting_bill->address }}<br>
                    โทร. {{ $setting_bill->phone }}<br>
                    <strong>ลูกค้า (Customer)</strong><br>
                    {{ $invoice_contract->room_for_rent->renter->prefix.' '.$invoice_contract->room_for_rent->renter->name.' '.$invoice_contract->room_for_rent->renter->surname }}<br>
                    {{ $renter->address.' '.$renter->fullThaiAddress() }}<br>
                    เลขประจำตัวผู้เสียภาษี {{ $invoice_contract->room_for_rent->renter->id_card_number }}<br>
                    โทร {{ $invoice_contract->room_for_rent->renter->phone }}
                </td>
                <td style="text-align: right;">
                    <strong>สำเนา (Copy)</strong><br>
                    เลขที่(ID) {{ $invoice_contract->invoice_number }}<br>
                    วันที่(Date) {{ date('d/m/Y',strtotime($invoice_contract->created_at)) }}<br>
                    ห้อง(Room) {{ $invoice_contract->room_for_rent->room->name }}<br>
                    พนักงาน(Staff) {{ $invoice_contract->user->name }}<br>
                </td>
            </tr>
        </table>
            
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
       
        <div class="full-width">
            @if (@$receipt_rent_room_not_deducted || @$receipt_bad_debt_not_deducted)
            <table class="table">
                <tr>
                    <th width="1px">ลำดับ</th>
                    <th>รายการรับ </th>
                    <th>ราคา (Price)</th>
                </tr>
                <tr>
                    <td style=padding-top:2px;></td>
                </tr>
                @php
                    $num = 1;
                @endphp
                @if (@$receipt_rent_room_not_deducted)
                    @foreach ($receipt_rent_room_not_deducted->payment_list as $key => $rrrd_list)
                        <tr>
                            <td class="pt-5"> {{ $num++ }} </td>
                            <td class="pt-5">{{ $rrrd_list->title }}</td>
                            <td class="pt-5"> {{ number_format($rrrd_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                @if (@$receipt_bad_debt_not_deducted)
                    @foreach ($receipt_bad_debt_not_deducted->payment_list as $key => $rmond_list)
                        <tr>
                            <td class="pt-5"> {{ $num++ }} </td>
                            <td class="pt-5">
                                {{ $rmond_list->title }}
                            </td>
                            <td class="pt-5"> {{ number_format($rmond_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                    <tr>
                        <td style=padding-bottom:3px;></td>
                    </tr>
                    <tr>
                        <td class="pt-5"> </td>
                        <th class="pt-5" align="center">
                            รวมจำนวนเงินรับ
                        </th>
                        <td class="pt-5" width="90px"> {{ number_format(($receipt_bad_debt_not_deducted->total_amount ?? 0.00)+($receipt_rent_room_not_deducted->total_amount ?? 0.00) , 2) }}</td>
                    </tr>
            </table>
            @endif
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

            <table class="table">
                <tr>
                    <th width="1px">ลำดับ</th>
                    <th>รายการคืน/หักเงินประกัน </th>
                    <th>ราคา (Price)</th>
                </tr>
                <tr>
                    <td style=padding-top:2px;></td>
                </tr>
                @php 
                $key_count2_copy = 1;
                @endphp
                @foreach ($invoice_contract->payment_not_discount as $key => $icpl)
                    <tr>
                        <td class="pt-5"> {{ $key_count2_copy++ }} </td>
                        <td class="pt-5">
                            {{ $icpl->title }}
                        </td>
                        <td class="pt-5"> {{ number_format($icpl->price, 2) }}</td>
                    </tr>
                @endforeach

                @if (@$receipt_rent_room_deducted)
                    @foreach ($receipt_rent_room_deducted->payment_list as $key2 => $rmod_list)
                        <tr>
                            <td class="pt-5"> {{ $key_count2_copy++ }} </td>
                            <td class="pt-5"> {{ $rmod_list->title }}
                            </td>
                            <td class="pt-5">{{  ($rmod_list->discount == 1 ? "" : "-").number_format($rmod_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                
                @if (@$receipt_bad_debt_deducted)
                    @foreach ($receipt_bad_debt_deducted->payment_list as $key2 => $ip_list)
                        @php
                            $pd_5px = "";
                        @endphp
                        <tr>
                            <td class="pt-5"> {{ $key_count2_copy++ }} </td>
                            {{-- <td class="pt-5"> {{ $key2+count($invoice_contract->payment_not_discount) }} </td> --}}
                            <td class="pt-5"> {{ $ip_list->title }}
                            </td>
                            <td class="pt-5">{{  ($ip_list->discount == 1 ? "" : "-").number_format($ip_list->price, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                    <tr>
                        <th style="padding: 14px 0;"></th>
                        <th style="padding-left: 124px;">รวม</th>
                        <th>{{ number_format($cal, 2) }}</th>
                    </tr>
            </table>
        </div>
            
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}
{{-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// --}}

        <table class="total-table">
            <tr style="vertical-align: top;">
                <td style="font-size: large;">({{ $amount_thai }})</td>
                @if ($cal > 0)
                    <td>สรุป หอพักได้รับเงินประกัน</td>
                    <td style="font-size: large;">
                        {{ number_format(abs($cal)) }} บาท
                    </td>
                @else
                    <td>สรุป หนี้สูญ</td>
                    <td style="font-size: large;">
                        {{ number_format(abs($cal)) }} บาท
                    </td>
                @endif
            </tr>
        </table>
        <table>
            <tr>
                <td width="150px" style="padding-top: -45px;">
                    <img src="/upload/qr-code/{{ $invoice_contract->room_for_rent->room->floor->building->qr_code }}" alt="" width="70%" >
                </td>
                <td width="80%" style="padding: 0px 40px;">
                    <div class="note">หมายเหตุ(Note): <span style="font-weight: 400;">{{ @$invoice->receipt[0]->remark }}</span></div>
                    <div class="signature" style="margin: auto 15px;">
                        <div class="signature-line">
                            <span>ลงชื่อ ................................................. ผู้รับเงิน</span>
                        </div>
                        {{-- <div class="signature-line">
                            <span>( ................................................. )</span>
                        </div> --}}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
