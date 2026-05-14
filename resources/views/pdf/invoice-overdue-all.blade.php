<style>
    @page {
        margin: 20px;
    }

    body {
        font-family: "TH Sarabun New", sans-serif;
        font-size: 14px;
        color: #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        display: table-header-group;
    }

    tr {
        page-break-inside: avoid;
    }

    th, td {
        border: 1px solid #000;
        padding: 6px;
    }

    th {
        text-align: center;
        font-weight: bold;
        background: #eaeaea;
    }

    td {
        vertical-align: middle;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-danger {
        color: #000; /* PDF ไม่ควรใช้สี */
        font-weight: bold;
    }

    .no-print {
        display: none;
    }
</style>
@php
    $thaiMonths = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];
@endphp
<div style="margin: 20px">
    <h3 align="center">ค้างชำระ</h3>
    <table>
        <thead>
            <tr>
                <th style="width:50px;">ลำดับ</th>
                <th>ชื่อ</th>
                <th>ห้อง</th>
                <th>ค้างชำระ</th>
                <th>จำนวนเงินรวม</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list_data as $key => $row)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>

                <td class="text-center">
                    {{ $row->room_for_rent?->renter?->fullName() ?? '-' }}
                </td>

                <td class="text-center">
                    {{ $row->name }}
                </td>

                <td class="text-right">
                    {{ number_format($row->total_overdue, 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($row->total_overdue, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>