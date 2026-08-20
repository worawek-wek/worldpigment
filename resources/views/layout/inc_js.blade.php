
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

