{{-- ─── Summary cards (โหลดผ่าน AJAX จาก color-matching/summary) ─── --}}

<div class="col-sm-6 col-lg-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                    <span class="fw-semibold text-body">งานทั้งหมด</span>
                    <div class="d-flex align-items-center my-1">
                        <h4 class="mb-0 me-2 fw-bold text-heading">{{ number_format($summary['total']) }}</h4>
                    </div>
                    <small class="text-body-secondary">ใบเทียบสีในระบบ</small>
                </div>
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-secondary">
                        <i class="ti ti-files ti-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                    <span class="fw-semibold text-body">รอวัตถุดิบ</span>
                    <div class="d-flex align-items-center my-1">
                        <h4 class="mb-0 me-2 fw-bold text-heading">{{ number_format($summary['waiting']) }}</h4>
                    </div>
                    <small class="text-body-secondary">มีค่า RminWating</small>
                </div>
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-warning">
                        <i class="ti ti-hourglass ti-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                    <span class="fw-semibold text-body">กำลังเทียบสี</span>
                    <div class="d-flex align-items-center my-1">
                        <h4 class="mb-0 me-2 fw-bold text-heading">{{ number_format($summary['matching']) }}</h4>
                    </div>
                    <small class="text-body-secondary">ยังไม่มีเลขที่ใบส่ง ต.ย.</small>
                </div>
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-info">
                        <i class="ti ti-palette ti-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                    <span class="fw-semibold text-body">ส่ง ต.ย. ให้ลูกค้าแล้ว</span>
                    <div class="d-flex align-items-center my-1">
                        <h4 class="mb-0 me-2 fw-bold text-heading">{{ number_format($summary['sent_customer']) }}</h4>
                    </div>
                    <small class="text-body-secondary">มีเลขที่ใบส่ง ต.ย.</small>
                </div>
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-success">
                        <i class="ti ti-package-export ti-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
