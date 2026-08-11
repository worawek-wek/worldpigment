<div class="table-responsive">
    <table class="table table-hover align-middle">

        @php
            // สถานะการเรียงปัจจุบัน (ส่งมาจาก controller) — ใช้เลือกไอคอนหัวตาราง
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
            // class เน้นทั้งคอลัมน์ที่กำลังเรียง (ใช้ทั้งหัวตาราง th และ cell td)
            $sortActive = fn ($col) => $sortCol === $col ? ' col-sorted' : '';
        @endphp

        <thead class="cm-thead-dark">
            <tr class="align-middle">
                <th class="align-middle text-center" style="width: 50px;">ลำดับ</th>
                <th class="align-middle th-sort{{ $sortActive('DsendT') }}" data-sort="DsendT">
                    เอกสาร / เลขที่ {!! $sortIcon('DsendT') !!}
                    <br>
                    <small class="text-body-secondary fw-normal">วันที่ส่งเทียบสี</small>
                </th>
                <th class="align-middle th-sort{{ $sortActive('custno') }}" data-sort="custno">ลูกค้า {!! $sortIcon('custno') !!}</th>
                <th class="align-middle th-sort{{ $sortActive('Type_Work') }}" data-sort="Type_Work">ประเภทงาน {!! $sortIcon('Type_Work') !!}</th>
                <th class="align-middle th-sort{{ $sortActive('color') }}" data-sort="color">สี / นำไปทำชิ้นงาน {!! $sortIcon('color') !!}</th>
                <th class="align-middle th-sort{{ $sortActive('STD') }}" data-sort="STD">Standard {!! $sortIcon('STD') !!}</th>
                <th class="align-middle th-sort{{ $sortActive('lotno') }}" data-sort="lotno">Lot No. {!! $sortIcon('lotno') !!}</th>
                <th class="align-middle text-center th-sort{{ $sortActive('status') }}" data-sort="status">สถานะ {!! $sortIcon('status') !!}</th>
                <th class="align-middle text-center" width="160">จัดการ</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($list_data as $key => $row)
                @php
                    // มี Testno = ใบส่ง ต.ย. (SD), ไม่มี = ใบนำส่งเทียบสี (CM)
                    $isSD = !empty(trim((string)$row->Testno));

                    if ($row->cancel == 1) {
                        $statusClass = 'bg-label-danger';
                        $statusIcon  = 'ti-x';
                        $statusText  = 'ยกเลิก';
                    } elseif (!empty(trim((string)$row->RminWating))) {
                        $statusClass = 'bg-label-warning';
                        $statusIcon  = 'ti-hourglass';
                        $statusText  = 'รอวัตถุดิบ';
                    } elseif (empty(trim((string)$row->Testno))) {
                        $statusClass = 'badge-status-cm';
                        $statusIcon  = 'ti-palette';
                        $statusText  = 'กำลังเทียบสี';
                    } else {
                        $statusClass = 'bg-label-success';
                        $statusIcon  = 'ti-package-export';
                        $statusText  = 'ส่ง ต.ย. แล้ว';
                    }

                    // ── ผลการทดสอบตัวอย่างสี (เฉพาะใบส่ง ต.ย.) — testmain.TyResp (10/08/2569) ──
                    $tr = $row->testResult();
                @endphp

                <tr class="{{ $isSD ? 'tr-sd' : 'tr-cm' }}">
                    <td class="text-center text-muted">
                        {{ $list_data->firstItem() + $key }}
                    </td>

                    <td class="{{ trim($sortActive('DsendT')) }}">
                        @if ($isSD)
                            <span class="badge badge-doc-sd mb-1">
                                <i class="ti ti-package me-1"></i>ใบส่ง ต.ย.
                            </span>
                            <br>
                            <strong class="text-primary">{{ $row->Testno ?: '—' }}</strong>
                            <br>
                            <small class="text-muted">อ้างอิงใบเทียบสี: {{ $row->SendNo ?: '—' }}</small>
                        @else
                            <span class="badge badge-doc-cm mb-1">
                                <i class="ti ti-file-text me-1"></i>ใบนำส่งเทียบสี
                            </span>
                            <br>
                            <strong class="text-primary">{{ $row->SendNo }}</strong>
                        @endif
                        <br>
                        <small class="text-muted">
                            {{ $row->DsendT ? \Carbon\Carbon::parse($row->DsendT)->format('d/m/Y') : '-' }}
                        </small>
                    </td>

                    <td class="{{ trim($sortActive('custno')) }}">
                        <span class="badge bg-label-secondary mb-1">{{ $row->custno ?? '-' }}</span>
                        <br>
                        <small>{{ $row->custname ?: '—' }}</small>
                    </td>

                    <td class="{{ trim($sortActive('Type_Work')) }}">
                        @if ($row->Type_Work)
                            <span class="badge bg-label-primary">{{ $row->Type_Work }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="{{ trim($sortActive('color')) }}">
                        <div>
                            <div class="small fw-semibold">{{ $row->color ?: '—' }}</div>
                            @if ($row->Model)
                                <small class="text-muted">{{ $row->Model }}</small>
                            @endif
                        </div>
                    </td>

                    <td class="{{ trim($sortActive('STD')) }}">{{ $row->STD ?: '-' }}</td>

                    <td class="{{ trim($sortActive('lotno')) }}">{{ $row->lotno ?: '-' }}</td>

                    <td class="td-status text-center{{ $sortActive('status') }}">
                        <span class="badge {{ $statusClass }} text-dark fw-bold">
                            <i class="ti {{ $statusIcon }} me-1"></i>
                            {{ $statusText }}
                        </span>

                        {{-- ผลการทดสอบตัวอย่างสี — แสดงเฉพาะใบส่ง ต.ย. (10/08/2569) --}}
                        @if ($isSD)
                            <br>
                            <span class="badge {{ $tr['class'] }} mt-1 fw-normal" title="ผลการทดสอบตัวอย่างสี">
                                <i class="ti ti-clipboard-check me-1"></i>{{ $tr['label'] }}
                            </span>
                        @endif
                    </td>

                    <td class="text-center td-action">
                        <div class="d-inline-flex gap-1">
                            {{-- ดูรายละเอียด (อ่านอย่างเดียว) — แสดงคนละหน้าตาตามชนิดเอกสาร --}}
                            <button class="btn btn-sm btn-icon {{ $isSD ? 'btn-theme-sd' : 'btn-theme-cm' }}"
                                title="ดูรายละเอียด"
                                onclick="viewDetail('{{ $row->id }}')">
                                <i class="ti ti-eye"></i>
                            </button>

                            @if ($isSD)
                                <button class="btn btn-sm btn-icon btn-theme-sd"
                                    title="แก้ไขใบส่ง ต.ย."
                                    onclick="viewSampleDelivery('{{ $row->id }}')">
                                    <i class="ti ti-edit"></i>
                                </button>

                                {{-- บันทึกผลการทดสอบตัวอย่างสี (10/08/2569) --}}
                                <button class="btn btn-sm btn-icon btn-theme-result"
                                    title="ผลการทดสอบตัวอย่างสี"
                                    onclick="viewTestResult('{{ $row->id }}')">
                                    <i class="ti ti-clipboard-check"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-icon btn-theme-cm"
                                    title="แก้ไขใบนำส่งเทียบสี"
                                    onclick="view('{{ $row->id }}')">
                                    <i class="ti ti-edit"></i>
                                </button>
                            @endif

                            {{-- ปุ่มลบย้ายไปอยู่ใน form แก้ไข (modal-cm / modal-sd) แล้ว --}}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                        ไม่พบข้อมูลที่ตรงกับเงื่อนไข
                    </td>
                </tr>
            @endforelse

        </tbody>

    </table>
</div>

<div class="cm-pagination-wrap mt-4 mb-3">
    @include('layout/pagination')
</div>

<style>
    /* ─── ปรับ pagination ของหน้าเทียบสีให้ชิดขวา ─── */
    .cm-pagination-wrap .pagination {
        justify-content: flex-end;
        margin-bottom: 0;
    }
    .cm-pagination-wrap .dataTables_paginate {
        text-align: right;
    }
    .cm-pagination-wrap .dataTables_info {
        padding-top: 0.4rem;
    }
</style>
