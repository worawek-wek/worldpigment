{{-- ประวัติการแก้ไขกลุ่มราคา — คืนจาก ColorRateController::history() 15/10/2569
     ค่าที่เปลี่ยนแสดงเป็น "เดิม → ใหม่" (ค่าที่ไม่เปลี่ยนแสดงค่าเดียว) --}}
@php
    $money = function ($v) {
        return ($v === null || $v === '') ? '-' : number_format((float) $v, 2);
    };
    $day = function ($v) {
        return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '-';
    };
    // แสดงค่าหนึ่งช่อง: เพิ่ม = ค่าใหม่ · ลบ = ค่าเดิม · แก้ไข = เดิม → ใหม่ (เฉพาะที่เปลี่ยน)
    $cell = function ($r, $field, $fmt) {
        $old = $fmt($r->{'old_'.$field});
        $new = $fmt($r->{'new_'.$field});
        if ($r->action === 'create') {
            return e($new);
        }
        if ($r->action === 'delete') {
            return '<span class="text-decoration-line-through text-muted">'.e($old).'</span>';
        }
        if ($old === $new) {
            return '<span class="text-muted">'.e($new).'</span>';
        }

        return '<span class="text-muted">'.e($old).'</span> <i class="ti ti-arrow-right ti-xs"></i> <strong class="text-primary">'.e($new).'</strong>';
    };
@endphp

<div class="small text-muted mb-2">
    พบ {{ number_format($total) }} รายการ
    @if ($total > $limit)
        — แสดง {{ number_format($limit) }} รายการล่าสุด
    @endif
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover mb-0 w-100">
        <thead class="table-light">
            <tr>
                <th class="text-nowrap">วันเวลาที่แก้</th>
                @unless ($single)
                    <th>รหัสสินค้า</th>
                @endunless
                <th class="text-center">การกระทำ</th>
                <th class="text-end text-nowrap">กลุ่ม A</th>
                <th class="text-end text-nowrap">กลุ่ม B</th>
                <th class="text-end text-nowrap">กลุ่ม C</th>
                <th class="text-center text-nowrap">วันที่ปรับราคา</th>
                <th>ผู้แก้ไข</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                @php $a = $actions[$r->action] ?? ['label' => $r->action, 'badge' => 'bg-label-secondary']; @endphp
                <tr>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($r->changed_at)->format('d/m/Y H:i') }}</td>
                    @unless ($single)
                        <td class="fw-semibold">{{ $r->colorno }}</td>
                    @endunless
                    <td class="text-center"><span class="badge {{ $a['badge'] }}">{{ $a['label'] }}</span></td>
                    <td class="text-end text-nowrap">{!! $cell($r, 'rate_A', $money) !!}</td>
                    <td class="text-end text-nowrap">{!! $cell($r, 'rate_B', $money) !!}</td>
                    <td class="text-end text-nowrap">{!! $cell($r, 'rate_C', $money) !!}</td>
                    <td class="text-center text-nowrap">{!! $cell($r, 'RDate', $day) !!}</td>
                    <td>{{ $r->changed_by_name ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $single ? 7 : 8 }}" class="text-center text-muted py-4">ยังไม่มีประวัติการแก้ไข</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
