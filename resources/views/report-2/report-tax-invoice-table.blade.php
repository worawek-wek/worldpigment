<base href="{{ url('/') }}/">
<!-- Icons -->

<!-- Core CSS -->
<link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
<link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
<link rel="stylesheet" href="assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="../../assets/vendor/libs/spinkit/spinkit.css" />
                                            <!-- Header -->
<div class="container mt-4 text-dark" style="max-width: 900px; font-size:14px;">
@php
function bahtText($number)
{
    $number = str_replace(",", "", $number);
    $number = number_format($number, 2, ".", "");

    $txtnum1 = ["", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
    $txtnum2 = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];

    $number = explode(".", $number);
    $integer = $number[0];
    $decimal = $number[1];

    $baht = "";
    $len = strlen($integer);

    for ($i = 0; $i < $len; $i++) {
        $n = substr($integer, $i, 1);
        if ($n != 0) {
            if ($i == ($len - 1) && $n == 1 && $len > 1) {
                $baht .= "เอ็ด";
            } elseif ($i == ($len - 2) && $n == 2) {
                $baht .= "ยี่";
            } elseif ($i == ($len - 2) && $n == 1) {
                $baht .= "";
            } else {
                $baht .= $txtnum1[$n];
            }
            $baht .= $txtnum2[$len - $i - 1];
        }
    }

    $baht .= "บาท";

    if ($decimal == "00") {
        $baht .= "ถ้วน";
    } else {
        $satang = "";
        for ($i = 0; $i < 2; $i++) {
            $n = substr($decimal, $i, 1);
            if ($n != 0) {
                if ($i == 1 && $n == 1) {
                    $satang .= "เอ็ด";
                } elseif ($i == 0 && $n == 2) {
                    $satang .= "ยี่";
                } elseif ($i == 0 && $n == 1) {
                    $satang .= "";
                } else {
                    $satang .= $txtnum1[$n];
                }
                $satang .= $txtnum2[2 - $i - 1];
            }
        }
        $baht .= $satang . "สตางค์";
    }

    return $baht;
}
@endphp
    <!-- Header -->
    <div class="d-flex justify-content-between mb-3">
        <div>
            <strong>{{ $branch->name }}</strong><br>
            ที่อยู่ {{ $branch->address.' '.$branch->fullThaiAddress() }}<br>
            เลขประจำตัวผู้เสียภาษี {{ $branch->id_card_number }}
        </div>
        <div class="text-end">
            <strong>ใบเสร็จรับเงิน/ใบกำกับภาษี</strong><br>
            ต้นฉบับ (Original)<br>
            เลขที่ {{ $invoice->invoice_number }}<br>
            วันที่ {{ date('d/m/Y') }}<br>
            ห้อง {{ $invoice->room->name }}
        </div>
    </div>

    <!-- Customer -->
    <div class="mb-3">
        <strong>ลูกค้า (Customer)</strong><br>
        {{ $invoice->room_for_rent->renter->fullName() }}<br>
        ที่อยู่ {{ $invoice->room_for_rent->renter->address.' '.$invoice->room_for_rent->renter->fullThaiAddress() }}<br>
        เลขประจำตัวผู้เสียภาษี {{ $invoice->room_for_rent->renter->id_card_number }}<br>
        โทร {{ $invoice->room_for_rent->renter->phone }}
    </div>

    <!-- Table -->
<table class="table table-bordered text-center align-middle">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th class="text-start">รายการ</th>
            <th>จำนวน</th>
            <th>ราคา</th>
            <th>จำนวนเงิน</th>
            <th>ภาษี</th>
            <th>รวมเงิน</th>
        </tr>
    </thead>
    <tbody>
        @php
            $num = 1;
            $vat_exempt_amount = 0;
            $taxable_amount = 0;
            $vat_amount = 0;
        @endphp
        @foreach ($invoice->payment_list as $payment_list)
        @php
            $price = $payment_list->price;   
            if ($payment_list->discount == 1) {
                $price = 0-$payment_list->price;   
            }
            if ($invoice->occupancy->booking_type == 2) {
                if ($payment_list->id == $invoice->payment_rent_room->id || $payment_list->discount == 1 ){
                    $vat_exempt_amount += $price;
                }else{
                    $taxable_amount += $payment_list->before_vat;
                    $vat_amount += $payment_list->vat;
                }
            }else{
                $taxable_amount += $payment_list->before_vat;
                $vat_amount += $payment_list->vat;
            }
        @endphp
            <tr>
                <td>{{ $num++ }}</td>
                <td class="text-start">{{ $payment_list->title }}</td>
                <td>1</td>
                <td>{{ number_format($price, 2) }}</td>
                @if ($invoice->occupancy->booking_type == 2) 
                    @if ($payment_list->id == $invoice->payment_rent_room->id || $payment_list->discount == 1)
                        <td>{{ number_format($price, 2) }}</td>
                        <td>-</td>
                    @else
                        <td>{{ number_format($payment_list->before_vat, 2) }}</td>
                        <td>{{ number_format($payment_list->vat, 2) }}</td>
                    @endif
                @else
                    <td>{{ number_format($payment_list->before_vat, 2) }}</td>
                    <td>{{ number_format($payment_list->vat, 2) }}</td>
                @endif
                <td>{{ number_format($price, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" rowspan="4" class="text-center" style="vertical-align: bottom;">
                **{{ bahtText($vat_exempt_amount+$taxable_amount+$vat_amount) }}**
            </td>
            <td colspan="3">มูลค่าสินค้าและบริการที่ยกเว้นภาษี</td>
            <td class="text-end">{{ number_format($vat_exempt_amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">มูลค่าสินค้าและบริการรวมภาษี</td>
            <td class="text-end">{{ number_format($taxable_amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">ภาษีมูลค่าเพิ่ม</td>
            <td class="text-end">{{ number_format($vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3">ยอดเงินรวมทั้งสิ้น</td>
            <td class="text-end">{{ number_format($vat_exempt_amount+$taxable_amount+$vat_amount, 2) }}</td>
        </tr>

    </tbody>
</table>

    <!-- Footer -->
    <div class="row mt-4">
        <div class="col-6">
            ผู้รับเงิน ....................................................
        </div>
        <div class="col-6 text-end">
            ลายเซ็นผู้รับมอบอำนาจ ..................................
        </div>
    </div>

</div>
                                            

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script>
        // bahtText({{$vat_exempt_amount+$taxable_amount+$vat_amount}});
        // function bahtText($number)
        // {
        //     $number = str_replace(",", "", $number);
        //     $number = number_format($number, 2, ".", "");

        //     $txtnum1 = ["", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
        //     $txtnum2 = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];

        //     $number = explode(".", $number);
        //     $integer = $number[0];
        //     $decimal = $number[1];

        //     $baht = "";
        //     $len = strlen($integer);

        //     for ($i = 0; $i < $len; $i++) {
        //         $n = substr($integer, $i, 1);
        //         if ($n != 0) {
        //             if ($i == ($len - 1) && $n == 1 && $len > 1) {
        //                 $baht .= "เอ็ด";
        //             } elseif ($i == ($len - 2) && $n == 2) {
        //                 $baht .= "ยี่";
        //             } elseif ($i == ($len - 2) && $n == 1) {
        //                 $baht .= "";
        //             } else {
        //                 $baht .= $txtnum1[$n];
        //             }
        //             $baht .= $txtnum2[$len - $i - 1];
        //         }
        //     }

        //     $baht .= "บาท";

        //     if ($decimal == "00") {
        //         $baht .= "ถ้วน";
        //     } else {
        //         $satang = "";
        //         for ($i = 0; $i < 2; $i++) {
        //             $n = substr($decimal, $i, 1);
        //             if ($n != 0) {
        //                 if ($i == 1 && $n == 1) {
        //                     $satang .= "เอ็ด";
        //                 } elseif ($i == 0 && $n == 2) {
        //                     $satang .= "ยี่";
        //                 } elseif ($i == 0 && $n == 1) {
        //                     $satang .= "";
        //                 } else {
        //                     $satang .= $txtnum1[$n];
        //                 }
        //                 $satang .= $txtnum2[2 - $i - 1];
        //             }
        //         }
        //         $baht .= $satang . "สตางค์";
        //     }

        //     return $baht;
        // }
    </script>