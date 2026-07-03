@php
    $qno   = trim($header->Qno);
    $tname = ($cust && $cust->name) ? $cust->name : $header->CustName;
    $ename = $header->Engname ?: ($cust->nameEN ?? '');
    $ptname = $pdtype->PDHead1 ?? null;
    $totalNet = $items->sum('QNet');
    // นับเฉพาะรายการสินค้าจริง (มีรหัส/ราคา) — ไม่นับบรรทัดต่อเนื่อง/บรรทัดว่าง
    $productCount = $items->filter(fn ($it) =>
        $it->Qitemno !== null || $it->QPrice !== null || $it->QNet !== null || $it->oldprice !== null
    )->count();
    $fmtDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $money   = fn ($v) => $v !== null ? number_format($v, 2) : '—';
@endphp

<style>
    /* สี font ทึบทั้งหมด — ไม่มีสีจาง */
    .qv { --qv-accent:#54BAB9; --qv-accent-dark:#2f7a78; --qv-ink:#20272e; --qv-label:#3a4750; color:var(--qv-ink); }

    .qv .qv-banner {
        background: linear-gradient(120deg, #54BAB9 0%, #3d8f8e 100%);
        color:#fff; padding:1.15rem 3.5rem 1.15rem 1.4rem;
        display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;
    }
    .qv .qv-banner .qv-qno { font-size:1.55rem; font-weight:700; letter-spacing:.5px; line-height:1.15; color:#fff; }
    .qv .qv-banner .qv-sub { font-size:.9rem; color:#fff; }
    .qv .qv-banner .badge { background:rgba(255,255,255,.25); color:#fff; font-weight:600; font-size:.82rem; }

    .qv .qv-body { padding:1.25rem 1.5rem 1.5rem; }

    .qv .qv-card { border:1px solid #dfe4e8; border-radius:.65rem; padding:1rem 1.15rem; height:100%; background:#fff; }
    .qv .qv-card-title, .qv .qv-section {
        font-size:.85rem; font-weight:700; color:var(--qv-accent-dark);
        letter-spacing:.3px; display:flex; align-items:center; gap:.4rem;
    }
    .qv .qv-card-title { margin-bottom:.75rem; }
    .qv .qv-section { margin:1.5rem 0 .6rem; }

    .qv .qv-row { display:flex; padding:.3rem 0; border-bottom:1px dashed #e7ebee; }
    .qv .qv-row:last-child { border-bottom:0; }
    .qv .qv-row .qv-label { color:var(--qv-label); width:42%; flex:none; font-size:.9rem; }
    .qv .qv-row .qv-val { color:var(--qv-ink); font-weight:600; word-break:break-word; }

    .qv table.qv-items { width:100%; border-collapse:separate; border-spacing:0; }
    .qv table.qv-items thead th {
        background:#eaf4f4; color:#2f6f6e; font-weight:700; font-size:.85rem;
        padding:.55rem .7rem; border-bottom:2px solid #cfe3e3; white-space:nowrap;
    }
    .qv table.qv-items tbody td { padding:.5rem .7rem; border-bottom:1px solid #eef1f3; vertical-align:top; color:var(--qv-ink); }
    .qv table.qv-items tbody tr:hover td { background:#f7fbfb; }
    .qv .qv-subrow td { color:var(--qv-ink); font-size:.9rem; padding-top:.15rem !important; }
    .qv .qv-subrow td.qv-desc { padding-left:1.4rem; }
    .qv .qv-code { font-family:ui-monospace,Menlo,Consolas,monospace; font-size:.86rem; }
    .qv .qv-num  { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .qv .qv-total-row td {
        background:#e2f2f1; font-weight:700; font-size:1.03rem; color:#256664;
        border-top:2px solid var(--qv-accent); padding:.7rem;
    }
    .qv .qv-count {
        background:var(--qv-accent-dark); color:#fff; font-weight:600;
        font-size:.75rem; padding:.2rem .5rem; border-radius:.35rem;
    }

    .qv .qv-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:.75rem; margin-top:1.3rem; }
    .qv .qv-stat { border:1px solid #dfe4e8; border-radius:.6rem; padding:.7rem .85rem; background:#f8fbfb; }
    .qv .qv-stat .s-label { color:var(--qv-label); font-size:.8rem; display:flex; align-items:center; gap:.35rem; }
    .qv .qv-stat .s-val { color:var(--qv-ink); font-weight:700; margin-top:.15rem; }
    @media (max-width:768px){ .qv .qv-stats{ grid-template-columns:repeat(2,1fr); } }
</style>

<div class="qv">

    {{-- ── แบนเนอร์หัว (ทำหน้าที่เป็น header ของ modal) ── --}}
    <div class="qv-banner">
        <div>
            <div class="qv-sub"><i class="ti ti-file-invoice me-1"></i>ใบเสนอราคา</div>
            <div class="qv-qno">{{ $qno }}</div>
        </div>
        <div class="text-end">
            <div class="mb-1">
                <span class="badge"><i class="ti ti-tag me-1"></i>{{ $header->PDtype }}@if($ptname) — {{ $ptname }}@endif</span>
                @if ($header->exam == 1)
                    <span class="badge"><i class="ti ti-flask me-1"></i>พร้อมตัวอย่าง</span>
                @endif
            </div>
            <div class="qv-sub"><i class="ti ti-calendar me-1"></i>วันที่เสนอราคา {{ $fmtDate($header->Qdate) }}</div>
        </div>
    </div>

    <div class="qv-body">

        {{-- ── ข้อมูลเอกสาร / ลูกค้า ── --}}
        <div class="row g-3">
            <div class="col-md-6">
                <div class="qv-card">
                    <div class="qv-card-title"><i class="ti ti-clipboard-text"></i>ข้อมูลเอกสาร</div>
                    <div class="qv-row"><div class="qv-label">วันที่เสนอราคา</div><div class="qv-val">{{ $fmtDate($header->Qdate) }}</div></div>
                    <div class="qv-row"><div class="qv-label">Revise Date</div><div class="qv-val">{{ $fmtDate($header->Revisedate) }}</div></div>
                    <div class="qv-row"><div class="qv-label">ชนิดสินค้า</div><div class="qv-val">{{ $header->PDtype }}@if($ptname) — {{ $ptname }}@endif</div></div>
                    <div class="qv-row"><div class="qv-label">รหัสพนักงานขาย</div><div class="qv-val">{{ $header->EmpID ?: '—' }}</div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="qv-card">
                    <div class="qv-card-title"><i class="ti ti-building-store"></i>ข้อมูลลูกค้า</div>
                    <div class="qv-row"><div class="qv-label">รหัสลูกค้า</div><div class="qv-val">{{ $header->Custid ?: '—' }}</div></div>
                    <div class="qv-row"><div class="qv-label">ชื่อลูกค้า</div><div class="qv-val text-primary">{{ $tname ?: '—' }}</div></div>
                    <div class="qv-row"><div class="qv-label">ชื่อ (อังกฤษ)</div><div class="qv-val">{{ $ename ?: '—' }}</div></div>
                </div>
            </div>
        </div>

        {{-- ── รายการสินค้า ── --}}
        <div class="qv-section">
            <i class="ti ti-list-details"></i>รายการสินค้า
            <span class="qv-count ms-1">{{ number_format($productCount) }} รายการ</span>
        </div>

        <div class="table-responsive">
            <table class="qv-items">
                <thead>
                    <tr>
                        <th style="width:40px" class="text-center">#</th>
                        <th style="width:140px">รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th style="width:105px" class="text-end">ราคาเก่า</th>
                        <th style="width:105px" class="text-end">ราคาใหม่</th>
                        <th style="width:120px" class="text-end">ราคารวมภาษี</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 0; @endphp
                    @forelse ($items as $it)
                        @php
                            $hasCode  = $it->Qitemno !== null && trim($it->Qitemno) !== '';
                            $hasPrice = $it->oldprice !== null || $it->QPrice !== null || $it->QNet !== null;
                            $hasDesc  = $it->Qdesc !== null && trim($it->Qdesc) !== '';
                            $isProduct = $hasCode || $hasPrice;
                        @endphp
                        {{-- ข้ามบรรทัดว่างเปล่า (row คั่น) เพื่อให้ดูเป็นระเบียบ --}}
                        @if (!$isProduct && !$hasDesc)
                            @continue
                        @endif
                        @if ($isProduct)
                            <tr>
                                <td class="text-center">{{ ++$no }}</td>
                                <td class="qv-code">{{ $it->Qitemno ?: '—' }}</td>
                                <td>{{ $it->Qdesc }}</td>
                                <td class="qv-num">{{ $money($it->oldprice) }}</td>
                                <td class="qv-num">{{ $money($it->QPrice) }}</td>
                                <td class="qv-num fw-semibold">{{ $money($it->QNet) }}</td>
                            </tr>
                        @else
                            {{-- บรรทัดต่อเนื่อง (คำอธิบายเพิ่มเติมของสินค้าด้านบน) --}}
                            <tr class="qv-subrow">
                                <td></td><td></td>
                                <td class="qv-desc" colspan="4"><i class="ti ti-corner-down-right me-1"></i>{{ $it->Qdesc }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="text-center py-4">
                            <i class="ti ti-database-off d-block fs-4 mb-1"></i>ไม่มีรายการสินค้า
                        </td></tr>
                    @endforelse
                </tbody>
                @if ($items->isNotEmpty())
                    <tfoot>
                        <tr class="qv-total-row">
                            <td colspan="5" class="text-end">รวมมูลค่าทั้งสิ้น (รวมภาษี)</td>
                            <td class="qv-num">{{ number_format($totalNet, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- ── เงื่อนไข ── --}}
        <div class="qv-stats">
            <div class="qv-stat">
                <div class="s-label"><i class="ti ti-scale"></i>ยอดซื้อขั้นต่ำ (ก.ก.)</div>
                <div class="s-val">{{ $header->Qremark ?: '—' }}</div>
            </div>
            <div class="qv-stat">
                <div class="s-label"><i class="ti ti-credit-card"></i>Payment Term</div>
                <div class="s-val">{{ $header->Term ?: '—' }}</div>
            </div>
            <div class="qv-stat">
                <div class="s-label"><i class="ti ti-calendar-check"></i>ยืนราคาถึงวันที่</div>
                <div class="s-val">{{ $fmtDate($header->Validto) }}</div>
            </div>
            <div class="qv-stat">
                <div class="s-label"><i class="ti ti-truck-delivery"></i>ส่งได้ภายใน (วัน)</div>
                <div class="s-val">{{ $header->LeadTime ?: '—' }}</div>
            </div>
        </div>

        {{-- ── ปุ่มจัดการ ── --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-label-info" onclick="quotationPrint('{{ $qno }}')">
                <i class="ti ti-printer me-1"></i>พิมพ์
            </button>
            <button type="button" class="btn btn-primary"
                onclick="bootstrap.Modal.getInstance(document.getElementById('quotationViewModal')).hide(); quotationEdit('{{ $qno }}');">
                <i class="ti ti-edit me-1"></i>แก้ไข
            </button>
        </div>

    </div>
</div>
