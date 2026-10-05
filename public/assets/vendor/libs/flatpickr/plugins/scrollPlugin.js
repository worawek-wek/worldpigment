/*
 * flatpickr scrollPlugin — หมุนล้อเมาส์เพื่อเพิ่ม/ลดค่าในช่องชั่วโมง/นาที (และปี/เดือนของปฏิทิน)
 * ดัดแปลงจาก scrollPlugin ของ flatpickr v4 (MIT) เก็บไว้ในเครื่อง เพราะระบบต้องใช้ได้โดยไม่ต่อเน็ต
 * ต่างจากต้นฉบับ: ยิง event ไปที่ช่องตัวเลขเสมอ แม้ตัวชี้จะอยู่บนลูกศรขึ้น/ลงข้างช่อง
 *
 * ใช้งาน: flatpickr(el, { plugins: [scrollPlugin()] })
 */
(function (global) {
    'use strict';

    function delta(e) {
        return Math.max(-1, Math.min(1, e.wheelDelta || -e.deltaY));
    }

    // หาช่องตัวเลข (ชั่วโมง/นาที/ปี) ที่ตัวชี้เมาส์อยู่ — flatpickr อ่าน min/max/step จากช่องนี้
    function numInputOf(target) {
        if (!target || !target.closest) return null;
        if (target.tagName === 'INPUT') return target;
        var wrapper = target.closest('.numInputWrapper');
        return wrapper ? wrapper.querySelector('input') : null;
    }

    function scroll(e) {
        var input = numInputOf(e.target);
        if (!input) return;
        e.preventDefault();
        var ev = new CustomEvent('increment', { bubbles: true });
        ev.delta = delta(e);
        input.dispatchEvent(ev);
    }

    function scrollMonth(fp) {
        return function (e) {
            e.preventDefault();
            fp.changeMonth(delta(e));
        };
    }

    function scrollPlugin() {
        return function (fp) {
            var monthScroller = scrollMonth(fp);
            return {
                onReady: function () {
                    if (fp.timeContainer) {
                        fp.timeContainer.addEventListener('wheel', scroll);
                    }
                    if (fp.yearElements) {
                        fp.yearElements.forEach(function (yearElem) {
                            yearElem.addEventListener('wheel', scroll);
                        });
                    }
                    if (fp.monthElements) {
                        fp.monthElements.forEach(function (monthElem) {
                            monthElem.addEventListener('wheel', monthScroller);
                        });
                    }
                    fp.loadedPlugins.push('scroll');
                },
                onDestroy: function () {
                    if (fp.timeContainer) {
                        fp.timeContainer.removeEventListener('wheel', scroll);
                    }
                    if (fp.yearElements) {
                        fp.yearElements.forEach(function (yearElem) {
                            yearElem.removeEventListener('wheel', scroll);
                        });
                    }
                    if (fp.monthElements) {
                        fp.monthElements.forEach(function (monthElem) {
                            monthElem.removeEventListener('wheel', monthScroller);
                        });
                    }
                }
            };
        };
    }

    global.scrollPlugin = scrollPlugin;
})(window);
