{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Modal: ตั้งค่าเงื่อนไขราคา (10/08/2569)                                --}}
{{-- Trigger: data-bs-target="#priceRuleModal"                            --}}
{{--                                                                      --}}
{{-- แก้ได้เฉพาะ 3 ช่อง: ×คูณ / ÷หาร / บวก+  (ราคาขาย 1 = ราคาทุน × คูณ ÷ หาร + บวก) --}}
{{-- ส่วนชื่อเงื่อนไข / ตัวขึ้นต้น / ตัวลงท้าย แก้ที่ config/product_price.php เท่านั้น  --}}
{{-- ค่าที่แก้เก็บลง tb_price_rule แถวไหนไม่เคยแก้ = ใช้ค่าตั้งต้นจาก config  --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal modalHeadDecor fade" id="priceRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-adjustments-alt me-1"></i>
                    ตั้งค่าเงื่อนไขราคา
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3" style="background-color: #f8f9fb;">

                {{-- ─── คำอธิบายสูตร ─── --}}
                <div class="mb-3 p-3 rounded small" style="background-color: #eef2f7; border: 1px solid #d5dce6;">
                    <div class="fw-semibold mb-1">
                        <i class="ti ti-math-symbols me-1"></i>
                        ราคาขาย 1 = ราคาทุน × คูณ ÷ หาร + บวก
                    </div>
                    <div class="text-muted">
                        แก้ได้เฉพาะ 3 ช่องนี้ — ส่วนชื่อเงื่อนไขและตัวขึ้นต้น/ลงท้ายของรหัส
                        ต้องให้ผู้ดูแลระบบแก้ให้ · แถวที่เคยแก้ไว้จะมีป้าย
                        <span class="badge bg-label-warning">แก้แล้ว</span>
                    </div>
                </div>

                {{-- ─── ตารางเงื่อนไข ─── --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" id="pr_table">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="min-width: 220px;">เงื่อนไข</th>
                                <th style="width: 120px;">× คูณ</th>
                                <th style="width: 120px;">÷ หาร</th>
                                <th style="width: 120px;">บวก +</th>
                            </tr>
                        </thead>
                        <tbody id="pr_tbody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">กำลังโหลด…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="small mt-2 text-danger d-none" id="pr_error" style="white-space: pre-line;"></div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    ปิด
                </button>
                <button type="button" class="btn btn-theme-saleinfo" id="pr_save">
                    <i class="ti ti-device-floppy me-1"></i>
                    บันทึก
                </button>
            </div>

        </div>
    </div>
</div>
