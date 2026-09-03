{{-- ตารางรายงานผลิตตามพนักงาน (time-grid รายวัน) — โหลดผ่าน AJAX --}}
{{-- 1 พนักงาน = 1 บล็อก: หัวกลุ่ม + แถว รหัสสี/จำนวน/รหัสเครื่อง/วิธีการผลิต + 2 แถวเว้นให้เซ็นมือ --}}
@php
    // รวมค่าใน array เป็น HTML (escape แล้วคั่นด้วย <br>)
    $fmtCell = fn ($arr) => collect($arr)->map(fn ($v) => e($v))->implode('<br>');
@endphp

<div class="d-flex justify-content-between align-items-center mb-2">
    <span class="text-muted small">พบทั้งหมด {{ number_format($total) }} รายการ</span>
    <span class="text-muted small"><i class="ti ti-info-circle me-1"></i>ช่อง "ผู้ทวนสอบ/เวลา" และ "ผู้ผลิต" เว้นว่างไว้ให้เซ็นมือ</span>
</div>

<table id="employeeReportTable" class="table table-bordered table-striped table-hover align-middle" style="width:100%">
    <thead class="table-light">
        <tr>
            <th class="text-center" style="width:110px;"></th>
            @foreach($slots as $slot)
                <th class="text-center">{{ $slot['label'] }}</th>
            @endforeach
        </tr>
    </thead>

    @forelse($groups as $group)
        {{-- หัวกลุ่มพนักงาน --}}
        <tbody class="emp-head">
            <tr>
                <td colspan="{{ count($slots) + 1 }}">
                    <i class="ti ti-user me-1"></i>
                    <strong>{{ $group['label'] }}</strong>
                    <span class="small ms-1" style="color:#6c757d;">({{ number_format($group['job_count']) }} รายการ)</span>
                </td>
            </tr>
        </tbody>

        <tbody>
            {{-- รหัสสี --}}
            <tr>
                <td class="rowlabel">รหัสสี</td>
                @foreach($slots as $i => $slot)
                    <td class="slot">{!! $fmtCell($group['cells'][$i]['color']) !!}</td>
                @endforeach
            </tr>
            {{-- จำนวน --}}
            <tr>
                <td class="rowlabel">จำนวน</td>
                @foreach($slots as $i => $slot)
                    <td class="slot">{{ $group['cells'][$i]['qty'] !== null ? number_format($group['cells'][$i]['qty'], 2).' KG' : '' }}</td>
                @endforeach
            </tr>
            {{-- รหัสเครื่อง --}}
            <tr>
                <td class="rowlabel">รหัสเครื่อง</td>
                @foreach($slots as $i => $slot)
                    <td class="slot">{!! $fmtCell($group['cells'][$i]['machine']) !!}</td>
                @endforeach
            </tr>
            {{-- วิธีการผลิต --}}
            <tr>
                <td class="rowlabel">วิธีการผลิต</td>
                @foreach($slots as $i => $slot)
                    <td class="slot">{!! $fmtCell($group['cells'][$i]['method']) !!}</td>
                @endforeach
            </tr>
            {{-- ผู้ทวนสอบ/เวลา (เว้นว่างให้เซ็นมือ) --}}
            <tr>
                <td class="rowlabel">ผู้ทวนสอบ/เวลา</td>
                <td class="signcell" colspan="{{ count($slots) }}"></td>
            </tr>
            {{-- ผู้ผลิต (เว้นว่างให้เซ็นมือ) --}}
            <tr>
                <td class="rowlabel">ผู้ผลิต</td>
                <td class="signcell" colspan="{{ count($slots) }}"></td>
            </tr>

            {{-- รายการที่ไม่ระบุเวลา (วางลงกริดไม่ได้) --}}
            @if(!empty($group['unscheduled']))
                @foreach($group['unscheduled'] as $u)
                    <tr>
                        <td class="rowlabel text-warning">ไม่ระบุเวลา</td>
                        <td colspan="{{ count($slots) }}" class="small text-muted">
                            รหัสสี <strong>{{ $u['color'] ?: '-' }}</strong>
                            | จำนวน {{ $u['qty'] !== null ? number_format($u['qty'], 2).' KG' : '-' }}
                            | เครื่อง {{ $u['machine'] ?: '-' }}
                            | วิธี {{ $u['method'] ?: '-' }}
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    @empty
        <tbody>
            <tr>
                <td colspan="{{ count($slots) + 1 }}" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
            </tr>
        </tbody>
    @endforelse
</table>
