<div class="table-responsive">
    <table class="table table-hover align-middle">

        @php
            // สถานะการเรียงปัจจุบัน (ส่งมาจาก controller) — เลือกไอคอนหัวตาราง
            $sortCol = $sort_col ?? 'id';
            $sortDir = $sort_dir ?? 'desc';
            // คอลัมน์ที่กำลังเรียง = ลูกศรขึ้น/ลง (เด่น), ที่เหลือ = ลูกศรจาง
            $sortIcon = function ($col) use ($sortCol, $sortDir) {
                if ($sortCol === $col) {
                    $dir = $sortDir === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down';
                    return '<i class="ti ' . $dir . ' th-sort-icon active"></i>';
                }
                return '<i class="ti ti-arrows-sort th-sort-icon"></i>';
            };
            // class เน้นคอลัมน์ที่กำลังเรียง (ใช้ทั้งหัวตาราง th และ cell td)
            $sortActive = fn ($col) => $sortCol === $col ? ' col-sorted' : '';
        @endphp

        <thead class="table-light">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle th-sort{{ $sortActive('Qdate') }}" data-sort="Qdate">
                    เลขที่ใบเสนอราคา {!! $sortIcon('Qdate') !!}
                    <br>
                    <small class="text-body-secondary fw-normal">วันที่เสนอราคา</small>
                </th>
                <th class="align-middle th-sort{{ $sortActive('Custid') }}" data-sort="Custid">ลูกค้า {!! $sortIcon('Custid') !!}</th>
                <th class="align-middle text-center th-sort{{ $sortActive('PDtype') }}" data-sort="PDtype">ชนิดสินค้า {!! $sortIcon('PDtype') !!}</th>
                <th class="align-middle text-end th-sort{{ $sortActive('item_count') }}" data-sort="item_count">จำนวนรายการ {!! $sortIcon('item_count') !!}</th>
                <th class="align-middle text-end th-sort{{ $sortActive('total_net') }}" data-sort="total_net">มูลค่ารวม {!! $sortIcon('total_net') !!}</th>
                <th class="align-middle">หมายเหตุ</th>
                <th class="align-middle text-center" width="160">จัดการ</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($list_data as $key => $row)
                <tr>
                    <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>

                    <td class="{{ $sortActive('Qdate') }}">
                        <span class="badge bg-label-primary mb-1">
                            <i class="ti ti-file-invoice me-1"></i>ใบเสนอราคา
                        </span>
                        <br>
                        <strong class="text-primary">{{ $row->Qno }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $row->Qdate ? \Carbon\Carbon::parse($row->Qdate)->format('d/m/Y') : '-' }}
                        </small>
                    </td>

                    <td class="{{ $sortActive('Custid') }}">
                        <span class="badge bg-label-secondary mb-1">{{ $row->Custid ?: '-' }}</span>
                        <br>
                        <small>{{ $row->cust_name ?: '—' }}</small>
                    </td>

                    <td class="text-center{{ $sortActive('PDtype') }}">
                        @if ($row->PDtype)
                            <span class="badge bg-label-info">{{ $row->PDtype }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-end{{ $sortActive('item_count') }}">{{ number_format($row->item_count) }}</td>

                    <td class="text-end{{ $sortActive('total_net') }}">{{ number_format($row->total_net, 2) }}</td>

                    <td>
                        @if ($row->exam == 1)
                            <span class="badge bg-label-warning"><i class="ti ti-flask me-1"></i>พร้อมตัวอย่าง</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            {{-- @if ($row->Custid)
                                <button class="btn btn-sm btn-icon btn-label-success" title="ประวัติใบเสนอราคาของลูกค้ารายนี้"
                                    onclick="quotationHistory('{{ $row->Custid }}')">
                                    <i class="ti ti-history"></i>
                                </button>
                            @endif --}}
                            <button class="btn btn-sm btn-icon btn-label-secondary" title="ดูรายละเอียด"
                                onclick="quotationView('{{ $row->Qno }}')">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-primary" title="แก้ไข"
                                onclick="quotationEdit('{{ $row->Qno }}')">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-label-info" title="พิมพ์"
                                onclick="quotationPrint('{{ $row->Qno }}')">
                                <i class="ti ti-printer"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
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
</style>
