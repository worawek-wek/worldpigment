<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use App\Models\RoomForRents;
use App\Models\IncomeExpenses;
use App\Models\RentBill;
use App\Models\Receipt;
use App\Models\Renter;
use App\Models\Contract;
use App\Models\PaymentList;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function get_summary_menu(){

        if(!@$branch_id){
            $branch_id = session("branch_id");
        }
        $overdue_bill = RentBill::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('ref_type_id', 1)
                                        ->whereNotIn('ref_status_id', [3, 5])
                                        ->get()->count();

        $booking_room = Room::whereHas('floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('status', 1)
                                        ->get()->count();

        $data['overdue_bill'] = $overdue_bill;
        $data['booking_room'] = $booking_room;

        return $data;
    }
    public function summary($branch_id, $month = null, $year = null)
    {
        if(!$branch_id){
            $branch_id = session("branch_id");
        }
        $lastMonth = Carbon::now();
        // $lastMonth = Carbon::now()->subMonth();

// ------------------------------------------------------------------------------------- //
        // ยอดชำระเงินทั้งหมด
        $all_receipt = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->get()
                                        ->sum(function ($receipt) {
                                            return $receipt->total_amount; // <-- ใช้ accessor ได้ที่นี่
                                        });

// ------------------------------------------------------------------------------------- //
        // ยอดชำระเงิน ค่าเช่าห้อง ทั้งหมด
        $all_rent_bill_receipt = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('ref_type_id', 1)
                                        ->get()
                                        ->sum(function ($receipt) {
                                            return $receipt->total_amount; // <-- ใช้ accessor ได้ที่นี่
                                        });

// ------------------------------------------------------------------------------------- //
        // ยอดชำระเงิน ค่าปรับ ทั้งหมด
        $all_receipt_fine = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->get()
                                        ->sum(function ($receipt) {
                                            return $receipt->total_fine_amount; // <-- ใช้ accessor ได้ที่นี่
                                        });

// ------------------------------------------------------------------------------------- //
        // ยอดชำระเงิน ทั้งหมด ไม่รวม ค่าปรับ
        $all_receipt_not_fine = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->get()
                                        ->sum(function ($receipt) {
                                            return $receipt->total_not_fine_amount; // <-- ใช้ accessor ได้ที่นี่
                                        });
        
// ------------------------------------------------------------------------------------- //
        // จ่ายตรงเวลา
        $all_receipt_on_time = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        });
        if($month){
            $all_receipt_on_time = $all_receipt_on_time->whereHas('invoice', function ($q) use ($year, $month) {
                                                            $q->where('year', $year)
                                                                ->where('month', $month);
                                                        });
        }
        $all_receipt_on_time = $all_receipt_on_time->where('ref_type_id', 1)
                                                    ->where('payment_on_time', 1)
                                                    // ->where('ref_status_id', 5)
                                                    ->get() // ดึงข้อมูลออกมาเป็น Collection
                                                    ->sum(function ($receipt) {
                                                        return $receipt->total_amount; // ใช้ accessor ที่คุณเขียนไว้
                                                    });

// ------------------------------------------------------------------------------------- //
        // จ่ายล่าช้าแบบนัด
        $all_receipt_late_with_appointment = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                                            $query->where('ref_branch_id', $branch_id);
                                                        });
        if($month){
            $all_receipt_late_with_appointment = $all_receipt_late_with_appointment->whereHas('invoice', function ($q) use ($year, $month) {
                                                            $q->where('year', $year)
                                                                ->where('month', $month);
                                                        });
        }
        $all_receipt_late_with_appointment = $all_receipt_late_with_appointment->where('ref_type_id', 1)
                                                    ->where('payment_on_time', 2)
                                                    // ->where('ref_status_id', 5)
                                                    ->get() // ดึงข้อมูลออกมาเป็น Collection
                                                    ->sum(function ($receipt) {
                                                        return $receipt->total_amount; // ใช้ accessor ที่คุณเขียนไว้
                                                    });
// ------------------------------------------------------------------------------------- //
        // จ่ายล่าช้าแบบไม่ได้นัดเวลา
        $all_receipt_late = Receipt::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        });
        if($month){
            $all_receipt_late = $all_receipt_late->whereHas('invoice', function ($q) use ($year, $month) {
                                                    $q->where('year', $year)
                                                        ->where('month', $month);
                                                });
        }
        $all_receipt_late = $all_receipt_late->where('ref_type_id', 1)
                                            // ->where('ref_status_id', 5)
                                            ->where('payment_on_time', 3)
                                            ->get() // ดึงข้อมูลออกมาเป็น Collection
                                            ->sum(function ($receipt) {
                                                return $receipt->total_amount; // ใช้ accessor ที่คุณเขียนไว้
                                            });

// ------------------------------------------------------------------------------------- //
        // ยอดเรียกเก็บเงิน ทั้งหมด
        $all_invoice = RentBill::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->whereIn('ref_status_id', [2,4,5,7])
                                        ->get()
                                        ->sum(function ($invoice) {
                                            return $invoice->total_amount; // <-- ใช้ accessor ได้ที่นี่
                                        });

// ------------------------------------------------------------------------------------- //

        $total_confirm_by_employee = Receipt::with('payment_list')
                                        ->where('ref_status_id', 5)
                                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->get()
                                        // ->filter(function ($bill) {
                                        //     return $bill->total_amount;
                                        // })
                                        ->sum('total_amount');

// ------------------------------------------------------------------------------------- //
        $all_receipt_last_month = Receipt::with('payment_list')
                                        ->whereHas('invoice', function ($q) use ($lastMonth) {
                                            $q->where('year', $lastMonth->year)
                                                ->where('month', $lastMonth->month);
                                        })
                                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('ref_type_id', 1)
                                        // ->where('ref_status_id', 5)
                                        ->get()
                                        ->sum(function ($receipt) {
                                            return $receipt->total_amount; // <-- ใช้ accessor ได้ที่นี่
                                        });

// ------------------------------------------------------------------------------------- //
        $confirm_by_ceo = Receipt::with('payment_list')->where('ref_status_id', 5)->whereIn('ref_type_id', [1,2,3])
                                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->get()
                                        ->sum('total_amount');
// ------------------------------------------------------------------------------------- //
        $confirm_by_ceo_this_month = RentBill::with('payment_list')->where('month', explode('-', date('m-Y'))[0])           //
                                                ->where('year', explode('-', date('m-Y'))[1])->where('ref_status_id', 5)      //
                                                ->get()->sum('total_amount');
// ------------------------------------------------------------------------------------- //
        $all_rent_bill_last_month = RentBill::with('payment_list')->where('month', explode('-', date('m-Y'))[0])            //
                                                ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                                    $query->where('ref_branch_id', $branch_id);
                                                })
                                                ->whereIn('ref_status_id', [2,4,5,7])
                                                ->where('year', explode('-', date('m-Y'))[1])       //
                                                ->where('ref_type_id', 1)->get()->sum('total_amount');

// ------------------------------------------------------------------------------------- // เงินสดคอนเฟิร์มแล้ว
        $cash = Receipt::with('payment_list')
                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                            $query->where('ref_branch_id', $branch_id);
                        })
                        ->where('ref_type_id', 1)
                        ->where('payment_channel', 1)
                        ->whereHas('invoice', function ($query) use ($month, $year) {
                                $query->where('ref_status_id', 5);
                            });
        if($month){
            $cash = $cash->whereHas('invoice', function ($query) use ($month, $year) {
                                $query->where('month', $month)
                                    ->where('year', $year);
                            });
        }
        $cash = $cash->get()
                        ->sum(function ($receipt) {
                            return $receipt->total_amount; // <-- ใช้ accessor ได้ที่นี่
                        });
// ------------------------------------------------------------------------------------- // เงินสดรอคอนเฟิร์ม
        $cash_wait_for_confirm = Receipt::with('payment_list')
                                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('payment_channel', 1)
                                        ->where('ref_status_id', 5);
        if($month){
            // $cash_wait_for_confirm = $cash_wait_for_confirm->where('month', $month)
            //                                                 ->where('year', $year);
        }
        $cash_wait_for_confirm = $cash_wait_for_confirm->get()
                                                        ->filter(function ($bill) {
                                                            return $bill->total_amount;
                                                        })
                                                        ->sum('total_amount');
// ------------------------------------------------------------------------------------- // เงินสดรอคอนเฟิร์ม
        $transfer_wait_for_confirm = Receipt::with('payment_list')
                                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('payment_channel', 2)
                                        ->where('ref_status_id', 5);
        if($month){
            // $transfer_wait_for_confirm = $transfer_wait_for_confirm->where('month', $month)
            //                                                 ->where('year', $year);
        }
        $transfer_wait_for_confirm = $transfer_wait_for_confirm->get()
                                                        ->filter(function ($bill) {
                                                            return $bill->total_amount;
                                                        })
                                                        ->sum('total_amount');
        // dd($cash_wait_for_confirm);
// -------------------------------------------------------------------------------------
        $transfer = RentBill::with('payment_list')
                                        ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                            $query->where('ref_branch_id', $branch_id);
                                        })
                                        ->where('ref_type_id', 1)
                                        // ->where('payment_channel', 1)
                                        ->where('ref_status_id', 5);
        if($month){
            $transfer = $transfer->where('month', $month)
                                    ->where('year', $year);
        }
        $transfer = $transfer->get()
                            ->filter(function ($bill) {
                                return $bill->total_not_discount_amount >= $bill->total_amount;
                            })
                            ->sum('total_paid_transfer');


        $all_renter = Renter::join('room_for_rents', 'renters.id', '=', 'room_for_rents.ref_renter_id')
                            ->join('rooms', 'room_for_rents.ref_room_id', '=', 'rooms.id')
                            ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                            ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                            ->distinct('renters.id')
                            ->where('buildings.ref_branch_id', $branch_id)
                            ->count();

        $all_renter_for_room = Contract::join('rooms', 'contracts.ref_room_id', '=', 'rooms.id')
                            ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                            ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                            ->distinct('rooms.id')
                            ->where('buildings.ref_branch_id', $branch_id)
                            ->count();

        // $all_booking_room = RoomForRents::join('rooms', 'room_for_rents.ref_room_id', '=', 'rooms.id')
        //                                 ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
        //                                 ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
        //                                 ->where('rooms.status', 1)
        //                                 ->where('buildings.ref_branch_id', $branch_id)
        //                                 ->distinct('rooms.id')
        //                                 ->count();
        
        $all_booking_rented = Room::whereHas('floor.building', function ($query) use ($branch_id) {
                                        $query->where('ref_branch_id', $branch_id);
                                    })
                                    ->where('rooms.status', 2)->count();

                                    
        $all_booking_room = Room::whereHas('floor.building', function ($query) use ($branch_id) {
                                        $query->where('ref_branch_id', $branch_id);
                                    })
                                    ->where('rooms.status', 1)->count();


        $all_room = Room::join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                            ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                            ->where('buildings.ref_branch_id', $branch_id)->count();

        $vacant_room = Room::whereHas('floor.building', function ($query) use ($branch_id) {
                                $query->where('ref_branch_id', $branch_id);
                            })
                            ->where('status', 0)->count();
// บิลค่าเช่าทั้งหมด
        $all_overdue = RentBill::join('room_for_rents', 'rent_bills.ref_room_for_rent_id', '=', 'room_for_rents.id')
                                ->join('rooms', 'room_for_rents.ref_room_id', '=', 'rooms.id')
                                ->join('floors', 'rooms.ref_floor_id', '=', 'floors.id')
                                ->join('buildings', 'floors.ref_building_id', '=', 'buildings.id')
                                ->where('buildings.ref_branch_id', $branch_id)
                                ->whereIn('rent_bills.ref_status_id', [2, 5, 7])
                                ->distinct()
                                ->count('rooms.id'); // ✅ ใช้ count กับคอลัมน์ตรง ๆ
// ยอดบิลค่าเช่าห้องทั้งหมด
        $all_rent_bill_invoice = RentBill::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                                $query->where('ref_branch_id', $branch_id);
                                            })
                                            ->whereIn('ref_status_id', [2, 4, 5, 7])
                                            ->where('ref_type_id', 1)
                                            ->get()
                                            ->sum(function ($invoice) {
                                                return $invoice->total_amount; // <-- ใช้ accessor ได้ที่นี่
                                            });

// บิลค้างชำระทั้งหมด               
        $overdueData = RentBill::with('receipt')
                                ->whereHas('room.floor.building', function ($query) use ($branch_id) {
                                    $query->where('ref_branch_id', $branch_id);
                                })
                                ->whereIn('ref_status_id', [2, 4, 5, 7])
                                ->where('ref_type_id', 1)
                                ->get()
                                ->filter(function ($bill) {

                                    // total ของ RentBill
                                    $billTotal = $bill->total_amount;

                                    // total ของ Receipt (hasMany)
                                    $receiptTotal = $bill->receipt?->sum(fn ($r) => $r->total_amount) ?? 0;

                                    return $billTotal > $receiptTotal;
                                });
                                
        
// ยอดบิลค่าเช่าห้องทั้งหมด เดือน ล่าสุด
        $all_rent_bill_invoice_last_month = RentBill::whereHas('room.floor.building', function ($query) use ($branch_id) {
                                                $query->where('ref_branch_id', $branch_id);
                                            })
                                            ->where('year', $lastMonth->year)
                                            ->where('month', $lastMonth->month)
                                            ->whereIn('ref_status_id', [2, 4, 5, 7])
                                            ->where('ref_type_id', 1)
                                            ->get()
                                            ->sum(function ($invoice) {
                                                return $invoice->total_amount; // <-- ใช้ accessor ได้ที่นี่
                                            });
                                            
        $overdueData_1 = $all_rent_bill_invoice_last_month-$all_receipt_last_month;

        // นับจำนวนห้องไม่ซ้ำ
        $overdueRoomCount = $overdueData->pluck('ref_room_id')->unique()->count();

        // รวมยอดค้างชำระทั้งหมด
        $overdueTotalAmount = $all_rent_bill_invoice-$all_rent_bill_receipt;
// บิลค่าเช่าค้างชำระทั้งหมด               

        $data['percent'] = 0; // อัตราเข้าพัก
        if ($all_room > 0) {
            $data['percent'] = number_format((100/$all_room)*$all_booking_rented, 2); // อัตราเข้าพัก
        }
        
        $data['all_receipt'] = number_format($all_receipt); // ยอดชำระเงินทั้งหมด
        $data['all_receipt_fine'] = number_format($all_receipt_fine); // ยอดชำระเงิน ค่าปรับ ทั้งหมด
        $data['all_receipt_not_fine'] = number_format($all_receipt_not_fine); // ยอดชำระเงิน ทั้งหมด ไม่รวม ค่าปรับ
        $data['all_invoice'] = number_format($all_invoice); // ยอดเรียกเก็บเงินทั้งหมด
        $data['outstanding_balance'] = number_format($all_invoice - $all_receipt_not_fine); // ยอดค้างชำระทั้งหมด
        $data['all_receipt_last_month'] = $all_receipt_last_month;
        $data['confirm_by_employee'] = number_format($total_confirm_by_employee).' บาท'; // ชำระเงินโดยพนักงาน
        $data['confirm_by_ceo'] = number_format($confirm_by_ceo).' บาท'; // ชำระเงินโดยผู้บริหาร
        $data['confirm_by_ceo_this_month'] = number_format($confirm_by_ceo_this_month); // ชำระเงินโดยผู้บริหาร
        $data['all_rent_bill_last_month'] = $all_rent_bill_last_month; // ชำระเงินโดยผู้บริหาร
        $data['overdue_this_month'] = $overdueData_1; // ชำระเงินโดยผู้บริหาร
        $data['confirm_by_employee_confirm_by_ceo'] = number_format($total_confirm_by_employee + $confirm_by_ceo).' บาท'; // ชำระเงินหลังคอนเฟิร์ม
        $data['transfer'] = $transfer; // เงินโอน
        $data['transfer_wait_for_confirm'] = $transfer_wait_for_confirm; // เงินโอน
        $data['cash'] = $cash; // เงินสด
        $data['cash_wait_for_confirm'] = $cash_wait_for_confirm; // เงินสด

        $data['all_renter_for_room'] = $all_renter_for_room; // จำนวนห้องไม่ว่าง
        $data['all_renter'] = $all_renter; // ผู้เช่า
        $data['all_booking_room'] = $all_booking_room; // ห้องจอง
        $data['all_overdue'] = $all_overdue; // ห้องค้างชำระ
        $data['overdueRoomCount'] = $overdueRoomCount; // จำนวนห้องค้างชำระ
        $data['overdueTotalAmount'] = $overdueTotalAmount; // รวมยอดค้างชำระทั้งหมด
        $data['vacant_room'] = $vacant_room; // ห้องว่าง
        $data['all_receipt_on_time'] = $all_receipt_on_time; // ห้องว่าง
        $data['all_receipt_late_with_appointment'] = $all_receipt_late_with_appointment; // ห้องว่าง
        $data['all_receipt_late'] = $all_receipt_late; // ห้องว่าง
        return $data;
    }
    
    public function summary_calculate()
    {
        $income = IncomeExpenses::with('receipt_payment_list')->where('ref_branch_id', session("branch_id"))->where('type', 1)->get()
                                    ->sum(function ($item) {
                                        return $item->getTotalFromPaymentList();
                                    });
        $income_2 = PaymentList::whereHas('receipt.room.floor.building', function ($query) {
                                    $query->where('ref_branch_id', session("branch_id"));
                                })
                                // ->whereHas('receipt', function ($query) {
                                //     $query->where('ref_type_id', 2);
                                // })
                                // ->where('discount', 0)->sum('price')-PaymentList::whereHas('receipt.room.floor.building', function ($query) {
                                //     $query->where('ref_branch_id', session("branch_id"));
                                // })
                                // ->whereHas('receipt', function ($query) {
                                //     $query->where('ref_type_id', 2);
                                // })
                                ->where('discount', 0)->sum('price');
                                
        $income_3 = PaymentList::whereHas('receipt.room.floor.building', function ($query) {
                                    $query->where('ref_branch_id', session("branch_id"));
                                })
                                ->whereHas('receipt', function ($query) {
                                    $query->where('ref_type_id', 2);
                                })
                                ->where('discount', 1)->sum('price');
        
        $expenses = IncomeExpenses::where('type', 2)->where('ref_branch_id', session("branch_id"))->sum('amount');

        $data['income'] = $income;
        $data['income_3'] = $income_3;
        $data['expenses'] = $expenses;
        $data['total'] = $income-$expenses;
        return $data;
    }
}
