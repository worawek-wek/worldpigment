@extends('../layout/' . $layout)

@section('subhead')
    <title>Add Employee</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{$title}}</h2>
    </div>
    <div class="grid grid-cols-12 gap-6">
        <!-- END: Profile Menu -->
        <div class="col-span-12">
            <!-- BEGIN: Display Information -->
            <form action="{{$action}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="intro-y box lg:mt-5">
                <div class="p-5">
                    <div class="flex flex-col-reverse xl:flex-row flex-col">
                        <div class="flex-1 mt-6 xl:mt-0">
                            <div class="grid grid-cols-12 gap-x-5">
                                <div class="col-span-12 2xl:col-span-6">
                                    <div>
                                        <label for="update-profile-form-1" class="form-label">Position</label>
                                        <input name="position_name" id="update-profile-form-1" type="text" class="form-control" placeholder="Name" value="{{ @$position['position_name'] }}">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-20 mt-3">Save</button>
                        </div>


                    </div>
                </div>
            </div>
            </form>
            <!-- END: Personal Information -->
        </div>
    </div>
    <script>
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

        
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                $('.imagePreview').show();
                reader.onload = function(e) {
                $('.imagePreview').attr('src', e.target.result);
                }
                
                reader.readAsDataURL(input.files[0]); // convert to base64 string
                var nameImg = $('#nameImg').val();
                if(nameImg == ''){
                    var productCode = $('#productCode').val();
                    var productName = $('#productName').val();
                    
                    $('#nameImg').val(productCode+'_'+productName);
                }
            }
        }
            function imgChange(t) {
            
            const size =  
                    (t.files[0].size / 1024 / 1024).toFixed(2); 

                if (size > 2) { 
                    $('#customFile').val(null);
                     alert("The image size must not exceed 2 MB."); 
                     return false;
                }
            readURL(t);
        }
    </script>
@endsection
