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
        
        </tbody>
    </table>
</body>
</html>