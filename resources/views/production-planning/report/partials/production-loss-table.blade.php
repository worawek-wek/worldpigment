{{-- ตารางรายงานการผลิตและสูญเสีย — 10/11/2569 (client-side DataTables, init ใน production-loss.blade.php)
     สูญเสีย = weight (TP) − weight_produced · % = สูญเสีย ÷ weight × 100 --}}
@php
    $n2  = fn ($v) => $v !== null ? number_format((float) $v, 2) : '-';
    $dmy = fn ($v, $f = 'd/m/y') => $v ? \Carbon\Carbon::parse($v)->format($f) : '-';
    $pctClass = fn ($p) => $p === null ? '' : ($p < 0 ? 'loss-neg' : ($p > 5 ? 'loss-high' : ''));
@endphp

<div class="d-flex flex-wrap gap-3 mb-3">
    <div class="border rounded px-3 py-2"><div class="small text-muted">รายการ</div><div class="fs-5 fw-bold">{{ number_format($total) }}</div></div>
    <div class="border rounded px-3 py-2"><div class="small text-muted">น้ำหนักผลิต (TP) รวม</div><div class="fs-5 fw-bold">{{ number_format($sum['tp'], 2) }}</div></div>
    <div class="border rounded px-3 py-2"><div class="small text-muted">นน.บรรจุได้ รวม</div><div class="fs-5 fw-bold">{{ number_format($sum['produced'], 2) }}</div></div>
    <div class="border rounded px-3 py-2"><div class="small text-muted">สูญเสียรวม (กก.)</div><div class="fs-5 fw-bold">{{ number_format($sum['loss_kg'], 2) }}</div></div>
    <div class="border rounded px-3 py-2 bg-label-warning"><div class="small text-muted">สูญเสียเฉลี่ย (ถ่วงน้ำหนัก)</div><div class="fs-5 fw-bold">{{ $sum['loss_pct'] !== null ? number_format($sum['loss_pct'], 2).'%' : '-' }}</div></div>
</div>
@if($sum['no_tp'] > 0)
    <div class="small text-warning mb-2"><i class="ti ti-alert-triangle"></i> {{ number_format($sum['no_tp']) }} รายการไม่มีน้ำหนักผลิต (TP) จึงคำนวณสูญเสียไม่ได้ (ไม่นับในยอดสูญเสียรวม)</div>
@endif

<table id="productionLossTable" class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
    <thead class="table-light">
        <tr class="text-center">
            <th>#</th>
            <th>หน่วย</th>
            <th>เลขที่ใบสั่ง</th>
            <th>เลขที่ใบเบิก</th>
            <th>รหัสสินค้า</th>
            <th>Lot No.</th>
            <th>น้ำหนัก PO</th>
            <th>น้ำหนักผลิต (TP)</th>
            <th>นน.บรรจุได้</th>
            <th class="col-loss">สูญเสีย (กก.)</th>
            <th class="col-loss">สูญเสีย (%)</th>
            <th>Inplan</th>
            <th>วันที่ผลิตเสร็จ</th>
            <th>บรรจุล่าสุด</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $it)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $it->dept ?: '-' }}</td>
                <td>{{ $it->orderno ?: '-' }}</td>
                <td>{{ $it->red_bill_code ?: '-' }}</td>
                <td>{{ $it->itemno ?: '-' }}</td>
                <td>{{ $it->lot ?: '-' }}</td>
                <td class="text-end" data-order="{{ (float) $it->quantity }}">{{ $n2($it->quantity) }}</td>
                <td class="text-end" data-order="{{ (float) $it->weight }}">{{ $n2($it->weight) }}</td>
                <td class="text-end" data-order="{{ (float) $it->weight_produced }}">{{ $n2($it->weight_produced) }}</td>
                <td class="text-end col-loss {{ $pctClass($it->loss_pct) }}" data-order="{{ $it->loss_kg ?? -999999 }}">{{ $n2($it->loss_kg) }}</td>
                <td class="text-end col-loss {{ $pctClass($it->loss_pct) }}" data-order="{{ $it->loss_pct ?? -999999 }}">{{ $it->loss_pct !== null ? number_format($it->loss_pct, 2).'%' : '-' }}</td>
                <td class="text-center" data-order="{{ $it->inplan ?? '' }}">{{ $dmy($it->inplan) }}</td>
                <td class="text-center" data-order="{{ $it->end_date ?? '' }}">{{ $dmy($it->end_date) }}</td>
                <td class="text-center" data-order="{{ $it->pk_last ?? '' }}">
                    {{ $dmy($it->pk_last, 'd/m/y H:i') }}
                    @if((int) $it->pk_count > 1)<span class="small text-muted">({{ (int) $it->pk_count }} ครั้ง)</span>@endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@if($total === 0)
    <div class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
@endif
