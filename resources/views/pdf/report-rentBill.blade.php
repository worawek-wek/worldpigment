<style>
    @media print {
        body {
            font-size: 9px;
            margin: 5mm;
        }

        table {
            width: 100% !important;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed; /* คุมความกว้างคอลัมน์ */
        }

        th, td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        /* scale ทั้งหน้ากระดาษ */
        html, body {
            zoom: 0.9; /* ลองปรับ 0.8, 0.85, 0.9 จนกว่าจะพอดี */
        }
    }
</style>
<style>
    .table td {
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
            <th class="text-center">
                ชื่อผู้เช่า
            </th>
            <th class="text-center">
                เลขที่ใบเสร็จ
            </th>
            <th class="text-center">
                วันที่รับชำระ
            </th>
            <th class="text-nowrap text-center">
                ช่องทาง
            </th>
            <th class="text-nowrap text-center">
                รับชำระโดย
            </th>
            {{-- <th class="text-center">
                รวม
            </th> --}}
            <th class="text-center" style="width: 100px;">
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
                อื่น ๆ
            </th>
            <th class="text-nowrap text-center">
                ส่านลด
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
                <td class="sorting_1">{{ $row->room->name }}</td>
                <td><span class="text-truncate">
                    @if (@$row->room->room_for_rent_main->renter)
                    {{ $row->room->room_for_rent_main->renter->fullName(); }}    
                    @endif
                    </span>
                </td>
                <td><span>{{ @$row->receipt_number ?? '-' }}</span></td>
                <td style="padding: 0 22px;"><span>
                    @if ($row->payment_date)
                        {{ date('d/m/Y', strtotime($row->payment_date)) }}
                    @endif
                </span></td>
                <td>
                    @if (in_array($row->ref_status_id,[2,5]))
                        <span>
                        @if ($row->payment_channel == 1)
                            เงินสด
                        @else
                            โอนเงิน
                        @endif    
                        </span>
                    @endif    

                </td>
                <td>
                    <span>
                        @if (in_array($row->ref_status_id,[2,5]))
                            {{ $row->user->name }}
                        @endif    
                    </span>
                </td>
                <td><span>{{ number_format(@$row->payment_rent_room->price ?? 0) }}</span></td>
                <td><span>{{ number_format(@$row->payment_water->price ?? 0) }}</span></td>
                <td><span>{{ number_format(@$row->payment_electricity->price ?? 0) }}</span></td>
                <td><span>{{ number_format(@$row->payment_car_parking_fee->price ?? 0) }}</span></td>
                <td><span>{{ number_format(@$row->payment_motorcycle_parking_fee->price ?? 0) }}</span></td>
                <td><span>{{ number_format(@$row->payment_other_total_amount ?? 0) }}</span></td>
                <td><span>{{ number_format(@$row->payment_discount_total_amount ?? 0) }}</span></td>
                <td><span>{{ number_format($row->total_amount) }}</span></td>
                <td><span class="badge bg-label-{{ $row->status->color }}"
                        text-capitalized="">{{ $row->status->name }}</span></td>
            </tr>
            @empty

                <tr>
                    <td colspan="14" class="text-center text-muted py-4">
                        <i class="ti ti-file-search" style="font-size: 24px;"></i><br>
                        ไม่พบข้อมูล
                    </td>
                </tr>

            @endforelse
    </tbody>
</table>
