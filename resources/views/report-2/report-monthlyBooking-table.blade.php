<table class="datatables-products table dataTable no-footer dtr-column"
    id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info"
    style="width: 1396px;">
    <thead class="border-top">
        <tr class=" table-info">
            <th class="text-center" tabindex="0" style="width: 40px;">
                ห้อง
            </th>
            <th class="text-center">
                ชื่อผู้จอง</th>
            {{-- <th class="text-center" style="width: 123px;">
                หมายเลขการจอง</th> --}}
            <th class="text-center">
                วันที่จอง
            </th>
            <th class="text-center">
                วันที่เข้าพัก</th>
            <th class="text-center">
                ช่องทาง
            </th>
            <th class="text-center">
                รับจองโดย
            </th>
            <th class="text-center">
                ค่ามัดจำ
            </th>
            {{-- <th class="text-center">
                รวม
            </th> --}}
            <th class="text-center">
                สถานะ
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($list_data as $row)
            <tr class="odd">
                
                <td class="text-center">{{ $row->room->name }}</td>
                <td class="text-center"><span class="text-truncate">{{ $row->room_for_rent->renter->fullName() }}</span>
                </td>
                {{-- <td class="text-center"><span>{{ $row->id }}</span></td> --}}
                <td class="text-center"><span>{{  date("d/m/Y" , strtotime($row->room_for_rent->renter->booking_date)) }}</span></td>
                <td class="text-center"><span>{{  date("d/m/Y" , strtotime(@$row->contract->contract_date ?? $row->room_for_rent->start_stay_date)) }}</span></td>
                <td class="text-center"><span> 
                @if (@$row->room_for_rent->rent_bill_reserve)
                    @if (@$row->room_for_rent->rent_bill_reserve->receipt[0]->payment_channel == 1)
                        เงินสด
                    @else
                        โอนเงิน
                    @endif 
                @else
                    -
                @endif
                </span></td>
                <td class="text-center"><span>{{  $row->room_for_rent->user->name }}</span></td>
                <td class="text-center"><span>{{ $row->room_for_rent->deposit }}</span></td>
                {{-- <td class="text-center"><span>{{ $row->amount }}</span></td> --}}
                <td class="text-center">
                    @if ($row->status != 3)
                    <span class="badge bg-label-success"
                        text-capitalized="">จองแล้ว</span>
                        
                    @else
                    <span class="badge bg-label-danger"
                        text-capitalized="">ยกเลิก</span>

                    @endif
                </td>
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
    @include('layout/pagination')
<script>
    // setTimeout(() => {
        // $('#all-booking').html("{{ $list_data->total() }}");
        $('#all-booking').html("{{ $reserve }}");
        $('#all-booking-cancel').html("{{ $cancel }}");
    // }, 2000);
</script>