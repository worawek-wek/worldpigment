{{-- คำอธิบายรหัสสาเหตุที่ยังไม่ซื้อ (TyResp A–H จาก config/color_matching.php) — ใช้ใน follow-up-pdf 4 ช่องต่อแถว --}}
<table width="100%" class="legend" style="margin-top: 6px;">
    @foreach ($legend->chunk(4) as $row)
        <tr>
            <td style="width: 12%;">{{ $loop->first ? 'สาเหตุที่ยังไม่ซื้อ' : '' }}</td>
            @foreach ($row as $text)
                <td style="width: 22%;">{{ $text }}</td>
            @endforeach
        </tr>
    @endforeach
</table>
