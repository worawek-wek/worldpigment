@php
    use App\Models\Holiday;

    // $holidays = collection ของทั้งปี key = 'Y-m-d' (ส่งมาจาก HolidayController::calendar)
    $dayHeads = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
    $active   = $holidays->where('is_active', 'Y');
@endphp

<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    <h5 class="mb-0">ปฏิทินปี {{ $year + 543 }} <span class="text-muted fw-normal">({{ $year }})</span></h5>
    <span class="text-muted">วันหยุดที่เปิดใช้งาน {{ $active->count() }} วัน</span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        @foreach(Holiday::TYPES as $key => $label)
            <span class="badge {{ Holiday::typeBadge($key) }}">{{ $label }}</span>
        @endforeach
        <span class="badge bg-label-secondary">ปิดใช้งาน</span>
    </div>
</div>

<div class="row g-3">
    @for($month = 1; $month <= 12; $month++)
        @php
            $first     = mktime(0, 0, 0, $month, 1, $year);
            $daysIn    = (int) date('t', $first);
            $startBlank = (int) date('w', $first);   // 0 = อาทิตย์
            $cells      = $startBlank + $daysIn;
            $rows       = (int) ceil($cells / 7);
        @endphp

        <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
            <div class="card h-100 border">
                <div class="card-body p-3">
                    <div class="fw-semibold text-center mb-2">{{ Holiday::MONTHS_FULL[$month] }}</div>
                    <table class="table table-sm table-borderless holiday-mini-calendar mb-0">
                        <thead>
                            <tr>
                                @foreach($dayHeads as $i => $head)
                                    <th class="text-center {{ in_array($i, [0, 6], true) ? 'text-muted' : '' }}">
                                        {{ $head }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for($row = 0; $row < $rows; $row++)
                                <tr>
                                    @for($col = 0; $col < 7; $col++)
                                        @php
                                            $dayNum = $row * 7 + $col - $startBlank + 1;
                                            $inMonth = $dayNum >= 1 && $dayNum <= $daysIn;
                                            $ymd = $inMonth ? sprintf('%04d-%02d-%02d', $year, $month, $dayNum) : null;
                                            $holiday = $ymd ? ($holidays[$ymd] ?? null) : null;
                                            $isWeekend = in_array($col, [0, 6], true);
                                        @endphp
                                        <td class="text-center">
                                            @if(!$inMonth)
                                                <span class="hc-day hc-empty"></span>
                                            @elseif($holiday)
                                                {{-- คลิกวันที่มีวันหยุด = เปิดฟอร์มแก้ไขวันนั้น (btn_edit_calendar → ฟอร์มจะมีปุ่มลบ)
                                                     ชื่อวันหยุดใช้ tooltip ของ Bootstrap (data-bs-*) ไม่ใช่ title ธรรมดา
                                                     — ปฏิทินโหลดผ่าน AJAX จึงต้อง init เองที่ initCalendarTooltips() --}}
                                                <span class="hc-day hc-holiday hc-{{ $holiday->is_active === 'Y' ? $holiday->type : 'off' }} btn_edit_calendar"
                                                    data-id="{{ $holiday->id }}" role="button"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-title="{{ $holiday->name }}{{ $holiday->is_active === 'Y' ? '' : ' (ปิดใช้งาน)' }}">
                                                    {{ $dayNum }}
                                                </span>
                                            @else
                                                <span class="hc-day {{ $isWeekend ? 'text-muted' : '' }}">{{ $dayNum }}</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endfor
</div>

@if($holidays->isEmpty())
    <div class="alert alert-warning mt-3 mb-0">
        ยังไม่มีวันหยุดของปี {{ $year + 543 }} ในระบบ — กดปุ่ม "เพิ่มวันหยุด" เพื่อเริ่มบันทึก
    </div>
@else
    <div class="table-responsive mt-4">
        <table class="table table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px;">#</th>
                    <th style="width:190px;">วันที่</th>
                    <th style="width:110px;">วัน</th>
                    <th>ชื่อวันหยุด</th>
                    <th style="width:170px;">ประเภท</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                {{-- $holidays ถูก keyBy ด้วยวันที่ → ใช้ values() เพื่อให้ $i เป็นเลขลำดับ --}}
                @foreach($holidays->values() as $i => $holiday)
                    <tr class="{{ $holiday->is_active === 'Y' ? '' : 'text-muted' }}">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ date('d/m/Y', strtotime($holiday->holiday_date)) }}
                            <small class="text-muted">({{ Holiday::thaiDate($holiday->holiday_date) }})</small>
                        </td>
                        <td>{{ Holiday::thaiWeekday($holiday->holiday_date) }}</td>
                        <td>
                            {{ $holiday->name }}
                            @if($holiday->is_active !== 'Y')
                                <span class="badge bg-label-secondary ms-1">ปิดใช้งาน</span>
                            @endif
                        </td>
                        <td><span class="badge {{ Holiday::typeBadge($holiday->type) }}">{{ Holiday::typeLabel($holiday->type) }}</span></td>
                        <td>{{ $holiday->remark ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
