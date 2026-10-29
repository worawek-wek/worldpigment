{{-- แถวการเข้าคลัง 1 แถว ใน modal อัพเดทสถานะ (Worker) — $row = PlanningWarehouse (แถวเดิม) หรือ null (แถวใหม่)
     แถวเดิม: อ่านอย่างเดียว (readonly, ไม่มีปุ่มลบ, ไม่ถูกส่งไป server) · แถวใหม่: กรอกได้ ลบได้ก่อนบันทึก (29/10/2569)
     ผังเดียวกับ packing-row — ความกว้างคอลัมน์ 4/3/4/1 ต้องตรงกับหัวคอลัมน์ใน status-form --}}
@if($row)
<div class="row gx-2 gy-1 align-items-center py-2 border-bottom worker-warehouse-row wwh-saved">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">วันเวลาที่เข้าคลัง</label>
        <input type="text" class="form-control form-control-sm bg-light" readonly tabindex="-1"
               value="{{ $row->warehouse_datetime ? \Carbon\Carbon::parse($row->warehouse_datetime)->format('d/m/Y H:i') : '' }}">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0 d-md-none">น้ำหนักเข้าคลัง</label>
        <input type="text" class="form-control form-control-sm bg-light text-end" readonly tabindex="-1"
               value="{{ $row->weight_warehouse !== null ? number_format((float) $row->weight_warehouse, 2) : '' }}">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">หมายเหตุ</label>
        <input type="text" class="form-control form-control-sm bg-light" readonly tabindex="-1" value="{{ $row->warehouse_remark ?? '' }}">
    </div>
    <div class="col-12 col-md-1 text-md-center">
        <i class="ti ti-lock text-warning fs-5" title="ข้อมูลที่บันทึกแล้ว แก้ไข/ลบไม่ได้"></i>
    </div>
</div>
@else
<div class="row gx-2 gy-1 align-items-center py-2 border-bottom worker-warehouse-row wwh-new">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">วันเวลาที่เข้าคลัง</label>
        <input type="text" class="form-control form-control-sm wwh-datetime" autocomplete="off">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0 d-md-none">น้ำหนักเข้าคลัง</label>
        <input type="text" inputmode="decimal" class="form-control form-control-sm text-end js-comma wwh-weight">
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">หมายเหตุ</label>
        <input type="text" class="form-control form-control-sm wwh-remark" maxlength="1000">
    </div>
    <div class="col-12 col-md-1 text-md-center">
        <button type="button" class="btn btn-sm btn-outline-danger btn_worker_remove_warehouse" title="ลบแถวใหม่"><i class="ti ti-trash"></i></button>
    </div>
</div>
@endif
