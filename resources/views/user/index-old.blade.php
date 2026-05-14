@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6 mt-4">
        <div class="col-span-12 
        @if (in_array(Auth::user()->ref_position_id,[1,3]))
            2xl:col-span-9
        @endif
        ">
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <!-- END: General Report -->
                <!-- BEGIN: Visitors -->
                
                <!-- END: Visitors -->
                <!-- BEGIN: Users By Age -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-3 mt-2 lg:mt-6 xl:mt-2">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5"><b>ข้อมูลส่วนตัว</b></h2>
                    </div>
                    <div class="report-box-2 intro-y mt-5">
                        <div class="intro-y box mt-5 lg:mt-0">
                            <div class="relative flex items-center p-5">
                                <div class="w-12 h-12 image-fit">
                                    <img alt="Midone - HTML Admin Template" class="rounded-full" @if(!empty($user->image_name)) src="{{ asset('upload/user/' . $user->image_name) }}" @else src="{{asset('dist/images/user-index.png')}}" @endif>
                                </div>
                                <div class="ml-4 mr-auto">
                                    <div class="font-medium text-base">{{ $user->name }}</div>
                                    <div class="text-slate-500">{{ $user->position->position_name }}</div>
                                </div>
                                <div class="dropdown">
                                    <a class="dropdown-toggle w-5 h-5 block" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="more-horizontal" class="w-5 h-5 text-slate-500"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400">
                                <a class="flex items-center text-primary font-medium">
                                    <i data-lucide="box" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->position->position_name }}
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="activity" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->employee_id }}
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->name }}
                                    @if (!empty($row->nickname))
                                    ({{$row->nickname}})
                                    @endif
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i> &nbsp; 
                                    @php
                                        if($user->gender == 1){
                                            echo "ชาย";
                                        }else{
                                            echo "หญิง";
                                        };
                                    @endphp
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->branch->branch_name }}
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="mail" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->email }}
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="phone" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->phone }}
                                </a>
                                <a class="flex items-center mt-3">
                                    <i data-lucide="home" class="w-4 h-4 mr-2"></i> &nbsp; {{ $user->address }}
                                </a>
                            </div>
                            <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400"><a class="flex items-center text-primary font-medium">
                                <div>
                                    <a class="items-center font-medium">
                                        <span class="h-4 mr-2 mt-3"><b>หัวหน้างาน</b> &nbsp; &nbsp;  {{ $user->user->name }}</span> 
                                    </a>
                                </div>
                                <a class="flex items-center">
                                    <span class="h-4 mr-2 mt-3"><b>กะ</b> &nbsp; &nbsp;  {{ $user->work_shift->work_shift_name }}</span> 
                                </a>
                                <a class="flex items-center">
                                @if ($user->ref_branch_id == 8)
                                        <span class="h-4 mr-2 mt-3"><b>วันหยุด</b> &nbsp; &nbsp;  {{ $user->schedule->schedule_name }}</span> 
                                @endif
                                </a>
                                <a class="flex items-center">
                                    <span class="h-4 mr-2 mt-3"><b>วันที่เริ่มงาน</b></span> <br>
                                </a>
                                <a class="flex items-center mt-1 ml-5">
                                    {{ $user->work_start_date_th }}
                                </a>
                                <a class="flex items-center mt-1 ml-5">
                                    (<span id="workkingCalcuShow" style="color: #37d01a;"></span>)
                                </a>
                                <a class="flex items-center">
                                    <span class="h-4 mr-2 mt-3"><b>เกิด</b></span> <br>
                                </a>
                                <a class="flex items-center mt-1 ml-5">
                                    {{ $user->birthday_th }}
                                </a>
                                <a class="flex items-center mt-1 ml-5">
                                    (<span id="oldCalcuShow" style="color: #37d01a;"></span>)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Users By Age -->
                
                <div class="col-span-12 lg:col-span-8 xl:col-span-9 mt-2">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5"><b>ข่าวสาร</b></h2>
                        {{-- <select class="sm:ml-auto mt-3 sm:mt-0 sm:w-auto form-select box">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom-date">Custom Date</option>
                        </select> --}}
                    </div>
                    <div class="report-box-2 intro-y mt-12 sm:mt-5">
                        <div class="box sm:flex">
                            <div id="news_detail">
                                @include('dashboard/news')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <hr>
                </div>
                <!-- END: Ads 2 -->
                <!-- BEGIN: Weekly Top Products -->
                <div class="col-span-12 mb-8">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5"><b>ลางาน</b></h2>
                        <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5 mb-8" data-tw-toggle="modal" data-tw-target="#large-modal-size-preview">
                        @foreach ($leave as $lea)
                            
                            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y" onclick="la('{{$lea->leave_name}}',{{$lea->id}})">
                                <div class="report-box zoom-in">
                                    <div class="box p-5">
                                        <div class="flex">
                                                    {!! $lea->icon !!}
                                            <div class="ml-auto">
                                                <div class="report-box__indicator bg-success tooltip cursor-pointer" style="padding-right: 7px" title="คุณลาไปแล้ว {{$leave_remaining[$lea->id]}} วัน">
                                                    {{ $leave_remaining[$lea->id] .'/'. $lbu[$lea->id] }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-3xl font-medium leading-8 mt-6"> </div>
                                        {{-- <div class="text-3xl font-medium leading-8 mt-6">4.710</div> --}}
                                        <div class="text-base text-slate-500 mt-1">{{ $lea->leave_name }}</div>
                                    </div>
                                </div>
                            </div>
    
                        @endforeach
                    </div>
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <div class="intro-y block sm:flex items-center h-10">
                            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                                <div class="mt-3 ml-3 2xl:mt-0">
                                    <select name="ref_leave_id" onchange="loadDataLeave(this.value)" data-search="true" class="tom-select w-full p_search">
                                        <option value="0" hidden> &nbsp;เลือกประเภทการลา&nbsp; </option>
                                        @foreach ($leave as $lea2)
                                            <option @if($lea2->id == @$_GET['ref_leave_id']) selected @endif value="{{$lea2->id}}"> &nbsp;{{$lea2->leave_name}}  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <table class="table table-report -mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap"><b>#</b></th>
                                    <th class="whitespace-nowrap"><b>ประเภท</b></th>
                                    <th class="whitespace-nowrap"><b>ชื่อ - สกุล</b></th>
                                    <th class="text-center whitespace-nowrap"><b>สาเหตุ</b></th>
                                    <th class="text-center whitespace-nowrap"><b>วันที่ลา</b></th>
                                    <th class="text-center whitespace-nowrap"><b>สถานะ(หัวหน้า)</b></th>
                                    <th class="text-center whitespace-nowrap"><b>สถานะ(ฝ่ายบุคคล)</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $num = 1;
                                @endphp
                                @foreach ($list_data as $row)
                                    <tr class="intro-x">
                                        <td>
                                            <span>{!! $row->leave->icon !!}</span>
                                        </td>
                                        <td>
                                            <span>{{@$row->leave->leave_name}}</span>
                                        </td>
                                        <td>
                                            {{@$row->user->name}}
                                        </td>
                                        <td class="text-center">{{$row->detail}}</td>
                                        <td class="text-center">
                                            @if($row->from_time == "00:00:00")
                                                {{ date("d/m/Y", strtotime($row->from_date)).' - '.date("d/m/Y", strtotime($row->to_date)) }}
                                            @else
                                                {{ date("H:i", strtotime($row->from_time)).' - '.date("H:i", strtotime($row->to_time)) }}
                                                <br>
                                                {{date("d/m/Y", strtotime($row->from_date))}}
                                            @endif
                                        </td>
                                        <td class="text-center text-{{ $row->status->color }}">
                                        {{ $row->status->status_name }}
                                        </td>
                                        <td class="text-center text-{{ $row->status->color }}">
                                        {{ $row->boss_status->status_name }}
                                        </td>
                                        
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- <div class="intro-y flex flex-wrap sm:flex-row sm:flex-nowrap items-center mt-3">
                        <nav class="w-full sm:w-auto sm:mr-auto">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevrons-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">...</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">...</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevrons-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <select class="w-20 form-select box mt-3 sm:mt-0">
                            <option>10</option>
                            <option>25</option>
                            <option>35</option>
                            <option>50</option>
                        </select>
                    </div> --}}
                </div>
                <div id="large-modal-size-preview" class="modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content"><div class="modal-body">
                            <form action="{{url('/leave')}}" autocomplete="off" enctype="multipart/form-data" id="formLeave" method="POST">
                                @csrf
                                <h5 class="mb-1">ยื่นลา</h5>
                                <div class="marked">
                                    <div class="form-group">
                                        <input type="hidden" id="la_id" class="form-control" readonly="" name="ref_leave_id" value="ลาป่วย">
                                        <input type="text" id="la" class="form-control" readonly="" name="subject_leave_show">
                                    </div>
                                    <div class="form-group mt-2">
                                        <label for="exampleTextarea">เหตุผลการลา</label>
                                        <textarea class="form-control mt-2" id="detail" name="detail" rows="3" required></textarea>
                                    </div>
                                </div>
                                
                                <div class="row" id="form-other-none" style="display:block">
                                    <div class="col-12">
                                        <div class="form-group" style="margin-bottom:40px">
                                            <label>รูปแบบการลา</label>
                                            <ul class="type-leave-ul">
                                                <li id="type-half">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" name="type_time" class="custom-control-input" id="day" value="day" checked>
                                                        <label class="custom-control-label" for="day"> &nbsp;วัน</label>
                                                    </div>
                                                    <div>
                                                        <input name="from_date" type="text" data-daterange="true" class="datepicker form-control w-56 block mx-auto" value="{{ date('Y-m-d') }} - {{ date('Y-m-d') }}">
                                                    </div>
                                                </li>
                                                <li id="type-full">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" name="type_time" class="custom-control-input" id="time" value="time">
                                                        <label class="custom-control-label" for="time"> &nbsp;ชั่วโมง</label>
                                                    </div>
                                                    <div class="mt-3">
                                                        <div class="relative mb-3">
                                                            <div class="absolute rounded-l w-10 h-full flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400">
                                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                                            </div>
                                                            <input type="text" class="datepicker form-control pl-12" name="date_leave" id="update-profile-form-3" placeholder="วันที่ลา" data-single-mode="true">
                                                        </div>
                                                        <input name="from_time" type="time" value="08:30">
                                                        <input name="to_time" type="time" value="17:30">
                                                    </div>
                
                                                </li>
                
                                            </ul>
                
                
                                        </div>
                                    </div>
                
                                </div>
                
                                <div class="input-file-doc">
                                    <label for="exampleTextarea">เอกสาร</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="file" id="validatedCustomFile">
                                        <label class="custom-file-label" for="validatedCustomFile">Choose file...</label>
                                        <div class="invalid-feedback">Example invalid custom file feedback</div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-20 mt-6">Save</button>
                
                                {{-- <div class="row button">
                                    <div class="col-6 padding-rightca">
                                        <div class="cancel" data-dismiss="modal" a="" href="#">ยกเลิก</div>
                                    </div>
                                                                                                            </div> --}}
                            </form>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 mt-8">
                    <hr>
                </div>
                <!-- END: Weekly Top Products -->
            @if (in_array(Auth::user()->ref_position_id,[1,3]))
                
                <div class="col-span-12 mt-6">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5"><b>พนักงาน</b></h2>
                        <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                            @if (Auth::user()->ref_position_id == 1)
                            <a href="user/create" class="btn btn-primary shadow-md mr-2">เพิ่ม &nbsp; <i class="w-4 h-4" data-lucide="plus"></i></a>
                            @endif
                        {{-- ////////// --}}
                            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                                <div class="w-56 relative text-slate-500">
                                    <input name="search" type="text" class="form-control w-56 box pr-10 p_search" onkeyup="loadData('user/datatable')" placeholder="Search...">
                                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                                </div>
                            </div>
                            <div class="mt-3 ml-3 2xl:mt-0">
                                <select name="ref_branch_id" onchange="loadData('user/datatable')" data-search="true" class="tom-select w-full p_search">
                                        <option value="0" hidden> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; สาขา &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </option>
                                    @foreach ($branch as $bra)
                                        <option value="{{$bra->id}}">{{$bra->branch_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3 ml-3 2xl:mt-0">
                                <select name="ref_position_id" onchange="loadData('user/datatable')" data-search="true" class="tom-select w-full p_search">
                                        <option value="0" hidden> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ตำแหน่ง &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </option>
                                    @foreach ($position as $pos)
                                        <option value="{{$pos->id}}">{{$pos->position_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        
                    <div id="table-data" class="intro-y col-span-12 overflow-auto lg:overflow-visible">
                    </div>
                </div>
                <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <div class="p-5 text-center">
                                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                                    <div class="text-3xl mt-5">ต้องการลบ ?</div>
                                    <div class="text-slate-500 mt-2">พนักงานจะถูกเปลี่ยนสถานะเป็น ลาออก</div>
                                </div>
                                <div class="px-5 pb-8 text-center">
                                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                                    <button type="button" data-tw-dismiss="modal" onclick="confirmDelete()" class="btn btn-danger w-24">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                @endif
            <!-- END: Weekly Top Products -->
            </div>
        </div>
    </div>
        @if (in_array(Auth::user()->ref_position_id,[1,3]))
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <!-- BEGIN: Important Notes -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">พนักงาน</h2>
                        </div>
                        <div class="mt-5">
                           @foreach ($user_list_data as $row)
                                <div class="intro-x">
                                    <div class="box px-5 py-3 mb-3 flex items-center zoom-in">
                                        <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                            <img alt="Midone - HTML Admin Template" @if(!empty($row->image_name)) src="{{ asset('upload/user/' . $row->image_name) }}" @else src="{{asset('dist/images/user-index.png')}}" @endif>
                                        </div>
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium">{{ $row['name'] }}</div>
                                            <div class="text-slate-500 text-xs mt-0.5">{{ date('F พ.ศ. Y',strtotime($row['work_start_date'])) }}</div>
                                        </div>
                                        {{-- <div class="{{ $row['true_false'][0] ? 'text-success' : 'text-danger' }}">{{ $row['true_false'][0] ? '+' : '-' }}${{ $row['totals'][0] }}</div> --}}
                                    </div>
                                </div>
                            @endforeach
                            <a href="{{url('user-page')}}" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">ดูทั้งหมด</a>
                        </div>
                    </div>
                    <!-- END: Important Notes -->
                    <!-- BEGIN: Recent Activities -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">กะการทำงาน</h2>
                            <a href="{{url('work-shift-page')}}" class="ml-auto text-primary truncate">จัดการ</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            @foreach ($work_shift as $w_s)
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                    {{ $w_s->work_shift_name }}
                                </div>
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $w_s->work_shift_name }}</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">{{ date('H:i น.',strtotime($w_s->from_time)) }} {{ date('H:i น.',strtotime($w_s->to_time)) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- END: Recent Activities -->
                    <!-- BEGIN: Transactions -->
                    <!-- END: Schedules -->
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
@section('script')
        {{-- /////////////////////////// โปรไฟล์ //////////////////////////////// --}}
        <script>
                workkingCalcu();
                oldCalcu();
                function workkingCalcu(){
                    let Startday = "{{ $user->work_start_date }}";
                    document.getElementById("workkingCalcuShow").innerText = ageCalcu(Startday);
                }
                function oldCalcu(){
                    let Birthday = "{{ $user->birthday }}";
                    document.getElementById("oldCalcuShow").innerText = ageCalcu(Birthday);
                }
                function ageCalcu(date){
                    // alert(date);
                    var birthDate = new Date(date);

                    // ใช้ฟังก์ชั่นใน Date Object ในการเอาข้อมูลมาสร้าง string ใหม่ตาม format ที่ต้องการ
                    var birthDateFormat = birthDate.getFullYear() + '-' + ('0' + (birthDate.getMonth() + 1)).slice(-2) + '-' + ('0' + birthDate.getDate()).slice(-2);
                            // return alert(v);
                    var currentDate = new Date();
                    // วันเกิด
                    var dob = new Date(birthDateFormat);
                    
                    // หาความแตกต่างระหว่างวันที่ปัจจุบันกับวันเกิด
                    var diff = currentDate.getTime() - dob.getTime();
                    
                    // หาปี
                    var years = Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
                    diff -= years * (1000 * 60 * 60 * 24 * 365.25);
                    
                    // หาเดือน
                    var months = Math.floor(diff / (1000 * 60 * 60 * 24 * 30.44));
                    diff -= months * (1000 * 60 * 60 * 24 * 30.44);
                    
                    // หาวัน
                    var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    
                    // แสดงผลลัพธ์
                    let result = '';
                    if(years > 0){
                        result += years + " ปี ";
                    }
                    if(months > 0){
                        result += months + " เดือน ";
                    }
                    if(days > 0){
                        result += days + " วัน ";
                    }
                    return result;
                }
        </script>
        {{-- /////////////////////////////////////////////////////////// --}}
        <script>
            function loadDataLeave(v){
                window.location.replace("dashboard-page?ref_leave_id="+v);
            }
        </script>
        {{-- /////////////////////////////////////////////////////////// --}}
        <script>
             function la(la,la_id){
                $("#la").val(la);
                $("#la_id").val(la_id);
                let leave_not_time = [3,4,5,6];
                if (leave_not_time.includes(la_id)) {
                    $("#type-full").css('display','none');
                } else {
                    $("#type-full").css('display','block');
                }
            }
            $(document).ready(function(){
                $('#edit_news').submit(function(event){

                    event.preventDefault();

                    document.getElementById('cover-content').value = coverEditor.root.innerHTML;

                    $(".id_loading_icon").show();
                    var formData = $(this).serialize();
                    $(this).css("pointer-events", "none");
                    $(this).css("opacity", "0.4");

                    // AJAX request
                    $.ajax({
                        type: 'POST',
                        url: '{{url("edit_news")}}', // URL to submit form data
                        data: formData,
                        success: function(response){
                            var textEdit = $("#viewEdit").html();
                            setTimeout(() => {
                                changeTapNews(2)
                                $('#view_news').html(response);

                                // $('#view_news').css('display','block');
                                // $('#edit_news').css('display','none');

                                $('#edit_news').css("pointer-events", "unset");
                                $('#edit_news').css("opacity", "unset");
                                if(ButtonViewNewsAll == 1)
                                {
                                    $('#ViewNewsAll').show();
                                }else{
                                    $('#ViewNewsAll').hide();
                                }
                                
                                // $("#changeTapNews").html(textEdit);
                                $(".id_loading_icon").hide();
                            }, 1000);
                        }
                    });
                });
            });
            function import_excel_user() {
                var formData = new FormData();
                var file = $('#file_excel')[0].files[0];
                formData.append('file', file);
                formData.append('_token', "{{ csrf_token() }}");

                $.ajax({
                    url: 'user/import_excel_user', // URL ที่จะส่งไป
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response == true){
                            const el = document.querySelector("#basic-modal-upload-excel");
                            const modal = tailwind.Modal.getOrCreateInstance(el);
                            modal.hide();
                        }
                    }
                });
            }
            var loading = $("#loading").html();
            $("#table-data").html(loading);
            
            var page = "user/datatable";
            var searchData = {};
            
            loadData(page);
            // function search(){
            //     loadData(page, searchData);
            // }
            
            function loadData(pages,){
                
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
                    }
                });
                // alert(page);
            }
            let DeleteId = '';
            function Delete(id){
                DeleteId = id;
                // console.log(DeleteId);
            }
            function confirmDelete(){
                $.ajax({
                    type: "DELETE",
                    url: "user/"+DeleteId,
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
    <script src="{{ mix('dist/js/ckeditor-classic.js') }}"></script>
        
{{-- backend\libs\jquery --}}
@endsection
