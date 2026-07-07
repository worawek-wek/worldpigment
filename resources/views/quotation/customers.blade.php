{{-- ═══════════════════════════════════════════════════════════════════════
     MOCKUP — รายชื่อลูกค้า (ข้อมูลนิ่ง ไม่พึ่ง DB) สำหรับขึ้น server โชว์ก่อน
     ▶ ตอนจะกลับมาเขียนโปรแกรมต่อ: ลบไฟล์นี้ทิ้ง แล้วเปลี่ยนชื่อ
       customers.blade.php.bak → customers.blade.php (ของจริงที่ดึงจาก DB)
     ═══════════════════════════════════════════════════════════════════════ --}}
@php
    // ── ข้อมูลจำลอง (mockup) — แก้/เพิ่มแถวได้ที่นี่ ──
    $mockCustomers = [
        ['custid' => 'C0001', 'name' => 'บริษัท สยามพลาสติก จำกัด',      'nameEN' => 'Siam Plastic Co., Ltd.',        'qcount' => 14, 'last' => '05/07/2026'],
        ['custid' => 'C0002', 'name' => 'บริษัท ไทยพิกเมนต์ อินดัสทรี จำกัด', 'nameEN' => 'Thai Pigment Industry Co., Ltd.', 'qcount' => 9,  'last' => '02/07/2026'],
        ['custid' => 'C0003', 'name' => 'บริษัท มาสเตอร์แบทช์ เอเชีย จำกัด',  'nameEN' => 'Masterbatch Asia Co., Ltd.',     'qcount' => 7,  'last' => '28/06/2026'],
        ['custid' => 'C0004', 'name' => 'หจก. รุ่งเรืองการพิมพ์',           'nameEN' => 'Rungruang Printing Ltd., Part.', 'qcount' => 5,  'last' => '21/06/2026'],
        ['custid' => 'C0005', 'name' => 'บริษัท คัลเลอร์เทค (ประเทศไทย) จำกัด', 'nameEN' => 'ColorTech (Thailand) Co., Ltd.', 'qcount' => 4,  'last' => '15/06/2026'],
        ['custid' => 'C0006', 'name' => 'บริษัท โพลิเมอร์ พลัส จำกัด',        'nameEN' => 'Polymer Plus Co., Ltd.',        'qcount' => 3,  'last' => '09/06/2026'],
        ['custid' => 'C0007', 'name' => 'บริษัท อีสเทิร์น คอมพาวด์ จำกัด',    'nameEN' => 'Eastern Compound Co., Ltd.',    'qcount' => 2,  'last' => '30/05/2026'],
        ['custid' => 'C0008', 'name' => 'บริษัท กรีนแพ็ค อุตสาหกรรม จำกัด',   'nameEN' => 'Greenpack Industry Co., Ltd.',  'qcount' => 1,  'last' => '18/05/2026'],
    ];
@endphp

<div class="qcust">

    {{-- แบนเนอร์หัว --}}
    <div class="qcust-head px-4 py-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="ti ti-users fs-4"></i>
            <span class="fw-bold fs-5">ประวัติใบเสนอราคา — เลือกลูกค้า</span>
        </div>
        <div class="small opacity-75">ลูกค้าทั้งหมด {{ number_format(count($mockCustomers)) }} ราย — กดที่ลูกค้าเพื่อดูใบเสนอราคา</div>
    </div>

    <div class="p-3">
        {{-- ค้นหา (กรองฝั่ง client) --}}
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control" placeholder="ค้นหารหัส / ชื่อลูกค้า"
                oninput="filterCustomerRows(this.value)">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="qcustTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">ลำดับ</th>
                        <th style="width:120px;">รหัสลูกค้า</th>
                        <th>ชื่อลูกค้า</th>
                        <th class="text-end" style="width:130px;">ใบเสนอราคา</th>
                        <th class="text-center" style="width:110px;">ล่าสุด</th>
                        <th class="text-center" style="width:70px;">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mockCustomers as $i => $row)
                        <tr class="qcust-row" style="cursor:pointer;"
                            data-search="{{ strtolower($row['custid'] . ' ' . $row['name'] . ' ' . $row['nameEN']) }}"
                            onclick="quotationHistory('{{ $row['custid'] }}')">
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td><span class="badge bg-label-secondary">{{ $row['custid'] }}</span></td>
                            <td>
                                <strong>{{ $row['name'] }}</strong>
                                @if (!empty($row['nameEN']))
                                    <br><small class="text-muted">{{ $row['nameEN'] }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="badge bg-label-primary">{{ number_format($row['qcount']) }} ใบ</span>
                            </td>
                            <td class="text-center">
                                <small class="text-muted">{{ $row['last'] }}</small>
                            </td>
                            <td class="text-center">
                                <i class="ti ti-chevron-right text-muted"></i>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- แถวว่างเมื่อกรองแล้วไม่พบ --}}
        <div id="qcustNoResult" class="text-center py-4 text-muted d-none">
            <i class="ti ti-search-off fs-4 d-block mb-1 opacity-50"></i>ไม่พบลูกค้าที่ตรงกับคำค้น
        </div>
    </div>

</div>

<script>
    // กรองแถวลูกค้าฝั่ง client (ไม่ยิง server ซ้ำ)
    function filterCustomerRows(term){
        term = (term || '').trim().toLowerCase();
        var rows = document.querySelectorAll('#qcustTable .qcust-row');
        var shown = 0;
        rows.forEach(function(tr){
            var match = tr.getAttribute('data-search').indexOf(term) !== -1;
            tr.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        document.getElementById('qcustNoResult').classList.toggle('d-none', shown > 0 || rows.length === 0);
    }
</script>

<style>
    .qcust-head { background-color: #54BAB9; color: #fff; }
    .qcust-row:hover { background-color: #eafbfa; }
    #qcustTable thead th { white-space: nowrap; }
</style>
