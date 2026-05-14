<table class="datatables-products table dataTable no-footer dtr-column"
    id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info"
    style="width: 1396px; border-top: 2px solid #dbdbdb !important;">
    <thead class="border-top">
    </thead>
    <tbody>
        @foreach ($list_data as $key => $row)
            <tr style="background-color: #d8f0f4;">
              <th class="position-relative text-center">
                  <h4 class="mb-0">{{ $row->room->name }}</h4>

                  <button type="button"
                      class="btn btn-primary waves-effect"
                      style="position:absolute; right:10px; top:50%; transform:translateY(-50%);"
                      onclick="printPdfInvoice({{ @$row->invoice->id }})"
                      >
                      <span class="ti-sm ti ti-printer me-2"></span>พิมพ์ใบย้ายออก
                  </button>
              </th>
            </tr>
            <tr class="odd text-center">
                
            <td>

                <div class="col-md mb-4 mb-md-2">
                  <div class="accordion mt-3" id="accordionWithIcon">
                    <div class="card accordion-item">
                      <h2 class="accordion-header d-flex align-items-center">
                        <button
                          type="button"
                          class="accordion-button"
                          data-bs-toggle="collapse"
                          data-bs-target="#accordionWithIcon-1{{ $row->id }}"
                          aria-expanded="true">
                          บิลค่าเช่าห้อง
                        </button>
                      </h2>

                      <div id="accordionWithIcon-1{{ $row->id }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                          @if (@$row->receipt_rent_bill_move_out)
                          <style>
                            .table-receipt th {
                                text-align: center !important;
                            }
                          </style>
                                <p align="right" style="color: black; font-weight: 500;">เลขที่ใบเสร็จ: &nbsp; <span class="text-success">{{ $row->receipt_rent_bill_move_out->receipt_number }}</span></p>
                                    <table class="table table-detail table-bordered mt-4 table-receipt">
                                        <thead>
                                            <tr style="background-color: antiquewhite;">
                                                <th>วันที่</th>
                                                <th>
                                                    รับชำระโดย
                                                </th>
                                                <th>
                                                    รูปแบบชำระ
                                                </th>
                                                <th>
                                                    ช่องทางการชำระ
                                                </th>
                                                <th>
                                                    รายการชำระ
                                                </th>
                                                <th>
                                                    จำนวนเงิน (บาท)
                                                </th>
                                                <th>
                                                    รวม
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $rowCount = count($row->receipt_rent_bill_move_out->payment_list);
                                            @endphp
                                            @foreach ($row->receipt_rent_bill_move_out->payment_list as $key => $item_payment_list)
                                                <tr>
                                                    {{-- แสดงช่องทางการชำระเงินเฉพาะแถวแรก --}}
                                                    @if($key === 0)
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        {{ date('d/m/Y', strtotime($row->receipt_rent_bill_move_out->payment_date)) }}
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        {{ $row->receipt_rent_bill_move_out->user->name }}
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        {{ $row->receipt_rent_bill_move_out->payment_channel == 7 ? "-" : "จ่ายเต็ม" ; }}
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        @switch($row->receipt_rent_bill_move_out->payment_channel)
                                                            @case(1)
                                                                เงินสด
                                                                @break

                                                            @case(2)
                                                                โอนเงิน
                                                                @break

                                                            @case(7)
                                                                หักจากเงินประกัน
                                                                @break

                                                        @endswitch
                                                    </td>
                                                    @endif

                                                    <td>{{ $item_payment_list->title }}</td>
                                                    @if ($item_payment_list->discount == 1)
                                                        <td align="right" class="text-danger fw-bold">{{ number_format(0-$item_payment_list->price) }}</td>
                                                    @else
                                                        <td align="right">{{ number_format($item_payment_list->price) }}</td>
                                                    @endif
                                                    {{-- แสดงรวมเฉพาะแถวแรก --}}
                                                    @if($key === 0)
                                                        <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                            {{ number_format($row->receipt_rent_bill_move_out->total_amount, 0) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            </tr>
                                            {{-- @endforeach --}}
                                        </tbody>
                                        <tfoot>
                                            <tr style="background-color: #e5e5e5;">
                                                <th colspan="6">รวม</th>
                                                <th align="right" class=" mb-0 fw-bold" style="color: #28c76f !important;text-align: right">
                                                {{ number_format($row->receipt_rent_bill_move_out->total_amount) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div class="my-2 mx-2" align="left"><b>หมายเหตุ: </b>{{ $row->receipt_rent_bill_move_out->remark }}</div>
                            @endif
                        </div>
                      </div>
                    </div>

                    <div class="accordion-item card active">
                      <h2 class="accordion-header d-flex align-items-center">
                        <button
                          type="button"
                          class="accordion-button collapsed"
                          data-bs-toggle="collapse"
                          data-bs-target="#accordionWithIcon-2{{ $row->id }}"
                          aria-expanded="true">
                          ใบเสร็จย้ายออก
                        </button>
                      </h2>
                      <div id="accordionWithIcon-2{{ $row->id }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                          @if (@$row->receipt_move_out)
                          <style>
                            .table-receipt th {
                                text-align: center !important;
                            }
                          </style>
                            {{-- <div class="p-4 mb-4" style="border: 1px solid #59d57a;border-radius: 5px;"> --}}
                                <p align="right" style="color: black; font-weight: 500;">เลขที่ใบเสร็จ: &nbsp; <span class="text-success">{{ $row->receipt_move_out->receipt_number }}</span></p>
                                    <table class="table table-detail table-bordered mt-4 table-receipt">
                                        <thead>
                                            <tr style="background-color: antiquewhite;">
                                                <th>วันที่</th>
                                                <th>
                                                    รับชำระโดย
                                                </th>
                                                <th>
                                                    รูปแบบชำระ
                                                </th>
                                                <th>
                                                    ช่องทางการชำระ
                                                </th>
                                                <th>
                                                    รายการชำระ
                                                </th>
                                                <th>
                                                    จำนวนเงิน (บาท)
                                                </th>
                                                <th>
                                                    รวม
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $rowCount = count($row->receipt_move_out->payment_list);
                                            @endphp
                                            @foreach ($row->receipt_move_out->payment_list as $key => $item_payment_list)
                                                <tr>
                                                    {{-- แสดงช่องทางการชำระเงินเฉพาะแถวแรก --}}
                                                    @if($key === 0)
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        {{ date('d/m/Y', strtotime($row->receipt_move_out->payment_date)) }}
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        {{ $row->receipt_move_out->user->name }}
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        {{ $row->receipt_move_out->payment_channel == 7 ? "-" : "จ่ายเต็ม" ; }}
                                                    </td>
                                                    <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                        @switch($row->receipt_move_out->payment_channel)
                                                            @case(1)
                                                                เงินสด
                                                                @break

                                                            @case(2)
                                                                โอนเงิน
                                                                @break

                                                            @case(7)
                                                                หักจากเงินประกัน
                                                                @break

                                                        @endswitch
                                                    </td>
                                                    @endif

                                                    <td>{{ $item_payment_list->title }}</td>
                                                    @if ($item_payment_list->discount == 1)
                                                        <td align="right" class="text-danger fw-bold">{{ number_format(0-$item_payment_list->price) }}</td>
                                                    @else
                                                        <td align="right">{{ number_format($item_payment_list->price) }}</td>
                                                    @endif
                                                    {{-- แสดงรวมเฉพาะแถวแรก --}}
                                                    @if($key === 0)
                                                        <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                                            {{ number_format($row->receipt_move_out->total_amount, 0) }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            </tr>
                                            {{-- @endforeach --}}
                                        </tbody>
                                        <tfoot>
                                            <tr style="background-color: #e5e5e5;">
                                                <th colspan="6">รวม</th>
                                                <th align="right" class=" mb-0 fw-bold" style="color: #28c76f !important;text-align: right">
                                                {{ number_format($row->receipt_move_out->total_amount) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div class="my-2 mx-2" align="left"><b>หมายเหตุ: </b>{{ $row->receipt_move_out->remark }}</div>
                                    {{-- <div class="modal-footer rounded-0 d-flex justify-content-between mt-2 pb-0">
                                        <button type="button" class="btn btn-label-primary waves-effect" onclick="printPdf({{ $row->receipt_move_out->id }})">
                                            <span class="ti-sm ti ti-printer me-2"></span>พิมพ์ใบเสร็จรับเงิน
                                        </button>
                                    </div> --}}
                                {{-- </div> --}}
                            @endif
                        </div>
                      </div>
                    </div>

                    <div class="accordion-item card">
                      <h2 class="accordion-header d-flex align-items-center">
                        <button
                          type="button"
                          class="accordion-button collapsed"
                          data-bs-toggle="collapse"
                          data-bs-target="#accordionWithIcon-3{{ $row->id }}"
                          aria-expanded="false">
                          เงินประกัน
                        </button>
                      </h2>
                      <div id="accordionWithIcon-3{{ $row->id }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                          @if (@$row->deposit_move_out)

                            <div class="p-4 mb-4" style="border: 1px solid #59d57a;border-radius: 5px;">
                                <p align="right" style="color: black; font-weight: 500;">เลขที่ใบเสร็จ: &nbsp; <span class="text-success">{{ $row->deposit_move_out->receipt_number }}</span></p>
                                    <table class="table table-detail table-bordered mt-4">
                                        <thead>
                                            <tr>
                                                <th width="70%">รายการ</th>
                                                <th style="text-align: right">
                                                    จำนวนเงิน (บาท)
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $amount = 0;
                                            @endphp
                                            @foreach ($row->deposit_move_out->payment_list as $key => $item_payment_list)
                                            <tr>
                                                <td align="left">
                                                    {{ $item_payment_list->title }}
                                                </td>

                                                    @if ($item_payment_list->discount == 1)
                                                        @php
                                                            $amount -= $item_payment_list->price;
                                                        @endphp
                                                        <td align="right" class="text-danger fw-bold">{{ number_format(0-$item_payment_list->price) }}</td>

                                                    @else
                                                        @php    
                                                        $amount += $item_payment_list->price;
                                                        @endphp
                                                        <td align="right">{{ number_format($item_payment_list->price) }}</td>
                                                    @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>รวม</th>
                                                <th align="right" class=" mb-0 fw-bold" style="color: #28c76f !important;text-align: right">
                                                {{ number_format($amount) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                                    @php
                                        $calculate = ($row->deposit_move_out->total_amount ?? 0) - ($row->receipt_move_out->total_amount ?? 0) - ($row->receipt_rent_bill_move_out->total_amount ?? 0);
                                    @endphp
                                  <div class="col-sm-11 mt-3">
                                      <h4 class="my-4" align="center">
                                          @if ($calculate >= 0)
                                          <span class="text-danger">
                                              ยอดเงินประกันคืนผู้เช่า
                                          @else
                                          <span class="text-success">
                                              เก็บเงินผู้เช่าเพิ่ม
                                          @endif
                                          &nbsp; {{ number_format(abs($calculate)) }}&nbsp; บาท
                                          </span>
                                      </h4>
                                  </div>
            </td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
    function printPdfInvoice(id) {
        $.ajax({
            url: '/pdf/invoice/'+id,
            type: 'GET',
            success: function(html) {
                const iframe = document.getElementById('print-iframe');
                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();

                // รอโหลดก่อนค่อยพิมพ์
                iframe.onload = function () {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                };
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let messages = '';
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        messages += value + '<br>';
                    });

                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        html: messages,
                        icon: 'error',
                    });
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                    console.error('เกิดข้อผิดพลาด:', xhr);
                }
            }
        });
    }
</script>