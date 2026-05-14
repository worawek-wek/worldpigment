<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>
</head>
<style>
    .new_box .col-md-6 {
        padding: 5px 12px;
    }
    .table th {
        font-size: 15px;
        font-weight: bold;
    }
    /* .table td {
        padding-top: 14px;
        padding-bottom: 14px;
    } */
    .modalHeadDecor .modal-header {
        padding: 0;
    }

    .modalHeadDecor .modal-title {
        padding: 1.25rem 0.5rem 1.25rem 1.25rem;
        color: white;
        background-color: #54BAB9;
        position: relative;
    }

    .modalHeadDecor .modal-title::after {
        position: absolute;
        top: 0;
        right: -64px;
        content: '';
        width: 0;
        height: 0;
        border-top: 67px solid #54BAB9;
        border-right: 65px solid transparent;
    }

    #pills-tablayout button {
        background: transparent;
    }

    #pills-tablayout button.active {
        color: #54BAB9 !important;
    }

    .select-floor {
        width: 100px;
    }

    .box {
        display: none;
    }

    @media screen and (min-width:1024px) {
        .col-lg5 {
            width: calc(100%/5);
        }
    }

    @media screen and (max-width:767px) {
        .select-floor {
            width: 100%;
        }
    }
</style>

<link rel="stylesheet" href="assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="assets/vendor/libs/bootstrap-select/bootstrap-select.css" />

<body style="padding: 50px;">
    <h4 align="center">ใบสรุปบิล</h4>
    <table class="datatables-basic table dataTable no-footer dtr-column"
        id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
        <thead class="border-top">
            <tr class=" table-info">
                {{-- <th class="control sorting_disabled dtr-hidden" rowspan="1"
                    colspan="1" style="width: 0px; display: none;"
                    aria-label=""></th> --}}
                {{-- <th class="sorting_disabled dt-checkboxes-cell dt-checkboxes-select-all"
                    rowspan="1" colspan="1" style="width: 18px;"
                    data-col="1" aria-label="">
                    <input id="checkAll" type="checkbox" class="form-check-input"></th> --}}
                <th class="text-center" tabindex="0" style="width: 40px;">
                    ห้อง
                </th>
                <th class="text-center">
                    ผู้เช่า
                </th>
                <th class="text-center">
                    จำนวนเงินรวม
                </th>
                <th class="text-center">
                    สถานะบิล
                </th>
            </tr>
        </thead>
        <tbody>
        @foreach ($list_data as $key => $row_2)
            <tr class="odd" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#invoice" onclick="view({{ $row_2->id }},'table')">
                {{-- <td class="control" tabindex="0" style="display: none;">
                </td> --}}
                {{-- <td class="  dt-checkboxes-cell">
                    {{ $loop->iteration + (($list_data->currentPage() - 1) * $list_data->perPage()) }} --}}
                <td class="text-center">{{ $row_2->room_name }}</td>
                <td class="text-center"><span class="text-truncate">{{ $row_2->prefix.' '.$row_2->renter_name }}</span>
                </td>
                <td class="text-center">
                    <span class="text-truncate">
                        {{-- @if($row_2->total > 0)
                            {{ number_format($row_2->rent+$row_2->electricity_amount+$row_2->water_amount) }}
                        @else
                            {{ number_format($row_2->total) }}
                        @endif --}}
                        {{
                            number_format($row_2->total_amount)
                        }}
                         {{-- // total_amount ไม่ใช่ collomn ใน database แต่มาจาก Function ใน Model payment_list(), getTotalAmountAttribute() --}}
                    </span>
                    @if (count(@$row_2->receipt) > 0 & $row_2->ref_status_id == 7)
                        <br><span class="text-truncate text-success">จ่ายแล้ว {{ $row_2->receipt->pluck('payment_list')->flatten()->sum('price') }}</span>
                        <br><span class="text-truncate text-danger">ค้างจ่าย {{ number_format($row_2->total_amount - $row_2->receipt->pluck('payment_list')->flatten()->sum('price')) }}</span>
                    @endif
                </td>
                <td class="text-center">
                    @if (count(@$row_2->receipt) > 0 & $row_2->ref_status_id == 3)
                        <span class="badge bg-danger py-1" aria-expanded="false" text-capitalized="" style="font-size: unset;">
                        <i class="ti ti-mail ti-md me-2"></i>
                        ค้างชำระ</span>
                    @else
                        <span class="badge bg-{{ $row_2->status->color }} py-1" aria-expanded="false" text-capitalized="" style="font-size: unset;">
                            <i class="{{ $row_2->status->icon }} me-2" style="font-size: 20px;"></i>
                            {{ $row_2->status->name }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>