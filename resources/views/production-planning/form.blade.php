<div class="modal-body">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="exampleFormControlInput1" class="form-label">ชื่อหมวดหมู่</label>
            <input class="form-control" name="name" id="exampleFormControlInput1" placeholder="ชื่อหมวดหมู่" value="{{ @$equipments['name'] }}">
        </div>
    </div>
</div>
<div class="modal-footer rounded-0 justify-content-center">
    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
    <button type="submit" class="btn btn-main" disabled >บันทึก</button>
</div>