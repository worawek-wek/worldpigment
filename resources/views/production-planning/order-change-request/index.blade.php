@extends('./layout/main')

@php
    use Illuminate\Support\Carbon;
    // ตัดเลขทศนิยมส่วนเกิน (เก็บได้สูงสุด 6 ตำแหน่ง) ให้อ่านง่าย
    $fmtNum = function ($v) {
        if ($v === null || $v === '') return '-';
        return rtrim(rtrim(number_format((float) $v, 6, '.', ','), '0'), '.');
    };
    // แสดงวันที่แบบ d/m/Y (รับค่า Y-m-d หรือ datetime) — ค่าว่าง/parse ไม่ได้ → '-'
    $fmtDate = function ($d) {
        if (!$d) return '-';
        try { return Carbon::parse($d)->format('d/m/Y'); } catch (\Exception $e) { return '-'; }
    };
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h3 class="mb-1">
                                <i class="ti ti-file-text text-primary"></i>
                                ใบขอเปลี่ยนแปลงคำสั่งซื้อภายใน
                            </h3>
                            <p class="text-muted mb-0">
                                ดึงข้อมูล Order ที่ปิดจบงานในช่วงที่เลือก หรือ มีการเปลี่ยนวันกำหนดทบทวน (senddate) ในช่วงที่เลือก (แม้ยังไม่ปิดจบงาน) แล้วออกเป็น PDF
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <form method="GET" action="{{ route('production.orderchange.index') }}" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label" for="keyword">ค้นหา</label>
                                    <input type="text" class="form-control" id="keyword" name="keyword"
                                           value="{{ $keyword }}"
                                           placeholder="เลขที่ใบทบทวน / รหัสสินค้า / ชื่อลูกค้า">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="date_from">วันที่ปิดจบงาน (จาก)</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from"
                                           value="{{ $date_from }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="date_to">ถึง</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to"
                                           value="{{ $date_to }}">
                                </div>
                                <div class="col-md-3 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i> ค้นหา
                                    </button>
                                    <a href="{{ route('production.orderchange.pdf', ['date_from' => $date_from, 'date_to' => $date_to, 'keyword' => $keyword]) }}"
                                       target="_blank"
                                       class="btn btn-danger {{ $rows->isEmpty() ? 'disabled' : '' }}">
                                        <i class="ti ti-file-type-pdf me-1"></i> พิมพ์ PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle">#</th>
                                        <th rowspan="2" class="align-middle">รหัสสินค้า</th>
                                        <th rowspan="2" class="align-middle">ตามคำสั่งลูกค้าเพื่อ</th>
                                        <th rowspan="2" class="align-middle">เลขที่ใบทบทวนคำสั่งซื้อ</th>
                                        <th rowspan="2" class="align-middle">กำหนดเสร็จเดิม</th>
                                        <th rowspan="2" class="align-middle">ขอเลื่อนเป็นวันที่</th>
                                        <th colspan="2">เปลี่ยนน้ำหนัก</th>
                                        <th rowspan="2" class="align-middle">สาเหตุ</th>
                                    </tr>
                                    <tr>
                                        <th>จาก</th>
                                        <th>เป็น</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $i => $row)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $row['itemno'] ?: '-' }}</td>
                                            <td>{{ $row['custname'] ?: '-' }}</td>
                                            <td>{{ $row['red_bill_code'] ?: '-' }}</td>
                                            <td class="text-center">{{ $fmtDate($row['due_original']) }}</td>
                                            <td class="text-center">{{ $fmtDate($row['due_postpone']) }}</td>
                                            <td class="text-end">{{ $fmtNum($row['weight_from']) }}</td>
                                            <td class="text-end">{{ $fmtNum($row['weight_to']) }}</td>
                                            <td>{{ $row['reason'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                @if($searched)
                                                    ไม่พบข้อมูล Order ที่ปิดจบงาน หรือมีการเปลี่ยน senddate ในช่วงวันที่ที่เลือก
                                                @else
                                                    เลือกช่วงวันที่แล้วกด "ค้นหา" เพื่อแสดงข้อมูล
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
