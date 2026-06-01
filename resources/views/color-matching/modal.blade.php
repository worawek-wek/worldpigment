<div class="modal modalHeadDecor fade" id="colorMatchingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    เทียบสี (Color Matching)
                </h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <form id="color_matching_form" enctype="multipart/form-data">
                @csrf

                <!-- Body -->
                <div class="modal-body px-5 py-4" style="background-color: #f8f9fb;">
                    @include('color-matching.form')
                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-between flex-wrap gap-2">

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-label-primary">
                            ค้นหาเลขที่ส่ง
                        </button>
                        <button type="button" class="btn btn-label-primary">
                            ค้นหารหัสสี
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-label-secondary"
                            data-bs-dismiss="modal">
                            ปิด
                        </button>
                        <button type="submit" class="btn btn-success px-5">
                            ลงข้อมูล
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>
</div>
