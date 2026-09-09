{{-- ตารางงานที่รอ QC — โหลดผ่าน AJAX --}}
<div class="mb-2">
    <span class="text-muted small">พบทั้งหมด {{ number_format($total) }} รายการ</span>
</div>

<table class="table table-striped table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th class="text-center" style="width:50px;">#</th>
            <th>เลขที่ใบเบิก</th>
            <th>รหัสสี</th>
            <th>รหัสเครื่อง</th>
            <th class="text-end">จำนวน</th>
            <th class="text-center">ส่ง QC เมื่อ</th>
            <th class="text-center">ผล QC</th>
            <th class="text-center" style="width:200px;">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        @forelse($jobs as $i => $job)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $job->red_bill_code ?: '-' }}</td>
                <td><strong>{{ $job->itemno ?: '-' }}</strong></td>
                <td>{{ $job->machine_no ?: '-' }}</td>
                <td class="text-end">{{ $job->quantity !== null ? number_format($job->quantity, 2) : '-' }}</td>
                <td class="text-center">
                    @if($job->qc_date)
                        {{ \Carbon\Carbon::parse($job->qc_date)->format('d/m/Y') }}
                        @if($job->qc_time)
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($job->qc_time)->format('H:i') }}</div>
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if($job->qc_status_name)
                        <span class="badge bg-label-success">{{ $job->qc_status_name }}</span>
                        @if($job->qc_datetime)
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($job->qc_datetime)->format('d/m/Y H:i') }}</div>
                        @endif
                    @else
                        <span class="badge bg-label-secondary">ยังไม่ตรวจ</span>
                    @endif
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary" onclick="openResult({{ $job->id }})">
                        <i class="ti ti-clipboard-check me-1"></i>บันทึกผล QC
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openDetail({{ $job->id }})">
                        <i class="ti ti-eye"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">ไม่พบงานที่รอ QC ตามเงื่อนไขที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
