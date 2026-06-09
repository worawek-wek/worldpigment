{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal shell: ดูรายละเอียด (อ่านอย่างเดียว)                            --}}
{{-- เนื้อหา (modal-content) โหลดผ่าน AJAX จาก color-matching/detail/{id}  --}}
{{-- → ดูที่ color-matching/detail.blade.php                               --}}
{{-- ชั้น 1 (detailModal) = CM/SD หลัก                                     --}}
{{-- ชั้น 2 (detailModal2) = SD ที่กดดูจากในรายละเอียด CM (ซ้อนทับ)        --}}
{{-- Trigger: viewDetail(id) / viewDetail2(id)                            --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade modalHeadDecor" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" id="detailView">
        {{-- เติมจาก AJAX --}}
    </div>
</div>

<div class="modal fade modalHeadDecor" id="detailModal2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" id="detailView2">
        {{-- เติมจาก AJAX (ชั้น 2) --}}
    </div>
</div>
