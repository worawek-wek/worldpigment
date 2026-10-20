{{-- ตารางงานของพนักงานหน้างาน (Worker) — โหลดผ่าน AJAX
     ค้นด้วยเลขที่ใบเบิกแล้วเจองานของคนอื่นในแผนกได้ (20/10/2569) → คอลัมน์ "ผู้รับผิดชอบ" บอกว่าเป็นงานของใคร --}}
<div class="mb-2">
    <span class="text-muted small">
        {{ $searching ? 'ผลการค้นหา' : 'งานที่จ่ายให้คุณ' }} — พบทั้งหมด {{ number_format($total) }} รายการ
    </span>
</div>

@if($searching && $no_dept)
    <div class="alert alert-warning py-2 small">
        บัญชีของคุณยังไม่ได้ระบุแผนก จึงค้นได้เฉพาะงานที่จ่ายให้คุณ — กรุณาแจ้งผู้ดูแลให้กำหนดแผนกที่หน้า "จัดการพนักงาน"
    </div>
@endif

<table class="table table-striped table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th class="text-center" style="width:50px;">#</th>
            <th>เลขที่ใบเบิก</th>
            <th>รหัสสี</th>
            <th>รหัสเครื่อง</th>
            <th class="text-end">จำนวน</th>
            <th class="text-center">วันที่ (Inplan)</th>
            <th>ผู้รับผิดชอบ (ตามแผน)</th>
            <th class="text-center">สถานะ</th>
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
                <td class="text-center">{{ $job->inplan ? \Carbon\Carbon::parse($job->inplan)->format('d/m/Y') : '-' }}</td>
                <td>
                    @if($job->empno !== null && $job->empno === $my_empno)
                        <span class="badge bg-label-success">งานของฉัน</span>
                    @else
                        {{ trim(($job->empname ?? '').' '.($job->empsur ?? '')) ?: ($job->empno ?: 'ไม่ระบุ') }}
                        <span class="badge bg-label-warning ms-1">ทำแทน</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($job->planning_status)
                        <span class="badge bg-label-info">{{ $job->planning_status }}</span>
                    @else
                        <span class="badge bg-label-secondary">ยังไม่ระบุ</span>
                    @endif
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary" onclick="openStatus({{ $job->id }})">
                        <i class="ti ti-edit me-1"></i>อัพเดทสถานะ
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openDetail({{ $job->id }})">
                        <i class="ti ti-eye"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">ไม่พบงานตามเงื่อนไขที่เลือก</td>
            </tr>
        @endforelse
    </tbody>
</table>
