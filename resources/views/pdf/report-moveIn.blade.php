<style>
    @media print {
        body {
            font-size: 10px;
            margin: 10mm;
        }

        table {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed;
            border-collapse: collapse;
        }

        th, td {
            font-size: 10px;
            word-wrap: break-word;
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .text-truncate {
            white-space: normal !important;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    }

    .text-center {
        text-align: center;
    }
</style>
<table class="datatables-products table dataTable no-footer dtr-column"
    id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info"
    style="width: 1396px;">
    
    <thead class="border-top">
        <tr class=" table-info">
            <th class="text-center" tabindex="0">
                ห้อง
            </th>
            <th class="text-center">
                ผู้เช่า</th>
            <th class="text-center">
                รูปแบบ
            </th>
            <th class="text-center">
                วันที่เข้าพัก
            </th>
            <th class="text-center">
                วันที่สิ้นสุดสัญญา
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($list_data as $row)          
        <tr class="odd text-center">
            <td class="text-center">
                {{ $row->room->name }}
            </td>
            <td class="text-center">
                {{-- @if ($row->status != 0) --}}
                    <span class="text-truncate">{{ @$row->contract->renter->fullName() ?? '' }}</span>
                {{-- @endif --}}
            </td>
            <td class="text-center" >
                @if($row->booking_type == 1)
                    <span class="badge bg-info" style="font-size: unset;" text-capitalized="">รายวัน</span>
                @else
                    <span class="badge bg-success" style="font-size: unset;" text-capitalized="">รายเดือน</span>
                @endif
            </td>
            <td class="text-center">
                 {{ date('d/m/Y',strtotime($row->contract->contract_date)) }}
            </td>
            <td class="text-center">
                 {{ date('d/m/Y',strtotime($row->contract->contract_end_date)) }}
            </td>
        </tr>
            @empty

                <tr>
                    <td colspan="20" class="text-center text-muted py-4">
                        <i class="ti ti-file-search" style="font-size: 24px;"></i><br>
                        ไม่พบข้อมูล
                    </td>
                </tr>

            @endforelse
    </tbody>
</table>
    </tbody>
</table>