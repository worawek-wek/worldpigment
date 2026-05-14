<div class="modal-body">
        <div class="row">
            <!-- ผู้ยืม -->
            <div class="col-md-6 mb-3">
                <label>ผู้ยืม</label>
                <input type="text" name="borrower_name" class="form-control" required>
            </div>

            <!-- เบอร์โทร -->
            <div class="col-md-6 mb-3">
                <label>เบอร์โทร</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <!-- อุปกรณ์ -->
            <div class="col-md-6 mb-3">
                <label>อุปกรณ์</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- เลือกอุปกรณ์ --</option>
                    <option value="1">Notebook Dell</option>
                    <option value="2">Projector Epson</option>
                    <option value="3">Tablet iPad</option>
                </select>
            </div>

            <!-- จำนวน -->
            <div class="col-md-6 mb-3">
                <label>จำนวน</label>
                <input type="number" name="qty" class="form-control" min="1" value="1" required>
            </div>

            <!-- วันที่ยืม -->
            <div class="col-md-6 mb-3">
                <label>วันที่ยืม</label>
                <input type="datetime-local" name="borrow_date" class="form-control" required>
            </div>

            <!-- กำหนดคืน -->
            <div class="col-md-6 mb-3">
                <label>กำหนดคืน</label>
                <input type="date" name="due_date" class="form-control" required>
            </div>

            <!-- หมายเหตุ -->
            <div class="col-md-12 mb-3">
                <label>หมายเหตุ</label>
                <textarea name="note" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <!-- ปุ่ม -->
        <div class="text-end">
            <button type="submit" class="btn btn-primary">บันทึกการยืม</button>
        </div>
</div>