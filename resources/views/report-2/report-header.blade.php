<div class="row g-3 justify-content-between mb-4">
    <div class="col-sm-12">
        <h4 class="mb-0">
            <i class="tf-icons ti ti-chart-pie-3 text-main ti-md"></i>
            รายงานบิลค่าเช่า
        </h4>
    </div>
    <div class="col-sm-6">
        <div class="card card-border-shadow-success h-100">
            <div
                class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="card-icon me-3">
                        <span class="badge bg-label-success rounded p-2">
                            <i class="ti ti-check ti-26px"></i>
                        </span>
                    </div>
                    <h3 class="mb-0 me-2 text-success" id="paid">{{ $paid }}</h3>
                </div>
                <div class="card-title mb-0">
                    <p class="mb-0">ชำระแล้ว</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card card-border-shadow-danger bg-danger-subtle h-100">
            <div
                class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="card-icon me-3">
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="ti ti-x ti-26px"></i>
                        </span>
                    </div>
                    <h3 class="mb-0 me-2 text-danger" id="overdue">{{ $overdue }}</h3>
                </div>
                <div class="card-title mb-0">
                    <p class="mb-0">ยอดค้างชำระ</p>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div>
    <h5 class="card-title">แยกตามการชำระ</h5>
    <div class="row justify-content-center">
        <div class="col-sm-3">
            <div class="d-flex mb-3 pb-1 align-items-center">
                <div class="chart-progress me-3" data-color="primary"
                    data-series="{{ $percent_cash }}" data-progress_variant="true"></div>
                <div class="me-2">
                    <h6 class="mb-1">เงินสด</h6>
                    <small id="cash">{{ $cash }}</small>
                </div>
            </div>
        </div>
        {{-- <div class="col-sm-3">
            <div class="d-flex mb-3 pb-1 align-items-center">
                <div class="chart-progress me-3" data-color="success"
                    data-series="{{ $percent_cash }}" data-progress_variant="true"></div>
                <div class="me-2">
                    <h6 class="mb-1">เงินสดคอนเฟิร์มแล้ว</h6>
                    <small id="cash">{{ $cash }}</small>
                </div>
            </div>
        </div> --}}
        <div class="col-sm-3">
            <div class="d-flex mb-3 pb-1 align-items-center">
                <div class="chart-progress me-3" data-color="danger"
                    data-series="{{ $percent_transfer }}" data-progress_variant="true"></div>
                <div class="me-2">
                    <h6 class="mb-1">ผ่านการโอนเงิน </h6>
                    <small id="transfer">{{ $transfer }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
    <script src="/assets/js/app-academy-dashboard.js"></script>
