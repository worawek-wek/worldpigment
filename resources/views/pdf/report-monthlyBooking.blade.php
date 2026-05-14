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
            font-size: 8px;
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
            font-weight: bold;
            font-size: 14px;
            padding-bottom: 4px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
<table class="datatables-products table table-bordered dataTable"
       style="width: 100%; max-width: 100%;">
    <thead class="border-top">
        <tr class=" table-info">
            <th class="text-center" tabindex="0" style="width: 40px;">
                ห้อง
            </th>
            <th class="text-center" style="width: 70px;">
                ชื่อผู้จอง</th>
            <th class="text-center" style="width: 70px;">
                หมายเลขการจอง</th>
            <th class="text-center">
                วันที่จอง
            </th>
            <th class="text-center">
                วันที่เข้าพัก</th>
            <th class="text-center">
                ช่องทาง
            </th>
            <th class="text-center" style="width: 70px;">
                รับจองโดย
            </th>
            <th class="text-center">
                ค่ามัดจำ
            </th>
            <th class="text-center">
                รวม
            </th>
            <th class="text-center">
                สถานะ
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($list_data as $row)
            <tr class="odd">
                
                <td class="text-center">{{ $row->room_name }}</td>
                <td class="text-center"><span class="text-truncate">{{ $row->renter_name }}</span>
                </td>
                <td class="text-center"><span>{{ $row->receipt_number }}</span></td>
                <td class="text-center"><span>{{  date("d/m/Y" , strtotime($row->booking_date)) }}</span></td>
                <td class="text-center"><span>{{  date("d/m/Y" , strtotime($row->date_stay)) }}</span></td>
                <td class="text-center"><span> 
                @if ($row->payment_method == 1)
                    เงินสด
                @else
                    โอนเงิน
                @endif 
                </span></td>
                <td class="text-center"><span>{{  $row->user->name }}</span></td>
                <td class="text-center"><span>{{ $row->amount }}</span></td>
                <td class="text-center"><span>{{ $row->amount }}</span></td>
                <td class="text-center"><span class="badge bg-label-success"
                        text-capitalized="">จองแล้ว</span></td>
            </tr>
        
            @empty

                <tr>
                    <td colspan="20" class="text-center text-muted py-4">
                        <i class="ti ti-file-search" style="font-size: 24px;"></i><br>
                        ไม่พบข้อมูล
                    </td>
                </tr>

            @endforelse
        
    </tbody>
</table>
</body>
</html>