{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: รายละเอียดการกำหนดราคา (อ่านอย่างเดียว) — 15/10/2569           --}}
{{-- Trigger: showSaleinfoView(id) จากปุ่มตาในตารางรายการ                    --}}
{{-- ผังตาม mockup ที่ผู้ใช้ส่งมา: การ์ดแยกกลุ่ม + ไอคอนสีบนหัวการ์ด          --}}
{{--   ข้อมูลลูกค้า | ข้อมูลสินค้า · ราคา และเงื่อนไข · บรรจุภัณฑ์             --}}
{{--   · ประวัติการปรับราคา · คำนวนราคา · ราคากลุ่ม                           --}}
{{-- ค่าเป็นข้อความทั้งหมด (ไม่ใช่ input) · id ขึ้นต้น sv_ · เติมด้วย JS       --}}
{{-- ใน index.blade.php (showSaleinfoView)                                   --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<style>
    #saleinfoViewModal .modal-content { border-radius: .75rem; overflow: hidden; }
    #saleinfoViewModal .sv-head { padding: 1rem 1.5rem; border-bottom: 1px solid #eef0f4; display: flex; align-items: center; gap: .75rem; }
    #saleinfoViewModal .sv-head-icon { width: 40px; height: 40px; border-radius: .6rem; background: #3b6fd8; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0; }
    #saleinfoViewModal .sv-head-title { font-size: 1.15rem; font-weight: 700; color: #233044; margin: 0; }
    #saleinfoViewModal .sv-head-sub { font-size: .8rem; color: #8a93a3; }
    #saleinfoViewModal .modal-body { background: #f5f7fb; padding: 1rem 1.25rem; }

    /* การ์ดกลุ่ม */
    #saleinfoViewModal .sv-card { background: #fff; border: 1px solid #e4e8ef; border-radius: .6rem; padding: .85rem 1rem; height: 100%; }
    #saleinfoViewModal .sv-card-title { display: flex; align-items: center; gap: .6rem; font-weight: 700; color: #233044; margin-bottom: .75rem; }
    #saleinfoViewModal .sv-ico { width: 30px; height: 30px; border-radius: .45rem; color: #fff; display: inline-flex;
        align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    #saleinfoViewModal .sv-ico-blue   { background: #3b6fd8; }
    #saleinfoViewModal .sv-ico-sky    { background: #3fa2e6; }
    #saleinfoViewModal .sv-ico-green  { background: #2fae7a; }
    #saleinfoViewModal .sv-ico-orange { background: #f08c2e; }
    #saleinfoViewModal .sv-ico-purple { background: #8a63d2; }

    /* ตาราง label : ค่า */
    #saleinfoViewModal .sv-kv { background: #f7f9fc; border-radius: .5rem; padding: .6rem .85rem; }
    #saleinfoViewModal .sv-kv-row { display: flex; gap: .75rem; padding: .25rem 0; font-size: .875rem; }
    #saleinfoViewModal .sv-kv-label { width: 120px; flex-shrink: 0; color: #6b7587; }
    #saleinfoViewModal .sv-kv-val { font-weight: 600; color: #233044; word-break: break-word; }

    /* ช่องย่อย (tile) */
    #saleinfoViewModal .sv-tile { border: 1px solid #e8ecf2; border-radius: .5rem; padding: .65rem .85rem; height: 100%; background: #fff; }
    #saleinfoViewModal .sv-tile-label { font-size: .8rem; color: #6b7587; margin-bottom: .2rem; }
    #saleinfoViewModal .sv-tile-big { font-size: 1.05rem; font-weight: 700; color: #233044; }
    #saleinfoViewModal .sv-tile-green { background: #eaf7f0; border-color: #c9ead7; }
    #saleinfoViewModal .sv-tile-green .sv-tile-head { color: #1f8a5b; font-weight: 700; font-size: .85rem; margin-bottom: .35rem; }

    /* กล่องค่าข้อความ (แทน input) */
    #saleinfoViewModal .sv-box { border: 1px solid #e2e6ed; border-radius: .4rem; background: #fbfcfe; padding: .4rem .65rem;
        min-height: 2.1rem; font-size: .875rem; font-weight: 500; color: #233044; word-break: break-word; }

    /* ราคาคำนวณ */
    #saleinfoViewModal .sv-price { border: 1px solid #e8ecf2; border-radius: .45rem; padding: .4rem .7rem; background: #fff; }
    #saleinfoViewModal .sv-price-label { font-size: .78rem; color: #6b7587; }
    #saleinfoViewModal .sv-price-row { display: flex; justify-content: space-between; align-items: baseline; }
    #saleinfoViewModal .sv-baht { color: #f08c2e; font-weight: 700; font-size: .85rem; }
    #saleinfoViewModal .sv-price-val { font-weight: 700; color: #233044; font-variant-numeric: tabular-nums; }
    #saleinfoViewModal .sv-colorbox { border: 1.5px dashed #f1a3a3; background: #fdeeee; border-radius: .5rem; height: 100%;
        display: flex; }
    #saleinfoViewModal .sv-colorbox > div { flex: 1; text-align: center; display: flex; flex-direction: column; justify-content: center; padding: .5rem; }
    #saleinfoViewModal .sv-colorbox > div + div { border-left: 1px solid #f4c4c4; }

    #saleinfoViewModal .sv-history thead th { background: #f3f5f9; font-size: .8rem; color: #4b5566; font-weight: 600; }
    #saleinfoViewModal .sv-history td { font-size: .85rem; }
    #saleinfoViewModal .sv-history .badge-current { background: #f08c2e; color: #fff; border-radius: 1rem; padding: .1rem .5rem;
        font-size: .7rem; margin-left: .5rem; }
    #saleinfoViewModal .sv-note-warn { color: #c0392b; }
</style>

<div class="modal fade" id="saleinfoViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="sv-head">
                <div class="sv-head-icon"><i class="ti ti-tag"></i></div>
                <div class="flex-grow-1">
                    <h5 class="sv-head-title">รายละเอียดการกำหนดราคา</h5>
                    <div class="sv-head-sub">ข้อมูลการกำหนดราคาสินค้า และรายละเอียดต่าง ๆ ที่เกี่ยวข้อง</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ─── ข้อมูลลูกค้า | ข้อมูลสินค้า ─── --}}
                <div class="row g-3 mb-3">
                    <div class="col-lg-6">
                        <div class="sv-card">
                            <div class="sv-card-title"><span class="sv-ico sv-ico-blue"><i class="ti ti-user"></i></span>ข้อมูลลูกค้า</div>
                            <div class="sv-kv">
                                <div class="sv-kv-row"><span class="sv-kv-label">รหัสลูกค้า</span><span class="sv-kv-val sv-val" id="sv_custno">—</span></div>
                                <div class="sv-kv-row"><span class="sv-kv-label">ชื่อลูกค้า</span><span class="sv-kv-val sv-val" id="sv_cust_name">—</span></div>
                                <div class="sv-kv-row"><span class="sv-kv-label">ที่อยู่</span><span class="sv-kv-val sv-val" id="sv_cust_road">—</span></div>
                                <div class="sv-kv-row"><span class="sv-kv-label">เงื่อนไข</span><span class="sv-kv-val sv-val" id="sv_cust_term">—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="sv-card">
                            <div class="sv-card-title"><span class="sv-ico sv-ico-sky"><i class="ti ti-box"></i></span>ข้อมูลสินค้า</div>
                            <div class="sv-kv">
                                <div class="sv-kv-row"><span class="sv-kv-label">รหัสสินค้า</span><span class="sv-kv-val sv-val" id="sv_itemno">—</span></div>
                                <div class="sv-kv-row"><span class="sv-kv-label">ชื่อสินค้า</span><span class="sv-kv-val sv-val" id="sv_stcode">—</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── ราคา และเงื่อนไข ─── --}}
                <div class="sv-card mb-3">
                    <div class="sv-card-title"><span class="sv-ico sv-ico-green"><i class="ti ti-tag"></i></span>ราคา และเงื่อนไข</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="sv-tile d-flex gap-2">
                                <i class="ti ti-calendar text-primary fs-4"></i>
                                <div>
                                    <div class="sv-tile-label">วันที่เริ่มซื้อ</div>
                                    <div class="sv-tile-big sv-val" id="sv_date">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sv-tile d-flex gap-2">
                                <i class="ti ti-star-filled fs-4" style="color: #f08c2e;"></i>
                                <div>
                                    <div class="sv-tile-label">ราคา</div>
                                    <div class="sv-tile-big sv-val" id="sv_price">—</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            {{-- สั่งซื้อล่าสุด (เฉพาะเบอร์นี้) — ไม่พบ = เกิน 3 ปี (กติกาเดียวกับฟอร์ม) --}}
                            <div class="sv-tile sv-tile-green">
                                <div class="sv-tile-head"><i class="ti ti-chart-bar me-1"></i>สั่งซื้อล่าสุด (เฉพาะเบอร์นี้)</div>
                                <div class="sv-kv-row py-0"><span class="sv-kv-label">Lot ล่าสุด</span><span class="sv-kv-val sv-val" id="sv_last_lot">—</span></div>
                                <div class="sv-kv-row py-0"><span class="sv-kv-label">วันที่ส่งมอบล่าสุด</span><span class="sv-kv-val sv-val" id="sv_last_dv">—</span></div>
                                <div class="small fw-semibold text-danger text-end" id="sv_last_note"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex gap-2 align-items-start">
                                <span class="small fw-semibold text-nowrap pt-2"><i class="ti ti-note me-1 text-muted"></i>หมายเหตุ 1</span>
                                <div class="sv-box flex-grow-1 sv-val" id="sv_rem1">—</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 align-items-start">
                                <span class="small fw-semibold text-nowrap pt-2"><i class="ti ti-note me-1 text-muted"></i>หมายเหตุ 2</span>
                                <div class="sv-box flex-grow-1 sv-val" id="sv_rem2">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── บรรจุภัณฑ์ และผู้อนุมัติ ─── --}}
                <div class="sv-card mb-3">
                    <div class="sv-card-title"><span class="sv-ico sv-ico-orange"><i class="ti ti-package"></i></span>บรรจุภัณฑ์ และผู้อนุมัติ</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="sv-tile">
                                <div class="sv-tile-label fw-semibold text-body">ระบุข้างบรรจุภัณฑ์</div>
                                <div class="sv-box sv-val" id="sv_packrem">—</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sv-tile">
                                <div class="sv-tile-label fw-semibold text-body">Label DB <span class="fw-normal text-muted">(ข้อความบนฉลาก)</span></div>
                                <div class="sv-box sv-val" id="sv_label">—</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sv-tile">
                                <div class="sv-tile-label fw-semibold text-body">ผู้บริหาร (ผู้อนุมัติราคา)</div>
                                <div class="sv-box sv-val" id="sv_author">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── ประวัติการปรับราคา (ไม่รวมแถวที่กำลังดู) ─── --}}
                <div class="sv-card mb-3">
                    <div class="sv-card-title">
                        <span class="sv-ico sv-ico-purple"><i class="ti ti-history"></i></span>ประวัติการปรับราคา
                        <span class="badge bg-label-secondary ms-1" id="sv_history_count">0 รายการ</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 sv-history">
                            <thead>
                                <tr>
                                    <th>วันที่เริ่มซื้อ</th>
                                    <th>รหัสสินค้า</th>
                                    <th class="text-end">ราคา</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody id="sv_history_body"></tbody>
                        </table>
                    </div>
                </div>

                {{-- ─── คำนวนราคา ─── --}}
                <div class="sv-card mb-3">
                    <div class="sv-card-title">
                        <span class="sv-ico sv-ico-blue"><i class="ti ti-calculator"></i></span>คำนวนราคา
                        <span class="ms-auto small fw-normal text-muted">คำนวณจากรหัสสินค้า</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-lg-8">
                            <div class="row g-2">
                                @foreach ([['ราคา 1', 'sv_p1'], ['ราคา 2', 'sv_p2'], ['ราคา 3', 'sv_p3'], ['DB 3 - 4 Kg.', 'sv_db34'], ['DB 1 - 2 Kg.', 'sv_db12']] as [$lbl, $id])
                                    <div class="col-md-4">
                                        <div class="sv-price">
                                            <div class="sv-price-label">{{ $lbl }}</div>
                                            <div class="sv-price-row">
                                                <span class="sv-baht">฿</span>
                                                <span class="sv-price-val sv-val" id="{{ $id }}">—</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- ค่าสีทั้งสิ้น / % สี — ยังไม่รู้สูตร (wip เหมือนในฟอร์ม) --}}
                        <div class="col-lg-4">
                            <div class="sv-colorbox">
                                <div><div class="small text-danger">ค่าสีทั้งสิ้น</div><div class="fw-bold text-danger">—</div></div>
                                <div><div class="small text-danger">% สี</div><div class="fw-bold text-danger">—</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="small text-muted mt-2" id="sv_price_note"></div>
                </div>

                {{-- ─── ราคากลุ่ม (zcolorrate) ─── --}}
                <div class="sv-card">
                    <div class="sv-card-title">
                        <span class="sv-ico sv-ico-purple"><i class="ti ti-stack-2"></i></span>ราคากลุ่ม
                        <span class="small fw-normal text-danger d-none" id="sv_group_none">รหัสสินค้านี้ไม่มีในกลุ่มราคา</span>
                    </div>
                    <div class="row g-2">
                        @foreach (\App\Http\Controllers\PriceApprovalController::priceGroups() as $g)
                            <div class="col-md-4">
                                <div class="sv-price">
                                    <div class="sv-price-label">{{ $g['label'] }}</div>
                                    <div class="sv-price-row">
                                        <span class="sv-baht">฿</span>
                                        <span class="sv-price-val sv-val" id="sv_group_{{ $g['group'] }}">—</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>ปิด
                </button>
            </div>

        </div>
    </div>
</div>
