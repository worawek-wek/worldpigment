<div class="table-responsive">
    <table class="table table-hover align-middle">

        @php
            // สถานะการเรียงปัจจุบัน (ส่งมาจาก controller) — เลือกไอคอนหัวตาราง
            $sortCol = $sort_col ?? 'code';
            $sortDir = $sort_dir ?? 'asc';
            $sortIcon = function ($col) use ($sortCol, $sortDir) {
                if ($sortCol === $col) {
                    $dir = $sortDir === 'asc' ? 'ti-arrow-up' : 'ti-arrow-down';
                    return '<i class="ti ' . $dir . ' th-sort-icon active"></i>';
                }
                return '<i class="ti ti-arrows-sort th-sort-icon"></i>';
            };
            $sortActive = fn ($col) => $sortCol === $col ? ' col-sorted' : '';
        @endphp

        <thead class="table-light">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle th-sort{{ $sortActive('code') }}" data-sort="code" style="width: 90px;">
                    รหัส {!! $sortIcon('code') !!}
                </th>
                <th class="align-middle th-sort{{ $sortActive('name') }}" data-sort="name">
                    ชื่อลูกค้า {!! $sortIcon('name') !!}
                </th>
                <th class="align-middle th-sort{{ $sortActive('city') }}" data-sort="city">
                    ที่อยู่ {!! $sortIcon('city') !!}
                </th>
                <th class="align-middle" style="width: 120px;">
                    โทร
                    <br>
                    <small class="text-body-secondary fw-normal">Fax</small>
                </th>
                <th class="align-middle th-sort{{ $sortActive('type') }}" data-sort="type">
                    ประเภทลูกค้า {!! $sortIcon('type') !!}
                </th>
                <th class="align-middle text-center th-sort{{ $sortActive('sale') }}" data-sort="sale" style="width: 90px;">
                    ผู้ขาย {!! $sortIcon('sale') !!}
                </th>
                <th class="align-middle th-sort{{ $sortActive('term') }}" data-sort="term" style="width: 110px;">
                    เครดิต {!! $sortIcon('term') !!}
                </th>
                <th class="align-middle text-center" style="width: 80px;">ผู้ติดต่อ</th>
                <th class="align-middle text-center" style="width: 110px;">จัดการ</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($list_data as $key => $row)
                @php
                    // ที่อยู่บรรทัดเดียว: เลขที่ + ถนน + อำเภอ/เขต
                    $addr = collect([$row->no, $row->road, $row->amphur])
                        ->map(fn ($v) => trim((string) $v))
                        ->filter()
                        ->implode(' ');
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $list_data->firstItem() + $key }}</td>

                    <td class="{{ $sortActive('code') }}">
                        <strong class="text-primary">{{ $row->code }}</strong>
                        @if ((int) $row->black === -1)
                            <br>
                            <span class="badge bg-label-danger mt-1" title="{{ $row->blackrem }}">Blacklist</span>
                        @endif
                    </td>

                    <td class="{{ $sortActive('name') }}">
                        <span class="fw-medium">{{ $row->name ?: '—' }}</span>
                        @if (trim((string) $row->name_en) !== '')
                            <br>
                            <small class="text-muted">{{ $row->name_en }}</small>
                        @endif
                        @if (trim((string) $row->nickname) !== '')
                            <br>
                            <small class="text-body-secondary">
                                <i class="ti ti-tag me-1"></i>{{ $row->nickname }}
                            </small>
                        @endif
                    </td>

                    <td class="{{ $sortActive('city') }}">
                        <small>{{ $addr ?: '—' }}</small>
                        @if (trim((string) $row->city) !== '')
                            <br>
                            <small class="text-muted">{{ $row->city }} {{ $row->zip }}</small>
                        @endif
                    </td>

                    <td class="cm-tel">
                        <small>{{ $row->tel ?: '—' }}</small>
                        @if (trim((string) $row->fax) !== '')
                            <br>
                            <small class="text-muted">{{ $row->fax }}</small>
                        @endif
                    </td>

                    <td class="{{ $sortActive('type') }}">
                        @if (trim((string) $row->type_name) !== '')
                            <small>{{ $row->type_name }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-center{{ $sortActive('sale') }}">
                        @if (trim((string) $row->sale) !== '')
                            <span class="badge bg-label-secondary">{{ $row->sale }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="{{ $sortActive('term') }}">
                        <small>{{ $row->term ?: '—' }}</small>
                        @if ($row->cashdisc !== null && (int) $row->cashdisc !== 0)
                            <br>
                            <small class="text-muted">ส่วนลด {{ (int) $row->cashdisc }}%</small>
                        @endif
                    </td>

                    <td class="text-center">
                        @if ((int) $row->contact_count > 0)
                            <span class="badge bg-label-info">{{ number_format($row->contact_count) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <button class="btn btn-sm btn-icon btn-label-primary" title="แก้ไขข้อมูลลูกค้า"
                            onclick="customerOpen('{{ $row->code }}')">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-label-danger" title="ลบลูกค้า"
                            onclick="customerDelete('{{ $row->code }}', '{{ addslashes($row->name) }}')">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
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

    /* คอลัมน์ โทร/Fax — ตรึงความกว้างไว้ ไม่ให้เบอร์ยาว ๆ (เช่น "4168225-8/4168229") ดันคอลัมน์กว้างออก */
    .cm-tel { max-width: 120px; overflow-wrap: break-word; }
</style>
