{{-- ตารางรายงานผลิตตามเครื่องจักร (จัดกลุ่มตามเครื่องจักร) — โหลดผ่าน AJAX --}}
{{-- แต่ละงาน = 1 <tbody class="qjob"> (แถวขั้นตอน ↳ + แถวผลิตอยู่ด้วยกัน) เพื่อให้ลากจัดคิวทั้งงานเป็นก้อนเดียว --}}
<div class="d-flex justify-content-between align-items-center mb-2">
    <span class="text-muted small">พบทั้งหมด {{ number_format($total) }} รายการ</span>
    <span class="text-muted small"><i class="ti ti-arrows-move me-1"></i>ลากไอคอน <i class="ti ti-grip-vertical"></i> เพื่อจัด/แทรกคิว (เฉพาะภายในเครื่องจักร+วันเดียวกัน)</span>
</div>

<table id="machineReportTable" class="table table-striped table-hover nowrap align-middle" style="width:100%">
    <thead class="table-light">
        <tr>
            <th style="width:36px;"></th>
            <th class="text-center" style="width:60px;">#</th>
            <th>รหัสเครื่องจักร</th>
            <th class="text-center">วัน Inplan</th>
            <th>Cust Name</th>
            <th class="text-center">เลขที่ใบแดง</th>
            <th>Item No</th>
            <th class="text-center">Lot</th>
            <th class="text-end">น้ำหนักผลิต</th>
        </tr>
    </thead>
    @php $rownum = 0; @endphp
    @forelse($groups as $group)
        {{-- หัวกลุ่มเครื่องจักร (ไม่ลาก) --}}
        <tbody class="machine-head">
            <tr style="background-color:#1e40af;color:#fff;">
                <td colspan="9" style="background-color:#1e40af;color:#fff;">
                    <i class="ti ti-tools me-1"></i>
                    <strong>เครื่องจักร: {{ $group['machine'] !== '' ? $group['machine'] : 'ไม่ระบุเครื่องจักร' }}</strong>
                    <span class="small ms-1" style="color:#dbeafe;">({{ number_format($group['items']->count()) }} รายการ)</span>
                </td>
            </tr>
        </tbody>
        @foreach($group['items'] as $it)
            <tbody class="qjob"
                data-planning-id="{{ $it->id }}"
                data-machine="{{ $group['machine'] }}"
                data-day="{{ $it->day_key }}">
                {{-- แถวขั้นตอน "สถานะวิธีการผลิต / การล้าง" (แสดงก่อนแถวผลิต เรียงตาม work_date/start_time) --}}
                @foreach($it->steps as $s)
                    <tr style="background-color:#f8f9fa;">
                        <td></td>
                        <td class="text-center text-muted">↳</td>
                        <td colspan="7" style="padding-left:1.75rem;">
                            <span class="text-muted small">ขั้นตอน:</span>
                            <strong>{{ $s->method_name ?: '-' }}</strong>
                            <span class="text-muted small ms-1">
                                [{{ $s->work_date ? \Carbon\Carbon::parse($s->work_date)->format('d/m/Y') : '-' }}
                                {{ $s->start_time ? substr($s->start_time, 0, 5) : '--' }}–{{ $s->end_time ? substr($s->end_time, 0, 5) : '--' }}]
                            </span>
                        </td>
                    </tr>
                @endforeach
                {{-- แถวผลิตสินค้า --}}
                <tr>
                    <td class="text-center">
                        <i class="ti ti-grip-vertical qhandle text-muted" style="cursor:grab;" title="ลากเพื่อจัดคิว"></i>
                    </td>
                    <td class="text-center qnum">{{ ++$rownum }}</td>
                    <td>{{ $it->machine_no ?: '-' }}</td>
                    <td class="text-center">{{ $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $it->cust_name ?: '-' }}</td>
                    <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                    <td>{{ $it->itemno ?: '-' }}</td>
                    <td class="text-center">{{ $it->lot ?: '-' }}</td>
                    <td class="text-end">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '-' }}</td>
                </tr>
            </tbody>
        @endforeach
    @empty
        <tbody>
            <tr>
                <td colspan="9" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
            </tr>
        </tbody>
    @endforelse
</table>
