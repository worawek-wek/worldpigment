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

    // ช่องของกลุ่ม "รายละเอียดผลิตภัณฑ์" ของใบส่ง ต.ย. — ใช้ทั้งหน้า View ของ SD
    // และแถวที่กางออกในตาราง "ใบส่ง ต.ย. ให้ลูกค้า" ของหน้า CM (29/10/2569) ⇒ แก้ที่นี่ที่เดียว
    $sdProductItems = fn ($r) => [
        'รหัสสินค้า'      => $v($r->CodeNo),
        'Lot No.'        => $v($r->lotno),
        'น้ำหนัก (กรัม)'  => $v($r->Wage),
        // Resin / PHR (28/10/2569 ตามที่ผู้ใช้สั่ง)
        'Resin (Match)'  => $v($r->ResinMatch),
        'PHR'            => $v($r->PHR),
    ];

    // ไอคอน + โทนสีของแต่ละช่องใน popup รายละเอียดผลิตภัณฑ์ (ตาม mockup ผู้ใช้ 29/10/2569)
    // key = label ใน $sdProductItems · ช่องที่ไม่มีใน map ใช้ค่า default
    $sdTileMeta = [
        'รหัสสินค้า'      => ['ti-tag',       'purple'],
        'Lot No.'        => ['ti-packages',  'blue'],
        'น้ำหนัก (กรัม)'  => ['ti-box',       'green'],
        'Resin (Match)'  => ['ti-flask',     'pink'],
        'PHR'            => ['ti-barcode',   'indigo'],
    ];

    // กลุ่มข้อมูล: title / icon / items[label => value]
    if ($isSD) {
        $sections = [
            ['อ้างอิงใบนำส่งเทียบสี', 'ti-link', [
                'เลขที่ใบนำส่งเทียบสี (อ้างอิง)' => $v($row->SendNo),
                'เลขที่ใบส่ง ต.ย.'               => $v($row->Testno),
            ]],
            // กลุ่ม "ข้อมูลลูกค้า" เอาออกจาก View ใบส่ง ต.ย. (28/10/2569 ตามที่ผู้ใช้สั่ง)
            ['รายละเอียดผลิตภัณฑ์', 'ti-flask', $sdProductItems($row)],
            ['ข้อมูลการขาย / การยกเลิก', 'ti-alert-circle', [
                // Saleman Code เอาออก (28/10/2569 ตามที่ผู้ใช้สั่ง)
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
                'คำนวณ Znst+EBS ร่วมในชิ้นงาน' => $v($row->ZnstEBS),
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
                                        {{-- เลขที่ใบส่ง ต.ย. / วันที่เบิก / รหัสสินค้า เอาออก → แสดง รายละเอียด / Resin / PHR แทน (28/10/2569 ตามที่ผู้ใช้สั่ง) --}}
                                        <th>Lot No.</th>
                                        <th>รายละเอียด</th>
                                        <th>Resin (Match)</th>
                                        <th class="text-end">PHR</th>
                                        <th>สถานะ</th>
                                        {{-- ปุ่มดินสอ → ฟอร์มแก้ไขใบส่ง ต.ย. (29/10/2569 ตามที่ผู้ใช้สั่ง) --}}
                                        <th class="text-center" style="width:70px;">แก้ไข</th>
                                        {{-- คอลัมน์ "ดู" ปิดไว้ (28/10/2569 ตามที่ผู้ใช้สั่ง) — เปิดคืน: เอา @if(false) ออกทั้งหัวและแถว --}}
                                        @if (false)
                                        <th class="text-center" style="width:70px;">ดู</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- กดแถว = popup "รายละเอียดผลิตภัณฑ์" ของใบนั้น (29/10/2569 ตามที่ผู้ใช้สั่ง — ผังตาม mockup)
                                         เนื้อหาอยู่ใน <template class="sd-pop"> ของแถว · Swal ลอยเหนือ modal ได้ (inc_header ตั้ง z-index 9999)
                                         CSS อยู่ใน <style> .sdpop ท้ายไฟล์ · ปุ่มปิด/กากบาทวาดเองใน markup (เรียก Swal.close()) --}}
                                    @foreach ($relatedSd as $i => $sd)
                                        <tr class="sd-row" role="button" title="คลิกเพื่อดูรายละเอียดผลิตภัณฑ์"
                                            onclick="Swal.fire({ html: $(this).find('template.sd-pop').html(), width: 780, padding: 0, showConfirmButton: false, customClass: { popup: 'sdpop-swal', htmlContainer: 'sdpop-html' } });">
                                            <td class="text-center text-muted">
                                                {{ $i + 1 }}
                                                <template class="sd-pop">
                                                    <div class="sdpop text-start">
                                                        <div class="sdpop-head">
                                                            <span class="sdpop-head-icon"><i class="ti ti-file-text"></i></span>
                                                            <span class="sdpop-head-sep"></span>
                                                            <span class="sdpop-head-title">รายละเอียดผลิตภัณฑ์</span>
                                                            <span class="sdpop-head-badge">ใบส่ง ต.ย. {!! $v($sd->Testno) !!}</span>
                                                            <button type="button" class="sdpop-x" onclick="Swal.close()" aria-label="ปิด"><i class="ti ti-x"></i></button>
                                                        </div>
                                                        <div class="sdpop-body">
                                                            <div class="row g-3">
                                                                @foreach ($sdProductItems($sd) as $label => $val)
                                                                    @php [$tIcon, $tTone] = $sdTileMeta[$label] ?? ['ti-info-circle', 'indigo']; @endphp
                                                                    <div class="col-md-6">
                                                                        <div class="sdpop-tile sdpop-{{ $tTone }}">
                                                                            <span class="sdpop-tile-icon"><i class="ti {{ $tIcon }}"></i></span>
                                                                            <div class="min-w-0">
                                                                                <div class="sdpop-tile-label">{{ $label }}</div>
                                                                                <div class="sdpop-tile-value">{!! $val !!}</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div class="sdpop-foot">
                                                                <button type="button" class="sdpop-btn" onclick="Swal.close()">
                                                                    <i class="ti ti-circle-check me-1"></i>ปิด
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </td>
                                            <td>{{ $v($sd->lotno) }}</td>
                                            {{-- $v() escape ให้แล้ว จึงใช้ {!! !!} กัน & กลายเป็น &amp; --}}
                                            <td>{!! $v($sd->TestDesc) !!}</td>
                                            <td>{!! $v($sd->ResinMatch) !!}</td>
                                            <td class="text-end">{{ $v($sd->PHR) }}</td>
                                            <td>
                                                @if ($sd->cancel == 1)
                                                    <span class="badge bg-label-danger">ยกเลิก</span>
                                                @else
                                                    <span class="badge bg-label-success">ปกติ</span>
                                                @endif
                                            </td>
                                            {{-- stopPropagation: ไม่ให้คลิกปุ่มไปเปิด popup ของแถวด้วย --}}
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-icon btn-label-primary"
                                                    title="แก้ไขใบส่ง ต.ย."
                                                    onclick="event.stopPropagation(); editSdFromDetail('{{ $sd->id }}', '{{ $row->id }}');">
                                                    <i class="ti ti-pencil"></i>
                                                </button>
                                            </td>
                                            @if (false)
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-icon btn-label-secondary"
                                                    title="ดูรายละเอียดใบส่ง ต.ย." onclick="viewDetail2('{{ $sd->id }}')">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </td>
                                            @endif
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
    <div class="modal-footer justify-content-end flex-wrap gap-2">
        {{-- ปุ่มลบเอาออกจากหน้า View ทั้ง CM และ SD (28/10/2569 ตามที่ผู้ใช้สั่ง) — ยังลบได้จากฟอร์มแก้ไข (modal-cm / modal-sd) --}}
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
    </div>

</div>

{{-- popup รายละเอียดผลิตภัณฑ์ของใบส่ง ต.ย. (Swal) — ผังตาม mockup ผู้ใช้ 29/10/2569 --}}
<style>
    .sdpop-swal { border-radius: 1.25rem !important; overflow: hidden; }
    .sdpop-html { margin: 0 !important; padding: 0 !important; overflow: visible !important; }
    .sdpop-head {
        display: flex; align-items: center; gap: .9rem;
        padding: 1.1rem 1.5rem;
        background: linear-gradient(120deg, #f6f4ff 0%, #ffffff 55%, #f3f1ff 100%);
        border-bottom: 1px solid #ebe8f7;
    }
    .sdpop-head-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #8b7cf6, #6c5ce7); color: #fff; font-size: 1.4rem;
        box-shadow: 0 4px 12px rgba(108, 92, 231, .3);
    }
    .sdpop-head-sep { width: 1px; height: 26px; background: #d6d1f2; }
    .sdpop-head-title { font-size: 1.35rem; font-weight: 700; color: #2b2560; }
    .sdpop-head-badge {
        padding: .3rem .85rem; border-radius: 999px;
        background: #ece9fd; color: #4b3fb8; font-weight: 600; font-size: .85rem;
    }
    .sdpop-x {
        margin-left: auto; border: 0; background: transparent; color: #6b6f86;
        font-size: 1.5rem; line-height: 1; padding: .25rem;
    }
    .sdpop-x:hover { color: #2b2560; }
    .sdpop-body { padding: 1.25rem 1.5rem 1.25rem; }
    .sdpop-tile {
        display: flex; align-items: center; gap: 1rem; height: 100%;
        padding: 1rem 1.1rem; border-radius: 14px;
        border: 1px solid var(--t-border); background: var(--t-bg);
    }
    .sdpop-tile-icon {
        width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--t-icon-bg); color: var(--t-icon); font-size: 1.5rem;
    }
    .sdpop-tile-label { color: #5d6178; font-size: .9rem; margin-bottom: .15rem; }
    .sdpop-tile-value { color: #1f2346; font-size: 1.35rem; font-weight: 700; word-break: break-word; }
    .sdpop-purple { --t-bg: #f8f7fd; --t-border: #ebe8f7; --t-icon-bg: #ece8fc; --t-icon: #6c5ce7; }
    .sdpop-blue   { --t-bg: #f7f9fd; --t-border: #e6ebf5; --t-icon-bg: #e3ecfb; --t-icon: #2f6fe0; }
    .sdpop-green  { --t-bg: #f2fbf7; --t-border: #dcf0e6; --t-icon-bg: #dcf3e8; --t-icon: #2fa37a; }
    .sdpop-pink   { --t-bg: #fdf5fa; --t-border: #f4e2ee; --t-icon-bg: #f8e3f0; --t-icon: #b04fc0; }
    .sdpop-indigo { --t-bg: #f7f9fd; --t-border: #e6ebf5; --t-icon-bg: #e3eafb; --t-icon: #3b5bdb; }
    .sdpop-foot { border-top: 1px solid #eceef4; margin-top: 1.25rem; padding-top: 1rem; text-align: center; }
    .sdpop-btn {
        border: 0; border-radius: 10px; padding: .6rem 3rem;
        background: linear-gradient(135deg, #7b6cf0, #5a4bd6); color: #fff;
        font-weight: 600; font-size: 1rem; box-shadow: 0 4px 12px rgba(90, 75, 214, .3);
    }
    .sdpop-btn:hover { filter: brightness(1.05); }
</style>
