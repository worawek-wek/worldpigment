    {{-- {{dd($list_data['to'])}} --}}
    <table class="datatables-basic table dataTable no-footer dtr-column"
            id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
            <thead class="border-top">
                <tr class=" table-info">
                    <th class="text-center" tabindex="0" style="width: 40px;">
                        ลำดับ
                    </th>
                    <th class="text-center">
                        ชื่ออุปกรณ์
                    </th>
                    <th class="text-center">
                        หมวดหมู่
                    </th>
                    <th class="text-center">
                        รายละเอียด
                    </th>
                    <th class="text-center">
                        ดำเนินการ
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($list_data as $key => $row)
                    <tr class="odd">
                        <td class="text-center" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#editModal" onclick="view({{ $row->id }})">
                            {{ $loop->iteration + (($list_data->currentPage() - 1) * $list_data->perPage()) }}
                        </td>
                        <td class="text-center" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#editModal" onclick="view({{ $row->id }})">
                            {{ $row->name }}
                        </td>
                        <td class="text-center" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#editModal" onclick="view({{ $row->id }})">
                            {{ @$row->category->name }}
                        </td>
                        <td class="text-center" style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#editModal" onclick="view({{ $row->id }})">
                            {{ $row->detail }}
                        </td>
                        <td class="text-center">
                            {{-- @if(Auth::user()->user_has_branch->position->id == 1) --}}
                            <a href="javascript:void(0);" class="card-reload text-danger me-2" onclick='Delete({{$row->id}})'><i class="tf-icons ti ti-trash ti-sm"></i></a>
                            <button type="button" class="btn btn-warning waves-effect waves-light" onclick="getHistory({{ $row->id }})" data-bs-toggle="modal" data-bs-target="#history">ประวัติการเบิก</button>
                            {{-- @endif --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
<!-- END: Data List -->
<!-- BEGIN: Pagination -->
@include('layout/pagination')

