@extends('./layout/main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 mt-5">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <i class="ti ti-lock-off text-danger" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 mb-2">ยังไม่มีสิทธิ์เข้าใช้งาน</h3>
                        <p class="text-muted mb-4">
                            บัญชีของคุณยังไม่ได้รับการกำหนดสิทธิ์ (Role) หรือไม่มีเมนูที่เข้าถึงได้<br>
                            กรุณาติดต่อผู้ดูแลระบบ
                        </p>
                        <a href="{{ route('logout') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-logout me-1"></i>ออกจากระบบ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
