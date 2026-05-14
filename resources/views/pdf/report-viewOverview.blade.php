<style>
    @media print {
        body {
            font-size: 10px;
            margin: 10mm;
        }

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        th, td {
            font-size: 10px;
            word-wrap: break-word;
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .text-truncate {
            white-space: normal !important;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    }
       
        .text-center {
            text-align: center;
        }
    </style>
<table
    class="datatables-products table table-bordered dataTable no-footer dtr-column"
    id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info"
    style="width: 100%; max-width: 100%;">
    <thead class="border-top table-info">
        <tr class="text-nowrap ">
            <th class="control sorting_disabled dtr-hidden" rowspan="2"
                style="width: 0px; display: none;" aria-label=""></th>
            <th class="text-center" rowspan="2">ห้อง</th>
            <th class="text-center" colspan="7">บิลค่าเช่าห้อง</th>
            <th class="text-center" colspan="2">บิลจองห้อง</th>
            <th class="text-center" colspan="2">บิลเงินประกัน</th>
            <th class="text-center" colspan="2">บิลย้ายออก</th>
            <th class="text-center" rowspan="2">คืนเงินประกัน</th>
        </tr>
        <tr class="text-nowrap">
            <th>ค่าเช่าห้อง</th>
            <th>ค่าน้ำ</th>
            <th>ค่าไฟ</th>
            <th>ค่าที่จอดรถยนต์</th>
            <th>ค่าที่จอดรถมอเตอร์ไซค์</th>
            <th>ส่วนกลาง</th>
            <th>ค่าไฟเกิน</th>
            <th>ค่ามัดจำการจอง</th>
            <th>คืนมัดจำการจอง</th>
            <th>ค่าประกันห้อง</th>
            <th>หักค่ามัดจำจอง</th>
            <th>ค่าน้ำ</th>
            <th>ค่าไฟ</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($list_data as $row)
            <tr class="odd text-center">
                <td class="  control" tabindex="0" style="display: none;">
                </td>
                <td class="sorting_1">{{ $row->room_name }}</td>
                <td><span class="text-truncate">{{ number_format($row->invoice->payment_rent_room->price ?? 0) }}</span>
                </td>
                <td><span>{{ number_format($row->water_amount) }}</span></td>
                <td><span>{{ number_format($row->electricity_amount) }}</span></td>
                <td><span>{{ number_format($row->invoice->payment_car_parking_fee->price ?? 0) }}</span></td>
                <td><span>{{ number_format($row->invoice->payment_motorcycle_parking_fee->price ?? 0) }}</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
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