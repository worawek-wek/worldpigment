@php
    $r_company       = $row['company'] ?? '';
    $r_mdate         = substr($row['mdate']    ?? '', 0, 10);
    $r_custwant      = substr($row['custwant'] ?? '', 0, 10);
    $r_custno        = $default_custno; // custno = company ของ header แม่
    $r_itemno        = $row['itemno']        ?? '';
    $r_weight_req    = $row['weight_request'] ?? '';
    // ฟิลด์ที่เก็บเป็น hidden (ไม่แสดงเป็นคอลัมน์)
    $hidden = [
        'semi_code'           => $row['semi_code']           ?? '',
        'primary_color'       => $row['primary_color']       ?? '',
        'balance'             => $row['balance']             ?? '',
        'lot_no'              => $row['lot_no']              ?? '',
        'retrospective'       => $row['retrospective']       ?? '',
        'increase_production' => $row['increase_production'] ?? '',
        'weight_production'   => $row['weight_production']   ?? '',
        'red_bill_code'       => $row['red_bill_code']       ?? '',
    ];
@endphp
{{-- แถวรออนุมัติ: แสดงผลอ่านอย่างเดียว + เก็บค่าไว้ใน hidden input (แก้ไขผ่าน modal) --}}
<tr data-id="{{ $row['id'] ?? '' }}">
    <td class="text-center row-num">{{ $i + 1 }}
        @foreach($hidden as $field => $val)
            <input type="hidden" data-field="{{ $field }}" value="{{ $val }}">
        @endforeach
    </td>
    <td>{{ $r_company !== '' ? $r_company : '-' }}<input type="hidden" data-field="company" value="{{ $r_company }}"></td>
    <td>{{ $r_mdate !== '' ? $r_mdate : '-' }}<input type="hidden" data-field="mdate" value="{{ $r_mdate }}"></td>
    <td>{{ $r_custwant !== '' ? $r_custwant : '-' }}<input type="hidden" data-field="custwant" value="{{ $r_custwant }}"></td>
    <td>{{ $r_custno !== '' ? $r_custno : '-' }}<input type="hidden" data-field="custno" value="{{ $r_custno }}"></td>
    <td>{{ $r_itemno !== '' ? $r_itemno : '-' }}<input type="hidden" data-field="itemno" value="{{ $r_itemno }}"></td>
    <td>{{ $r_weight_req !== '' ? $r_weight_req : '-' }}<input type="hidden" data-field="weight_request" value="{{ $r_weight_req }}"></td>
    <td class="text-center"><span class="badge bg-label-warning">รออนุมัติ</span></td>
    <td class="text-center text-nowrap">
        <button type="button" class="btn btn-sm btn-warning btn-icon btn_edit_row" title="แก้ไข">
            <i class="ti ti-pencil ti-sm"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger btn-icon btn_remove_row" title="ลบ">
            <i class="ti ti-trash ti-sm"></i>
        </button>
    </td>
</tr>
