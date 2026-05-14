<div class="modal-body">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="exampleFormControlInput1" class="form-label">คงเหลือ</label>
            <input class="form-control" type="number" name="old_qty" id="old_qty" placeholder="คงเหลือ" value="{{ @$equipments['quantity'] }}" readonly>
        </div>
        <div class="col-sm-6">
            <label for="exampleFormControlInput1" class="form-label">จำนวนนำเข้าใหม่</label>
            <input class="form-control" type="number" name="new_qty" id="new_qty" placeholder="จำนวนนำเข้าใหม่" value="0" oninput="updateAllQty()">
        </div>
        <div class="col-sm-6">
            <label for="exampleFormControlInput1" class="form-label">ทั้งหมด</label>
            <input class="form-control" type="number" name="all_qty" id="all_qty" placeholder="ทั้งหมด" value="{{ @$equipments['quantity'] }}" readonly>
        </div>
        
    </div>
</div>
<div class="modal-footer rounded-0 justify-content-center">
    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
    <button type="submit" class="btn btn-main">บันทึก</button>
</div>
<script>
    function updateAllQty() {
        var old_qty = Number($('#old_qty').val());
        var new_qty = Number($('#new_qty').val());
        var all_qty = old_qty + new_qty;

        $('#all_qty').val(all_qty);
    }
    function previewImage(event, previewId) {
        const input = event.target;
        const preview = document.getElementById(previewId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }
</script>