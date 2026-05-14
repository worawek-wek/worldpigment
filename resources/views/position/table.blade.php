    {{-- {{dd($list_data['to'])}} --}}
    <table class="table table-report -mt-2">
        <thead>
            <tr>
                <th class="text-center whitespace-nowrap">ตำแหน่ง</th>
                @if (Auth::user()->ref_position_id == 1)
                    <th class="text-center whitespace-nowrap">ดำเนินการ</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($list_data as $row)
                <tr class="intro-x">
                    <td align="center">
                        {{ $row['position_name'] }}
                        {{-- <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $row['products'][0]['category'] }}</div> --}}
                    </td>
                    @if (Auth::user()->ref_position_id == 1)
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                <a class="flex items-center mr-3" href="{{$page_url}}/{{$row->id}}/edit">
                                    <i class="fa fa-pen"></i>&nbsp; แก้ไข
                                </a>
                                <a class="flex items-center text-danger" href="javascript:;" onclick='Delete({{$row["id"]}})' data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal">
                                    <i class="fa fa-trash" aria-hidden="true"></i>&nbsp; ลบ
                                </a>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
<!-- END: Data List -->
<!-- BEGIN: Pagination -->
@include('layout/pagination')
