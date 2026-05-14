<style>
    .table td {
        text-align: center;
    }
</style>
<table class="datatables-products table dataTable no-footer dtr-column"
    id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info"
    style="width: 1396px;">
    <thead class="border-top">
        <tr class=" table-info">
            <th class="text-center" style="padding: 0 50px;">
                ห้อง
            </th>
            <th class="text-center">
                รอบบิล
            </th>
            <th class="text-center">
                ค่าเช่าห้อง</th>
            <th class="text-center">
                ค่าน้ำ
            </th>
            <th class="text-center">
                ค่าไฟ</th>
            <th class="text-center">
                ชำระแล้ว
            </th>
            <th class="text-center">
                คืนเงินประกัน
            </th>
            <th class="text-center">
                แจ้งหนี้โดย
            </th>
            <th class="text-center">
                วันที่
            </th>
            {{-- <th class="text-center">
                รวมหนี้สูญ
            </th> --}}
        <tr>
    <thead>
    <tbody>
        @forelse ($list_data as $row)
            <tr class="odd">
                <td class="sorting_1">{{ $row->room_name }}</td>
                <td><span>{{ $row->month.'/'.$row->year }}</span></td>
                <td><span>{{ $row->rent }}</span></td>
                <td><span>{{ $row->payment_water->price }}</span></td>
                <td><span>{{ $row->payment_electricity->price }}</span></td>
                <td><span>0</span></td>
                <td><span>0</span></td>
                <td><span>{{ $row->user->name }}</span></td>
                <td><span>{{ date('d/m/Y',strtotime($row->created_at)) }}</span></td>
                {{-- <td>
                    <div class="d-inline-block text-nowrap">
                        <button
                            class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light"
                            data-bs-toggle="modal"
                            data-bs-target="#viewModal"><i
                                class="ti ti-eye ti-md"></i></button>
                    </div>
                </td> --}}
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