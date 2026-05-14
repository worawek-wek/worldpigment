<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายรับรายจ่าย</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .no-border {
            border: none;
        }

        .summary-table td {
            text-align: left;
            padding-left: 8px;
        }

        .summary-label {
            font-weight: bold;
        }

        .header-title {
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            padding-bottom: 4px;
        }
    </style>
</head>

<body>
    <div class="header-title">{{@$name_branch}}</div>
    <div class="header-title">รายรับ-รายจ่าย เดือน {{ $month_to }}</div>

    <!-- ตารางสรุป -->
    <table class="summary-table" style="margin-bottom: 10px;">
        <tr>
            <td class="summary-label">สรุป</td>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td>รายรับทั้งหมด</td>
            <td colspan="6">{{ number_format($income,2) }}</td>
        </tr>
        <tr>
            <td>รายจ่ายทั้งหมด</td>
            <td colspan="6">{{ number_format($expenses,2) }}</td>
        </tr>
        <tr>
            <td>รวมทั้งหมด</td>
            <td colspan="6">{{ number_format($total,2) }}</td>
        </tr>
    </table>

    <!-- ตารางข้อมูล -->
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 40px;">วันที่</th>
                <th class="text-center" style="width: 100px;">เลขที่ใบเสร็จ</th>
                <th class="text-center">รายละเอียด</th>
                <th class="text-center">ห้อง</th>
                <th class="text-center">หมวดหมู่</th>
                <th class="text-center">จำนวนเงิน</th>
                <th class="text-center">โดย</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list_data as $key => $row)
                <tr style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#insurance"
                    onclick="view({{ $row->id }})">
                    <td class="text-center">{{ date('d/m/Y', strtotime($row->date)) }}</td>
                    <td class="text-center">{{ $row->receipt->receipt_number ?? '-' }}</td>
                    <td class="text-center">{{ $row->label }}</td>
                    <td class="text-center">{{ $row->room->name ?? "รายจ่ายของ Office" }}</td>
                    <td class="text-center">{{ @$row->category->name }}</td>
                    @if ($row->type == 1)
                        <td class="text-center text-success">{{ number_format($row->receipt->total_amount ?? $row->total_amount, 2) }}</td>
                    @else
                        <td class="text-center text-danger">-{{ number_format($row->amount, 2) }}</td>
                    @endif
                    <td class="text-center">{{ $row->user->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
