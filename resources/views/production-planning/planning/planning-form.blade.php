<div class="modal-header">
    <h5 class="modal-title" style="padding: 1.25rem 1.5rem 1.25rem; color: white; background-color: #54BAB9; position: relative;">
        สร้างแผนการผลิต
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body" id="result_detail">
    <form>
        <div class="col-12">

            @if(!$planning)
                ไม่มีข้อมูลมา สร้างใหม่ ให้เลือก OrderNo.
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Order No.</label>
                        <select name="orderno" class="form-select">
                            <option>ทุกสถานะ</option>
                            <option>Pending</option>
                            <option>Production</option>
                            <option>Completed</option>
                        </select>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Order No.</label>
                        <input type="text" name="orderno"
                        value="{{ $planning?->planning_header?->orderno ?? '' }}"
                        disabled
                        class="form-control">
                    </div>
                </div>
            @endif

            <div class="row">
                <table>
                    {{  $planning?->planning_header->planning }}
                    <tr>
                        <td></td>
                    </tr>
                </table>
            </div>

            <input type="hidden" name="planning_id" value="{{ $planning?->id ?? '' }}" class="form-control">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Item No.</label>
                    <input type="text" name="itemno" value="{{ $planning?->itemno ?? '' }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Quantity</label>
                    <input type="text" name="quantity" value="{{ $planning?->quantity ?? '' }}" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Weight</label>
                    <input type="text" name="weight" value="{{ $planning?->weight ?? '' }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Machine No.</label>
                    <input type="text" name="machine_no" value="{{ $planning?->machine_no ?? '' }}" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Lot</label>
                    <input type="text" name="lot" value="{{ $planning?->lot ?? '' }}" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Planning Status</label>
                    <input type="text" name="planning_status" value="{{ $planning?->planning_status ?? '' }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Start Date</label>
                    <input type="text" name="start_date" value="{{ $planning?->start_date ?? '' }}" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>qc_date</label>
                    <input type="text" name="qc_date" value="{{ $planning?->qc_date ?? '' }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>qc_time</label>
                    <input type="text" name="qc_time" value="{{ $planning?->qc_time ?? '' }}" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>qc_status</label>
                    <input type="text" name="qc_status" value="{{ $planning?->qc_status ?? '' }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>packing_datetie</label>
                    <input type="text" name="packing_datetie" value="{{ $planning?->packing_datetie ?? '' }}" class="form-control">
                </div>
            </div>

        </div>
    </form>
</div>
