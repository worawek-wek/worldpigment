<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'sarabun', sans-serif; }
        body { font-size: 9px; color: #000; }
        .head { width: 100%; margin-bottom: 4px; }
        .head td { font-size: 11px; }
        .title { font-size: 13px; font-weight: bold; }
        .date-box { border: 1px solid #000; padding: 1px 8px; font-weight: bold; background-color: #fff3a0; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 2px 3px; font-size: 8px; text-align: center; }
        table.data th { background-color: #e9ecef; }
        td.rowlabel { text-align: left; width: 70px; background-color: #f4f4f4; }
        tr.emp-head td { background-color: #d9d9d9; font-weight: bold; text-align: left; }
        td.signcell { height: 16px; }
        .unsched { text-align: left; font-style: italic; color: #333; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td class="title">แผนและการผลิตจริง{{ $dept ? ' แผนก '.$dept : '' }}</td>
            <td style="text-align:center;">
                <span class="date-box">{{ $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '' }}</span>
            </td>
            <td style="text-align:right;">พบ {{ number_format($total) }} รายการ</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:70px;"></th>
                @foreach($slots as $slot)
                    <th>{{ $slot['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $group)
                <tr class="emp-head">
                    <td colspan="{{ count($slots) + 1 }}">{{ $group['label'] }} ({{ number_format($group['job_count']) }} รายการ)</td>
                </tr>
                {{-- รหัสสี --}}
                <tr>
                    <td class="rowlabel">รหัสสี</td>
                    @foreach($slots as $i => $slot)
                        <td>{!! implode('<br>', array_map('e', $group['cells'][$i]['color'])) !!}</td>
                    @endforeach
                </tr>
                {{-- จำนวน --}}
                <tr>
                    <td class="rowlabel">จำนวน</td>
                    @foreach($slots as $i => $slot)
                        <td>{{ $group['cells'][$i]['qty'] !== null ? number_format($group['cells'][$i]['qty'], 2).' KG' : '' }}</td>
                    @endforeach
                </tr>
                {{-- รหัสเครื่อง --}}
                <tr>
                    <td class="rowlabel">รหัสเครื่อง</td>
                    @foreach($slots as $i => $slot)
                        <td>{!! implode('<br>', array_map('e', $group['cells'][$i]['machine'])) !!}</td>
                    @endforeach
                </tr>
                {{-- วิธีการผลิต --}}
                <tr>
                    <td class="rowlabel">วิธีการผลิต</td>
                    @foreach($slots as $i => $slot)
                        <td>{!! implode('<br>', array_map('e', $group['cells'][$i]['method'])) !!}</td>
                    @endforeach
                </tr>
                {{-- ผู้ทวนสอบ/เวลา --}}
                <tr>
                    <td class="rowlabel">ผู้ทวนสอบ/เวลา</td>
                    <td class="signcell" colspan="{{ count($slots) }}"></td>
                </tr>
                {{-- ผู้ผลิต --}}
                <tr>
                    <td class="rowlabel">ผู้ผลิต</td>
                    <td class="signcell" colspan="{{ count($slots) }}"></td>
                </tr>
                @if(!empty($group['unscheduled']))
                    @foreach($group['unscheduled'] as $u)
                        <tr>
                            <td class="rowlabel">ไม่ระบุเวลา</td>
                            <td class="unsched" colspan="{{ count($slots) }}">
                                รหัสสี {{ $u['color'] ?: '-' }}
                                | จำนวน {{ $u['qty'] !== null ? number_format($u['qty'], 2).' KG' : '-' }}
                                | เครื่อง {{ $u['machine'] ?: '-' }}
                                | วิธี {{ $u['method'] ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="{{ count($slots) + 1 }}" style="padding:14px;">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
