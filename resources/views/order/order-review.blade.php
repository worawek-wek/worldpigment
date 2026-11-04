{{-- ═══════════════════════════════════════════════════════════════
     ใบทบทวนคำสั่งซื้อ (03/11/2569) — แปลงจากฟอร์ม Access "OrderPRT"
     เลือกวันที่ Order + แผนกที่ผลิต → Refresh → เดินดูทีละรายการ (suborder) → พิมพ์
     เฉพาะใบที่อนุมัติแล้ว · ข้อมูลจาก OrderReviewController::data (order/order-review/data)
     ปุ่มพิมพ์ → OrderReviewController::print (order/order-review/print) A4 แนวตั้ง ฟอร์มอยู่ครึ่งบน
     ═══════════════════════════════════════════════════════════════ --}}
<style>
    #orderReviewModal .orv-rec .form-control[readonly] { background: #f6f7f9; }
    #orderReviewModal .orv-rec .form-check-input:disabled { opacity: 1; }
    #orderReviewModal .orv-rec .form-check-input:disabled ~ .form-check-label { opacity: 1; }
    #orderReviewModal #orv_remark { min-height: 132px; resize: none; }
    #orderReviewModal .orv-nav .form-control { width: 70px; text-align: center; }
</style>

<div class="modal modalHeadDecor fade" id="orderReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">ใบทบทวนคำสั่งซื้อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ── เงื่อนไข ── --}}
                <div class="small text-muted mb-2">เพื่อเลือกพิมพ์ "ใบทบทวนฯ" เฉพาะที่อนุมัติแล้ว</div>
                <div class="row gx-3 gy-2 of-form align-items-center" style="--of-lw: 96px;">
                    <div class="col-md-5 of-row">
                        <label class="form-label" for="orv_date">วันที่ Order</label>
                        <div class="of-ctl">
                            <input type="text" id="orv_date" class="form-control form-control-sm" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4 of-row">
                        <label class="form-label" for="orv_dept">แผนกที่ผลิต</label>
                        <div class="of-ctl">
                            <select id="orv_dept" class="form-select form-select-sm no-enhance">
                                <option value="">ทุกแผนก</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-sm btn-primary w-100" onclick="orvLoad()">
                            <i class="ti ti-refresh me-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <hr class="my-3">

                {{-- ── ระเบียน (อ่านอย่างเดียว) ── --}}
                <div class="row gx-4 gy-2 of-form orv-rec" style="--of-lw: 96px;">
                    <div class="col-md-6">
                        <div class="row gy-2">
                            <div class="col-12 of-row"><label class="form-label">วัน-เวลารับ</label><div class="of-ctl"><input type="text" id="orv_mdate" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Orderno</label><div class="of-ctl"><input type="text" id="orv_orderno" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Company</label><div class="of-ctl"><input type="text" id="orv_company" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Custno</label><div class="of-ctl"><input type="text" id="orv_custno" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Custname</label><div class="of-ctl"><input type="text" id="orv_custname" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Emp</label><div class="of-ctl"><input type="text" id="orv_emp" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">PO</label><div class="of-ctl"><input type="text" id="orv_po" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Sale</label><div class="of-ctl"><input type="text" id="orv_sale" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 d-flex flex-wrap gap-3 pt-1">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="orv_send" disabled><label class="form-check-label" for="orv_send">ส่งก่อนได้</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="orv_rp" disabled><label class="form-check-label" for="orv_rp">RP</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="orv_spec" disabled><label class="form-check-label" for="orv_spec">Spec</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="orv_cer" disabled><label class="form-check-label" for="orv_cer">Cer</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row gy-2">
                            <div class="col-12 of-row"><label class="form-label">รหัสสินค้า</label><div class="of-ctl"><input type="text" id="orv_itemno" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Lot No</label><div class="of-ctl"><input type="text" id="orv_lotno" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Stock</label><div class="of-ctl"><input type="text" id="orv_stock" class="form-control form-control-sm text-end" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">Production</label><div class="of-ctl"><input type="text" id="orv_prod" class="form-control form-control-sm text-end" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">price</label><div class="of-ctl"><div class="input-group input-group-sm"><input type="text" id="orv_price" class="form-control text-end" readonly><span class="input-group-text">Baht</span></div></div></div>
                            <div class="col-12 of-row"><label class="form-label">กำหนดส่ง</label><div class="of-ctl"><input type="text" id="orv_senddate" class="form-control form-control-sm" readonly></div></div>
                            <div class="col-12 of-row"><label class="form-label">remark</label><div class="of-ctl"><textarea id="orv_remark" class="form-control form-control-sm" readonly></textarea></div></div>
                        </div>
                    </div>
                </div>

                <div id="orv_empty" class="text-center text-muted py-3 d-none">ไม่พบใบสั่งซื้อที่อนุมัติแล้วตามเงื่อนไข</div>
            </div>

            <div class="modal-footer justify-content-between">
                {{-- ตัวเดินระเบียน --}}
                <div class="d-flex align-items-center gap-1 orv-nav">
                    <span class="small me-1">Record:</span>
                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="orvGo(0)"><i class="ti ti-player-skip-back"></i></button>
                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="orvGo(ORV.i - 1)"><i class="ti ti-chevron-left"></i></button>
                    <input type="text" id="orv_pos" class="form-control form-control-sm" autocomplete="off">
                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="orvGo(ORV.i + 1)"><i class="ti ti-chevron-right"></i></button>
                    <button type="button" class="btn btn-sm btn-label-secondary" onclick="orvGo(ORV.rows.length - 1)"><i class="ti ti-player-skip-forward"></i></button>
                    <span class="small ms-1">of <span id="orv_total">0</span></span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="orvPrint()">
                        <i class="ti ti-printer me-1"></i> พิมพ์ใบทบทวนฯ
                    </button>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // ⚠ include ก่อน layout/inc_js — เรียก jQuery ตอนฟังก์ชันถูกเรียกเท่านั้น ส่วนการผูก event รอ DOMContentLoaded
    var ORV_DATA_URL = "{{ route('order.review.data') }}";
    var ORV_PRINT_URL = "{{ route('order.review.print') }}";
    var ORV = { rows: [], i: -1 };

    function orderReviewOpen(){
        var el = document.getElementById('orv_date');
        if (!el._flatpickr) {
            flatpickr(el, { dateFormat: 'd/m/Y', allowInput: true, disableMobile: true, static: true });
        }
        if (!el.value) el._flatpickr.setDate(new Date());
        $('#orderReviewModal').modal('show');
        orvLoad();
    }

    function orvLoad(){
        $.get(ORV_DATA_URL, { date: $('#orv_date').val(), dept: $('#orv_dept').val() || '' })
            .done(function(res){
                ORV.rows = res.rows || [];
                orvGo(0);
            })
            .fail(function(){
                ORV.rows = [];
                orvGo(0);
                Swal.fire({ icon: 'error', title: 'โหลดข้อมูลไม่สำเร็จ', target: '#orderReviewModal', heightAuto: false });
            });
    }

    function orvNum(v){
        return (v == null || v === '') ? '' : Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function orvGo(i){
        var n = ORV.rows.length;
        ORV.i = n ? Math.max(0, Math.min(i, n - 1)) : -1;
        var r = ORV.i >= 0 ? ORV.rows[ORV.i] : {};

        $('#orv_mdate').val(r.mdate || '');
        $('#orv_orderno').val(r.orderno || '');
        $('#orv_company').val(r.company || '');
        $('#orv_custno').val(r.custno || '');
        $('#orv_custname').val(r.custname || '');
        $('#orv_emp').val(r.emp || '');
        $('#orv_po').val(r.po || '');
        $('#orv_sale').val(r.sale || '');
        $('#orv_send').prop('checked', !!r.send);
        $('#orv_rp').prop('checked', !!r.rp);
        $('#orv_spec').prop('checked', !!r.spec);
        $('#orv_cer').prop('checked', !!r.cer);
        $('#orv_itemno').val(r.itemno || '');
        $('#orv_lotno').val(r.lotno || '');
        $('#orv_stock').val(ORV.i >= 0 ? orvNum(r.stock) : '');
        $('#orv_prod').val(ORV.i >= 0 ? orvNum(r.production) : '');
        $('#orv_price').val(orvNum(r.price));
        $('#orv_senddate').val(r.senddate || '');
        $('#orv_remark').val(r.remark || '');

        $('#orv_pos').val(n ? ORV.i + 1 : '');
        $('#orv_total').text(n);
        $('#orv_empty').toggleClass('d-none', n > 0);
    }

    // พิมพ์รายการที่ดูอยู่ (1 รายการ / แผ่น) — หน้า A4 แนวตั้ง ฟอร์มอยู่ครึ่งบน · สั่งพิมพ์ที่ Scale 100%
    function orvPrint(){
        var r = ORV.i >= 0 ? ORV.rows[ORV.i] : null;
        if (!r) {
            Swal.fire({ icon: 'warning', title: 'ไม่มีรายการให้พิมพ์', target: '#orderReviewModal', heightAuto: false });
            return;
        }
        // เปิด PDF ในแท็บใหม่ (ผู้ใช้สั่ง 04/11/2569) — ทางเดิมพิมพ์ผ่าน iframe ซ่อน คอมเมนต์ไว้ด้านล่าง
        window.open(ORV_PRINT_URL + '?' + $.param({ orderno: r.orderno, runno: r.runno, _: Date.now() }), '_blank');
        return;

        var frame = document.getElementById('orvPrintFrame');
        if (!frame) {
            frame = document.createElement('iframe');
            frame.id = 'orvPrintFrame';
            frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
            document.body.appendChild(frame);
        }
        frame.onload = function(){
            // หน่วงให้ตัวแสดง PDF ของเบราว์เซอร์โหลดเสร็จก่อนสั่งพิมพ์
            setTimeout(function(){
                try { frame.contentWindow.focus(); frame.contentWindow.print(); }
                catch (e) { console.error(e); }
            }, 500);
        };
        frame.src = ORV_PRINT_URL + '?' + $.param({ orderno: r.orderno, runno: r.runno, _: Date.now() });
    }

    document.addEventListener('DOMContentLoaded', function(){
        $('#orv_pos').on('keydown', function(e){
            if (e.key !== 'Enter') return;
            e.preventDefault();
            var v = parseInt(this.value, 10);
            orvGo(isNaN(v) ? ORV.i : v - 1);
        });
        $('#orv_dept').on('change', orvLoad);
        $('#orv_date').on('change', orvLoad);
    });
</script>
