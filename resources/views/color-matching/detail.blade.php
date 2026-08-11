@php
    // มี Testno = ใบส่ง ต.ย. (SD), ไม่มี = ใบนำส่งเทียบสี (CM)
    $isSD = !empty(trim((string) $row->Testno));

    // ── ธีมสีให้ตรงกับ modal form (CM เขียว / SD ม่วง) ──
    $accent     = $isSD ? '#6c5ce7' : '#54BAB9';   // เส้น/ขอบ
    $accentTxt  = $isSD ? '#4b3fb8' : '#2a8a89';   // ไอคอน + ข้อความหัวข้อกลุ่ม
    $cardBorder = $isSD ? '#dcd8f5' : '#cfe4e3';
    $cardHeadBg = $isSD ? '#efedfd' : '#e8f6f6';
    $cardHeadTx = $isSD ? '#3a309d' : '#1f5d5c';
    $cardTitle  = $isSD ? 'ใบส่ง ต.ย. ให้ลูกค้า' : 'ใบนำส่งเทียบสี';
    $modalTitle = $isSD ? 'รายละเอียดใบส่ง ต.ย.' : 'รายละเอียดใบนำส่งเทียบสี';
    $icon       = $isSD ? 'ti-package' : 'ti-file-text';

    $v = fn ($val) => trim((string) $val) === '' ? '—' : e($val);
    $d = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d/m/Y') : '—';

    // ── ผลการทดสอบตัวอย่างสี (เฉพาะใบส่ง ต.ย.) — testmain.TyResp (10/08/2569) ──
    $tr      = $row->testResult();
    $trBadge = '<span class="badge ' . $tr['class'] . '">' . e($tr['label']) . '</span>';

    // กลุ่มข้อมูล: title / icon / items[label => value]
    if ($isSD) {
        $sections = [
            ['อ้างอิงใบนำส่งเทียบสี', 'ti-link', [
                'เลขที่ใบนำส่งเทียบสี (อ้างอิง)' => $v($row->SendNo),
                'เลขที่ใบส่ง ต.ย.'               => $v($row->Testno),
            ]],
            ['ข้อมูลลูกค้า', 'ti-user-circle', [
                'รหัสลูกค้า'       => [$v($row->custno), 'col-12'],
                'ชื่อบริษัท (TH)'  => $v($row->custname),
                'ชื่อบริษัท (EN)'  => $v($row->custnameEN),
            ]],
            ['รายละเอียดผลิตภัณฑ์', 'ti-flask', [
                'รหัสสินค้า'      => $v($row->CodeNo),
                'Lot No.'        => $v($row->lotno),
                'น้ำหนัก (กรัม)'  => $v($row->Wage),
            ]],
            ['ข้อมูลการขาย / การยกเลิก', 'ti-alert-circle', [
                'Saleman Code'    => $v($row->sale),
                'สถานะ'           => $row->cancel == 1
                    ? '<span class="badge bg-label-danger">ยกเลิก</span>'
                    : '<span class="badge bg-label-success">ปกติ</span>',
                'สาเหตุที่ยกเลิก' => $v($row->CancalRes),
                'หมายเหตุ'        => $v($row->remark),
            ]],
            ['เอกสารปิดงาน', 'ti-receipt', [
                'เลขที่ใบส่ง ต.ย.' => $v($row->Testno),
                'วันที่เบิก'       => $d($row->TestDate),
            ]],
            ['ผลการทดสอบตัวอย่างสี', 'ti-clipboard-check', [
                'ผลการทดสอบ'    => [$trBadge, 'col-md-4'],
                'วันที่ทราบผล'   => [$d($row->Respdate), 'col-md-4'],
                'ระบุเพิ่มเติม'  => [$v($row->Resp), 'col-md-4'],
            ]],
        ];
    } else {
        $sections = [
            ['ข้อมูลลูกค้า', 'ti-user-circle', [
                'รหัสลูกค้า'       => [$v($row->custno), 'col-12'],
                'ชื่อบริษัท (TH)'  => $v($row->custname),
                'ชื่อบริษัท (EN)'  => $v($row->custnameEN),
            ]],
            ['ข้อมูลใบนำส่ง', 'ti-file-invoice', [
                'เลขที่ใบนำส่งเทียบสี' => $v($row->SendNo),
                'วันที่ส่งเทียบสี'      => $d($row->DsendT),
                'ประเภทงาน'           => $v($row->Type_Work),
                'ปรับแก้ไขครั้งที่'    => $v($row->Adj),
            ]],
            ['ข้อมูลสี', 'ti-palette', [
                'สี'                    => $v($row->color),
                'เม็ดที่ลูกค้าใช้'      => $v($row->CustPigment),
                'คุณสมบัติ'             => $v($row->pop),
                'นำไปทำชิ้นงาน (Model)' => $v($row->Model),
                'ยี่ห้อ'                => $v($row->Brand),
                'Chemical Safety'       => $v($row->ChemSafety),
            ]],
            ['วันที่ดำเนินการ และผู้เทียบสี', 'ti-calendar-event', [
                'Start Date'    => $d($row->startdate),
                'Sample Date'   => $d($row->SampleDate),
                'Ready Date'    => $d($row->ReadyDate),
                'Color Matcher' => $v($row->ColorMatcher),
            ]],
            ['รายละเอียดผลิตภัณฑ์', 'ti-flask', [
                'รายละเอียด'     => $v($row->TestDesc),
                'ประเภท'         => $v($row->testTypeLabel()),
                'Standard'       => $v($row->STD),
                'Resin (Match)'  => $v($row->ResinMatch),
                'PHR'            => $v($row->PHR),
            ]],
            ['การติดตามงาน', 'ti-clipboard-check', [
                'ผู้รับเอกสาร'       => $v($row->TNname),
                'เลขที่ใบรายงานผล'   => $v($row->rptno),
                'รอวัตถุดิบ'         => $v($row->RminWating),
                'กำหนดเทียบสีเสร็จ'  => $d($row->TNDate),
                'หมายเหตุ'           => $v($row->Mems),
            ]],
        ];
    }
@endphp

<div class="modal-content">

    <!-- Header -->
    <div class="modal-header">
        <h5 class="modal-title">
            <i class="ti {{ $icon }} me-1"></i>
            {{ $modalTitle }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <!-- Body : card เดียวข้างนอก, หัวข้อกลุ่มเป็น Label -->
    <div class="modal-body px-5 py-4" style="background-color: #f8f9fb;">

        <div class="card shadow-sm mb-0" style="border: 1px solid {{ $cardBorder }};">

            <div class="card-header py-2 px-3 d-flex align-items-center"
                style="background-color: {{ $cardHeadBg }}; border-bottom: 2px solid {{ $accent }}; border-radius: 0.375rem 0.375rem 0 0;">
                <h6 class="mb-0 fw-semibold" style="color: {{ $cardHeadTx }};">
                    <i class="ti {{ $icon }} me-1"></i>{{ $cardTitle }}
                </h6>
            </div>

            <div class="card-body p-4">
                @foreach ($sections as [$secTitle, $secIcon, $items])
                    <div class="{{ $loop->last ? 'mb-0' : 'mb-4 pb-3 border-bottom' }}">

                        {{-- Label หัวข้อกลุ่ม (เส้นซ้าย + ไอคอน) --}}
                        <div class="d-flex align-items-center mb-3 ps-2" style="border-left: 3px solid {{ $accent }};">
                            <i class="ti {{ $secIcon }} me-2" style="color: {{ $accentTxt }};"></i>
                            <span class="fw-semibold" style="font-size: 0.95rem; color: {{ $accentTxt }};">{{ $secTitle }}</span>
                        </div>

                        {{-- field : label เล็กด้านบน / ค่าด้านล่าง (2 คอลัมน์)
                             value เป็น array [ค่า, คลาสคอลัมน์] เพื่อกำหนดความกว้างเองได้ --}}
                        <div class="row g-3">
                            @foreach ($items as $label => $value)
                                @php
                                    $col = is_array($value) ? $value[1] : 'col-md-6';
                                    $val = is_array($value) ? $value[0] : $value;
                                @endphp
                                <div class="{{ $col }}">
                                    <div class="text-muted small mb-1">{{ $label }}</div>
                                    <div class="fw-medium">{!! $val !!}</div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endforeach
            </div>

        </div>

        {{-- ── ใบส่ง ต.ย. ที่อ้างอิงใบนำส่งเทียบสีนี้ (แสดงเฉพาะ CM) ── --}}
        @php $relatedSd = $relatedSd ?? collect(); @endphp
        @if (!$isSD)
            <div class="card shadow-sm mt-3" style="border: 1px solid #dcd8f5;">
                <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                    style="background-color: #efedfd; border-bottom: 2px solid #6c5ce7; border-radius: 0.375rem 0.375rem 0 0;">
                    <h6 class="mb-0 fw-semibold" style="color: #3a309d;">
                        <i class="ti ti-package me-1"></i>ใบส่ง ต.ย. ให้ลูกค้า
                    </h6>
                    <span class="badge bg-label-primary" style="background-color:#e7e3fb;color:#4b3fb8;">
                        {{ count($relatedSd) }} ใบ
                    </span>
                </div>

                <div class="card-body p-0">
                    @if (count($relatedSd))
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:50px;">#</th>
                                        <th>เลขที่ใบส่ง ต.ย.</th>
                                        <th>วันที่เบิก</th>
                                        <th>รหัสสินค้า</th>
                                        <th>Lot No.</th>
                                        <th>สถานะ</th>
                                        <th class="text-center" style="width:70px;">ดู</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($relatedSd as $i => $sd)
                                        <tr>
                                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                                            <td><strong class="text-primary">{{ $v($sd->Testno) }}</strong></td>
                                            <td>{{ $d($sd->TestDate) }}</td>
                                            <td>{{ $v($sd->CodeNo) }}</td>
                                            <td>{{ $v($sd->lotno) }}</td>
                                            <td>
                                                @if ($sd->cancel == 1)
                                                    <span class="badge bg-label-danger">ยกเลิก</span>
                                                @else
                                                    <span class="badge bg-label-success">ปกติ</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-icon btn-label-secondary"
                                                    title="ดูรายละเอียดใบส่ง ต.ย." onclick="viewDetail2('{{ $sd->id }}')">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-package-off fs-4 d-block mb-1 opacity-50"></i>
                            ยังไม่มีใบส่ง ต.ย. ที่อ้างอิงใบนำส่งเทียบสีนี้
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <!-- Footer -->
    <div class="modal-footer justify-content-between flex-wrap gap-2">
        {{-- ลบเอกสารนี้ — ป้ายกำกับยืนยัน = เลขที่ SD (Testno) หรือ CM (SendNo) --}}
        <button type="button" class="btn btn-label-danger"
            onclick='deleteFromDetail({{ $row->id }}, @json($isSD ? ($row->Testno ?: "") : ($row->SendNo ?: "")))'>
            <i class="ti ti-trash me-1"></i>
            {{ $isSD ? 'ลบใบส่ง ต.ย.' : 'ลบใบนำส่งเทียบสี' }}
        </button>
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
    </div>

</div>
