{{-- Modal อัพเดทสถานะงาน (Worker) --}}
<div class="modal-header">
    <h5 class="modal-title"><i class="ti ti-edit me-1"></i>อัพเดทสถานะงาน</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <input type="hidden" id="status_planning_id" value="{{ $job->id }}">

    <div class="row g-2 mb-2">
        <div class="col-6">
            <div class="small text-muted">รหัสสี</div>
            <div class="fw-semibold">{{ $job->itemno ?: '-' }}</div>
        </div>
        <div class="col-6">
            <div class="small text-muted">รหัสเครื่อง</div>
            <div class="fw-semibold">{{ $job->machine_no ?: '-' }}</div>
        </div>
        <div class="col-6">
            <div class="small text-muted">เลขที่ใบเบิก</div>
            <div class="fw-semibold">{{ $job->red_bill_code ?: '-' }}</div>
        </div>
        <div class="col-6">
            <div class="small text-muted">สถานะปัจจุบัน</div>
            <div class="fw-semibold">{{ $job->planning_status ?: 'ยังไม่ระบุ' }}</div>
        </div>
    </div>

    <label class="form-label">เลือกสถานะใหม่</label>
    <select id="status_select" class="form-select">
        <option value="">-- เลือกสถานะ --</option>
        @forelse($statuses as $st)
            <option value="{{ $st->name }}" data-packing="{{ ($st->is_packing ?? 'N') === 'Y' ? 'Y' : 'N' }}" data-warehouse="{{ ($st->is_warehouse ?? 'N') === 'Y' ? 'Y' : 'N' }}" @selected($job->planning_status === $st->name)>{{ $st->name }}</option>
        @empty
            <option value="" disabled>ไม่มีสถานะของแผนกนี้</option>
        @endforelse
    </select>
    @if($statuses->isEmpty())
        <div class="text-warning small mt-2"><i class="ti ti-alert-triangle me-1"></i>ยังไม่มีสถานะที่กำหนดไว้สำหรับแผนกของงานนี้</div>
    @endif

    {{-- การ์ดการบรรจุ (22/10/2569) — แสดงเมื่อเลือกสถานะที่ is_packing='Y' · บันทึกลง tb_planning_packing --}}
    <style>
        /* flatpickr static:true ห่อ input ด้วย .flatpickr-wrapper (inline-block) → ให้เต็มช่อง ไม่งั้นช่องหดซ้อนกัน */
        #worker_packing_card .flatpickr-wrapper { display: block; width: 100%; }
        #worker_packing_card .wpk-head { font-size: .8rem; color: #5d596c; font-weight: 600; }
        #worker_packing_card .worker-packing-row { margin-left: 0; margin-right: 0; }
        #worker_packing_card .worker-packing-row.wpk-new { background: #fff; }
        #worker_warehouse_card .flatpickr-wrapper { display: block; width: 100%; }
        #worker_warehouse_card .wpk-head { font-size: .8rem; color: #5d596c; font-weight: 600; }
        #worker_warehouse_card .worker-warehouse-row { margin-left: 0; margin-right: 0; }
        #worker_warehouse_card .worker-warehouse-row.wwh-new { background: #fff; }
    </style>
    <div id="worker_packing_card" class="border border-success rounded p-3 mt-3 d-none" style="border-style:dashed !important; background:#f2fbe9;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-success fw-semibold"><i class="ti ti-package me-1"></i>การบรรจุ</span>
            <button type="button" id="btn_worker_add_packing" class="btn btn-sm btn-primary">
                <i class="ti ti-plus me-1"></i>เพิ่ม
            </button>
        </div>
        {{-- หัวคอลัมน์ (จอกว้าง) — ความกว้างตรงกับ packing-row --}}
        <div class="row gx-2 d-none d-md-flex border-bottom pb-1 wpk-head" style="margin-left:0;margin-right:0;">
            <div class="col-md-4">วันเวลาที่บรรจุเสร็จ</div>
            <div class="col-md-3">น้ำหนักบรรจุได้</div>
            <div class="col-md-4">หมายเหตุ</div>
            <div class="col-md-1 text-center">ลบ</div>
        </div>
        <div id="worker_packing_rows">
            @foreach($packing_rows as $row)
                @include('worker.planning.partials.packing-row', ['row' => $row])
            @endforeach
            {{-- แถวใหม่ว่าง 1 แถวไว้กรอกเลย (ว่างทั้งแถว = ไม่บันทึก) --}}
            @include('worker.planning.partials.packing-row', ['row' => null])
        </div>
        <div class="small text-muted mt-2">
            <i class="ti ti-lock me-1"></i>แถวที่บันทึกแล้วอ่านอย่างเดียว (แก้ไข/ลบไม่ได้) · น้ำหนักที่ผลิตได้ = ผลรวมน้ำหนักบรรจุทุกแถว
        </div>
    </div>
    <template id="worker_packing_row_tpl">
        @include('worker.planning.partials.packing-row', ['row' => null])
    </template>

    {{-- การ์ดการเข้าคลัง (29/10/2569) — แสดงเมื่อเลือกสถานะที่ is_warehouse='Y' · บันทึกลง tb_planning_warehouse (เพิ่มได้อย่างเดียว) --}}
    <div id="worker_warehouse_card" class="border border-warning rounded p-3 mt-3 d-none" style="border-style:dashed !important; background:#fff6e5;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-warning fw-semibold"><i class="ti ti-building-warehouse me-1"></i>การเข้าคลัง</span>
            <button type="button" id="btn_worker_add_warehouse" class="btn btn-sm btn-primary">
                <i class="ti ti-plus me-1"></i>เพิ่ม
            </button>
        </div>
        <div class="row gx-2 d-none d-md-flex border-bottom pb-1 wpk-head" style="margin-left:0;margin-right:0;">
            <div class="col-md-4">วันเวลาที่เข้าคลัง</div>
            <div class="col-md-3">น้ำหนักเข้าคลัง</div>
            <div class="col-md-4">หมายเหตุ</div>
            <div class="col-md-1 text-center">ลบ</div>
        </div>
        <div id="worker_warehouse_rows">
            @foreach($warehouse_rows as $row)
                @include('worker.planning.partials.warehouse-row', ['row' => $row])
            @endforeach
            @include('worker.planning.partials.warehouse-row', ['row' => null])
        </div>
        <div class="small text-muted mt-2">
            <i class="ti ti-lock me-1"></i>แถวที่บันทึกแล้วอ่านอย่างเดียว (แก้ไข/ลบไม่ได้)
        </div>
    </div>
    <template id="worker_warehouse_row_tpl">
        @include('worker.planning.partials.warehouse-row', ['row' => null])
    </template>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="button" class="btn btn-primary" onclick="submitStatus()" @disabled($statuses->isEmpty())>
        <i class="ti ti-device-floppy me-1"></i>บันทึก
    </button>
</div>
