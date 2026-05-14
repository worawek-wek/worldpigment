    {{-- {{dd($list_data['to'])}} --}}
    <table class="datatables-basic table dataTable no-footer dtr-column"
            id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
            <thead class="border-top">
                <tr class=" table-info">
                    <th class="text-center" tabindex="0" style="width: 40px;">
                        ลำดับ
                    </th>
                    <th class="text-center">
                        รายการ
                    </th>
                    <th class="text-center">
                        จำนวนที่เบิก
                    </th>
                    <th class="text-center">
                        วันที่
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipments->notificate as $key => $row)
                    <tr class="odd">
                        <td class="text-center">
                            {{ $key+1 }}
                        </td>
                        <td class="text-center">
                            {{ $row->title }}
                        </td>
                        <td class="text-center">
                            {{ $row->qty }}
                        </td>
                        <td class="text-center">
                            {{ date('d/m/Y', strtotime($row->created_at)) }}
                        </td>
                @endforeach
            </tbody>
        </table>
<!-- END: Data List -->
<!-- BEGIN: Pagination -->

