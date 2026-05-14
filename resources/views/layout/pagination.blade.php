<div class="row">
    <div class="col-sm-12 col-md-6 ps-4">
        <div class="dataTables_info" id="DataTables_Table_1_info" role="status" aria-live="polite">
            All &nbsp; {{$list_data->total()}} &nbsp; entries
        </div>
    </div>

    <div class="col-sm-12 col-md-6 pe-4">
        <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_1_paginate">
            @if ($list_data->lastPage() > 1)
                <ul class="pagination">
                    <!-- ปุ่ม First -->
                    <li class="page-item {{ ($list_data->currentPage() == 1) ? ' disabled' : '' }}">
                        <a class="page-link" href="javascript:void(0)" onclick='loadData("{{ $list_data->url(1) }}")'>First</a>
                    </li>

                    <?php
                        // จำนวนหน้าที่ย่อ (ตัวอย่างนี้แสดงแค่ 8 หน้า)
                        $total_links = 9;  // เปลี่ยนจาก 5 เป็น 9
                        $half_total_links = floor($total_links / 2);
                        $from = $list_data->currentPage() - $half_total_links;
                        $to = $list_data->currentPage() + $half_total_links;

                        // แก้ไขการคำนวณจากหน้าแรกหรือหน้าสุดท้าย
                        if ($list_data->currentPage() < $half_total_links) {
                            $to += $half_total_links - $list_data->currentPage();
                        }
                        if ($list_data->lastPage() - $list_data->currentPage() < $half_total_links) {
                            $from -= $half_total_links - ($list_data->lastPage() - $list_data->currentPage()) - 1;
                        }

                        // กำหนดให้ค่าของ $from และ $to ไม่ให้ต่ำกว่า 1 หรือมากกว่าหน้าสุดท้าย
                        $from = max($from, 1);
                        $to = min($to, $list_data->lastPage());
                    ?>

                    <!-- แสดงหน้าที่ในช่วงที่คำนวณ -->
                    @for ($i = $from; $i <= $to; $i++)
                        <li class="page-item {{ ($list_data->currentPage() == $i) ? ' active' : '' }}">
                            <a class="page-link" href="javascript:void(0)" onclick='loadData("{{ $list_data->url($i) }}")'>{{ $i }}</a>
                        </li>
                    @endfor

                    <!-- เพิ่มการแสดงเลขหน้าสุดท้าย -->
                    @if ($to < $list_data->lastPage())
                        <li class="px-2 pe-1 mt-4">
                            ...
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0)" onclick='loadData("{{ $list_data->url($list_data->lastPage()) }}")'>{{ $list_data->lastPage() }}</a>
                        </li>
                    @endif

                    <!-- ปุ่ม Last -->
                    <li class="page-item {{ ($list_data->currentPage() == $list_data->lastPage()) ? ' disabled' : '' }}">
                        <a class="page-link" href="javascript:void(0)" onclick='loadData("{{ $list_data->url($list_data->lastPage()) }}")'>Last</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</div>
