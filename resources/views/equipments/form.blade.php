<div class="modal-body">
    <div class="row g-3">
        <div class="col-sm-6">
            <label for="" class="form-label">หมวดหมู่</label>
            <select name="ref_position_id" id="select2Position1" class="select2 form-select form-select-lg" data-allow-clear="true">
                @foreach ($category as $cate)
                    <option value="{{ $cate->id }}">{{ $cate->name }}</option>
                @endforeach

            </select>
        </div>
        <div class="col-sm-6">
            <label for="exampleFormControlInput1" class="form-label">ชื่ออุปกรณ์</label>
            <input class="form-control" name="name" id="exampleFormControlInput1" placeholder="ชื่ออุปกรณ์" value="{{ @$equipments['name'] }}">
        </div>
        <div class="col-sm-12">
            <label for="exampleFormControlInput1" class="form-label">รายละเอียด</label>
            <textarea class="form-control" name="detail" id="exampleFormControlInput1" placeholder="รายละเอียด">{{ @$equipments['detail'] }}</textarea>
        </div>
        <!-- รูปภาพก่อนเข้าพัก -->
            <div class="row mt-1">
                <label for="image" class="col-sm-12 col-form-label text-black">
                <strong>รูปภาพอุปกรณ์</strong>
                <input class="form-control mt-1" type="file" id="image" name="image" accept="image/*" onchange="previewImage(event, 'preview{{ @$equipments['id'] }}')">
                </label>
            </div>
            
            <img id="preview{{ @$equipments['id'] }}"
                alt="Preview"
                class="img-thumbnail"
                style="width: 400px;{{ !@$equipments['image'] ? 'display:none;' : '' }}"
                src="{{ @$equipments['image'] ? '/upload/equipments/' . $equipments['image'] : '' }}">
    </div>
</div>
<div class="modal-footer rounded-0 justify-content-center">
    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
    <button type="submit" class="btn btn-main">บันทึก</button>
</div>
<script>
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