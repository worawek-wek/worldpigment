@php $isEdit = (bool) $role; @endphp
<form id="role_form">
    @csrf

    @if($isEdit)
        <input type="hidden" name="id" value="{{ $role->id }}">
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="role_name">
                ชื่อ Role <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="role_name" name="name"
                value="{{ $role?->name }}" maxlength="255" placeholder="เช่น ผู้ดูแลระบบ, ฝ่ายผลิต">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="role_description">รายละเอียด</label>
            <input type="text" class="form-control" id="role_description" name="description"
                value="{{ $role?->description }}" maxlength="255" placeholder="รายละเอียด (ถ้ามี)">
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="role_is_active" name="is_active" value="Y"
                {{ ($role?->is_active ?? 'Y') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="role_is_active">เปิดใช้งาน</label>
        </div>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" role="switch"
                id="role_is_default" name="is_default" value="Y"
                {{ ($role?->is_default ?? 'N') === 'Y' ? 'checked' : '' }}>
            <label class="form-check-label" for="role_is_default">
                ตั้งเป็นค่าเริ่มต้นของพนักงานใหม่
                <small class="text-muted d-block">Role นี้จะถูกเลือกให้อัตโนมัติเมื่อเพิ่มพนักงานใหม่ (มีได้เพียง Role เดียว)</small>
            </label>
        </div>
    </div>

    <hr class="my-3">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="form-label mb-0 fw-bold">
            <i class="ti ti-menu-2 me-1"></i>เมนูที่เข้าถึงได้
        </label>
        <div>
            <button type="button" class="btn btn-sm btn-label-primary" id="btn_check_all">เลือกทั้งหมด</button>
            <button type="button" class="btn btn-sm btn-label-secondary" id="btn_uncheck_all">ล้างทั้งหมด</button>
        </div>
    </div>

    <div class="border rounded p-3" style="max-height: 45vh; overflow-y: auto;">
        @foreach($menu as $key => $item)
            @if(isset($item['type']) && $item['type'] === 'header')
                <div class="text-muted small text-uppercase mt-2 mb-1">{{ $item['title'] }}</div>
                @continue
            @endif

            @if(isset($item['sub_menu']))
                {{-- เมนูหลักที่มีเมนูย่อย --}}
                <div class="mb-2">
                    <div class="form-check">
                        <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]"
                            value="{{ $key }}" id="perm_{{ $key }}"
                            {{ in_array($key, $selectedKeys) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="perm_{{ $key }}">
                            {{ $item['title'] }}
                        </label>
                    </div>
                    <div class="ms-4">
                        @foreach($item['sub_menu'] as $subKey => $sub)
                            <div class="form-check">
                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]"
                                    value="{{ $subKey }}" id="perm_{{ $subKey }}"
                                    {{ in_array($subKey, $selectedKeys) ? 'checked' : '' }}>
                                <label class="form-check-label {{ isset($sub['sub_menu']) ? 'fw-semibold' : '' }}" for="perm_{{ $subKey }}">
                                    {{ $sub['title'] }}
                                </label>
                            </div>
                            {{-- เมนูย่อยระดับ 3 --}}
                            @if(isset($sub['sub_menu']))
                                <div class="ms-4">
                                    @foreach($sub['sub_menu'] as $childKey => $child)
                                        <div class="form-check">
                                            <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]"
                                                value="{{ $childKey }}" id="perm_{{ $childKey }}"
                                                {{ in_array($childKey, $selectedKeys) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $childKey }}">
                                                {{ $child['title'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="form-check mb-1">
                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]"
                        value="{{ $key }}" id="perm_{{ $key }}"
                        {{ in_array($key, $selectedKeys) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="perm_{{ $key }}">
                        {{ $item['title'] }}
                    </label>
                </div>
            @endif
        @endforeach
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="btn_role_save">
            <i class="ti ti-device-floppy me-1"></i>บันทึก
        </button>
    </div>
</form>

<script>
    // เลือก/ล้าง ทุกเมนู (ผูกแบบ delegation เพราะฟอร์มโหลดผ่าน AJAX)
    $(document).off('click.roleperm', '#btn_check_all').on('click.roleperm', '#btn_check_all', function () {
        $('#role_form .perm-checkbox').prop('checked', true);
    });
    $(document).off('click.roleperm', '#btn_uncheck_all').on('click.roleperm', '#btn_uncheck_all', function () {
        $('#role_form .perm-checkbox').prop('checked', false);
    });
</script>
