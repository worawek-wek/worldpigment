@php
    // ระดับความลึกของ semi ย่อย (0 = ตัวบนสุด) ใช้เยื้องแสดงเป็นชั้นๆ
    $level = $level ?? 0;
    $semi  = $node['semi'];
    $plan  = $node['planning'] ?? null;

    // สีของ badge ตามสถานะ semi/pigment
    $spStatusCls = [
        'request'  => 'bg-label-warning',
        'approved' => 'bg-label-success',
        'reject'   => 'bg-label-danger',
    ];
    $planStatus = $plan['planning_status'] ?? null;
@endphp

<div class="semi-plan-node mb-3 @if($level > 0) ms-3 ps-3 border-start border-2 @endif">
    {{-- ── หัวข้อ Semi (semi_code / primary_color) ── --}}
    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
        <span class="badge bg-primary"><i class="ti ti-box me-1"></i>Semi</span>
        <strong>{{ $semi['semi_code'] ?: '-' }}</strong>
        @if(!empty($semi['primary_color']))
            <span class="badge bg-label-info">สีหลัก: {{ $semi['primary_color'] }}</span>
        @endif
        <span class="text-muted small">Item No.: {{ $semi['itemno'] ?: '-' }}</span>
        <span class="badge {{ $spStatusCls[$semi['status']] ?? 'bg-label-secondary' }}">{{ $semi['status_label'] }}</span>
    </div>

    {{-- ── ตารางแผนการผลิตที่สร้างจาก Semi นี้ ── --}}
    <div class="table-responsive mb-2">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th style="min-width:150px">รหัสวางแผน</th>
                    <th style="min-width:120px">รหัส Order</th>
                    <th class="text-center" style="min-width:120px">สถานะการผลิต</th>
                    <th class="text-center" style="min-width:140px">วันที่วางแผนผลิต</th>
                </tr>
            </thead>
            <tbody>
                @if($plan)
                <tr>
                    <td>{{ $plan['planning_code'] ?: '-' }}</td>
                    <td>{{ $plan['orderno'] ?: '-' }}</td>
                    <td class="text-center">
                        @if($planStatus)
                            <span class="badge bg-label-primary">{{ $planStatus }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ !empty($plan['inplan']) ? \Carbon\Carbon::parse($plan['inplan'])->format('d/m/Y') : '-' }}
                    </td>
                </tr>
                @else
                <tr>
                    <td colspan="4" class="text-center text-muted py-2">ยังไม่ได้สร้างแผนการผลิต</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- ── ตาราง Pigment ของแผนนี้ ── --}}
    @if(!empty($node['child_pigments']))
    <div class="mb-2">
        <div class="small fw-semibold text-success mb-1">
            <i class="ti ti-color-swatch me-1"></i>Pigment
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th class="text-center" style="width:36px">#</th>
                        <th style="min-width:110px">วันที่สั่ง</th>
                        <th style="min-width:120px">วันที่ต้องการรับ</th>
                        <th style="min-width:100px">Cust No.</th>
                        <th style="min-width:130px">Item No.</th>
                        <th style="min-width:80px">Quantity</th>
                        <th class="text-center" style="min-width:90px">สถานะ</th>
                        <th class="text-center" style="min-width:110px">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($node['child_pigments'] as $pi => $pg)
                    <tr>
                        <td class="text-center">{{ $pi + 1 }}</td>
                        <td>{{ !empty($pg['order_date']) ? \Carbon\Carbon::parse($pg['order_date'])->format('d/m/Y') : '-' }}</td>
                        <td>{{ !empty($pg['want_date'])  ? \Carbon\Carbon::parse($pg['want_date'])->format('d/m/Y')  : '-' }}</td>
                        <td>{{ $pg['custno'] ?: '-' }}</td>
                        <td>{{ $pg['itemno'] ?: '-' }}</td>
                        <td>{{ $pg['weight_request'] ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $spStatusCls[$pg['status']] ?? 'bg-label-secondary' }}">{{ $pg['status_label'] }}</span>
                        </td>
                        <td class="text-center">
                            @if(!empty($pg['plan_code']))
                                <span class="badge bg-label-primary" title="รหัสวางแผน: {{ $pg['plan_code'] }}">
                                    {{ $pg['plan_status'] ?: 'สร้างแผนแล้ว' }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Semi ย่อย (recursive) ── --}}
    @if(!empty($node['child_semis']))
        @foreach($node['child_semis'] as $child)
            @include('production-planning.planning.partials.semi-plan-tree', ['node' => $child, 'level' => $level + 1])
        @endforeach
    @endif
</div>
