{{-- ═══════════════════════════════════════════════════════════════
     การเปลี่ยนแปลงคำสั่งซื้อ (20/10/2569) — แทนฟอร์ม Access "การเปลี่ยนแปลงคำสั่งซื้อ"
     ที่มีช่อง "เลื่อนครั้งที่ 1-3" ตายตัว
     OrderChangeController (order/order-change/data · /save)
       - เลื่อนกำหนดส่ง: เลือกได้หลายรายการพร้อมกัน
       - แยกส่ง: แยกน้ำหนักบางส่วนของ 1 รายการเป็นแถวใหม่ + กำหนดส่งของส่วนที่แยก
       - เปลี่ยนน้ำหนัก: แก้ S / P ของ 1 รายการ
     1 ครั้งที่กดบันทึก = 1 ใบเปลี่ยนแปลง (เลขที่รันอัตโนมัติ) · ไม่มีขั้นอนุมัติ
     ═══════════════════════════════════════════════════════════════ --}}
<style>
    #orderChangeModal .oc-info .form-control[readonly] { background: #f6f7f9; }
    #orderChangeModal .oc-tree { color: #8a8d93; margin-right: .25rem; }
    #orderChangeModal #ocItemsTable td, #orderChangeModal #ocItemsTable th { white-space: nowrap; vertical-align: middle; }
    #orderChangeModal #ocItemsTable tr.oc-selected td { background: #eef4ff; }
    #orderChangeModal #ocItemsTable tr.oc-target td { background: #fff6e5; }
    #orderChangeModal .oc-panel { border: 1px solid #7367f0; border-radius: .5rem; background: #fbfaff; }
    #orderChangeModal .oc-doc { border-left: 3px solid #d8d6de; padding: .35rem 0 .35rem .75rem; margin-bottom: .6rem; }
    #orderChangeModal .oc-doc.oc-legacy { border-left-color: #c9c9c9; }
    #orderChangeModal .oc-doc.oc-web { border-left-color: #7367f0; }
    #orderChangeModal .oc-line { font-size: .875rem; }
</style>

<div class="modal modalHeadDecor fade" id="orderChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">การเปลี่ยนแปลงคำสั่งซื้อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ── หัวใบสั่งซื้อ (อ่านอย่างเดียว ยกเว้นช่องเลขที่ใบสั่ง) ── --}}
                <div class="row gx-3 gy-2 of-form oc-info" style="--of-lw: 96px;">
                    <div class="col-md-6 of-row">
                        <label class="form-label" for="oc_orderno">เลขที่ใบสั่ง</label>
                        <div class="of-ctl">
                            <div class="input-group input-group-sm">
                                <input type="text" id="oc_orderno" class="form-control" maxlength="15" autocomplete="off">
                                <button type="button" class="btn btn-primary" onclick="ocLoad($('#oc_orderno').val())">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 of-row">
                        <label class="form-label">วันที่</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_Mdate" readonly></div>
                    </div>
                    <div class="col-md-6 of-row mt-0">
                        <label class="form-label">แผนกที่ผลิต</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_Company" readonly></div>
                    </div>
                    <div class="col-md-6 of-row mt-0">
                        <label class="form-label">P/O No.</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_PO" readonly></div>
                    </div>
                    <div class="col-md-6 of-row mt-0">
                        <label class="form-label">รหัสลูกค้า</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_Custno" readonly></div>
                    </div>
                    <div class="col-md-6 of-row mt-0">
                        <label class="form-label">ชื่อลูกค้า</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_custname" readonly></div>
                    </div>
                    <div class="col-md-6 of-row mt-0">
                        <label class="form-label">สถานที่ส่ง</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_DVpoint" readonly></div>
                    </div>
                    <div class="col-md-3 of-row mt-0">
                        <label class="form-label">ผู้บันทึก</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_Emp" readonly></div>
                    </div>
                    <div class="col-md-3 of-row mt-0">
                        <label class="form-label">รหัสผู้ขาย</label>
                        <div class="of-ctl"><input type="text" class="form-control form-control-sm" id="oc_supno" readonly></div>
                    </div>
                    <div class="col-12 mt-1" id="oc_flags"></div>
                </div>

                <hr class="my-3">

                {{-- ── รายการ (suborder) — สถานะปัจจุบัน ── --}}
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <h6 class="mb-0 me-auto"><i class="ti ti-list-details me-1"></i>รายการ (น้ำหนักและกำหนดส่งปัจจุบัน)</h6>
                    <button type="button" class="btn btn-sm btn-warning" id="ocBtnPostpone" onclick="ocStartPostpone()" disabled>
                        <i class="ti ti-calendar-time me-1"></i>เลื่อนกำหนดส่งรายการที่เลือก <span id="ocSelCount" class="ms-1">(0)</span>
                    </button>
                </div>

                <div class="table-responsive border rounded">
                    <table class="table table-sm table-hover mb-0" id="ocItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:32px">
                                    <input type="checkbox" class="form-check-input" id="ocCheckAll" title="เลือกทั้งหมด">
                                </th>
                                <th class="text-center">#</th>
                                <th>รหัสสินค้า</th>
                                <th>Lotno.</th>
                                <th class="text-end">S</th>
                                <th class="text-end">P</th>
                                <th class="text-center">กำหนดที่ลูกค้าต้องการ</th>
                                <th class="text-center">กำหนดส่ง (ปัจจุบัน)</th>
                                <th class="text-center">ผลิตเสร็จ</th>
                                <th class="text-center">ลูกค้าได้รับ</th>
                                <th class="text-center">เปลี่ยนแปลง</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="ocItems">
                            <tr><td colspan="12" class="text-center text-muted py-4">กรอกเลขที่ใบสั่งแล้วกด Enter</td></tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">รวม</th>
                                <th class="text-end" id="ocTotalS">0.00</th>
                                <th class="text-end" id="ocTotalP">0.00</th>
                                <th colspan="6"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- ── แผงบันทึกการเปลี่ยนแปลง (โผล่เมื่อกดปุ่มการกระทำ) ── --}}
                <div class="oc-panel p-3 mt-3 d-none" id="ocPanel">
                    <h6 class="mb-2" id="ocPanelTitle"></h6>
                    <div class="small text-muted mb-2" id="ocPanelTarget"></div>

                    <div class="row gx-3 gy-2 of-form" style="--of-lw: 150px;">
                        {{-- เลื่อน --}}
                        <div class="col-md-6 of-row oc-f oc-f-postpone">
                            <label class="form-label" for="oc_new_senddate">กำหนดส่งใหม่ *</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm oc-date" id="oc_new_senddate" autocomplete="off"></div>
                        </div>

                        {{-- แยกส่ง --}}
                        <div class="col-md-4 of-row oc-f oc-f-split">
                            <label class="form-label" for="oc_split_stock">แยก S (ก.ก.)</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm text-end js-comma" id="oc_split_stock" autocomplete="off" oninput="ocSplitPreview()"></div>
                        </div>
                        <div class="col-md-4 of-row oc-f oc-f-split">
                            <label class="form-label" for="oc_split_prod">แยก P (ก.ก.)</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm text-end js-comma" id="oc_split_prod" autocomplete="off" oninput="ocSplitPreview()"></div>
                        </div>
                        <div class="col-md-4 of-row oc-f oc-f-split">
                            <label class="form-label" for="oc_split_senddate">กำหนดส่งส่วนที่แยก *</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm oc-date" id="oc_split_senddate" autocomplete="off"></div>
                        </div>
                        <div class="col-12 oc-f oc-f-split">
                            <div class="small" id="ocSplitPreview"></div>
                        </div>

                        {{-- เปลี่ยนน้ำหนัก --}}
                        <div class="col-md-6 of-row oc-f oc-f-weight">
                            <label class="form-label" for="oc_new_stock">S ใหม่ (ก.ก.)</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm text-end js-comma" id="oc_new_stock" autocomplete="off"></div>
                        </div>
                        <div class="col-md-6 of-row oc-f oc-f-weight">
                            <label class="form-label" for="oc_new_prod">P ใหม่ (ก.ก.)</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm text-end js-comma" id="oc_new_prod" autocomplete="off"></div>
                        </div>

                        {{-- ทุกการกระทำ --}}
                        <div class="col-md-6 of-row">
                            <label class="form-label" for="oc_change_date">วันที่ใบเปลี่ยนแปลง</label>
                            <div class="of-ctl"><input type="text" class="form-control form-control-sm oc-date" id="oc_change_date" autocomplete="off"></div>
                        </div>
                        <div class="col-12 of-row">
                            <label class="form-label" for="oc_reason">เหตุผล</label>
                            <div class="of-ctl"><textarea class="form-control form-control-sm" id="oc_reason" rows="2"></textarea></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-label-secondary" onclick="ocClosePanel()">ยกเลิก</button>
                        <button type="button" class="btn btn-sm btn-primary" id="ocBtnSave" onclick="ocSave()">
                            <i class="ti ti-device-floppy me-1"></i>บันทึกใบเปลี่ยนแปลง
                        </button>
                    </div>
                </div>

                <hr class="my-3">

                {{-- ── ประวัติการเปลี่ยนแปลง ── --}}
                <h6 class="mb-2"><i class="ti ti-history me-1"></i>ประวัติการเปลี่ยนแปลง</h6>
                <div id="ocHistory"><div class="text-muted small">—</div></div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>

        </div>
    </div>
</div>

<script>
    // ⚠ ไฟล์นี้ถูก include ก่อน layout/inc_js — ตัวฟังก์ชันเรียก jQuery / helper ของหน้า (fmtDate, fmtNum,
    //   escHtml, commaFmt, numOf, loadData) ตอนถูกเรียกเท่านั้น ส่วนการผูก event รอ DOMContentLoaded
    var OC_DATA_URL = "{{ route('order.change.data') }}";
    var OC_SAVE_URL = "{{ route('order.change.save') }}";
    var OC = { orderno: null, items: [], actions: {}, action: null, runnos: [] };

    // เปิดฟอร์ม — ส่งเลขที่ใบมา = โหลดใบนั้นเลย
    function orderChangeOpen(orderno){
        ocReset();
        $('#oc_orderno').val(orderno || '');
        $('#orderChangeModal').modal('show');
        if (orderno) ocLoad(orderno);
        else setTimeout(function(){ $('#oc_orderno').trigger('focus'); }, 300);
    }

    function ocReset(){
        OC.orderno = null; OC.items = []; ocClosePanel();
        $('#orderChangeModal .oc-info input[readonly]').val('');
        $('#oc_flags').empty();
        $('#ocItems').html('<tr><td colspan="12" class="text-center text-muted py-4">กรอกเลขที่ใบสั่งแล้วกด Enter</td></tr>');
        $('#ocTotalS, #ocTotalP').text('0.00');
        $('#ocHistory').html('<div class="text-muted small">—</div>');
        ocSyncSelection();
    }

    function ocLoad(orderno, keepPanel){
        orderno = (orderno || '').trim();
        if (!orderno) return;
        $.getJSON(OC_DATA_URL, {orderno: orderno}, function(res){
            if (!res.found){
                ocReset();
                $('#oc_orderno').val(orderno);
                Swal.fire({icon: 'warning', title: 'ไม่พบใบสั่งซื้อ', text: 'เลขที่ ' + orderno + ' ไม่มีอยู่ในระบบ',
                           target: '#orderChangeModal', heightAuto: false});
                return;
            }
            if (!keepPanel) ocClosePanel();
            OC.orderno = res.order.Orderno;
            OC.items   = res.items || [];
            OC.actions = res.actions || {};
            ocFillHeader(res.order);
            ocRenderItems();
            ocRenderHistory(res.history || []);
        }).fail(function(){
            Swal.fire({icon: 'error', title: 'โหลดข้อมูลไม่สำเร็จ', target: '#orderChangeModal', heightAuto: false});
        });
    }

    function ocFillHeader(o){
        $('#oc_orderno').val(o.Orderno);
        $('#oc_Mdate').val(fmtDateTime(o.Mdate));
        $('#oc_Company').val(o.Company || '');
        $('#oc_PO').val(o.PO || '');
        $('#oc_Custno').val(o.Custno || '');
        $('#oc_custname').val(o.custname || '');
        $('#oc_DVpoint').val(o.DVpoint || 'ไม่ระบุ (ส่งตามที่อยู่)');
        $('#oc_Emp').val(o.Emp || '');
        $('#oc_supno').val(o.supno || '');
        var flags = [['Send', 'ส่งก่อนได้'], ['RP', 'RP'], ['Spec', 'SPEC'], ['Cer', 'CER']];
        $('#oc_flags').html(flags.map(function(f){
            return '<span class="badge me-1 ' + (o[f[0]] ? 'bg-label-success' : 'bg-label-secondary opacity-50') + '">'
                 + '<i class="ti ' + (o[f[0]] ? 'ti-square-check' : 'ti-square') + ' me-1"></i>' + f[1] + '</span>';
        }).join(''));
    }

    function ocItem(runno){
        runno = parseInt(runno, 10);
        for (var i = 0; i < OC.items.length; i++) if (parseInt(OC.items[i].Runno, 10) === runno) return OC.items[i];
        return null;
    }
    // ลำดับบนจอของรายการ (ใช้อ้างในประวัติ / แถวที่แยกออกมา)
    function ocNo(runno){
        for (var i = 0; i < OC.items.length; i++) if (parseInt(OC.items[i].Runno, 10) === parseInt(runno, 10)) return i + 1;
        return null;
    }

    function ocRenderItems(){
        var totS = 0, totP = 0;
        if (!OC.items.length){
            $('#ocItems').html('<tr><td colspan="12" class="text-center text-muted py-4">ใบนี้ไม่มีรายการ</td></tr>');
        } else {
            $('#ocItems').html(OC.items.map(function(r, i){
                totS += parseFloat(r.Stock) || 0;
                totP += parseFloat(r.Production) || 0;
                var depth  = parseInt(r.depth, 10) || 0;
                var parent = r.parent_runno ? ocNo(r.parent_runno) : null;
                var tree   = depth ? '<span class="oc-tree" style="margin-left:' + ((depth - 1) * 16) + 'px">└</span>' : '';
                var from   = parent ? ' <span class="badge bg-label-info" title="แยกส่งมาจากรายการ #' + parent + '">จาก #' + parent + '</span>' : '';
                var cnt    = r.change_count ? '<span class="badge bg-label-primary">' + r.change_count + ' ครั้ง</span>' : '<span class="text-muted">-</span>';
                return '<tr data-runno="' + r.Runno + '">'
                    + '<td class="text-center"><input type="checkbox" class="form-check-input oc-check" value="' + r.Runno + '"></td>'
                    + '<td class="text-center text-muted">' + (i + 1) + '</td>'
                    + '<td>' + tree + escHtml(r.Itemno || '') + from + '</td>'
                    + '<td>' + escHtml(r.Lotno || '') + '</td>'
                    + '<td class="text-end">' + fmtNum(r.Stock, 2) + '</td>'
                    + '<td class="text-end">' + fmtNum(r.Production, 2) + '</td>'
                    + '<td class="text-center">' + fmtDate(r.custwant) + '</td>'
                    + '<td class="text-center fw-semibold">' + fmtDate(r.senddate) + '</td>'
                    + '<td class="text-center">' + fmtDate(r.EndP) + '</td>'
                    + '<td class="text-center">' + fmtDate(r.DVDate) + '</td>'
                    + '<td class="text-center">' + cnt + '</td>'
                    + '<td class="text-center">'
                    +   '<button type="button" class="btn btn-sm btn-label-info me-1" onclick="ocStartSplit(' + r.Runno + ')" title="แยกน้ำหนักบางส่วนไปส่งอีกวัน">'
                    +     '<i class="ti ti-arrows-split-2 me-1"></i>แยกส่ง</button>'
                    +   '<button type="button" class="btn btn-sm btn-label-danger" onclick="ocStartWeight(' + r.Runno + ')" title="เปลี่ยนน้ำหนัก S / P">'
                    +     '<i class="ti ti-scale me-1"></i>น้ำหนัก</button>'
                    + '</td>'
                    + '</tr>';
            }).join(''));
        }
        $('#ocTotalS').text(fmtNum(totS, 2));
        $('#ocTotalP').text(fmtNum(totP, 2));
        $('#ocCheckAll').prop('checked', false);
        ocSyncSelection();
    }

    function ocSelected(){
        return $('#ocItems .oc-check:checked').map(function(){ return parseInt(this.value, 10); }).get();
    }
    function ocSyncSelection(){
        var sel = ocSelected();
        $('#ocSelCount').text('(' + sel.length + ')');
        $('#ocBtnPostpone').prop('disabled', !sel.length);
        $('#ocItems tr').each(function(){
            $(this).toggleClass('oc-selected', $(this).find('.oc-check').is(':checked'));
        });
    }

    // ── แผงบันทึก ──
    function ocOpenPanel(action, runnos){
        OC.action = action;
        OC.runnos = runnos;
        var label = (OC.actions[action] || {}).label || action;
        $('#ocPanelTitle').html('<i class="ti ti-pencil me-1"></i>' + escHtml(label));
        $('#ocPanelTarget').html('รายการ: ' + runnos.map(function(rn){
            var r = ocItem(rn) || {};
            return '#' + ocNo(rn) + ' ' + escHtml(r.Itemno || '') + ' (S ' + fmtNum(r.Stock, 2) + ' / P ' + fmtNum(r.Production, 2)
                 + ' · ส่ง ' + (fmtDate(r.senddate) || '-') + ')';
        }).join(', '));
        $('#ocPanel .oc-f').addClass('d-none');
        $('#ocPanel .oc-f-' + action).removeClass('d-none');
        $('#ocPanel input:not(.oc-date), #oc_reason').val('');
        ocInitDates();
        ['oc_new_senddate', 'oc_split_senddate'].forEach(function(id){ document.getElementById(id)._flatpickr.clear(); });
        document.getElementById('oc_change_date')._flatpickr.setDate(new Date());
        $('#ocItems tr').removeClass('oc-target');
        runnos.forEach(function(rn){ $('#ocItems tr[data-runno="' + rn + '"]').addClass('oc-target'); });
        $('#ocSplitPreview').empty();
        $('#ocPanel').removeClass('d-none');
        document.getElementById('ocPanel').scrollIntoView({behavior: 'smooth', block: 'nearest'});
    }
    function ocClosePanel(){
        OC.action = null; OC.runnos = [];
        $('#ocPanel').addClass('d-none');
        $('#ocItems tr').removeClass('oc-target');
    }

    function ocStartPostpone(){
        var sel = ocSelected();
        if (!sel.length) return;
        ocOpenPanel('postpone', sel);
    }
    function ocStartSplit(runno){
        ocOpenPanel('split', [runno]);
        var r = ocItem(runno) || {};
        ocSplitPreview();
        setTimeout(function(){ $(parseFloat(r.Production) > 0 ? '#oc_split_prod' : '#oc_split_stock').trigger('focus'); }, 200);
    }
    function ocStartWeight(runno){
        ocOpenPanel('weight', [runno]);
        var r = ocItem(runno) || {};
        $('#oc_new_stock').val(commaFmt(r.Stock, 2));
        $('#oc_new_prod').val(commaFmt(r.Production, 2));
    }

    // แยกส่ง — โชว์ว่าแถวเดิมจะเหลือเท่าไร
    function ocSplitPreview(){
        if (OC.action !== 'split') return;
        var r = ocItem(OC.runnos[0]) || {};
        var sS = numOf($('#oc_split_stock').val()) || 0, sP = numOf($('#oc_split_prod').val()) || 0;
        var kS = (parseFloat(r.Stock) || 0) - sS, kP = (parseFloat(r.Production) || 0) - sP;
        var bad = sS < 0 || sP < 0 || kS < 0 || kP < 0 || (sS + sP) <= 0 || (kS + kP) <= 0;
        $('#ocSplitPreview').html(
            '<span class="' + (bad ? 'text-danger' : 'text-muted') + '">'
            + 'รายการเดิมจะเหลือ S ' + fmtNum(kS, 2) + ' / P ' + fmtNum(kP, 2)
            + ' · ส่วนที่แยกออกไป S ' + fmtNum(sS, 2) + ' / P ' + fmtNum(sP, 2) + '</span>');
    }

    function ocInitDates(){
        $('#ocPanel .oc-date').each(function(){
            if (this._flatpickr) return;
            flatpickr(this, {dateFormat: 'd/m/Y', allowInput: true, disableMobile: true, static: true});
        });
    }

    function ocSave(){
        if (!OC.orderno || !OC.action) return;
        var payload = {
            _token:      '{{ csrf_token() }}',
            orderno:     OC.orderno,
            action:      OC.action,
            runnos:      OC.runnos,
            change_date: $('#oc_change_date').val(),
            reason:      $('#oc_reason').val()
        };
        if (OC.action === 'postpone'){
            payload.new_senddate = $('#oc_new_senddate').val();
        } else if (OC.action === 'split'){
            payload.split_stock    = numOf($('#oc_split_stock').val()) || 0;
            payload.split_prod     = numOf($('#oc_split_prod').val()) || 0;
            payload.split_senddate = $('#oc_split_senddate').val();
        } else {
            payload.new_stock = numOf($('#oc_new_stock').val()) || 0;
            payload.new_prod  = numOf($('#oc_new_prod').val()) || 0;
        }

        var $btn = $('#ocBtnSave').prop('disabled', true);
        $.post(OC_SAVE_URL, payload)
            .done(function(res){
                Swal.fire({icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 2500, showConfirmButton: false});
                ocLoad(OC.orderno);
                loadData(page);   // ตารางรายการหน้าแรก (น้ำหนักรวม / จำนวนรายการเปลี่ยน)
            })
            .fail(function(xhr){
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'บันทึกไม่สำเร็จ';
                Swal.fire({icon: 'warning', title: 'บันทึกไม่ได้', text: msg, target: '#orderChangeModal', heightAuto: false});
            })
            .always(function(){ $btn.prop('disabled', false); });
    }

    // ── ประวัติ ──
    function ocQty(s, p){ return 'S ' + fmtNum(s, 2) + ' / P ' + fmtNum(p, 2); }

    function ocLineText(l){
        var no   = ocNo(l.runno);
        var item = '<span class="text-muted">' + (no ? '#' + no + ' ' : '') + escHtml(l.Itemno || ('Runno ' + l.runno)) + '</span> ';
        var act  = OC.actions[l.action] || {label: l.action, badge: 'bg-label-secondary'};
        var badge = '<span class="badge ' + act.badge + ' me-1">' + act.label + '</span>';
        var body = '';
        if (l.action === 'postpone'){
            body = (fmtDate(l.old_senddate) || '-') + ' <i class="ti ti-arrow-right"></i> <b>' + (fmtDate(l.new_senddate) || '-') + '</b>';
        } else if (l.action === 'split'){
            var sn = l.split_runno ? ocNo(l.split_runno) : null;
            body = ocQty(l.old_stock, l.old_prod) + ' <i class="ti ti-arrow-right"></i> คงเดิม ' + ocQty(l.new_stock, l.new_prod)
                 + ' (ส่ง ' + (fmtDate(l.old_senddate) || '-') + ') + แยก' + (sn ? ' #' + sn : '') + ' <b>' + ocQty(l.split_stock, l.split_prod)
                 + '</b> ส่ง <b>' + (fmtDate(l.new_senddate) || '-') + '</b>';
        } else {
            body = ocQty(l.old_stock, l.old_prod) + ' <i class="ti ti-arrow-right"></i> <b>' + ocQty(l.new_stock, l.new_prod) + '</b>';
        }
        return '<div class="oc-line">' + badge + item + body + '</div>';
    }

    function ocRenderHistory(docs){
        if (!docs.length){
            $('#ocHistory').html('<div class="text-muted small">ยังไม่มีการเปลี่ยนแปลง</div>');
            return;
        }
        $('#ocHistory').html(docs.map(function(d){
            var legacy = d.source === 'legacy';
            return '<div class="oc-doc ' + (legacy ? 'oc-legacy' : 'oc-web') + '">'
                + '<div class="small mb-1">'
                +   '<b>' + escHtml(d.change_no) + '</b>'
                +   ' · ' + (fmtDate(d.change_date, true) || '-')
                +   (d.by ? ' · ' + escHtml(d.by) : '')
                +   (legacy ? ' <span class="badge bg-label-secondary">ระบบเดิม</span>' : '')
                + '</div>'
                + d.lines.map(ocLineText).join('')
                + (d.reason ? '<div class="small text-muted mt-1"><i class="ti ti-message me-1"></i>' + escHtml(d.reason) + '</div>' : '')
                + '</div>';
        }).join(''));
    }

    document.addEventListener('DOMContentLoaded', function(){
        $(document).on('keydown', '#oc_orderno', function(e){
            if (e.key !== 'Enter') return;
            e.preventDefault();
            ocLoad($(this).val());
        });
        $(document).on('change', '#ocItems .oc-check', ocSyncSelection);
        $(document).on('change', '#ocCheckAll', function(){
            $('#ocItems .oc-check').prop('checked', this.checked);
            ocSyncSelection();
        });
    });
</script>
