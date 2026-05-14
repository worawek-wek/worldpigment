<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template">

<head>
    @include('layout/inc_header')
    <title>Dashboard - CRM | Vuexy - Bootstrap Admin Template</title>
</head>
<style>
.table th {
    font-size: 15px;
    font-weight: bold;
}
.table td {
    padding-top: 14px;
    padding-bottom: 14px;
}
.modalHeadDecor .modal-header {
    padding: 0;
}

.modalHeadDecor .modal-title {
    padding: 1.25rem 1.5rem 1.25rem;
    color: white;
    background-color: #54BAB9;
    position: relative;
}

.modalHeadDecor .modal-title::after {
    position: absolute;
    top: 0;
    right: -65px;
    content: '';
    width: 0;
    height: 0;
    border-top: 65px solid #54BAB9;
    border-right: 65px solid transparent;
}
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layout/inc_sidemenu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('layout/inc_topmenu')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row ">
                            <div class="col-sm-12">
                                <div class="card mb-3">
                                    <div class="card-header border-bottom border-bottom">
                                        <div class="row g-3 justify-content-between">
                                            <div class="col-sm-12">
                                                <h4 class="mb-0">
                                                    <i class="tf-icons ti ti-copy text-main ti-md me-2"></i>
                                                    พนักงาน
                                                </h4>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="row">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                                                            <input
                                                            name="search"
                                                            type="text"
                                                            class="form-control p_search"
                                                            placeholder="ค้นหาคีเวิร์ดที่ต้องการ"
                                                            aria-label="ค้นหาคีเวิร์ดที่ต้องการ"
                                                            aria-describedby="basic-addon-search31" oninput="loadData('{{$page_url}}/datatable')" />
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="tab-content p-0">
                                            <div class="tab-pane fade show active" id="navs-pills-top-home"
                                                role="tabpanel">
                                                <div class="row p-3">
                                                    <div class="col-lg-4">
                                                        <div class="d-flex align-items-center mb-2 mb-md-0">
                                                            <label class="">Show</label>
                                                            <select onchange='loadData("{{$page_url}}/datatable")' name="limit" class="form-select ms-2 me-2 p_search" style="width:100px">
                                                                <option value="15">15</option>
                                                                <option value="50">50</option>
                                                                <option value="75">75</option>
                                                                <option value="100">100</option>
                                                                <option value="200">200</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 flex text-end" style="padding-right: unset !important;">
                                                        {{-- <button
                                                                style="padding-right: 14px;padding-left: 14px;"
                                                                class="btn btn-success buttons-collection btn-warning waves-effect waves-light me-2 d-write"
                                                                tabindex="0" aria-controls="DataTables_Table_0"
                                                                type="button" aria-haspopup="dialog"
                                                                aria-expanded="false"  
                                                                onclick="window.open('{{$page_url}}/export/excel', '_blank')">
                                                                <span>
                                                                    <i class="ti ti-upload"></i> 
                                                                    ดาวน์โหลด Excel
                                                                </span>
                                                        </button> --}}
                                                        <button
                                                                style="padding-right: 14px;padding-left: 14px;margin-right: 0px;"
                                                                class="btn btn-success buttons-collection  btn-info waves-effect waves-light d-write"
                                                                tabindex="0" aria-controls="DataTables_Table_0"
                                                                type="button" aria-haspopup="dialog"
                                                                aria-expanded="false" data-bs-toggle="modal" data-bs-target="#addserviceModal">
                                                            <span><i class="ti ti-plus"></i> เพิ่มพนักงาน</span>
                                                        </button>
                                                        {{-- <button
                                                                style="padding-right: 14px;padding-left: 14px;margin-right: 0px;"
                                                                class="btn btn-primary buttons-collection  btn-info waves-effect waves-light d-write"
                                                                tabindex="0" aria-controls="DataTables_Table_0"
                                                                type="button" aria-haspopup="dialog"
                                                                aria-expanded="false" data-bs-toggle="modal" data-bs-target="#addserviceModal_2">
                                                            <span><i class="ti ti-plus"></i> เพิ่ม แม่บ้าน, รปภ</span>
                                                        </button> --}}
                                                    </div>
                                                </div>
                                                <div class="card-body px-0 pt-0">
                                                    <div class="tab-content p-0" id="pills-tabContent">
                                                        <div class="tab-pane fade show active" id="pills-profile" role="tabpanel"
                                                            aria-labelledby="pills-profile-tab" tabindex="0">

                                                            <div id="table-data">

                                                                {{-- ตารางอยู่ตรงนี้นะจ๊ะ --}}

                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layout/inc_footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!--add service  Modal -->
    {{-- <div class="modal fade modalHeadDecor" id="addserviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title" id="exampleModalLabel1">&nbsp;เพิ่มพนักงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="insert_user">		
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3 p-4">
                            <div class="col-sm-6">
                                <label for="email" class="form-label">ค้นหาด้วย อีเมลหรือเบอร์โทร</label><span class="text-danger"> *</span>
                                <input id="email" name="email" type="text" class="form-control" placeholder="กรอกอีเมลหรือเบอร์โทร" required />
                            </div>
                            <div class="col-sm-6 d-flex align-items-end">
                                <button type="button" class="btn btn-info" onclick="check_user()"><i class="ti ti-search me-2"></i> ค้นหา</button>
                            </div>
                            <div class="col-sm-6">
                                <span id="text-check-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer rounded-0 justify-content-center">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-main">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
    <div class="modal fade modalHeadDecor" id="addserviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title" id="exampleModalLabel1">&nbsp;เพิ่มพนักงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="insert_user_2">		
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3 p-4">
                            <div class="col-sm-6">
                                <label for="" class="form-label">ชื่อพนักงาน</label><span class="text-danger"> *</span>
                                <input name="name" type="text" class="form-control" placeholder="ชื่อพนักงาน" required />
                            </div>
                            {{-- <div class="col-sm-6">
                                <label for="" class="form-label">เงินเดือน</label><span class="text-danger"> *</span>
                                <input name="salary" type="text" class="form-control" id="salary" placeholder="เงินเดือน" oninput="formatSalary()" required/>
                            </div> --}}
                            <div class="col-sm-6">
                                <label for="" class="form-label">เบอร์โทรศัพท์<span class="text-danger"> *</span></label>
                                <input name="phone" type="tel" class="form-control" placeholder="เบอร์โทรศัพท์" oninput="this.value=this.value.slice(0,10);" pattern="^\d{9,10}$" required/>
                            </div>
                            <div class="col-sm-6">
                                <label for="email_2" class="form-label">อีเมล</label><span class="text-danger"> *</span>
                                <input name="email" id="email_2" type="email" class="form-control" placeholder="อีเมล" oninput="check_have_email(this.value)" required/>
                                <span class="text-danger pt-4" id="Cant_Use" style="display: none;">Email นี้ถูกใช้แล้ว</span>
                            </div>
                            <div class="col-sm-6">
                                <label for="bs-datepicker-format" class="form-label">วันที่เข้าทำงาน</label>
                                <input name="work_start_date" type="text" class="form-control" id="bs-datepicker-format" placeholder="วัน/เดือน/ปี" autocomplete="off"/>
                            </div>
                            <div class="col-sm-6">
                                <label for="" class="form-label">ตำแหน่ง</label>
                                <select name="ref_position_id" id="select2Position1" class="select2 form-select form-select-lg" data-allow-clear="true">
                                    <option value="1">Admin</option>
                                    <option value="2">Staff</option>
                                </select>
                            </div>
                            
                            <div class="col-span-12">
                                <div class="col-sm-6 mt-3">
                                    <label for="username" class="form-label">ชื่อผู้ใช้</label><span class="text-danger"> *</span>
                                    <input name="username" type="text" class="form-control" placeholder="ชื่อผู้ใช้" id="username" required readonly />
                                </div>
                                <div class="col-sm-6 mt-3">
                                    <label for="update-profile-form-2" class="form-label">รหัสผ่าน</label><span class="text-danger"> *</span>
                                    <input name="password" id="password" type="password" class="form-control" placeholder="รหัสผ่าน">
                                </div>
                                <div class="col-sm-6 mt-3">
                                    <label for="update-profile-form-3" class="form-label">ยืนยัน รหัสผ่าน</label><span class="text-danger"> *</span>
                                    <input id="confirm_password" type="password" class="form-control" placeholder="ยืนยัน รหัสผ่าน">
                                </div>
                            </div>
                            <script>
                                //// ทำ input เงินเดือน เริ่ม
                                function formatSalary() {
                                    const input = document.getElementById('salary');
                                    let value = input.value.replace(/,/g, ''); // ลบเครื่องหมายจุลภาค
                                    if (!isNaN(value) && value !== '') {
                                        input.value = Number(value).toLocaleString(); // แปลงเป็นรูปแบบ number_format
                                    } else {
                                        input.value = ''; // ถ้าไม่ใช่ตัวเลขให้ลบค่าที่ป้อน
                                    }
                                }
                                //// ทำ input เงินเดือน จบ

                                //// ทำ เช็ค Password เริ่ม
                                var password = document.getElementById("password"), confirm_password = document.getElementById("confirm_password");

                                function validatePassword(){
                                    if(password.value != confirm_password.value) {
                                        confirm_password.setCustomValidity("โปรดกรอกรหัสผ่านให้ตรงกัน");
                                    } else {
                                        confirm_password.setCustomValidity('');
                                    }
                                }

                                password.onchange = validatePassword;
                                confirm_password.onkeyup = validatePassword;
                                //// ทำ เช็ค Password จบ

                            </script> 
                            <div class="col-sm-12">
                                <label for="" class="form-label">หมายเหตุ</label>
                                <textarea name="remark" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer rounded-0 justify-content-center">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-main">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade modalHeadDecor" id="insurance" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document" id="view">
            
        </div>
    </div>
    
    <div class="modal fade modalHeadDecor" id="delete_confirmation_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document" id="delete_html">
            
        </div>
    </div>
    <!--set rent Modal -->
    <iframe id="print-iframe" style="display: none;"></iframe>    
    <!-- / Layout wrapper -->
    @include('layout/inc_js')
    <script>
        var page = "{{$page_url}}/datatable";
        var searchData = {};
        loadData(page);
        
        function loadData(pages){
            
            $('.p_search').each(function() {
                var inputName = $(this).attr('name'); // ดึงชื่อ attribute 'name' ของ input
                var inputValue = $(this).val(); // ดึงค่า value ของ input
                
                searchData[inputName] = inputValue; // เก็บข้อมูลลงในออบเจ็กต์ searchData
            });

            // alert(page);
            page = pages;
            $.ajax({
                type: "GET",
                url: pages,
                data: searchData,
                success: function(data) {
                    $("#table-data").html(data);

                    $('.select2Position2').select2({
                        placeholder: 'เลือก',
                    });
                }
            });
            // alert(page);
        }

        function view(id){
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/"+id,
                success: function(data) {
                    $("#view").html(data);

                    $('#select2Position2').select2({
                        placeholder: 'เลือกตำแหน่ง',
                        allowClear: true,
                        dropdownParent: $('#insurance'), // 💥 สำคัญมาก ถ้าอยู่ใน modal
                        width: '100%'
                    });

                }
            });
        }
        
        var no_insert = 0;
        function check_have_email(email){
            $('#username').val(email);

            $.ajax({
                type: "GET",
                url: "user/check-have-email",
                data: { email: email },
                success: function(data) {
                    if(data == true){
                        $('#Cant_Use').hide();
                        no_insert = 0;
                    }else{
                        $('#Cant_Use').show();
                        no_insert = 1;
                    }
                }
            });
        }

        function Delete(id){
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'ผู้ลงทะเบียนจะถูกลบออก?',
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
                        type: "DELETE",
                        url: "/setting/user/"+id,
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if(response == true){
                                Swal.fire('ลบผู้ลงทะเบียนเรียบร้อยแล้ว', '', 'success');
                                loadData(page);
                                summary();
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
        }
        
        function check_user(){
            var email = $('#email').val();
            $.ajax({
                type: "GET",
                url: "{{ $page_url }}/check-user/"+email,
                success: function(data) {
                    $("#text-check-user").html(data);

                }
            });
        }

        $('#insert_user').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการเพิ่มพนักงานหรือไม่?',
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
                        url: '{{$page_url}}/insert_user_has_branch', // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if(response == true){
                                $('#insert_user')[0].reset();
                                $('#text-check-user').html('');
                                Swal.fire('เพิ่มพนักงานเรียบร้อยแล้ว', '', 'success');
                                $('#addserviceModal').modal('hide');
                                loadData(page);
                            }else{
                                Swal.fire(response, '', 'error');

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
        $('#insert_user_2').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการส่งฟอร์มปกติ
            if(!this.checkValidity()) {
                // ถ้าฟอร์มไม่ถูกต้อง
                this.reportValidity();
                return console.log('ฟอร์มไม่ถูกต้อง');
            }
            if(no_insert == 1){
                Swal.fire('Email นี้ถูกใช้แล้ว', '', 'error');
                $('#email_2').focus();

                return ;
            }

            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการเพิ่มพนักงานหรือไม่?',
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
                        url: '{{$page_url}}/insert_user_to_branch', // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if(response == true){
                                $('#insert_user_2')[0].reset();
                                Swal.fire('เพิ่มพนักงานเรียบร้อยแล้ว', '', 'success');
                                $('#addserviceModal_2').modal('hide');
                                loadData(page);
                            }else{
                                Swal.fire(response, '', 'error');
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
        function change_position(v, id){
            // return alert(123);
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: 'คุณต้องการเปลี่ยนตำแหน่งพนักงานหรือไม่?',
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
                        url: '{{$page_url}}/change-position/'+ id, // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: {
                            ref_position_id : v,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if(response == true){
                                Swal.fire('เปลี่ยนตำแหน่งพนักงานเรียบร้อยแล้ว', '', 'success');
                                loadData(page);
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
        };
        
        // window.onload = function() {
        //     $('#addserviceModal').modal('show');
        // };
        $('#bs-datepicker-format').datepicker({
            format: 'dd/mm/yyyy', // กำหนดรูปแบบวันที่
            autoclose: true,      // ปิด datepicker เมื่อเลือกวันที่
            todayHighlight: true  // ไฮไลต์วันที่ปัจจุบัน
        });
        $('#select2Position1').select2();
        function printPdfBill() {
            $.ajax({
                url: '/pdf/user/1',
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
                error: function(xhr) {
                    alert('เกิดข้อผิดพลาด');
                    console.error(xhr.responseText);
                }
            });
        }
    </script>
</body>

</html>