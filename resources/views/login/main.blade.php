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
    <link rel="stylesheet" href="{{url('')}}/assets/vendor/css/pages/page-auth.css" />
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
              <img src="{{url('')}}/assets/img/illustrations/main.png" width="300px" alt="" style="margin: auto;">
            </div>
            <!-- /Logo -->
            <h3 class="mb-1">ยินดีต้อนรับ</h3>
            <p class="mb-4">Please sign-in to your account and start the adventure</p>

                    {{-- @include('layout/inc_footer') --}}
            <div id="formAuthentication" class="mb-3">
              <form id="login-form">
                <div class="mb-3">
                  <label for="email" class="form-label">Email or Username</label>
                  <input
                    type="text"
                    class="form-control"
                    id="email"
                    name="email-username"
                    placeholder=""
                    autofocus
                    value="admin@gmail.com" />
                <div id="error-email" class="login__input-error text-danger mt-2"></div>
                </div>
                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                    {{-- <a href="auth-forgot-password-cover.html">
                      <small>Forgot Password?</small>
                    </a> --}}
                  </div>
                  <div class="input-group">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password"
                      value="172839" />
                    <span class="input-group-text cursor-pointer" onclick="togglePassword()"><i id="eye-icon" class="ti ti-eye-off"></i></span>
                  </div>
                  <div id="error-password" class="login__input-error text-danger mt-2"></div>
                </div>
              </form>
                {{-- <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                </div> --}}
                <button id="btn-login" class="btn btn-main w-100">Sign in</button>
            </div>
                <p class="text-center">
                  <span>ไม่มีบัญชี</span>
                  <a href="register">
                    <span>สมัครสมาชิก</span>
                  </a>
                </p>
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>

    @include('layout/inc_js')
    
@section('script')

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
(function () {
    async function login() {
        // Reset state
        $('#login-form').find('.login__input').removeClass('border-danger')
        $('#login-form').find('.login__input-error').html('')

        // Post form
        let email = $('#email').val()
        let password = $('#password').val()

        // Loading state
        $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
        tailwind.svgLoader()
        await helper.delay(1500)

        axios.post(`login`, {
            email: email,
            password: password
        }).then(res => {
            location.href = '/user'
        }).catch(err => {
            $('#btn-login').html('Login')

            // ✅ ตรวจจับกรณี token mismatch
            if (err.response && err.response.data && err.response.data.message === 'CSRF token mismatch.') {
                // แสดงข้อความ หรือ refresh หน้า
                alert('Session หมดอายุ กำลังรีเฟรชหน้าใหม่...')
                location.reload() // 🔄 รีเฟรชหน้า
                return
            }

            if (err.response.data.message != 'Wrong email or password.') {
                for (const [key, val] of Object.entries(err.response.data.errors)) {
                    $(`#${key}`).addClass('border-danger')
                    $(`#error-${key}`).html(val)
                }
            } else {
                $(`#password`).addClass('border-danger')
                $(`#error-password`).html(err.response.data.message)
            }
        })
    }

    $('#login-form').on('keyup', function(e) {
        if (e.keyCode === 13) {
            login()
        }
    })

    $('#btn-login').on('click', function() {
        login()
    })
})()
</script>

@endsection
  </body>
</html>

{{-- ////////////////////////////// --}}
    {{-- <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    <img alt="Icewall Tailwind HTML Admin Template" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                    <span class="text-white text-lg ml-3">
                        ลางาน
                    </span>
                </a>
                <div class="my-auto">
                    <img alt="Icewall Tailwind HTML Admin Template" class="-intro-x w-1/2 -mt-16" src="{{ asset('main_picture/logo.png') }}" style="width: min-content;margin-left: 90px;">
                    <div class="-intro-x text-white font-medium text-2xl leading-tight mt-10">บริษัท ทรงพล การบัญชีและกฎหมาย จำกัด</div>
                </div>
            </div>
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Sign In</h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">A few more clicks to sign in to your account. Manage all your e-commerce accounts in one place</div>
                    <div class="intro-x mt-8">
                        <form id="login-form">
                            <input id="email" type="text" class="intro-x login__input form-control py-3 px-4 block" placeholder="Email" value="wloverine.origins@gmail.com">
                            <div id="error-email" class="login__input-error text-danger mt-2"></div>
                            <input id="password" type="password" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password" value="172839">
                            <div id="error-password" class="login__input-error text-danger mt-2"></div>
                        </form>
                    </div>
                    <div class="intro-x flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                        <div class="flex items-center mr-auto">
                            <input id="remember-me" type="checkbox" class="form-check-input border mr-2">
                            <label class="cursor-pointer select-none" for="remember-me">Remember me</label>
                        </div>
                        <a href="">Forgot Password?</a>
                    </div>
                    <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                        <button autofocus id="btn-login" class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">Login</button>
                        <button class="btn btn-outline-secondary py-3 px-4 w-full xl:w-32 mt-3 xl:mt-0 align-top">Register</button>
                    </div>
                    <div class="intro-x mt-10 xl:mt-24 text-slate-600 dark:text-slate-500 text-center xl:text-left">
                        By signin up, you agree to our <a class="text-primary dark:text-slate-200" href="">Terms and Conditions</a> & <a class="text-primary dark:text-slate-200" href="">Privacy Policy</a>
                    </div>
                </div>
            </div>

        </div>
    </div> --}}
{{-- ///////////////////////////// --}}
{{-- @endsection --}}

