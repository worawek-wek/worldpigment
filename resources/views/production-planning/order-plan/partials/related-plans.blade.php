{{--
    แสดง "รายการที่เกี่ยวข้อง (Semi / Pigment)" ของ planning item หนึ่งๆ แบบ recursive
    ตัวแปรที่รับ:
      - $planning : Planning item (ต้องโหลด subHeadersRecursive มาแล้ว)
--}}
@php
    $subHeaders = $planning->subHeadersRecursive ?? collect();
@endphp
@if($subHeaders->count())
    <div class="small text-muted mb-1">
        <i class="ti ti-corner-down-right me-1"></i>รายการที่เกี่ยวข้อง (Semi / Pigment)
    </div>
    <table class="table table-sm table-bordered mb-2 bg-white">
        <thead>
            <tr>
                <th class="text-center" style="width:48px">#</th>
                <th class="text-center">ประเภท</th>
                <th>Planning Code</th>
                <th>Item No.</th>
                <th class="text-center">Lot</th>
                <th class="text-center">Weight</th>
                <th class="text-center">Machine No.</th>
                <th class="text-center">Inplan</th>
                <th class="text-center">Custwant</th>
                <th class="text-center">สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subHeaders as $sub)
                @forelse($sub->planningsRecursive as $subItem)
                <tr>
                    <td class="text-center">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                    <td class="text-center">
                        <span class="badge bg-label-{{ strtolower($sub->plan_type) === 'semi' ? 'warning' : 'success' }}">
                            {{ strtoupper($sub->plan_type) }}
                        </span>
                    </td>
                    <td>{{ $sub->planning_code ?? '-' }}</td>
                    <td>{{ $subItem->itemno ?? '-' }}</td>
                    <td class="text-center">{{ $subItem->lot ?? '-' }}</td>
                    <td class="text-center">{{ $subItem->weight ? number_format($subItem->weight, 2) : '-' }}</td>
                    <td class="text-center">{{ $subItem->machine_no ?? '-' }}</td>
                    <td class="text-center">{{ $subItem->inplan ? \Carbon\Carbon::parse($subItem->inplan)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $subItem->custwant ? \Carbon\Carbon::parse($subItem->custwant)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @if($subItem->planning_status)
                            <span class="badge bg-label-info">{{ $subItem->planning_status }}</span>
                        @else
                            <span class="badge bg-label-secondary">-</span>
                        @endif
                        {{-- บรรทัดที่ 2: สถานะปิดงานของ item ย่อย (อ้างอิงคอลัมน์ end_job ของ tb_planning) --}}
                        <br>
                        @if(($subItem->end_job ?? 'N') === 'Y')
                            <span class="badge bg-label-success">ปิดงาน</span>
                        @else
                            <span class="badge bg-label-warning">ยังไม่ปิดงาน</span>
                        @endif
                    </td>
                </tr>

                {{-- recurse: ถ้า planning item ย่อยนี้ยังมีย่อยลงไปอีก --}}
                @if(($subItem->subHeadersRecursive ?? collect())->count())
                <tr>
                    <td></td>
                    <td colspan="9" class="bg-light">
                        @include('production-planning.order-plan.partials.related-plans', ['planning' => $subItem])
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">
                        <span class="badge bg-label-{{ strtolower($sub->plan_type) === 'semi' ? 'warning' : 'success' }}">
                            {{ strtoupper($sub->plan_type) }}
                        </span>
                    </td>
                    <td>{{ $sub->planning_code ?? '-' }}</td>
                    <td colspan="7" class="text-muted">ยังไม่มีรายการ</td>
                </tr>
                @endforelse
            @endforeach
        </tbody>
    </table>
@endif
