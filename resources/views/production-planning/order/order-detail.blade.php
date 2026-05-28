<div class="row g-3">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">เลขที่ใบสั่ง</label>
            {{ $order->Orderno }}
        </div>
        <div class="col-md-6" style="text-align: right">
            <label class="form-label">วันที่</label>
            {{ $order->Mdate }}
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-2">
            <label class="form-label">รหัสลูกค้า</label>
            {{ $order->Custno }}
        </div>
        <div class="col-md-6">
            <label class="form-label">ชื่อลูกค้า</label>
            {{ $order->Custname }}
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Itemno</th>
                        <th class="text-center">Lotno</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Production</th>
                        <th class="text-center">prodname</th>
                        <th class="text-center">custwant</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ( $order->suborders as $suborders )
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $suborders->Itemno }}</td>
                        <td>{{ $suborders->Lotno }}</td>
                        <td>{{ $suborders->Stock }}</td>
                        <td>{{ $suborders->Production }}</td>
                        <td>{{ $suborders->prodname }}</td>
                        <td>{{ $suborders?->custwant ? date('Y-m-d', strtotime($suborders->custwant)) : '' }}</td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>
