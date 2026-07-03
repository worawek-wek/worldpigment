<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>ใบเสนอราคา - World Pigment</title>

</head>
<style>
.modalHeadDecor .modal-header {
    padding: 0;
}

.modalHeadDecor .modal-title {
    padding: 1.25rem 1.5rem 1.25rem;
    color: white;
    background-color: #54BAB9;
    position: relative;
}

.modalHeadDecor .modal-title::after {
    position: absolute;
    top: 0;
    right: -65px;
    content: '';
    width: 0;
    height: 0;
    border-top: 65px solid #54BAB9;
    border-right: 65px solid transparent;
}
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layout/inc_sidemenu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('layout/inc_topmenu')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                    <div>
                        <h3 class="mb-1">
                            <i class="ti ti-file-invoice text-primary"></i>
                            ใบเสนอราคา
                        </h3>
                        <p class="text-muted mb-0">
                            จัดการใบเสนอราคาและ Revision
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" onclick="openCreate()">
                            <i class="ti ti-plus me-1"></i>
                            สร้างใบเสนอราคา
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">

        <div class="card-header border-bottom">

            {{-- แถวตัวกรอง 1: ค้นหา + ช่วงวันที่ --}}
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">ค้นหา <span class="text-muted fw-normal">(เลขที่ใบเสนอราคา / รหัสลูกค้า / ชื่อลูกค้า)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control p_search"
                            oninput="loadData(page)">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">วันที่เสนอราคา</label>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-medium">ตั้งแต่</span>
                        <input type="date" name="date_from" class="form-control p_search" autocomplete="off" onchange="loadData(page)">
                        <span class="small fw-medium">ถึง</span>
                        <input type="date" name="date_to" class="form-control p_search" autocomplete="off" onchange="loadData(page)">
                    </div>
                </div>
            </div>

            {{-- แถวตัวกรอง 2: ชนิดสินค้า --}}
            <div class="row g-3 align-items-end mt-1">
                <div class="col-md-4">
                    <label class="form-label small fw-medium mb-1">ชนิดสินค้า</label>
                    <select name="product_type" class="form-select p_search" onchange="loadData(page)">
                        <option value="">ทั้งหมด</option>
                        @foreach ($pdtypes as $pt)
                            <option value="{{ $pt->PDType }}">{{ $pt->PDType }} — {{ $pt->PDHead1 }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end my-3">
                <button type="button" id="btnResetFilters" class="btn btn-label-secondary" onclick="resetFilters()">
                    <i class="ti ti-x me-1"></i>ล้างตัวกรอง<span class="filter-count ms-1"></span>
                </button>
            </div>

            {{-- จำนวนต่อหน้า (มุมขวาล่าง) --}}
            <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                <label class="form-label small fw-medium mb-0 me-2">แสดง</label>
                <select name="limit" class="form-select form-select-sm p_search" style="width: 90px;"
                    onchange='loadData("{{$page_url}}/datatable")'>
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                </select>
                <span class="ms-2 small fw-medium">รายการ/หน้า</span>
            </div>
        </div>

        <div id="table-data">
            {{-- Table โหลดผ่าน AJAX จาก quotation/datatable --}}
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div>
                กำลังโหลดข้อมูล...
            </div>
        </div>

    </div>

</div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layout/inc_footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: สร้าง / แก้ไข ใบเสนอราคา (ใช้ฟอร์มร่วมกัน)                  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal modalHeadDecor fade" id="quotationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="quotationModalTitle">สร้างใบเสนอราคา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="quotationForm">
                <input type="hidden" name="mode" id="q_mode" value="insert">
                {{-- qno เดิม (ใช้ตอน update/หา key) --}}
                <input type="hidden" name="qno" id="q_qno_key">

                <div class="modal-body px-5">

                    <!-- Top Form -->
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">เลขที่ใบเสนอราคา <span class="text-danger">*</span></label>
                            <input type="text" name="Qno" id="q_Qno" class="form-control" maxlength="10" required>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-2">
                            <label class="form-label">วันที่เสนอราคา</label>
                            <input type="date" name="Qdate" id="q_Qdate" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-danger">Revise Date</label>
                            <input type="date" name="Revisedate" id="q_Revisedate" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">ชนิดสินค้า</label>
                            <select name="PDtype" id="q_PDtype" class="form-select">
                                @foreach ($pdtypes as $pt)
                                    <option value="{{ $pt->PDType }}">{{ $pt->PDType }} — {{ $pt->PDHead1 }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" id="q_exam" name="exam" type="checkbox" value="1">
                                <label class="form-check-label" for="q_exam">พร้อมตัวอย่าง</label>
                            </div>
                        </div>
                        <div class="col-md-8"></div>

                        <div class="col-md-3">
                            <label class="form-label">รหัสพนักงานขาย</label>
                            <input type="number" name="EmpID" id="q_EmpID" class="form-control">
                        </div>
                        <div class="col-md-9"></div>

                        <div class="col-md-2">
                            <label class="form-label">รหัสลูกค้า</label>
                            <input type="text" name="Custid" id="q_Custid" class="form-control" maxlength="6"
                                oninput="lookupCustomer(this.value)">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">ชื่อลูกค้า</label>
                            <input type="text" name="CustName" id="q_CustName" class="form-control text-primary fw-bold">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">ชื่อลูกค้า (ภาษาอังกฤษ)</label>
                            <input type="text" name="Engname" id="q_Engname" class="form-control" maxlength="70">
                        </div>

                    </div>

                    <!-- ปุ่มเพิ่มรายการ -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-label-warning" onclick="addQuotationItem()">
                                    <i class="ti ti-plus me-1"></i>เพิ่มรายการ
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ตารางรายการสินค้า -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered align-middle" id="quotationItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="150">รหัสสินค้า</th>
                                    <th>ชื่อสินค้า</th>
                                    <th width="130">ราคาเก่า</th>
                                    <th width="130">ราคาใหม่</th>
                                    <th width="130">ราคารวมภาษี</th>
                                    <th width="60" class="text-center">ลบ</th>
                                </tr>
                            </thead>
                            <tbody id="quotationItems">
                                {{-- แถวเริ่มต้นเติมด้วย JS (resetItems) --}}
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Form -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-3">
                            <label class="form-label">ควรกำหนดยอดซื้อขั้นต่ำ (ก.ก.)</label>
                            <input type="text" name="Qremark" id="q_Qremark" class="form-control text-center" maxlength="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Term</label>
                            <input type="text" name="Term" id="q_Term" class="form-control text-center" maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ยืนราคาถึงวันที่</label>
                            <input type="date" name="Validto" id="q_Validto" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ส่งสินค้าได้ภายใน <span class="text-muted fw-normal">(วัน)</span></label>
                            <input type="text" name="LeadTime" id="q_LeadTime" class="form-control" maxlength="3">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="saveQuotation()">
                        <i class="ti ti-device-floppy me-1"></i>บันทึกใบเสนอราคา
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Modal: ดูรายละเอียด (โหลด HTML จาก quotation/show)               -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="quotationViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="overflow:hidden;">
            {{-- ปุ่มปิดลอย — แบนเนอร์ในเนื้อหาทำหน้าที่เป็น header แทน --}}
            <button type="button" class="btn-close btn-close-white position-absolute"
                style="top:1.15rem; right:1.25rem; z-index:1056;" data-bs-dismiss="modal" aria-label="ปิด"></button>
            <div class="modal-body p-0" id="quotationViewBody">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- / Layout wrapper -->
    @include('layout/inc_js')
<script>
    var page = "{{$page_url}}/datatable";
        var searchData = {};
        // ต้องประกาศก่อน loadData ครั้งแรก — เลี่ยง hoisting ทำให้ dtSeq เป็น NaN (spinner ค้าง)
        var dtXhr = null, dtSeq = 0;
        loadData(page);

        // ────────────────────────────────────────────────────────
        //  รายการสินค้าในใบเสนอราคา — เพิ่ม/ลบแถวได้
        // ────────────────────────────────────────────────────────
        function itemRow(it){
            it = it || {};
            return '<tr>'
                + '<td><input type="text"   name="item_code[]"        class="form-control" value="'      + (it.code  ?? '') + '"></td>'
                + '<td><input type="text"   name="item_name[]"        class="form-control" value="'      + (it.name  ?? '') + '"></td>'
                + '<td><input type="number" name="item_old_price[]"   class="form-control text-end" step="0.01" value="' + (it.old ?? '') + '"></td>'
                + '<td><input type="number" name="item_new_price[]"   class="form-control text-end" step="0.01" value="' + (it.newp ?? '') + '"></td>'
                + '<td><input type="number" name="item_total_price[]" class="form-control text-end" step="0.01" value="' + (it.net ?? '') + '"></td>'
                + '<td class="text-center">'
                +   '<button type="button" class="btn btn-sm btn-icon btn-label-danger" title="ลบรายการ" onclick="removeQuotationItem(this)">'
                +     '<i class="ti ti-trash"></i></button>'
                + '</td>'
                + '</tr>';
        }

        function addQuotationItem(it) {
            $('#quotationItems').append(itemRow(it));
        }

        function removeQuotationItem(btn) {
            // เหลืออย่างน้อย 1 แถว — ถ้าลบแถวสุดท้ายให้เคลียร์ค่าแทน
            if ($('#quotationItems tr').length <= 1) {
                $(btn).closest('tr').find('input').val('');
                return;
            }
            $(btn).closest('tr').remove();
        }

        // ────────────────────────────────────────────────────────
        //  ค้นชื่อลูกค้าจากรหัส (เติมชื่อไทย/อังกฤษ/เทอมอัตโนมัติ)
        // ────────────────────────────────────────────────────────
        // เรียกจาก oninput → debounce กันยิง AJAX ทุกตัวอักษร, ไม่เด้ง popup ระหว่างพิมพ์
        var custLookupTimer = null, custLookupXhr = null;
        function lookupCustomer(code){
            code = (code || '').trim();
            clearTimeout(custLookupTimer);
            if (!code){ $('#q_CustName').val(''); return; }
            custLookupTimer = setTimeout(function(){
                if (custLookupXhr) custLookupXhr.abort();
                custLookupXhr = $.getJSON("{{ $page_url }}/customer/" + encodeURIComponent(code), function(res){
                    // กันผลเก่ามาทับ: เช็คว่ารหัสในช่องยังตรงกับที่ค้นอยู่
                    if ($('#q_Custid').val().trim() !== code) return;
                    if (res.found){
                        $('#q_CustName').val(res.name || '');
                        if (res.nameEN) $('#q_Engname').val(res.nameEN);
                        if (res.term && !$('#q_Term').val()) $('#q_Term').val(res.term);
                    } else {
                        $('#q_CustName').val('');   // ยังพิมพ์ไม่ครบ/ไม่เจอ → เคลียร์เงียบๆ ไม่เด้ง popup
                    }
                });
            }, 350);
        }

        // ────────────────────────────────────────────────────────
        //  เปิดฟอร์มสร้างใหม่
        // ────────────────────────────────────────────────────────
        function resetItems(items){
            var body = $('#quotationItems').empty();
            if (items && items.length){
                items.forEach(function(i){ body.append(itemRow(i)); });
            } else {
                body.append(itemRow()).append(itemRow());  // เริ่มต้น 2 แถวว่าง
            }
        }

        function openCreate(){
            document.getElementById('quotationForm').reset();
            $('#q_mode').val('insert');
            $('#q_qno_key').val('');
            $('#quotationModalTitle').text('สร้างใบเสนอราคา');
            $('#q_Qno').prop('readonly', false);
            // วันที่เสนอราคา = วันนี้
            $('#q_Qdate').val(new Date().toISOString().slice(0,10));
            resetItems();
            // ขอเลขที่ถัดไป (แก้ไขได้)
            $.getJSON("{{ $page_url }}/next-qno", {prefix:'WH'}, function(res){
                if (res.qno) $('#q_Qno').val(res.qno);
            });
            new bootstrap.Modal('#quotationModal').show();
        }

        // ────────────────────────────────────────────────────────
        //  แก้ไข — ดึงข้อมูลเดิมมาเติมฟอร์ม
        // ────────────────────────────────────────────────────────
        function quotationEdit(qno){
            $.getJSON("{{ $page_url }}/edit", {qno: qno}, function(res){
                if (res.error){ Swal.fire('ไม่พบข้อมูล', qno, 'error'); return; }
                var h = res.header || {};
                document.getElementById('quotationForm').reset();
                $('#q_mode').val('update');
                $('#q_qno_key').val(h.Qno);
                $('#quotationModalTitle').text('แก้ไขใบเสนอราคา ' + (h.Qno || ''));
                $('#q_Qno').val((h.Qno || '').trim()).prop('readonly', true);   // ห้ามเปลี่ยนเลขที่
                $('#q_Qdate').val(dateOnly(h.Qdate));
                $('#q_Revisedate').val(dateOnly(h.Revisedate));
                $('#q_Validto').val(dateOnly(h.Validto));
                $('#q_PDtype').val(h.PDtype);
                $('#q_exam').prop('checked', h.exam == 1);
                $('#q_EmpID').val(h.EmpID);
                $('#q_Custid').val(h.Custid);
                // ชื่อไทยใช้จาก customer (join) ถ้ามี ไม่งั้น fallback CustName เดิม
                $('#q_CustName').val((res.cust && res.cust.name) ? res.cust.name : (h.CustName || ''));
                $('#q_Engname').val(h.Engname || (res.cust ? res.cust.nameEN : '') || '');
                $('#q_Qremark').val(h.Qremark);
                $('#q_Term').val(h.Term);
                $('#q_LeadTime').val(h.LeadTime);
                // รายการ
                var items = (res.items || []).map(function(d){
                    return {code:d.Qitemno||'', name:d.Qdesc||'', old:d.oldprice ?? '', newp:d.QPrice ?? '', net:d.QNet ?? ''};
                });
                resetItems(items);
                new bootstrap.Modal('#quotationModal').show();
            }).fail(function(){ Swal.fire('เกิดข้อผิดพลาด', 'โหลดข้อมูลไม่สำเร็จ', 'error'); });
        }

        // ตัดเวลาออกจาก datetime → 'YYYY-MM-DD' สำหรับ input[type=date]
        function dateOnly(v){
            if (!v) return '';
            return String(v).substring(0,10);
        }

        // ────────────────────────────────────────────────────────
        //  บันทึก (create/update)
        // ────────────────────────────────────────────────────────
        function saveQuotation(){
            var form = document.getElementById('quotationForm');
            if (!$('#q_Qno').val().trim()){
                Swal.fire('กรอกไม่ครบ', 'กรุณากรอกเลขที่ใบเสนอราคา', 'warning');
                return;
            }
            var mode = $('#q_mode').val();
            var url  = mode === 'update' ? "{{ $page_url }}/update" : "{{ $page_url }}/insert";
            var fd = new FormData(form);
            fd.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: url, type: 'POST', data: fd, contentType: false, processData: false,
                success: function(res){
                    if (res.ok){
                        bootstrap.Modal.getInstance(document.getElementById('quotationModal')).hide();
                        Swal.fire('บันทึกเรียบร้อย', 'เลขที่ ' + res.qno, 'success');
                        loadData(page);
                    } else {
                        Swal.fire('ไม่สำเร็จ', res.error || 'ไม่ทราบสาเหตุ', 'error');
                    }
                },
                error: function(xhr){
                    var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'เกิดข้อผิดพลาด';
                    Swal.fire('ไม่สำเร็จ', msg, 'error');
                }
            });
        }

        // ────────────────────────────────────────────────────────
        //  ดูรายละเอียด / พิมพ์ / ลบ
        // ────────────────────────────────────────────────────────
        function quotationView(qno){
            var el = document.getElementById('quotationViewModal');
            $('#quotationViewBody').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลด...</div>');
            new bootstrap.Modal(el).show();
            $.get("{{ $page_url }}/show", {qno: qno}, function(html){
                $('#quotationViewBody').html(html);
            }).fail(function(){
                $('#quotationViewBody').html('<div class="text-center py-5 text-danger">ไม่พบข้อมูล</div>');
            });
        }

        function quotationPrint(qno){
            // พิมพ์ในหน้าเดิมผ่าน iframe ซ่อน (ไม่เปิดแท็บ/หน้าต่างใหม่)
            var frame = document.getElementById('printFrame');
            if (!frame){
                frame = document.createElement('iframe');
                frame.id = 'printFrame';
                frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
                document.body.appendChild(frame);
            }
            frame.onload = function(){
                try { frame.contentWindow.focus(); frame.contentWindow.print(); }
                catch (e) { console.error(e); }
            };
            frame.src = "{{ $page_url }}/print?qno=" + encodeURIComponent(qno);
        }

        function quotationDelete(qno){
            Swal.fire({
                title: 'ยืนยันการลบ?',
                html: 'ลบใบเสนอราคา <strong>' + qno + '</strong> และรายการทั้งหมด<br>การลบไม่สามารถย้อนกลับได้',
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#d33',
            }).then(function(result){
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ $page_url }}/delete", type: 'POST',
                    data: {qno: qno, _token: '{{ csrf_token() }}'},
                    success: function(res){
                        if (res.ok){ Swal.fire('ลบเรียบร้อย', '', 'success'); loadData(page); }
                        else { Swal.fire('ไม่สำเร็จ', res.error || '', 'error'); }
                    },
                    error: function(){ Swal.fire('เกิดข้อผิดพลาด', '', 'error'); }
                });
            });
        }

        // ────────────────────────────────────────────────────────
        //  DataTable filter machinery (คงเดิม)
        // ────────────────────────────────────────────────────────
        // เก็บ filter สด (เฉพาะที่มีค่า) ทุกครั้ง — ไม่สะสมค่าเก่า
        function collectSearchData(){
            var data = {};
            $('.p_search').each(function(){
                var v = $(this).val();
                if (v !== '' && v !== null) data[$(this).attr('name')] = v;
            });
            return data;
        }

        // อัปเดตปุ่มล้างตัวกรองตามจำนวน filter ที่ใช้อยู่ (ไม่นับ limit)
        function updateFilterButtonState(){
            var count = 0;
            $('.p_search:not([name="limit"])').each(function(){
                var v = $(this).val();
                if (v !== '' && v !== null) count++;
            });
            var $btn = $('#btnResetFilters');
            if (count > 0) { $btn.removeClass('btn-label-secondary').addClass('btn-danger'); $btn.find('.filter-count').text('('+count+')'); }
            else           { $btn.removeClass('btn-danger').addClass('btn-label-secondary'); $btn.find('.filter-count').text(''); }
        }

        // กัน AJAX race: รับเฉพาะผลของ request ล่าสุด (dtXhr/dtSeq ประกาศด้านบนแล้ว)
        function loadData(pages){
            updateFilterButtonState();
            searchData = collectSearchData();
            page = pages;
            var seq = ++dtSeq;
            if (dtXhr) dtXhr.abort();
            dtXhr = $.ajax({
                type: "GET", url: pages, data: searchData,
                success: function(data){ if (seq === dtSeq) $("#table-data").html(data); },
                complete: function(){ if (seq === dtSeq) dtXhr = null; }
            });
        }

        function resetFilters(){
            $('.p_search:not([name="limit"])').val('');
            loadData("{{$page_url}}/datatable");
        }
</script>
</body>

</html>
