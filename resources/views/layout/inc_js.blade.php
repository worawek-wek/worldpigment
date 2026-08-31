
            <div class="modal fade modalHeadDecor" id="insurance_2" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="user_view">

                </div>
            </div>

          <script>
              function user_view(id){
                  $.ajax({
                      type: "GET",
                      url: "/user/"+id,
                      success: function(data) {
                          $("#user_view").html(data);

                            $('#select2Position2').select2({
                                placeholder: 'เลือกตำแหน่ง',
                                allowClear: true,
                                dropdownParent: $('#insurance_2'), // 💥 สำคัญมาก ถ้าอยู่ใน modal
                                width: '100%'
                            });

                            // $('#insurance').modal('show');
                            // setTimeout(() => {
                            //     console.log($('#user_view').html());
                            // }, 300);
                      }
                  });
              }
          </script>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/libs/i18n/i18n.js"></script>
    <script src="assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
    <script src="assets/vendor/libs/moment/moment.js"></script>
    <script src="assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    {{-- <script src="assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script> --}}
    <script src="assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="assets/vendor/libs/select2/select2.js"></script>
    <script src="assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/vendor/js/dropdown-hover.js"></script>

    {{-- <script src="assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

    <script src="assets/js/extended-ui-sweetalert2.js"></script> --}}

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Page JS -->
    <!-- <script src="assets/js/dashboards-crm.js"></script> -->
    <script src="assets/js/forms-selects.js"></script>
    {{-- <script src="assets/js/forms-pickers.js"></script> --}}

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ช่องกรอกตัวเลขแบบใส่คอมมาอัตโนมัติ (18/08/2569)                    --}}
    {{--                                                                 --}}
    {{-- วิธีใช้: ที่ input ใส่ type="text" class="js-comma"               --}}
    {{--   - data-decimals="0"  → จำนวนเต็ม (ไม่เติม .00 ตอนออกจากช่อง)     --}}
    {{--   - ไม่ระบุ = ทศนิยม 2 ตำแหน่ง                                    --}}
    {{-- ⚠ เวลาอ่านค่าไปคำนวณ/ส่ง server ห้ามใช้ .val() ตรง ๆ ให้ใช้        --}}
    {{--   numVal(sel) หรือเรียก stripCommaFields(form) ก่อน serialize      --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <script>
        // '1,234.50' → '1234.50'
        function stripCommas(v){
            return String(v == null ? '' : v).replace(/,/g, '').trim();
        }

        // แปลง "ค่า" เป็นตัวเลข — ว่าง/ไม่ใช่ตัวเลข = null
        function numOf(v){
            var raw = stripCommas(v);
            if (raw === '') return null;
            var n = parseFloat(raw);
            return isNaN(n) ? null : n;
        }

        // อ่านค่าจาก "ช่อง" (selector / element / jQuery) เป็นตัวเลข
        function numVal(sel){ return numOf($(sel).val()); }

        // ใส่คอมมาให้เฉพาะส่วนจำนวนเต็ม — คงส่วนทศนิยมที่กำลังพิมพ์ค้างไว้ตามเดิม
        function addCommas(s, decimals){
            s = String(s == null ? '' : s);
            var neg = s.charAt(0) === '-';
            s = s.replace(/[^\d.]/g, '');
            var maxDec = (decimals === undefined || decimals === null) ? 2 : Number(decimals);
            var dot = s.indexOf('.');
            var intPart = (dot === -1) ? s : s.substring(0, dot);
            var decPart = '';
            if (dot !== -1 && maxDec > 0){
                decPart = '.' + s.substring(dot + 1).replace(/\./g, '').substring(0, maxDec);
            }
            intPart = intPart.replace(/^0+(?=\d)/, '');                    // ตัดศูนย์นำหน้า
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            if (intPart === '' && decPart === '') return neg ? '-' : '';
            return (neg ? '-' : '') + intPart + decPart;
        }

        // ค่าจาก DB → ข้อความพร้อมคอมมาสำหรับใส่ในช่อง (ว่าง = '')
        function commaFmt(v, decimals){
            var n = numOf(v);
            if (n === null) return '';
            var d = (decimals === undefined || decimals === null) ? 2 : Number(decimals);
            return n.toLocaleString('en-US', {minimumFractionDigits: d, maximumFractionDigits: d});
        }

        // ทศนิยมของช่องนั้น (data-decimals, ไม่ระบุ = 2)
        function commaDecimals(el){
            var d = $(el).data('decimals');
            return (d === undefined || d === null || d === '') ? 2 : Number(d);
        }

        // ถอดคอมมาออกจากทุกช่อง .js-comma ใน scope — เรียกก่อน serialize()/new FormData()
        // ไม่งั้นค่าที่ส่งขึ้น server จะเป็น '1,234.50' แล้วลง DB เป็น 1.00
        function stripCommaFields(scope){
            $(scope).find('.js-comma').addBack('.js-comma').each(function(){
                this.value = stripCommas(this.value);
            });
        }

        // จัดรูปแบบระหว่างพิมพ์ + คืนตำแหน่งเคอร์เซอร์ให้อยู่หลังอักขระตัวเดิม
        $(document).on('input', '.js-comma', function(){
            var el = this;
            var head = el.value.substring(0, el.selectionStart).replace(/[^\d.]/g, '').length;
            el.value = addCommas(el.value, commaDecimals(el));
            var pos = 0, seen = 0;
            while (pos < el.value.length && seen < head){
                if (/[\d.]/.test(el.value.charAt(pos))) seen++;
                pos++;
            }
            try { el.setSelectionRange(pos, pos); } catch (e) {}
        });

        // ออกจากช่อง → จัดทศนิยมให้ครบตามที่กำหนด
        $(document).on('blur', '.js-comma', function(){
            var n = numOf(this.value);
            this.value = (n === null) ? '' : commaFmt(n, commaDecimals(this));
        });
    </script>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ยกระดับ <select> ให้ใช้งานง่ายขึ้น (25/08/2569)                    --}}
    {{--                                                                 --}}
    {{-- เกณฑ์อัตโนมัติ — นับจำนวน option ตอนรันไทม์:                       --}}
    {{--   ตั้งแต่ SELECT_SEARCH_MIN (10) ขึ้นไป → select2 (พิมพ์ค้นหาได้)   --}}
    {{--   น้อยกว่านั้น                        → bootstrap-select (basic)  --}}
    {{-- นับตอนรันไทม์เพราะรายการส่วนใหญ่มาจาก DB (จังหวัด/พนักงาน/Temp)     --}}
    {{-- จำนวนจึงโต/ลดได้เอง ไม่ต้องตามแก้ class ทีละหน้า                   --}}
    {{--                                                                 --}}
    {{-- วิธีใช้ในแต่ละหน้า: เรียก enhanceSelects(scope) หลัง DOM พร้อม      --}}
    {{--   (หรือหลังโหลด HTML จาก AJAX) — เรียกซ้ำได้ ไม่ init ทับของเดิม   --}}
    {{--                                                                 --}}
    {{-- class กำกับที่ <select>:                                         --}}
    {{--   no-enhance    = ปล่อยเป็น select ธรรมดา                        --}}
    {{--   force-select2 = บังคับ select2 แม้ option น้อย                  --}}
    {{--   force-picker  = บังคับ bootstrap-select แม้ option เยอะ         --}}
    {{--   select2-tags  = select2 ที่พิมพ์ค่านอกรายการเองได้ (บังคับ select2 --}}
    {{--                   เพราะ bootstrap-select พิมพ์ค่าใหม่ไม่ได้)       --}}
    {{--                                                                 --}}
    {{-- ⚠ ไม่ต้องตามสั่ง refresh เองหลัง .val() / form.reset() /           --}}
    {{--   เติม option ด้วย JS — มี hook กลางจัดการให้แล้ว (ดูท้ายบล็อก)    --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <script>
        // option ตั้งแต่เท่านี้ขึ้นไป = "ข้อมูลเยอะ" → ต้องค้นหาได้
        var SELECT_SEARCH_MIN = 10;

        // กัน hook กลาง (val / MutationObserver) ทำงานซ้อนตอนเรากำลัง init เอง
        var _selBusy = false;

        // เลือกว่า select ตัวนี้ควรเป็นแบบค้นหาได้หรือไม่
        function selectWantsSearch($el){
            if ($el.hasClass('select2-tags') || $el.hasClass('force-select2')) return true;
            if ($el.hasClass('force-picker')) return false;
            return $el[0].getElementsByTagName('option').length >= SELECT_SEARCH_MIN;
        }

        // ความกว้าง: ยึด inline style ของ select เดิม (เช่น style="width:90px") ไม่มีก็เต็มพื้นที่
        function selectWidthOf($el){
            return $el[0].style.width || '100%';
        }

        // select ตัวนี้ห้ามแตะหรือไม่
        function selectSkipped($el){
            if (!$el.is('select')) return true;
            if ($el.hasClass('no-enhance') || $el.is('[data-no-enhance]')) return true;
            // dropdown เดือน/ปี ที่ flatpickr สร้างเอง — แตะแล้วปฏิทินพัง
            if ($el.closest('.flatpickr-calendar').length) return true;
            // ตารางของ DataTables จัดการ dropdown จำนวนแถวเอง
            if ($el.closest('.dataTables_length').length) return true;
            return false;
        }

        // วาดหน้าปุ่ม/กล่องใหม่ให้ตรงกับค่าที่เลือกอยู่จริง
        // ⚠ bootstrap-select ใช้ 'render' ไม่ใช่ 'refresh' — refresh สร้าง option list ใหม่ทั้งชุด
        //   ซึ่งเวอร์ชันนี้ (เขียนมาสำหรับ BS4 แต่เรารันบน 5.3) วาดซ้อนกันจนข้อความมั่ว
        function syncSelect($el){
            var mode = $el.data('enhanced');
            if (mode === 'select2') {
                // select2 ตาม disabled ให้เองอยู่แล้ว (มี observer ภายในของมัน) — sync แค่ค่าที่เลือก
                $el.trigger('change.select2');
            } else if (mode === 'picker') {
                $el.selectpicker('render');
                syncPickerDisabled($el);
            }
        }

        // bootstrap-select วาดปุ่มแยกจาก <select> และไม่ตามสถานะ disabled ให้เอง
        // (ใช้ 'refresh' สั่งให้ตามได้ แต่ห้าม — มันสร้าง option list ใหม่แล้ววาดซ้อนกันบน BS 5.3)
        // → ปิด/เปิดปุ่มเองตามสถานะจริงของ select ไม่งั้นช่องที่ล็อกไว้ยังกดเลือกได้
        function syncPickerDisabled($el){
            var off  = !!$el.prop('disabled');
            var $box = $el.parent('.bootstrap-select');
            if (!$box.length) return;
            $box.toggleClass('disabled', off);
            $box.find('> .dropdown-toggle')
                .prop('disabled', off)
                .toggleClass('disabled', off)
                .attr('tabindex', off ? -1 : 0);
        }

        // ถอดของเดิมออกก่อนสลับชนิด (เช่น option ถูกเติมจนข้ามเกณฑ์)
        function destroySelect($el){
            var mode = $el.data('enhanced');
            try {
                if (mode === 'select2')     $el.select2('destroy');
                else if (mode === 'picker') $el.selectpicker('destroy');
            } catch (e) {}
            $el.removeData('enhanced');
        }

        // ลายเซ็นของชุด option ที่มีอยู่ตอนนี้ — ใช้ดูว่ารายการเปลี่ยนไปจากตอน init แล้วหรือยัง
        function optionsSig(el){
            var opts = el.getElementsByTagName('option'), out = [];
            for (var i = 0; i < opts.length; i++) out.push(opts[i].value + ' => ' + opts[i].text);
            return opts.length + ' :: ' + out.join(' || ');
        }

        // ยกระดับ select ตัวเดียว — เรียกซ้ำได้ (ชนิดเดิม + option ชุดเดิม = แค่ sync ค่า)
        function enhanceSelect(el){
            var $el = $(el);
            if (selectSkipped($el)) return;
            el = $el[0];                       // รับได้ทั้ง element และ jQuery

            var want = selectWantsSearch($el) ? 'select2' : 'picker';
            var cur  = $el.data('enhanced');

            /* ⚠ bootstrap-select อ่าน <option> เข้าไปทำรายการของตัวเองแค่ "ตอน init ครั้งเดียว"
                 — ในไลบรารีไม่มี MutationObserver เลย และ 'render' อัปเดตแค่ข้อความบนปุ่ม
                   ตัวที่สร้างรายการใหม่คือ buildList ซึ่งเรียกจาก 'refresh' ที่ห้ามใช้ (วาดซ้อนบน BS 5.3)
                 ⇒ option ที่ JS เติมทีหลังจะไม่โผล่ในดรอปดาวน์ ต้อง init ใหม่ทั้งตัว
               เทียบด้วยลายเซ็นจึงไม่วนซ้ำ: init เสร็จลายเซ็นตรงแล้ว mutation ที่เกิดจากตัว init เองไม่ทำอะไรต่อ
               (select2 อ่าน <option> สด ๆ ทุกครั้งที่กางอยู่แล้ว จึงไม่ต้อง init ใหม่) */
            var stale = (want === 'picker' && el._selOptSig !== optionsSig(el));
            if (cur === want && !stale) { syncSelect($el); return; }

            _selBusy = true;
            try {
                if (cur) destroySelect($el);

                if (want === 'select2') {
                    // อยู่ใน modal → ผูก dropdown กับ .modal-content (position:relative)
                    // ไม่ใช่ .modal (position:fixed + scroll) กันตำแหน่ง dropdown เพี้ยนตอน modal เลื่อน
                    var $mc = $el.closest('.modal-content');
                    $el.select2({
                        width: selectWidthOf($el),
                        tags: $el.hasClass('select2-tags'),
                        // ข้อความ hint ตอนยังไม่ได้เลือก — สำคัญกับ multiple ที่ไม่มี option "-- เลือก --"
                        placeholder: $el.attr('data-placeholder') || null,
                        dropdownParent: $mc.length ? $mc : $(document.body)
                    });
                } else {
                    // <option hidden> ของ HTML — bootstrap-select ไม่รู้จัก ต้องบอกด้วย data-hidden
                    $el.find('option[hidden]').attr('data-hidden', 'true');
                    $el.addClass('selectpicker');
                    // ช่องเล็ก (form-select-sm) ต้องได้ปุ่มเล็กตาม ไม่งั้นแถวตัวกรองสูงไม่เท่ากัน
                    if (!$el.attr('data-style')) {
                        $el.attr('data-style', $el.hasClass('form-select-sm') ? 'btn-default btn-sm' : 'btn-default');
                    }
                    $el.attr('data-width', selectWidthOf($el));
                    $el.selectpicker();
                }
                $el.data('enhanced', want);
                el._selOptSig = optionsSig(el);   // จำชุด option ที่ init ไปด้วย เอาไว้เทียบรอบหน้า
                observeSelectOptions(el);
            } finally {
                _selBusy = false;
            }
        }

        // ยกระดับทุก select ใน scope (ไม่ระบุ = ทั้งหน้า)
        function enhanceSelects(scope){
            $(scope || document).find('select').addBack('select').each(function(){
                enhanceSelect(this);
            });
        }

        // เรียกหลังเปลี่ยนค่า/เติม option เอง — ชนิดเดิมก็แค่ sync, ข้ามเกณฑ์แล้วสลับชนิดให้
        function refreshSelects(scope){
            $(scope || document).find('select').addBack('select').each(function(){
                if ($(this).data('enhanced')) enhanceSelect(this);
            });
        }

        // ── hook กลาง 1: option ถูกเติม/ลบด้วย JS (.html() / .append()) ──
        // รวมการแจ้งเป็นรอบเดียวด้วย setTimeout กันการวาดซ้ำถี่ ๆ ตอน append ทีละ option
        var _selObserver = null, _selDirty = [], _selTimer = null;
        function observeSelectOptions(el){
            if (typeof MutationObserver === 'undefined') return;
            if (el._selObserved) return;
            if (!_selObserver) {
                _selObserver = new MutationObserver(function(muts){
                    if (_selBusy) return;
                    muts.forEach(function(m){
                        if (_selDirty.indexOf(m.target) === -1) _selDirty.push(m.target);
                    });
                    clearTimeout(_selTimer);
                    _selTimer = setTimeout(function(){
                        var list = _selDirty.slice();
                        _selDirty = [];
                        list.forEach(function(node){ if (node.isConnected) enhanceSelect(node); });
                    }, 0);
                });
            }
            _selObserver.observe(el, { childList: true, attributes: true, attributeFilter: ['disabled'] });
            el._selObserved = true;
        }

        // ── hook กลาง 2: โค้ดเดิมที่ตั้งค่าด้วย .val() โดยไม่ได้สั่ง refresh ──
        // ครอบ $.fn.val ให้ sync หน้าปุ่ม/กล่องให้อัตโนมัติ เฉพาะ select ที่ยกระดับแล้ว
        (function(){
            var _val = $.fn.val;
            $.fn.val = function(){
                var out = _val.apply(this, arguments);
                if (arguments.length && !_selBusy) {
                    var $sel = this.filter('select');
                    if ($sel.length) {
                        _selBusy = true;
                        try {
                            $sel.each(function(){
                                var $el = $(this);
                                if ($el.data('enhanced')) syncSelect($el);
                            });
                        } finally { _selBusy = false; }
                    }
                }
                return out;
            };
        })();

        // ── hook กลาง 3: form.reset() ไม่ผ่าน .val() → sync หลังเบราว์เซอร์คืนค่าเริ่มต้นแล้ว ──
        $(document).on('reset', 'form', function(){
            var form = this;
            setTimeout(function(){ refreshSelects(form); }, 0);
        });

        // ── hook กลาง 4: ตอนถูกซ่อนอยู่ วัดความกว้างไม่ได้ ──
        // bootstrap-select/select2 คำนวณความกว้างตอน init — ถ้าตอนนั้นอยู่ในกล่องพับ
        // หรือใน modal ที่ยังไม่เปิด จะได้ความกว้างเพี้ยน → วาดใหม่ตอนโผล่มาจริง
        $(document).on('shown.bs.collapse shown.bs.modal', function(e){
            refreshSelects(e.target);
        });
    </script>

    <script>
            // get_summary_menu()
            // function get_summary_menu(){
            //     $.ajax({
            //         type: "GET",
            //         url: "get-summary-menu",
            //         success: function(data) {
            //             $("#countBill").html(data.overdue_bill);
            //             $("#countBookingRoom").html(data.booking_room);
            //         }
            //     });
            // }

    </script>

