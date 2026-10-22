{{-- แถวการบรรจุ 1 แถว ใน modal อัพเดทสถานะ (Worker) — $row = PlanningPacking (แถวเดิม) หรือ null (แถวใหม่)
     แถวเดิม: อ่านอย่างเดียว (readonly, ไม่มีปุ่มลบ, ไม่ถูกส่งไป server) · แถวใหม่: กรอกได้ ลบได้ก่อนบันทึก (22/10/2569)
     จอกว้าง: หัวคอลัมน์อยู่ที่ status-form (แถวนี้ไม่มี label) · จอแคบ: มี label เล็กในแต่ละช่อง (d-md-none) --}}
@if($row)
<div class="row gx-2 gy-1 align-items-center py-2 border-bottom worker-packing-row wpk-saved">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">วันเวลาที่บรรจุเสร็จ</label>
        <input type="text" class="form-control form-control-sm bg-light" readonly tabindex="-1"
               value="{{ $row->packing_datetime ? \Carbon\Carbon::parse($row->packing_datetime)->format('d/m/Y H:i') : '' }}">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0 d-md-none">น้ำหนักบรรจุได้</label>
        <input type="text" class="form-control form-control-sm bg-light text-end" readonly tabindex="-1"
               value="{{ $row->weight_packing !== null ? number_format((float) $row->weight_packing, 2) : '' }}">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">หมายเหตุ</label>
        <input type="text" class="form-control form-control-sm bg-light" readonly tabindex="-1" value="{{ $row->pack_remark ?? '' }}">
    </div>
    <div class="col-12 col-md-1 text-md-center">
        <i class="ti ti-lock text-success fs-5" title="ข้อมูลที่บันทึกแล้ว แก้ไข/ลบไม่ได้"></i>
    </div>
</div>
@else
<div class="row gx-2 gy-1 align-items-center py-2 border-bottom worker-packing-row wpk-new">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">วันเวลาที่บรรจุเสร็จ</label>
        <input type="text" class="form-control form-control-sm wpk-datetime" autocomplete="off">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0 d-md-none">น้ำหนักบรรจุได้</label>
        <input type="text" inputmode="decimal" class="form-control form-control-sm text-end js-comma wpk-weight">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">หมายเหตุ</label>
        <input type="text" class="form-control form-control-sm wpk-remark" maxlength="1000">
    </div>
    <div class="col-12 col-md-1 text-md-center">
        <button type="button" class="btn btn-sm btn-outline-danger btn_worker_remove_packing" title="ลบแถวใหม่"><i class="ti ti-trash"></i></button>
    </div>
</div>
@endif
