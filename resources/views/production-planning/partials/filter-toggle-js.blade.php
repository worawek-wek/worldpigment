{{--
    JS ของแถบตัวกรองแบบพับได้ (คู่กับ partials/filter-toggle) — 07/11/2569
    include ใน section('script') ก่อน script ของหน้า (ต้องมาหลัง jQuery ซึ่ง layout/main โหลดไว้แล้ว)

    plInitFilterToggle({
        box:   '#plFilterBox',    // กล่อง collapse ที่ครอบตัวกรอง
        table: '#dataTable',      // DataTable — สรุปใหม่ทุกครั้งที่ตารางยิง request (ไม่ระบุ = สรุปใหม่ทุก AJAX ของหน้า)
        reset: function(){ ... }  // ล้างค่าทุกช่องของหน้านั้น แล้ว draw ตารางเอง
    });
--}}
<script>
    window.plInitFilterToggle = function (opts) {
        var $box = $(opts.box);
        var $bar = $('.pl-filter-bar[data-box="' + opts.box + '"]');

        function escHtml(s) { return $('<div>').text(s == null ? '' : s).html(); }

        // ค่าที่ผู้ใช้เห็น — select ใช้ข้อความตัวเลือก · flatpickr ที่มี altInput ใช้ช่องที่แสดง (d/m/Y)
        function displayValue(el) {
            if ($(el).is('select')) return $(el).find('option:selected').text().trim();
            if (el._flatpickr && el._flatpickr.altInput) return el._flatpickr.altInput.value;
            return $(el).val();
        }

        function refresh() {
            var chips = [];
            $box.find('[data-fl-label]').each(function () {
                var raw = $(this).val();
                var def = $(this).attr('data-fl-default') || '';
                if (raw === null || raw === '' || raw === def) return;

                var label  = $(this).attr('data-fl-label');
                var prefix = $(this).attr('data-fl-prefix');
                if (prefix) label = $(prefix).find('option:selected').text().trim() + ' ' + label;

                chips.push('<span class="badge bg-label-primary fw-normal">'
                    + '<span class="text-dark fw-medium">' + escHtml(label) + ':</span> '
                    + escHtml(displayValue(this)) + '</span>');
            });

            $bar.find('.pl-filter-summary').html(chips.join(''));   // ไม่มีตัวกรอง = ปล่อยว่าง
            var $btn = $bar.find('.pl-filter-reset');
            if (chips.length) {
                $btn.removeClass('btn-label-secondary').addClass('btn-danger');
                $btn.find('.filter-count').text('(' + chips.length + ')');
            } else {
                $btn.removeClass('btn-danger').addClass('btn-label-secondary');
                $btn.find('.filter-count').text('');
            }
        }

        // หมุนลูกศร + ซ่อนแถบสรุปตอนกาง (ข้างในเห็นค่าจริงอยู่แล้ว)
        $box.on('show.bs.collapse hide.bs.collapse', function (e) {
            if (e.target !== this) return;
            $bar.find('.toggle-caret')
                .toggleClass('ti-chevron-down', e.type === 'hide')
                .toggleClass('ti-chevron-up',   e.type === 'show');
            $bar.find('.pl-filter-summary').toggleClass('d-none', e.type === 'show');
        });

        $bar.on('click', '.pl-filter-reset', function (e) {
            e.preventDefault();
            if (typeof opts.reset === 'function') opts.reset();
            refresh();
        });

        // ทุกการกรองสั่ง draw ตาราง → สรุปใหม่ตอนตารางยิง request
        // หน้ารายงาน (ไม่มี DataTable — โหลดผลด้วย $.ajax) ไม่ต้องส่ง table → สรุปใหม่ทุกครั้งที่หน้ายิง AJAX
        if (opts.table) $(opts.table).on('preXhr.dt', refresh);
        else            $(document).ajaxSend(refresh);
        // ช่องที่เปลี่ยนแล้วไม่ได้ draw ทันที (เช่น ช่องเวลาที่หน่วงไว้) ก็อัปเดตให้ด้วย
        $box.on('input change', refresh);

        refresh();
    };
</script>
