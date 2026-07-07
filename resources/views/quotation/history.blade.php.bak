{{-- ประวัติใบเสนอราคาทั้งหมดของลูกค้า 1 ราย (โหลดเข้า modal จาก quotation/history) --}}
<div class="qhist">

    {{-- แบนเนอร์หัว: ข้อมูลลูกค้า --}}
    <div class="qhist-head px-4 py-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="ti ti-history fs-4"></i>
            <span class="fw-bold fs-5">ประวัติใบเสนอราคา</span>
        </div>
        <div class="small opacity-75">
            <span class="badge bg-white text-dark me-1">{{ $custid }}</span>
            {{ $cust->name ?? '—' }}
            @if (!empty($cust->nameEN))
                <span class="opacity-75">({{ $cust->nameEN }})</span>
            @endif
            <span class="ms-2">— ทั้งหมด {{ number_format($list->count()) }} ใบ</span>
        </div>
    </div>

    <div class="p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">ลำดับ</th>
                        <th>เลขที่ / วันที่</th>
                        <th class="text-center">ชนิดสินค้า</th>
                        <th class="text-end">จำนวนรายการ</th>
                        <th class="text-end">มูลค่ารวม</th>
                        <th class="text-center" style="width:80px;">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list as $i => $row)
                        <tr>
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td>
                                <strong class="text-primary">{{ $row->Qno }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $row->Qdate ? \Carbon\Carbon::parse($row->Qdate)->format('d/m/Y') : '-' }}
                                </small>
                                @if ($row->Revisedate)
                                    <span class="badge bg-label-danger ms-1">ปรับราคา {{ \Carbon\Carbon::parse($row->Revisedate)->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($row->PDtype)
                                    <span class="badge bg-label-info">{{ $row->PDtype }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($row->item_count) }}</td>
                            <td class="text-end">{{ number_format($row->total_net, 2) }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-icon btn-label-secondary" title="ดูรายละเอียด"
                                    onclick="quotationView('{{ $row->Qno }}')">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                                ลูกค้ารายนี้ยังไม่มีใบเสนอราคา
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .qhist-head {
        background-color: #54BAB9;
        color: #fff;
    }
</style>
