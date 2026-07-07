{{-- ═══════════════════════════════════════════════════════════════════════
     MOCKUP — ประวัติใบเสนอราคาของลูกค้า (ข้อมูลนิ่ง ไม่พึ่ง DB) สำหรับโชว์
     ▶ ตอนจะกลับมาเขียนโปรแกรมต่อ: ลบไฟล์นี้ทิ้ง แล้วเปลี่ยนชื่อ
       history.blade.php.bak → history.blade.php (ของจริงที่ดึงจาก DB)
     ═══════════════════════════════════════════════════════════════════════ --}}
@php
    // ── ชื่อลูกค้าจำลอง (map ให้ตรงกับหน้ารายชื่อลูกค้า) ──
    $mockNames = [
        'C0001' => ['บริษัท สยามพลาสติก จำกัด', 'Siam Plastic Co., Ltd.'],
        'C0002' => ['บริษัท ไทยพิกเมนต์ อินดัสทรี จำกัด', 'Thai Pigment Industry Co., Ltd.'],
        'C0003' => ['บริษัท มาสเตอร์แบทช์ เอเชีย จำกัด', 'Masterbatch Asia Co., Ltd.'],
        'C0004' => ['หจก. รุ่งเรืองการพิมพ์', 'Rungruang Printing Ltd., Part.'],
        'C0005' => ['บริษัท คัลเลอร์เทค (ประเทศไทย) จำกัด', 'ColorTech (Thailand) Co., Ltd.'],
        'C0006' => ['บริษัท โพลิเมอร์ พลัส จำกัด', 'Polymer Plus Co., Ltd.'],
        'C0007' => ['บริษัท อีสเทิร์น คอมพาวด์ จำกัด', 'Eastern Compound Co., Ltd.'],
        'C0008' => ['บริษัท กรีนแพ็ค อุตสาหกรรม จำกัด', 'Greenpack Industry Co., Ltd.'],
    ];
    $custName   = $mockNames[$custid][0] ?? ($cust->name ?? '—');
    $custNameEN = $mockNames[$custid][1] ?? ($cust->nameEN ?? '');

    // ── ใบเสนอราคาจำลองของลูกค้ารายนี้ ──
    $mockQuotations = [
        ['qno' => 'WH690142', 'date' => '05/07/2026', 'revise' => null,         'pdtype' => 'MB', 'items' => 6, 'net' => 128500.00],
        ['qno' => 'WH690118', 'date' => '21/06/2026', 'revise' => '02/07/2026', 'pdtype' => 'MP', 'items' => 4, 'net' => 86200.50],
        ['qno' => 'WH690095', 'date' => '30/05/2026', 'revise' => null,         'pdtype' => 'CP', 'items' => 9, 'net' => 213750.00],
        ['qno' => 'WH690061', 'date' => '12/04/2026', 'revise' => null,         'pdtype' => 'MB', 'items' => 2, 'net' => 41000.00],
        ['qno' => 'WH690028', 'date' => '18/02/2026', 'revise' => null,         'pdtype' => 'RB', 'items' => 5, 'net' => 97300.75],
    ];
@endphp

<div class="qhist">

    {{-- แบนเนอร์หัว: ข้อมูลลูกค้า --}}
    <div class="qhist-head px-4 py-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="ti ti-history fs-4"></i>
            <span class="fw-bold fs-5">ประวัติใบเสนอราคา</span>
        </div>
        <div class="small opacity-75">
            <span class="badge bg-white text-dark me-1">{{ $custid }}</span>
            {{ $custName }}
            @if (!empty($custNameEN))
                <span class="opacity-75">({{ $custNameEN }})</span>
            @endif
            <span class="ms-2">— ทั้งหมด {{ number_format(count($mockQuotations)) }} ใบ</span>
        </div>
    </div>

    <div class="p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">ลำดับ</th>
                        <th>เลขที่ / วันที่</th>
                        <th class="text-center">ชนิดสินค้า</th>
                        <th class="text-end">จำนวนรายการ</th>
                        <th class="text-end">มูลค่ารวม</th>
                        <th class="text-center" style="width:80px;">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mockQuotations as $i => $row)
                        <tr>
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td>
                                <strong class="text-primary">{{ $row['qno'] }}</strong>
                                <br>
                                <small class="text-muted">{{ $row['date'] }}</small>
                                @if ($row['revise'])
                                    <span class="badge bg-label-danger ms-1">ปรับราคา {{ $row['revise'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-info">{{ $row['pdtype'] }}</span>
                            </td>
                            <td class="text-end">{{ number_format($row['items']) }}</td>
                            <td class="text-end">{{ number_format($row['net'], 2) }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon btn-label-secondary" title="ดูรายละเอียด"
                                    onclick="quotationView('{{ $row['qno'] }}')">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .qhist-head {
        background-color: #54BAB9;
        color: #fff;
    }
</style>
