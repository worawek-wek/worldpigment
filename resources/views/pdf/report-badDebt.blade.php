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
<table class="datatables-products table dataTable no-footer dtr-column"
    id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
    <thead class="border-top">
        <tr class="text-center table-info">
            <th class="control  dtr-hidden" rowspan="1"
                colspan="1" style="width: 0px; display: none;"
                aria-label=""></th>
            <th class="text-center">
                ห้อง
            </th>
            <th class="text-center" style="width: 75px;">
                ชื่อผู้เช่า
            </th>
            <th class="text-center" style="width: 75px;">
                เลขที่ใบเสร็จ
            </th>
            <th class="text-center" style="width: 60px;">
                วันที่รับชำระ
            </th>
            <th class="text-nowrap text-center" style="width: 35px;">
                ช่องทาง
            </th>
            <th class="text-nowrap text-center" style="width: 65px;">
                รับชำระโดย
            </th>
            {{-- <th class="text-center">
                รวม
            </th> --}}
            <th class="text-center" style="width: 35px;">
                ค่าห้องเช่า
            </th>
            <th class="text-center">
                ค่าน้ำ
            </th>
            <th class="text-center">
                ค่าไฟ
            </th>
            <th class="text-center">
                ค่าที่จอด <br> รถยนต์
            </th>
            <th class="text-center">
                ค่าที่จอด <br> รถมอเตอร์ไซค์
            </th>
            <th class="text-nowrap text-center">
                ส่วนกลาง
            </th>
            <th class="text-center">
                รวม
            </th>
            <th class="text-center">
                สถานะ
            </th>
            <!-- <th class="sorting_disabled" rowspan="1" colspan="1"
                style="width: 87px;" aria-label="Actions">จัดการ</th> -->
        </tr>
    </thead>
    <tbody>
        @forelse ($list_data as $row)
            <tr class="even table-success" align="center">
                <td class="control" tabindex="0" style="display: none;">
                </td>
                <td class="sorting_1">{{ $row->room_name }}</td>
                <td><span class="text-truncate">{{ $row->renter_name }}</span>
                </td>
                <td><span>{{ $row->receipt_number }}</span></td>
                <td><span>{{ date('d/m/Y', strtotime($row->payment_date)) }}</span></td>
                <td><span>
                    @if ($row->payment_method == 1)
                        เงินสด
                    @else
                        โอนเงิน
                    @endif     
                </span></td>
                <td><span>{{ $row->user->name }}</span></td>
                {{-- <td><span>{{ $row->rent }}</span></td> --}}
                <td><span>{{ $row->water_amount }}</span></td>
                <td><span>{{ $row->electricity_amount }}</span></td>
                <td><span> - </span></td>
                <td><span> - </span></td>
                <td><span> - </span></td>
                <td><span> - </span></td>
                <td><span>{{ number_format($row->total_amount) }}</span></td>
                <td><span class="badge bg-label-success"
                        text-capitalized="">ชำระแล้ว</span></td>
            </tr>
            @empty

                <tr>
                    <td colspan="14" class="text-center text-muted py-4" style="padding-bottom:15px;">
                        <i class="ti ti-file-search" style="font-size: 24px;"></i><br>
                        ไม่พบข้อมูล
                    </td>
                </tr>

            @endforelse
    </tbody>
</table>