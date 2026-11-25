{{-- โหมด "ตาราง DB" (25/11/2569 ตามที่ผู้ใช้สั่ง) — แถวดิบของ qmast ทุกคอลัมน์ ตามที่เก็บใน DB
     ใช้ตัวกรอง/การเรียง/แบ่งหน้าเดียวกับตารางปกติ (QuotationController::datatable, view_mode=db) --}}
<div class="table-responsive">
    <table class="table table-sm table-bordered table-hover align-middle mb-0 q-db-table">
        <thead class="table-light">
            <tr>
                <th class="text-center text-muted">#</th>
                @foreach ($db_columns as $col)
                    <th class="text-nowrap">{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($list_data as $key => $row)
                @php $attrs = $row->getAttributes(); @endphp
                <tr>
                    <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>
                    @foreach ($db_columns as $col)
                        @php $v = $attrs[$col] ?? null; @endphp
                        {{-- ค่าอยู่บรรทัดเดียวกับ <td> — ช่องใช้ pre-wrap (คงขึ้นบรรทัดในข้อมูล) เว้นวรรคของโค้ดจะโผล่ --}}
                        <td>@if ($v === null)<span class="text-muted fst-italic">NULL</span>@else{{ $v }}@endif</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($db_columns) + 1 }}" class="text-center py-5 text-muted">
                        <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                        ไม่พบข้อมูลที่ตรงกับเงื่อนไข
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="cm-pagination-wrap mt-4 mb-3 px-3">
    @include('layout/pagination')
</div>

<style>
    .cm-pagination-wrap .pagination { justify-content: flex-end; margin-bottom: 0; }
    .q-db-table { font-size: .8rem; }
    .q-db-table th, .q-db-table td { padding: .3rem .5rem; }
    .q-db-table td { white-space: pre-wrap; min-width: 80px; max-width: 320px; vertical-align: top; }
</style>
