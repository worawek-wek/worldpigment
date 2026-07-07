{{-- รายชื่อลูกค้าทั้งหมดที่มีใบเสนอราคา (ขั้นแรก) — กดลูกค้า → เปิดประวัติใบของรายนั้น --}}
<div class="qcust">

    {{-- แบนเนอร์หัว --}}
    <div class="qcust-head px-4 py-3">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="ti ti-users fs-4"></i>
            <span class="fw-bold fs-5">ประวัติใบเสนอราคา — เลือกลูกค้า</span>
        </div>
        <div class="small opacity-75">ลูกค้าทั้งหมด {{ number_format($list->count()) }} ราย — กดที่ลูกค้าเพื่อดูใบเสนอราคา</div>
    </div>

    <div class="p-3">
        {{-- ค้นหา (กรองฝั่ง client) --}}
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control" placeholder="ค้นหารหัส / ชื่อลูกค้า"
                oninput="filterCustomerRows(this.value)">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="qcustTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:50px;">ลำดับ</th>
                        <th style="width:120px;">รหัสลูกค้า</th>
                        <th>ชื่อลูกค้า</th>
                        <th class="text-end" style="width:130px;">ใบเสนอราคา</th>
                        <th class="text-center" style="width:110px;">ล่าสุด</th>
                        <th class="text-center" style="width:70px;">ดู</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list as $i => $row)
                        <tr class="qcust-row" style="cursor:pointer;"
                            data-search="{{ strtolower($row->custid . ' ' . $row->name . ' ' . $row->nameEN) }}"
                            onclick="quotationHistory('{{ $row->custid }}')">
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td><span class="badge bg-label-secondary">{{ $row->custid }}</span></td>
                            <td>
                                <strong>{{ $row->name ?: '—' }}</strong>
                                @if (!empty($row->nameEN))
                                    <br><small class="text-muted">{{ $row->nameEN }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="badge bg-label-primary">{{ number_format($row->qcount) }} ใบ</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $row->last_date ? \Carbon\Carbon::parse($row->last_date)->format('d/m/Y') : '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <i class="ti ti-chevron-right text-muted"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-database-off fs-2 d-block mb-2 opacity-50"></i>
                                ยังไม่มีใบเสนอราคาในระบบ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- แถวว่างเมื่อกรองแล้วไม่พบ --}}
        <div id="qcustNoResult" class="text-center py-4 text-muted d-none">
            <i class="ti ti-search-off fs-4 d-block mb-1 opacity-50"></i>ไม่พบลูกค้าที่ตรงกับคำค้น
        </div>
    </div>

</div>

<script>
    // กรองแถวลูกค้าฝั่ง client (ไม่ยิง server ซ้ำ)
    function filterCustomerRows(term){
        term = (term || '').trim().toLowerCase();
        var rows = document.querySelectorAll('#qcustTable .qcust-row');
        var shown = 0;
        rows.forEach(function(tr){
            var match = tr.getAttribute('data-search').indexOf(term) !== -1;
            tr.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        document.getElementById('qcustNoResult').classList.toggle('d-none', shown > 0 || rows.length === 0);
    }
</script>

<style>
    .qcust-head { background-color: #54BAB9; color: #fff; }
    .qcust-row:hover { background-color: #eafbfa; }
    #qcustTable thead th { white-space: nowrap; }
</style>
