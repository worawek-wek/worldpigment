<style>
.table th, td {
    padding: 14px;
    border: 1px solid rgb(195, 195, 195); border-collapse: collapse;
}
table {
    border: 0.1px solid rgb(149, 149, 149);
    border-collapse: collapse;
    font-size: 12px;
}
thead {
    background-color: #b0f6ff25;
}
</style>

<table class="datatables-basic table dataTable no-footer dtr-column" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info" width="100%">
    <thead class="border-top">
        <tr class="table-info">
            <th class="text-center" tabindex="0" style="width: 40px;">
                ลำดับ
            </th>
            <th class="text-center">
                เลขบัตรประชาชน
            </th>
            <th class="text-center">
                ชื่อ-สกุล
            </th>
            <th class="text-center">
                ที่อยู่
            </th>
            <th class="text-center">
                เบอร์โทรศัพท์
            </th>
            <th class="text-center">
                ผู้พาลงทะเบียน
            </th>
            <th class="text-center">
                วันที่
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($list_data as $key => $row)
        <tr class="odd" style="@if(@$row->royal()->id) background-color: #c7ffc2; @endif">
            <td class="text-center" align="center">
                {{ $key+1 }}
            </td>
            <td class="text-center">
                {{ $row->id_card_number }}
            </td>
            <td class="text-center">
                {{ $row->name }} {{ $row->surname }}
            </td>
            <td class="" style="font-size: small;">
                {{ $row->address }} {{ !empty($row->village)? "ม.".$row->village:""; }}
                         {{ @$row->subdistrict->name_in_thai }} {{ @$row->district->name_in_thai }} {{ @$row->province->name_in_thai }} {{ @$row->subdistrict->zip_code }}
            </td>
            <td class="text-center">{{ $row->phone }}
            </td>
            <td class="text-center">{{ $row->user->name }}
            </td>
            <td class="text-center">
                {{ date('d/m/Y', strtotime($row->created_at)) }} <br> {{ date('H:i', strtotime($row->created_at)) }} น.
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@include('layout/inc_js')