@extends('../layout/' . $layout)

@section('subhead')
    <title>ผู้ใช้งาน</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ตำแหน่ง</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2" style="margin-bottom: -30px">
            @if (Auth::user()->ref_position_id == 1)
                <a href="{{$page_url}}/create" class="btn btn-primary shadow-md mr-2">เพิ่ม &nbsp; <i class="w-4 h-4" data-lucide="plus"></i></a>
            @endif
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-slate-500">
                    <input type="text" class="form-control w-56 box pr-10" placeholder="Search...">
                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                </div>
            </div>
        </div>
        <!-- BEGIN: Data List -->
        <div id="table-data" class="intro-y col-span-12 overflow-auto lg:overflow-visible">
 
        </div>
        <!-- END: Pagination -->

    </div>
    <!-- BEGIN: Delete Confirmation Modal -->
    <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5">ต้องการลบ ?</div>
                        <div class="text-slate-500 mt-2">ตำแหน่งจะถูก ลบออก จากฐานข้อมูล</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                        <button type="button" data-tw-dismiss="modal" onclick="confirmDelete()" class="btn btn-danger w-24">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirmation Modal -->
@endsection

@section('script')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
        var page = "{{$page_url}}/datatable";
        loadData(page);
        function loadData(page){
            $.ajax({
                type: "GET",
                url: page,
                data: {
                },
                success: function(data) {
                    $("#table-data").html(data);
                }
            });
        }
        let DeleteId = 10;
        function Delete(id){
            DeleteId = id;
            // console.log(DeleteId);
        }
        function confirmDelete(){
            $.ajax({
                type: "DELETE",
                url: "{{$page_url}}/"+DeleteId,
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    // $('#delete-confirmation-modal').modal('hide');
                    loadData(page);
                }
            });

        }


</script>

@endsection