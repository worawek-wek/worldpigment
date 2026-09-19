{{--
    Modal "ดูกลุ่มราคา" (อ่านอย่างเดียว) — 19/09/2569
    ใช้ร่วมกัน 2 หน้า: กำหนดราคา (/saleinfo) และ ใบสั่งซื้อ (/order)
    เปิดด้วย onclick="openColorRateLookup()"

    ⚠ ยิงไปที่ route `colorratelookup.data` (ไม่ใช่ `colorrate.*`) เพื่อให้คนที่ไม่ได้ติ๊ก
      เมนู "จัดการกลุ่มราคา" ยังกดดูได้ — ดูเหตุผลเต็มที่ ColorRateController::lookup()
--}}
<div class="modal fade" id="colorRateLookupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-layers-difference me-1"></i>กลุ่มราคา
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <div class="flex-grow-1" style="min-width:220px">
                        <input type="text" class="form-control" id="cr_lookup_search" autocomplete="off"
                            placeholder="ค้นหารหัสสินค้า...">
                    </div>
                    <span class="text-muted small" id="cr_lookup_count"></span>
                </div>

                {{-- เกณฑ์แบ่งกลุ่ม — ข้อความมาจาก PriceApprovalController::PRICE_GROUPS --}}
                <div class="alert alert-info py-2 small">
                    <i class="ti ti-info-circle me-1"></i>
                    แบ่งกลุ่มตาม <strong>น้ำหนักรวมของใบสั่งซื้อ</strong> —
                    @foreach (\App\Http\Controllers\PriceApprovalController::priceGroups() as $g)
                        {{ $g['label'] }}@if (!$loop->last) · @endif
                    @endforeach
                </div>

                <div id="cr_lookup_body">
                    <div class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>กำลังโหลดข้อมูล...
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <span class="text-muted small me-auto">
                    <i class="ti ti-eye me-1"></i>อ่านอย่างเดียว — แก้ไขได้ที่เมนู "จัดการกลุ่มราคา"
                </span>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script>
// guard กันนิยามซ้ำ เผื่อ partial ถูก include มากกว่า 1 ครั้งในหน้าเดียว
// (ตัวฟังก์ชันเรียกใช้ jQuery ตอนถูกกดเท่านั้น จึงนิยามไว้ตรงนี้ได้แม้ inc_js ยังไม่โหลด)
if (typeof window.openColorRateLookup !== 'function') {

    window.openColorRateLookup = function () {
        $('#colorRateLookupModal').modal('show');
        $('#cr_lookup_search').val('');
        window.loadColorRateLookup();
    };

    window.loadColorRateLookup = function () {
        var $box = $('#cr_lookup_body');

        $box.html('<div class="text-center text-muted py-4">'
            + '<span class="spinner-border spinner-border-sm me-2"></span>กำลังโหลดข้อมูล...</div>');

        $.getJSON("{{ route('colorratelookup.data') }}", { search: $('#cr_lookup_search').val() })
            .done(function (res) {
                $box.html(res.data || '');
                $('#cr_lookup_count').text('พบ ' + (res.count || 0) + ' รหัส');
            })
            .fail(function () {
                $box.html('<div class="text-center text-danger py-4">'
                    + 'โหลดข้อมูลไม่สำเร็จ — กรุณาลองใหม่อีกครั้ง</div>');
                $('#cr_lookup_count').text('');
            });
    };

    // ค้นหาแบบหน่วงเวลา กันยิง request ทุกตัวอักษร
    // ⚠ ผูก event ตอน DOMContentLoaded ไม่ใช่ $(function(){...}) ตรง ๆ — partial นี้ถูก include
    //   ไว้ก่อน inc_js ที่ท้ายหน้า ตอน parse ถึงตรงนี้ jQuery ยังไม่ถูกโหลด
    // ⚠ ห้ามพิมพ์ชื่อ directive ของ Blade (at-section ฯลฯ) ในคอมเมนต์นี้ — Blade จะตีความเป็น
    //   directive จริงแล้วกลืน HTML ที่เหลือทั้งหน้า (เคยพลาดมาแล้ว 19/09/2569)
    document.addEventListener('DOMContentLoaded', function () {
        var t = null;
        $(document).on('input', '#cr_lookup_search', function () {
            clearTimeout(t);
            t = setTimeout(window.loadColorRateLookup, 350);
        });
    });
}
</script>
