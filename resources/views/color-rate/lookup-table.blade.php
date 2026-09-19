{{-- แถวของตาราง "ดูกลุ่มราคา" (อ่านอย่างเดียว) — คืนจาก ColorRateController::lookup() 19/09/2569 --}}
<table class="table table-sm table-striped table-hover mb-0 w-100">
    <thead class="table-light">
        <tr>
            <th style="width:32%">รหัสสินค้า</th>
            <th class="text-end" style="width:16%">กลุ่ม A</th>
            <th class="text-end" style="width:16%">กลุ่ม B</th>
            <th class="text-end" style="width:16%">กลุ่ม C</th>
            <th class="text-center" style="width:20%">วันที่ปรับราคา</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $r)
            <tr>
                <td class="fw-semibold">{{ $r->colorno }}</td>
                <td class="text-end">{{ $r->rate_A !== null ? number_format((float) $r->rate_A, 2) : '-' }}</td>
                <td class="text-end">{{ $r->rate_B !== null ? number_format((float) $r->rate_B, 2) : '-' }}</td>
                <td class="text-end">{{ $r->rate_C !== null ? number_format((float) $r->rate_C, 2) : '-' }}</td>
                <td class="text-center">
                    {{ $r->RDate ? \Carbon\Carbon::parse($r->RDate)->format('d/m/Y') : '-' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
            </tr>
        @endforelse
    </tbody>
</table>
