<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 9px; color: #000; }
        .title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .summary { text-align: center; font-size: 9px; margin-bottom: 6px; color: #333; }
        /* table-layout: fixed → คุมความกว้างคอลัมน์ตามที่กำหนดจริง + ข้อความยาวตัดบรรทัดแทนดันคอลัมน์กว้าง (2026-09-12) */
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data th, table.data td { border: 1px solid #000; padding: 1px 2px; font-size: 8px; }
        table.data th { background-color: #e9ecef; text-align: center; }
        /* คอลัมน์ วันที่ลงแผน (inplan) พื้นน้ำเงิน — ให้เหมือนฝั่งเว็บ */
        table.data th.col-inplan, table.data td.col-inplan { background-color: #cfe2ff; color: #084298; }
        .group-row td { background-color: #f1f3f5; font-weight: bold; }
        .step-row td { background-color: #f8f9fa; font-style: italic; }
        /* เส้นแนวนอนภายในงานเดียวกัน (ระหว่างขั้นตอน และระหว่างขั้นตอนกับแถว planning) ให้บาง+จาง
           เพื่อให้อ่านว่าเป็นกลุ่มเดียวกัน — เส้นขอบนอก/แนวตั้งยังเป็น 1px ดำเหมือนเดิม */
        .step-row td { border-top: 0.4px solid #c7ccd1; border-bottom: 0.4px solid #c7ccd1; }
        .prod-row.grouped td { border-top: 0.4px solid #c7ccd1; }
        .sum-row td { font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
@foreach($sections as $sec)
    {{-- คอลัมน์ข้อมูลสินค้า (Resin/Temp/CODE/Packaging/Batch/สุ่มตัวอย่าง) แสดงเฉพาะแผนก CP — แผนกอื่นซ่อน (2026-09-12) --}}
    @php $showProd = ($sec['dept'] === 'CP'); @endphp
    {{-- ขึ้นหน้าใหม่ต่อ section พร้อมสลับแนวกระดาษ: CP=แนวนอน(L), แผนกอื่น=แนวตั้ง(P) (2026-09-12)
         หน้าแรกไม่ต้องมี <pagebreak> (constructor ตั้งแนวให้แล้วจากแผนก section แรก) --}}
    @if(!$loop->first)
        <pagebreak orientation="{{ $showProd ? 'L' : 'P' }}" />
    @endif
    <div class="dept-section">
    <div class="title">รายงานผลิตตามเครื่องจักร</div>
    <div class="summary">{{ $sec['summary'] }}</div>

    {{-- ตารางเต็มความกว้างกระดาษทั้ง CP (แนวนอน) และแผนกอื่น (แนวตั้ง) — คอลัมน์กำหนดความกว้างตายตัว
         ส่วน Remark เป็นคอลัมน์ยืดหยุ่นรับพื้นที่ที่เหลือ จึงเต็มหน้าพอดีเสมอ (2026-09-12) --}}
    <table class="data">
        <thead>
            <tr>
                {{-- ความกว้างแยก 2 โปรไฟล์ (2026-09-12):
                     CP (แนวนอน) = มิลลิเมตรตายตัว รวมคอลัมน์อื่น ~254mm + Remark ยืดหยุ่นรับที่เหลือ → เต็มหน้า A4-L (~285mm)
                     แผนกอื่น (แนวตั้ง) = % รวมคอลัมน์อื่น 88% + Remark ยืดหยุ่น 12% → เต็มหน้า A4 --}}
                <th style="width: {{ $showProd ? '7mm' : '3%' }};">#</th>
                <th class="col-inplan" style="width: {{ $showProd ? '18mm' : '9%' }};">วันที่ลงแผน</th>
                <th style="width: {{ $showProd ? '18mm' : '9%' }};">Revise</th>
                <th style="width: {{ $showProd ? '28mm' : '16%' }};">Cust Name</th>
                <th style="width: {{ $showProd ? '18mm' : '9%' }};">เลขที่ใบเบิก</th>
                <th style="width: {{ $showProd ? '13mm' : '6%' }};">รอบการผลิต</th>
                <th style="width: {{ $showProd ? '24mm' : '12%' }};">PRODUCT NO</th>
                <th style="width: {{ $showProd ? '16mm' : '8%' }};">LOT</th>
                <th style="width: {{ $showProd ? '18mm' : '9%' }};">น้ำหนักออเดอร์</th>
                <th style="width: {{ $showProd ? '12mm' : '7%' }};">TP</th>
                @if($showProd)
                <th style="width: 16mm;">Resin</th>
                <th style="width: 11mm;">Temp</th>
                <th style="width: 14mm;">CODE</th>
                <th style="width: 14mm;">Packaging</th>
                <th style="width: 11mm;">Batch</th>
                <th style="width: 16mm;">สุ่มตัวอย่าง</th>
                @endif
                {{-- Remark = คอลัมน์ยืดหยุ่น (ไม่กำหนดความกว้าง) รับพื้นที่ที่เหลือของตาราง
                     — กันคอลัมน์สุดท้ายหายเมื่อผลรวมความกว้างคอลัมน์อื่นชนขอบตาราง (2026-09-12) --}}
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @php $rownum = 0; @endphp
            @forelse($sec['blocks'] as $group)
                @php $machineLabel = $group['machine'] !== '' ? $group['machine'] : 'ไม่ระบุเครื่องจักร'; $groupSum = 0; @endphp
                <tr class="group-row">
                    <td colspan="{{ $showProd ? 17 : 11 }}">เครื่องจักร: {{ $machineLabel }}@if(!empty($group['speed_rpm'])) (Speed RPM: {{ $group['speed_rpm'] }})@endif</td>
                </tr>
                @foreach($group['items'] as $it)
                    @php $groupSum += (float) ($it->quantity ?? 0); $hasSteps = count($it->steps) > 0; @endphp
                    {{-- แถวขั้นตอน "สถานะวิธีการผลิต / การล้าง" (แสดงก่อนแถวผลิต) --}}
                    @foreach($it->steps as $s)
                        <tr class="step-row">
                            <td class="text-center">↳</td>
                            <td class="text-center">{{ $s->work_date ? \Carbon\Carbon::parse($s->work_date)->format('d/m/Y') : '-' }}</td>
                            <td colspan="{{ $showProd ? 15 : 9 }}">
                                ขั้นตอน: {{ $s->method_name ?: '-' }}
                                ({{ $s->start_time ? substr($s->start_time, 0, 5) : '--' }}–{{ $s->end_time ? substr($s->end_time, 0, 5) : '--' }})
                            </td>
                        </tr>
                    @endforeach
                    {{-- แถวผลิตสินค้า --}}
                    <tr class="prod-row {{ $hasSteps ? 'grouped' : '' }}">
                        <td class="text-center">{{ ++$rownum }}</td>
                        <td class="text-center col-inplan">{{ $it->inplan ? \Carbon\Carbon::parse($it->inplan)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $it->senddate ? \Carbon\Carbon::parse($it->senddate)->format('d/m/Y') : '' }}</td> {{-- Revise = senddate (กำหนดส่งทบทวน) --}}
                        <td>{{ $it->cust_name ?: '-' }}</td>
                        <td class="text-center">{{ $it->red_bill_code ?: '-' }}</td>
                        <td class="text-center">{{ $it->cycles ?: '-' }}</td> {{-- รอบการผลิต (tb_planning.cycles) --}}
                        <td>{{ $it->itemno ?: '-' }}</td>
                        <td class="text-center">{{ $it->lot ?: '-' }}</td>
                        <td class="text-end">{{ $it->quantity !== null ? number_format($it->quantity, 2) : '-' }}</td>
                        <td class="text-end">{{ $it->weight !== null ? number_format($it->weight, 2) : '' }}</td> {{-- TP = น้ำหนัก TP (Weight) --}}
                        @if($showProd)
                        <td>{{ $it->product_resin ?: '' }}</td> {{-- Resin (tb_products.resin) --}}
                        <td class="text-center">{{ $it->product_temp ?: '' }}</td> {{-- Temp (temp.Temp1 via tb_products.temp_id) --}}
                        <td>{{ $it->product_code_val ?: '' }}</td> {{-- CODE (tb_products.code) --}}
                        <td class="text-center">{{ $it->product_pack ?: '' }}</td> {{-- Packaging (tb_products.pack) --}}
                        <td class="text-center">{{ $it->product_batch ?: '' }}</td> {{-- Batch (tb_products.batch) --}}
                        <td>{{ $it->product_sampling ?: '' }}</td> {{-- สุ่มตัวอย่าง (tb_products.sampling) --}}
                        @endif
                        <td>{{ $it->planning_remark ?: '' }}</td> {{-- Remark = หมายเหตุวางแผน (tb_planning.planning_remark) --}}
                    </tr>
                @endforeach
                <tr class="sum-row">
                    <td colspan="6" class="text-end">รวม {{ $machineLabel }}</td>
                    <td class="text-center">{{ number_format($group['items']->count()) }} รายการ</td>
                    <td></td>
                    <td class="text-end">{{ number_format($groupSum, 2) }}</td>
                    <td colspan="{{ $showProd ? 8 : 2 }}"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showProd ? 17 : 11 }}" class="text-center" style="padding: 14px;">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
@endforeach
</body>
</html>
