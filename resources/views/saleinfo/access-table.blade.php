{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ข้อมูลดิบจากไฟล์ Access (ตาราง access_compo / access_pdprice /       --}}
{{-- access_testmai บน MySQL) — อ่านอย่างเดียว ไม่มีปุ่มแก้ไข/ลบ           --}}
{{-- โหลดผ่าน AJAX จาก saleinfo/access-data?tab=...                       --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}

@php
    // ตัวเลขในไฟล์ Access เป็น Currency (ทศนิยม 4 ตำแหน่ง) — โชว์ 2 ตำแหน่งพอ ค่าว่างขีดแทน
    $num = fn ($v, $dec = 2) => ($v === null || $v === '') ? '—' : number_format((float) $v, $dec);
    // ข้อความว่าง → ขีด (ภาษาไทยจากไฟล์ Access เป็น '?' อยู่แล้วตั้งแต่ต้นทาง)
    $txt = fn ($v) => ($v === null || trim((string) $v) === '') ? '—' : $v;
    // ธง '0'/'1' → ป้ายสี
    $flag = fn ($v) => (string) $v === '1'
        ? '<span class="badge bg-label-success">ใช่</span>'
        : '<span class="text-muted">—</span>';
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle">

        @if ($tab === 'compo')
            {{-- ── Compo: สูตรส่วนผสม (PdCode = สูตร, PdCodes = ตัวที่ใส่เข้าไป) ── --}}
            <thead class="pr-thead-dark">
                <tr class="align-middle">
                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                    <th>รหัสสูตร<br><small class="fw-normal">PdCode</small></th>
                    <th>รหัสส่วนผสม<br><small class="fw-normal">PdCodes</small></th>
                    <th class="text-end" style="width: 110px;">% ส่วนผสม<br><small class="fw-normal">Compp</small></th>
                    <th class="text-end" style="width: 120px;">ต้นทุน<br><small class="fw-normal">CNet</small></th>
                    <th style="width: 150px;">เลขที่เทียบสี<br><small class="fw-normal">TestNo</small></th>
                    <th style="width: 150px;">เลขที่แก้ไข<br><small class="fw-normal">ChangeNo</small></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $key => $row)
                    <tr>
                        <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>
                        <td><strong class="text-primary">{{ $txt($row->PdCode) }}</strong></td>
                        <td>{{ $txt($row->PdCodes) }}</td>
                        <td class="text-end">{{ $num($row->Compp) }}</td>
                        <td class="text-end">{{ $num($row->CNet) }}</td>
                        <td><small class="text-muted">{{ $txt($row->TestNo) }}</small></td>
                        <td><small class="text-muted">{{ $txt($row->ChangeNo) }}</small></td>
                    </tr>
                @empty
                    @include('saleinfo.access-empty', ['cols' => 7])
                @endforelse
            </tbody>

        @elseif ($tab === 'pdprice')
            {{-- ── PdPrice: ราคาทุนต่อรหัสสินค้า (ตัวตั้งต้นของการคิดราคาขาย) ── --}}
            <thead class="pr-thead-dark">
                <tr class="align-middle">
                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                    <th>รหัสสินค้า<br><small class="fw-normal">PdCode</small></th>
                    <th class="text-end" style="width: 180px;">ราคาทุน<br><small class="fw-normal">Price</small></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $key => $row)
                    <tr>
                        <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>
                        <td><strong class="text-primary">{{ $txt($row->PdCode) }}</strong></td>
                        <td class="text-end fw-semibold">{{ $num($row->Price) }}</td>
                    </tr>
                @empty
                    @include('saleinfo.access-empty', ['cols' => 3])
                @endforelse
            </tbody>

        @else
            {{-- ── TestMai: หัวใบเทียบสี — คอลัมน์เยอะ (26) เลือกโชว์เฉพาะที่ใช้จริง ── --}}
            <thead class="pr-thead-dark">
                <tr class="align-middle">
                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                    <th style="width: 130px;">เลขที่เทียบสี<br><small class="fw-normal">TestNo</small></th>
                    <th class="text-center" style="width: 105px;">วันที่<br><small class="fw-normal">Tdate</small></th>
                    <th>รายละเอียด / เม็ดพลาสติก<br><small class="fw-normal">TDecs / Resin</small></th>
                    <th style="width: 150px;">ลูกค้า<br><small class="fw-normal">CCode / CName</small></th>
                    <th class="text-end" style="width: 100px;">ปริมาณ<br><small class="fw-normal">TQty</small></th>
                    <th class="text-end" style="width: 110px;">ต้นทุน<br><small class="fw-normal">TNet</small></th>
                    <th class="text-end" style="width: 110px;">ราคา 1<br><small class="fw-normal">PRICE1</small></th>
                    <th class="text-center" style="width: 90px;">อนุมัติ<br><small class="fw-normal">App</small></th>
                    <th style="width: 200px;">มาตรฐาน<br><small class="fw-normal">EN71 / AP89 / …</small></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_data as $key => $row)
                    <tr>
                        <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>
                        <td>
                            <strong class="text-primary">{{ $txt($row->TestNo) }}</strong>
                            @if ($row->Lotno)
                                <br><small class="text-muted">Lot: {{ $row->Lotno }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $row->Tdate ? \Carbon\Carbon::parse($row->Tdate)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            {{ $txt($row->TDecs) }}
                            @if ($row->Resin)
                                <br><small class="text-muted">{{ $row->Resin }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-secondary mb-1">{{ $txt($row->CCode) }}</span>
                            <br><small>{{ $txt($row->CName) }}</small>
                        </td>
                        <td class="text-end">{{ $num($row->TQty) }}</td>
                        <td class="text-end">{{ $num($row->TNet) }}</td>
                        <td class="text-end fw-semibold">{{ $num($row->PRICE1) }}</td>
                        <td class="text-center">
                            {!! $flag($row->App) !!}
                            @if ($row->AppDate)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($row->AppDate)->format('d/m/Y') }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                // โชว์เฉพาะธงที่ติด ('1') — ติดครบทุกตัวจะยาวเกินไป
                                $flags = collect([
                                    'EN71' => $row->EN71, 'AP89' => $row->AP89, 'EU94' => $row->EU94,
                                    'JHOSPA' => $row->JHOSPA, 'EU2002' => $row->EU2002, 'ROH52' => $row->ROH52,
                                    'MAT10' => $row->MAT10, 'NA' => $row->NA,
                                ])->filter(fn ($v) => (string) $v === '1')->keys();
                            @endphp
                            @forelse ($flags as $name)
                                <span class="badge bg-label-info mb-1">{{ $name }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                            @if ($row->Othe)
                                <br><small class="text-muted">{{ $row->Othe }}</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    @include('saleinfo.access-empty', ['cols' => 10])
                @endforelse
            </tbody>
        @endif

    </table>
</div>

{{-- pagination ของตารางนี้โดยเฉพาะ — เรียก loadAccessData ไม่ใช่ loadData ของตารางหลัก --}}
<div class="pr-pagination-wrap mt-4 mb-3">
    <div class="row">
        <div class="col-sm-12 col-md-6 ps-4">
            <div class="dataTables_info">
                ทั้งหมด &nbsp; {{ number_format($list_data->total()) }} &nbsp; รายการ
            </div>
        </div>
        <div class="col-sm-12 col-md-6 pe-4">
            <div class="dataTables_paginate paging_simple_numbers">
                @if ($list_data->lastPage() > 1)
                    @php
                        // แสดงเลขหน้าแบบย่อรอบ ๆ หน้าปัจจุบัน (รูปแบบเดียวกับ layout/pagination)
                        $half = 4;
                        $from = max($list_data->currentPage() - $half, 1);
                        $to   = min($list_data->currentPage() + $half, $list_data->lastPage());
                    @endphp
                    <ul class="pagination">
                        <li class="page-item {{ $list_data->currentPage() == 1 ? 'disabled' : '' }}">
                            <a class="page-link" href="javascript:void(0)"
                               onclick='loadAccessData("{{ $list_data->url(1) }}")'>First</a>
                        </li>

                        @for ($i = $from; $i <= $to; $i++)
                            <li class="page-item {{ $list_data->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="javascript:void(0)"
                                   onclick='loadAccessData("{{ $list_data->url($i) }}")'>{{ $i }}</a>
                            </li>
                        @endfor

                        @if ($to < $list_data->lastPage())
                            <li class="px-2 pe-1 mt-4">...</li>
                            <li class="page-item">
                                <a class="page-link" href="javascript:void(0)"
                                   onclick='loadAccessData("{{ $list_data->url($list_data->lastPage()) }}")'>{{ $list_data->lastPage() }}</a>
                            </li>
                        @endif

                        <li class="page-item {{ $list_data->currentPage() == $list_data->lastPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="javascript:void(0)"
                               onclick='loadAccessData("{{ $list_data->url($list_data->lastPage()) }}")'>Last</a>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
