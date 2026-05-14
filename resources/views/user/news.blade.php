<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" />
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" />
<div class="col-span-12">
    <div class="box pr-5 pl-5">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
            {{-- <h2 class="font-medium text-base mr-auto"><b>ข่าวสาร</b></h2> --}}
            <div class="form-check form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0">
                @if (Auth::user()->ref_position_id == 1)
                    <div  id="changeTapNews">
                        <label class="btn btn-warning mr-1" for="show-example-5" type="button" onclick="changeTapNews(1);">
                            <i data-lucide="check-square" class="w-4 h-4 mr-1" style="color: white;"></i> Edit
                        </label>
                    </div>
                @endif
            </div>
        </div>
        <div class="p-5">
            <p align="center" id="change_loading" style="display: none"><i data-loading-icon="oval" data-color="black"></i></p>
            {{-- /////  แสดง  ///// --}}
            <div class="preview" id="view_news" style="overflow: hidden;height: 300px;">
                {!! $news->detail !!}
            </div>
            <p align="right" id="ViewNewsAll" class="mr-1 mt-2" style="color: blue;">
                <a href="javascript:void();" onclick="ViewNewsAll()">
                ... ดูเพิ่มเติม 
                </a>
            </p>

            {{-- /////  จบ แสดง  ///// --}}

            {{-- /////  เริ่ม แก้ไข  ///// --}}
            <form id="edit_news" style="display: none;margin-top: -10px;">

                @csrf
                <button class="btn btn-primary mr-1 mb-2">
                    <i data-lucide="save" class="block mx-auto w-4 h-4 mr-1"></i>
                    Save
                    <span class="id_loading_icon" style="display: none">
                        <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2"></i>
                    </span>
                </button>
                    <div class="card-body">
                        <div id="cover-toolbar" class="mb-3">
                            <span class="ql-formats">
                                <select class="ql-font"></select>
                                <select class="ql-size"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                                <button class="ql-strike"></button>
                            </span>
                            <span class="ql-formats">
                                <select class="ql-color"></select>
                                <select class="ql-background"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-script" value="sub"></button>
                                <button class="ql-script" value="super"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-header" value="1"></button>
                                <button class="ql-header" value="2"></button>
                                <button class="ql-blockquote"></button>
                                <button class="ql-code-block"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                                <button class="ql-indent" value="-1"></button>
                                <button class="ql-indent" value="+1"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-direction" value="rtl"></button>
                                <select class="ql-align"></select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link"></button>
                                <button class="ql-image"></button>
                                <button class="ql-video"></button>
                                <button class="ql-formula"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-clean"></button>
                            </span>
                        </div>
                        <div id="cover_editor" class="mb-3">
                            {!! $news->detail !!}
                        </div>
                        <input type="hidden" name="detail" id="cover-content">
                    </div>
                    <button class="btn btn-primary mr-1 mt-2">
                        <i data-lucide="save" class="block mx-auto w-4 h-4 mr-1"></i>
                        Save
                        <span class="id_loading_icon" style="display: none">
                            <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2"></i>
                        </span>
                    </button>
                </form>
            {{-- /////  จบ แก้ไข  ///// --}}
            
        </div>
    </div>
</div>
<div id="viewNews" style="display: none;">
    <label class="btn btn-success mr-1" for="show-example-5" type="button" onclick="changeTapNews(2);">
        <i data-lucide="eye"  class="w-4 h-4 mr-1" style="color: white;"></i> View
    </label>
</div>
<div id="viewEdit" style="display: none;">
    <label class="btn btn-warning mr-1" for="show-example-5" type="button" onclick="changeTapNews(1);">
        <i data-lucide="check-square" class="w-4 h-4 mr-1" style="color: white;"></i> Edit
    </label>
</div>
<script>
    const coverEditor = new Quill('#cover_editor', {
        modules: {
            syntax: true,
            toolbar: '#cover-toolbar', // Unique toolbar for cover editor
        },
        placeholder: 'Compose the cover page...',
        theme: 'snow',
    });
</script>
<script>
    var ButtonViewNewsAll = 1;
    function changeTapNews(change_news){
        // alert(ViewNewsAll);
        $('#change_loading').css('display','block');
        setTimeout(() => {
            if(change_news == 1){
                // ButtonViewNewsAll = 0;
                $('#ViewNewsAll').css('display','none');
                var textView = $("#viewNews").html();
                $('#view_news').css('display','none');
                $('#edit_news').css('display','block');
                $("#changeTapNews").html(textView);
            }else{
                var textEdit = $("#viewEdit").html();
                $('#edit_news').css('display','none');
                $('#view_news').css('display','block');
                $("#changeTapNews").html(textEdit);
                // if(ButtonViewNewsAll == 0){
                //     ButtonViewNewsAll = 1;
                //     $('#ViewNewsAll').show();
                // }else{
                //     ButtonViewNewsAll = 0;
                //     $('#ViewNewsAll').hide();
                // }
            }
            $('#change_loading').css('display','none');
        }, 100);

    }
    function ViewNewsAll(){
        $('#ViewNewsAll').hide();
        $('#view_news').css('overflow','unset');
        $('#view_news').css('height','unset');
        ButtonViewNewsAll = 0;
    }
</script>
{{-- <script src="{{ mix('dist/js/ckeditor-classic.js') }}"></script> --}}
