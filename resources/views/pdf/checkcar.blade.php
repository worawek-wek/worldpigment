<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานเช็ครถยนต์</title>
    <style>
        body {
            font-family: Tahoma, sans-serif;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        .header-title {
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div style="text-align:center;margin-bottom:10px;">
    <div class="header-title">เอกสารเช็ครถยนต์ {{ @$name_branch }}</div>
    <div class="header-title">วันที่ {{ date('d/m/Y') }}</div>
</div>

<table>
    <thead>
        <tr>
            <th style="width: 50px;">ทะเบียนรถ</th>
            <th style="width: 50px;">ห้องพัก</th>
            <th style="width: 150px;">รายละเอียดรถ</th>
            <th style="width: 150px;">เลขสติ๊กเกอร์</th>
            <th style="width: 50px;">23.00 น.</th>
            <th style="width: 50px;">3.00 น.</th>
            <th style="width: 50px;">6.00 น.</th>
        </tr>
    </thead>
    <tbody>
        @php
            $max_rows = 25; // กำหนดจำนวนแถวทั้งหมดในหน้า
            $current_rows = 0;
        @endphp

        @foreach ($list_data as $room)
            @foreach ($room->room_for_rent_s as $rent)
                @php
                    $vehicles = $rent->vehicles ?? [];
                    $rowspan = count($vehicles) > 0 ? count($vehicles) : 1;
                @endphp

                @if (count($vehicles) > 0)
                    @foreach ($vehicles as $v_index => $vehicle)
                        <tr>
                            <td>{{ $vehicle->car_registration }}</td>

                            @if ($v_index == 0)
                                <td rowspan="{{ $rowspan }}">{{ $room->name }}</td>
                            @endif

                            <td>{{ $vehicle->detail }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        @php $current_rows++; @endphp
                    @endforeach
                {{-- @else
                    <tr>
                        <td>-</td>
                        <td>{{ $room->name }}</td>
                        <td>-</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @php $current_rows++; @endphp --}}
                @endif
            @endforeach
        @endforeach

        {{-- เติมแถวว่างให้ครบ 25 แถว --}}
        @for ($i = $current_rows; $i < $max_rows; $i++)
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>

</body>
</html>
