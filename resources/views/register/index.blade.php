<!doctype html>
@extends('../layout/' . $layout)
<html
  lang="en"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{url('')}}/assets/"
  data-template="vertical-menu-template">
  <head>
    @include('layout/inc_header')
    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" width="30%" href="{{url('')}}/assets/vendor/css/pages/page-auth.css" />
  </head>

  <body>
    <!-- Content -->
    <div class="authentication-wrapper authentication-cover authentication-bg">
      <div class="authentication-inner row">
        <!-- /Left Text -->
        <div class="d-none d-lg-flex col-lg-7 p-0">
          <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center mx-0">
            <img style="max-height: none;"
              src="{{url('')}}/assets/img/illustrations/auth-login-illustration-light.png"
              alt="auth-login-cover"
              class="img-fluid auth-illustration"
              data-app-light-img="illustrations/auth-login-illustration-light.png"
              data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
          </div>
        </div>
        <!-- /Left Text -->
        <!-- Login -->
        <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
          <div class="col-12 mx-auto">
            <!-- Logo -->
            <div class="app-brand mb-4">
              <img src="{{url('')}}/assets/img/illustrations/main.png" alt="" width="30%" style="margin: auto;">
            </div>
            <!-- /Logo -->
            <h3 class="mb-1">ยินดีต้อนรับ</h3>
            <p class="mb-4">Make your app management easy and fun!</p>

                    {{-- @include('layout/inc_footer') --}}
            <div id="formAuthentication" class="mb-3">
              <form id="insert_user">		
                    @csrf
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
                                <label for="" class="form-label">เบอร์โทรศัพท์</label></label><span class="text-danger"> *</span>
                                <input name="phone" type="tel" class="form-control" placeholder="เบอร์โทรศัพท์" oninput="this.value=this.value.slice(0,10);" pattern="^\d{9,10}$" required />
                            </div>
                            <div class="col-sm-6">
                                <label for="" class="form-label">อีเมล</label></label><span class="text-danger"> *</span>
                                <input name="email" id="email_2" type="email" class="form-control" placeholder="อีเมล" oninput="check_have_email(this.value)" required/>
                                <span class="text-danger pt-4" id="Cant_Use" style="display: none;">Email นี้ถูกใช้แล้ว</span>
                            </div>
                            <div class="col-sm-6">
                                <label for="bs-datepicker-format" class="form-label">วันที่เข้าทำงาน
                                <input name="work_start_date" type="text" class="form-control" id="bs-datepicker-format" placeholder="วัน/เดือน/ปี" autocomplete="off"/>
                            </div>
                            {{-- <div class="col-sm-6">
                                <label for="" class="form-label">ตำแหน่ง</label>
                                <select name="ref_position_id" id="select2Position1" class="select2 form-select form-select-lg" data-allow-clear="true">
                                    @foreach ($position as $pos)
                                        <option value="{{$pos->id}}">{{$pos->position_name}}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            
                            <div class="col-span-12">
                                <div class="col-sm-6 mt-3">
                                    <label for="" class="form-label">ชื่อผู้ใช้</label><span class="text-danger"> *</span>
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
                                        confirm_password.setCustomValidity("Passwords Don't Match");
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
                <button type="submit" class="btn btn-main w-100">สมัครสมาชิก</button>
                </form>
            </div>
                <p class="text-center">
                  <span>มีบัญชี</span>
                  <a href="login">
                    <span>เข้าสู่ระบบ</span>
                  </a>
                </p>
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>

    @include('layout/inc_js')
    
<script>
  document.addEventListener('DOMContentLoaded', function() {
    function togglePassword() {
      var passwordField = document.getElementById('password');
      var eyeIcon = document.getElementById('eye-icon');
      
      // Toggle the type of the input field between password and text
      if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove("ti-eye-off");
        eyeIcon.classList.add("ti-eye");
      } else {
        passwordField.type = "password";
        eyeIcon.classList.remove("ti-eye");
        eyeIcon.classList.add("ti-eye-off");
      }
    }
  });
</script>
<script>
    
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
        $('#insert_user').on('submit', function(event) {
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
                        url: 'user', // เปลี่ยน URL เป็นจุดหมายที่ต้องการ
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if(response == true){
                                $('#insert_user')[0].reset();
                                Swal.fire('สมัครสมาชิกเรียบร้อยแล้ว', '', 'success');
                                setTimeout(() => {
                                    location.href = '/branch/manage'
                                }, 1000);
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
        $('#bs-datepicker-format').datepicker({
            format: 'dd/mm/yyyy', // กำหนดรูปแบบวันที่
            autoclose: true,      // ปิด datepicker เมื่อเลือกวันที่
            todayHighlight: true  // ไฮไลต์วันที่ปัจจุบัน
        });
</script>

  </body>
</html>

