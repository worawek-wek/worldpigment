<div class="modal-content rounded-0">
    <div class="modal-header rounded-0">
        <span class="modal-title">
            <span class="h5" style="color: white;">&nbsp;รายละเอียด บุคลากร&nbsp;</span>
        </span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body pb-5" style="padding: 1em 3em;">
        
        <div class="col-md-12" style="padding-right: unset !important;">

        <div class="card shadow-none bg-transparent border mb-3">
            <div class="card-body">
                <div class="nav-align-top mb-4">
                    <ul class="nav nav-pills justify-content-end" role="tablist">
                      <li class="nav-item pe-2" role="presentation">
                        <button class="buttons-collection btn-label-primary waves-effect waves-light nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-profile" aria-controls="navs-pills-top-profile" aria-selected="true">
                            <i class="ti ti-user"></i> &nbsp;รายละเอียด
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                            class="buttons-collection btn-label-warning waves-effect waves-light nav-link d-write" 
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-top-edit" aria-controls="navs-pills-top-edit" aria-selected="false" tabindex="-1">
                            <span>
                                <i class="ti ti-pencil"></i> แก้ไข
                            </span>
                        </button>
                      </li>
                    </ul>
                    <div class="tab-content" style="box-shadow: unset;padding:0px">
                      <div class="tab-pane fade active show" id="navs-pills-top-profile" role="tabpanel">
                                <div class="col-sm-12 text-start">
                                    <h5 class="border-bottom text-primary pb-2">
                                        <i class="ti ti-user"></i> รายละเอียด
                                    </h5>
                                </div>
                                <div class="d-flex">
                                    <div class="col-sm-5">
                                        <ul class="list-unstyled mb-4 mt-2" style="padding: 0 20px;">
                                            <li class="d-flex align-items-center mb-3">
                                            <i class="ti ti-user text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">ชื่อ:</span> <span>{{ $user->name }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                            <i class="ti ti-crown text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">ตำแหน่ง:</span> <span>{{ $user->position->position_name }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                            <i class="ti ti-flag text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">เบอร์โทรศัพท์:</span> <span>{{ $user->phone }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                            <i class="ti ti-flag text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">Email:</span> <span>{{ $user->email }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                            <i class="ti ti-flag text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">วันที่เข้าทำงาน:</span> <span>{{ $user->work_start_date }}</span>
                                            </li>
                                            <li class="d-flex align-items-center mb-3">
                                            <i class="ti ti-file-description text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">หมายเหตุ:</span> <span>{{ $user->remark }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-sm-5">
                                        <ul class="list-unstyled mb-4 mt-2" style="padding: 0 20px;">
                                            <li class="d-flex align-items-center mb-3">
                                                <i class="ti ti-user text-heading"></i><span class="fw-medium mx-2 me-4 text-heading">Username:</span> <span>{{ $user->username }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                        </div>
                      <div class="tab-pane fade" id="navs-pills-top-edit" role="tabpanel">
                        <div class="col-sm-12 text-start">
                            <h5 class="border-bottom pb-3 text-warning">
                                <i class="ti ti-pencil"></i> แก้ไข
                            </h5>
                        </div>
                        <form id="edit_user">		
                            @csrf
                            
                            <div class="row g-3 p-4">
                                <div class="col-sm-6">
                                    <label for="" class="form-label">ชื่อพนักงาน</label><span class="text-danger"> *</span>
                                    <input name="name" value="{{ $user->name }}" type="text" class="form-control" placeholder="ชื่อพนักงาน" required />
                                </div>
                                {{-- <div class="col-sm-6">
                                    <label for="" class="form-label">เงินเดือน</label><span class="text-danger"> *</span>
                                    <input name="salary" value="{{ $user->salary }}" type="text" class="form-control" id="salary2" placeholder="เงินเดือน" oninput="formatSalary2()" required/>
                                </div> --}}
                                <div class="col-sm-6">
                                    <label for="" class="form-label">เบอร์โทรศัพท์</label>
                                    <input name="phone" value="{{ $user->phone }}" type="number" class="form-control" placeholder="เบอร์โทรศัพท์" oninput="this.value=this.value.slice(0,10);"/>
                                </div>
                                <div class="col-sm-6">
                                    <label for="" class="form-label">อีเมล</label>
                                    <input name="email" value="{{ $user->email }}" type="email" class="form-control" placeholder="อีเมล" />
                                </div>
                                <div class="col-sm-6">
                                    <label for="bs-datepicker-format" class="form-label">วันที่เข้าทำงาน</label><span class="text-danger"> *</span>
                                    <input name="work_start_date" value="{{date('d/m/Y', strtotime($user->work_start_date))}}" type="text" class="form-control" id="bs-datepicker-format2" placeholder="วัน/เดือน/ปี" required/>
                                </div>
                                <div class="col-sm-6">
                                    <label for="" class="form-label">ตำแหน่ง</label>
                                    <select name="ref_position_id" id="select2Position2" class="select2 form-select form-select-lg" data-allow-clear="true">
                                        @foreach ($position as $pos)
                                            <option @if ($user->ref_position_id == $pos->id)
                                                selected
                                            @endif value="{{$pos->id}}">{{$pos->position_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-span-12">
                                    <div class="col-sm-6 mt-3">
                                        <label for="" class="form-label">ชื่อผู้ใช้</label><span class="text-danger"> *</span>
                                        <input name="username" value="{{ $user->username }}" type="text" class="form-control" placeholder="ชื่อผู้ใช้" required />
                                    </div>
                                    <div class="col-sm-6 mt-3">
                                        <label for="update-profile-form-2" class="form-label">รหัสผ่าน <span class="text-warning">(กรณีเปลี่ยนรหัสผ่าน)</span></label>
                                        <input name="password" id="password2" type="password" class="form-control" placeholder="รหัสผ่าน">
                                    </div>
                                    <div class="col-sm-6 mt-3">
                                        <label for="update-profile-form-3" class="form-label">ยืนยัน รหัสผ่าน</label>
                                        <input id="confirm_password2" type="password" class="form-control" placeholder="ยืนยัน รหัสผ่าน">
                                    </div>
                                </div>
                                <script>
                                    //// ทำ input เงินเดือน เริ่ม
                                    function formatSalary2() {
                                        const input = document.getElementById('salary2');
                                        let value = input.value.replace(/,/g, ''); // ลบเครื่องหมายจุลภาค
                                        if (!isNaN(value) && value !== '') {
                                            input.value = Number(value).toLocaleString(); // แปลงเป็นรูปแบบ number_format
                                        } else {
                                            input.value = ''; // ถ้าไม่ใช่ตัวเลขให้ลบค่าที่ป้อน
                                        }
                                    }
                                    //// ทำ input เงินเดือน จบ

                                    //// ทำ เช็ค Password เริ่ม
                                    var password2 = document.getElementById("password2"), confirm_password2 = document.getElementById("confirm_password2");

                                    function validatePassword2(){
                                        if(password2.value != confirm_password2.value) {
                                            confirm_password2.setCustomValidity("โปรดกรอกรหัสผ่านให้ตรงกัน");
                                        } else {
                                            confirm_password2.setCustomValidity('');
                                        }
                                    }

                                    password2.onchange = validatePassword2;
                                    confirm_password2.onkeyup = validatePassword2;
                                    //// ทำ เช็ค Password จบ

                                </script> 
                                <div class="col-sm-12">
                                    <label for="" class="form-label">หมายเหตุ</label>
                                    <textarea name="remark" class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="modal-footer rounded-0 justify-content-center">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
                                <button type="submit" class="btn btn-main" disabled >บันทึก</button>
                            </div>
                        </form>
                      </div>
                    </div>
                    </div>
                </div>
        </div>
    </div>
</div>
</div>
    <script src="/assets/js/cards-actions.js"></script>
<script>
    
    $('#edit_user').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการ แก้ไข พนักงาน หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก',
                showDenyButton: false,
            didOpen: () => {
                // โฟกัสที่ปุ่ม confirm
                Swal.getConfirmButton().focus();
            }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{$page_url}}/{{$user->id}}', // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if(response == true){
                                Swal.fire('แก้ไขพนักงานเรียบร้อยแล้ว', '', 'success');
                                loadData(page);
                                view('{{$user->id}}');
                            }
                        },
                        error: function(error) {
                            Swal.fire('เกิดข้อผิดพลาด', '', 'error');
                            console.error('เกิดข้อผิดพลาด:', error);
                        }
                    });
                } else if (result.isDismissed) {
                    // Swal.fire('ยกเลิกการดำเนินการ', '', 'info');
                }
            });
        });
        
        $('#bs-datepicker-format2').datepicker({
            format: 'dd/mm/yyyy', // กำหนดรูปแบบวันที่
            autoclose: true,      // ปิด datepicker เมื่อเลือกวันที่
            todayHighlight: true  // ไฮไลต์วันที่ปัจจุบัน
        });
</script>