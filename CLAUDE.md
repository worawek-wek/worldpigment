# CLAUDE.md

ไฟล์นี้เป็นคู่มือสำหรับ Claude Code (claude.ai/code) ในการทำงานกับ repository นี้

<!-- อัพเดทล่าสุด: 06/08/2569 — เพิ่มการเก็บประวัติ/วันที่เปลี่ยน senddate, หน้าใบขอเปลี่ยนแปลงคำสั่งซื้อ, และลบ Temp ออกจากสถานะวิธีการผลิต -->

## ภาพรวมโปรเจกต์

**World Pigment** (`worldpigment`) — ระบบบริหารจัดการสำหรับโรงงานผลิตสีและพิกเมนต์ ครอบคลุมตั้งแต่การเทียบสี (color matching) การวางแผนการผลิต (production planning) การออกใบเสนอราคา (quotation) คำสั่งซื้อ (order) ข้อมูลลูกค้า (customer) อุปกรณ์ (equipment) ไปจนถึงระบบรายงาน (report) ครบวงจร

- ข้อความบน UI, comment ในโค้ด และคำศัพท์เฉพาะทางธุรกิจ ใช้**ภาษาไทย**
- Backend: Laravel 9 / PHP 8
- Frontend: Blade + Tailwind + Alpine.js / jQuery บน admin template ของ Left4code ชื่อ "Enigma"
- repository อยู่ภายใต้ XAMPP (`C:\xampp\htdocs\worldpigment`) และใช้ MySQL ของ XAMPP เป็น local database

## Tech Stack

| ด้าน | เทคโนโลยี |
|------|-----------|
| Backend | PHP 8.0+, Laravel 9.x |
| Frontend | Blade, Tailwind CSS 3, Alpine.js, jQuery |
| Database | MySQL |
| PDF | DomPDF, mPDF |
| Excel | PhpSpreadsheet |
| Tables | Yajra DataTables, Tabulator |
| Charts | Chart.js |
| Calendar | FullCalendar |
| Editor | CKEditor 5 |
| Auth | Laravel Sanctum |
| Build | Laravel Mix, PostCSS |

## โครงสร้างโปรเจกต์

```
worldpigment/
├── app/
│   ├── Http/Controllers/
│   │   ├── Production/          # วางแผนการผลิต
│   │   ├── ColorMatchingController.php
│   │   ├── OrderController.php      # เมนู O-Order — ฟอร์มบันทึกใบสั่งซื้อ morder/suborder (12/08/2569)
│   │   ├── PriceApprovalController.php  # ฟอร์มลูกของ O-Order — ขออนุมัติราคาพิเศษ appvreq (12/08/2569)
│   │   ├── OrderApprovalController.php  # ฟอร์มลูกของ O-Order — อนุมัติราคาใบสั่งซื้อ morder.appv (12/08/2569)
│   │   ├── QuotationController.php
│   │   ├── CustomerController.php
│   │   ├── ReportController.php
│   │   ├── UserController.php
│   │   ├── PermissionController.php
│   │   ├── PDFController.php
│   │   ├── SaleinfoController.php   # กำหนดราคา + ค้นหาราคาสินค้า (04/08/2569)
│   │   ├── ProductController.php    # ข้อมูลสินค้า tb_products — CRUD (07/08/2569)
│   │   └── ExportExcelController.php
│   ├── Models/
│   └── Services/                # AccessService, ProductPriceService (04/08/2569)
├── config/
│   ├── menu.php                 # โครงสร้างเมนู
│   ├── product_price.php        # ตารางเงื่อนไขคิดราคาขายจากราคาทุน (04/08/2569)
│   └── color_matching.php       # ตัวเลือกผลการทดสอบตัวอย่างสี (testmain.TyResp) (10/08/2569)
│                                # — mul/div/add เป็นแค่ "ค่าตั้งต้น" ผู้ใช้แก้ทับได้ (10/08/2569)
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/                   # Blade templates
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── production.php
│   ├── color-matching.php
│   ├── quotation.php
│   ├── order.php                # ใบสั่งซื้อ morder/suborder (12/08/2569)
│   ├── customer.php
│   ├── report.php
│   ├── permission.php
│   └── product.php              # ข้อมูลสินค้า tb_products (07/08/2569)
└── public/
```

## กฎการเขียนโค้ด (Coding Rules)

- หลีกเลี่ยง `any` ถ้าไม่จำเป็น
- เขียนโค้ดให้อ่านง่าย ดีกว่าเขียนสั้นเกินไป
- ห้ามลบโค้ดเดิม ถ้ายังไม่เข้าใจหน้าที่ของมัน
- อย่าแก้หลายเรื่องพร้อมกันเกินไป
- ถ้าไม่แน่ใจ ให้ถามก่อน
- ถ้ามี error ให้ใช้ error message เป็น feedback แล้วแก้ซ้ำจนผ่าน
- หลังจบงาน ให้สรุปว่าแก้ไขไฟล์อะไรไปบ้าง
- ถ้ามีความเสี่ยงด้าน security ให้แจ้งด้วย

## ขั้นตอนการทำงาน (Workflow)

ก่อนแก้ไขโค้ด ให้ทำตามลำดับนี้:

1. อ่านโครงสร้างโปรเจกต์ก่อน
2. อ่านไฟล์ที่เกี่ยวข้องก่อน
3. สรุปสิ่งที่เข้าใจ
4. อธิบายแผนการแก้แบบสั้น ๆ
5. วางแผนการทำงานเป็น task ย่อย
6. ลงมือแก้ทีละ task
7. แก้ไขเฉพาะไฟล์ที่จำเป็น
8. ตรวจสอบว่าโค้ดไม่กระทบส่วนอื่น
9. สรุปสิ่งที่แก้หลังทำเสร็จ และบอกวิธีตรวจสอบ
10. ถ้ามีการแก้ไขโครงสร้างหรือสร้างของใหม่ ให้อัพเดทโครงสร้างในไฟล์นี้ พร้อมใส่ comment ระบุวันที่อัพเดท

## คำสั่งที่ใช้บ่อย (Commands)

```bash
composer install          # ติดตั้ง PHP dependencies
npm install               # ติดตั้ง JS dependencies
php artisan serve         # dev server -> http://localhost:8000

# Frontend build (มี 2 ขั้นตอน — Mix ไม่ได้ compile resources/css/app.css ให้)
npm run dev               # build ครั้งเดียวด้วย Mix
npm run watch             # รัน postcss (app.css) + mix watch พร้อมกันด้วย concurrently
npm run prod              # build สำหรับ production (postcss app.css + mix --production)

# Tests
vendor/bin/phpunit                                  # รันทั้งหมด
vendor/bin/phpunit --testsuite Unit                 # รันเฉพาะ suite (Unit | Feature)
vendor/bin/phpunit --filter testMethodName          # รันเฉพาะ test เดียว

php artisan migrate --seed
```

- มี route `GET /clc` สำหรับ clear + rebuild cache ทั้งหมด (cache/config/view/route) — ใช้หลังแก้ไฟล์ใน `config/` หรือ routes แล้วการเปลี่ยนแปลงยังไม่แสดงผล
- Lint/style ควบคุมโดย **StyleCI** (`.styleci.yml`, Laravel preset, PHP 8, ปิด `no_unused_imports`) — ไม่มีคำสั่ง lint ในเครื่อง

## สถาปัตยกรรม Routing

`routes/web.php` เป็นจุดเริ่มต้น แต่ route ส่วนใหญ่แยกอยู่ใน**ไฟล์ตาม feature** ในโฟลเดอร์ `routes/` และถูกดึงเข้ามาด้วย `@include_once(...)` จาก*ภายใน* group `Route::middleware('auth')` ใน `web.php`:

- `color-matching.php`, `quotation.php`, `order.php`, `customer.php`, `report.php`, `permission.php`, `production.php`

ดังนั้น route ในไฟล์เหล่านี้จึงถูกป้องกันด้วย `auth` โดยปริยายจากตำแหน่งที่มัน include เข้ามา — **ห้าม** ครอบ `auth` ซ้ำอีก

เมื่อเพิ่ม module ใหม่ ให้สร้าง `routes/<module>.php` แล้ว `@include_once` ใน group เดียวกัน

`production.php` ถูก include เป็นไฟล์สุดท้ายใน `web.php` และ group ทุกอย่างไว้ใต้ URL prefix `production-planning` พร้อม route name ขึ้นต้นด้วย `production.*`

`GET /` จะ redirect ผู้ใช้ที่ login แล้วไปที่ `production.planning.index` (หน้าแรกของระบบ) และ redirect guest ไปที่ `login.index`

Auth เป็นแบบ session-based; middleware `loggedin` (`app/Http/Middleware/LoggedIn.php`) จะเด้งผู้ใช้ที่ login แล้วออกจากหน้า login/register

## ระบบเมนูและสิทธิ์ (Menu & Permission)

- Navigation ที่แสดงผลถูกกำหนดแบบ declarative ใน **`config/menu.php`** เป็น nested array
- แต่ละ item มี `route_name` และ `permission` ในรูปแบบ `"<module> read"` (เช่น `color_matching read`, `productionplanning read`)
- เมนูถูก inject เข้าทุก view โดย `ViewServiceProvider` ผ่าน `View::composer('*', ...)` → `App\Http\View\Composers\MenuComposer` ซึ่งดึงข้อมูลจาก `App\Main\{SideMenu,TopMenu,SimpleMenu}` และคำนวณ index ของเมนูที่ active จากการ match ชื่อ route ปัจจุบัน
- การกำหนดสิทธิ์แบบหลายสาขา (multi-branch) อ้างอิงจาก `session("branch_id")` ดูได้ที่ `User::permission_group_has_user_branch()`, `user_has_branch()` และ `branch()`
- **หน้าพนักงานหน้างาน (Worker portal)** (`WorkerPlanningController`, `routes/worker.php` prefix `worker`, layout สลิม `layout/worker` ไม่มี sidebar/เมนู, 11/08/2569): ให้ emp ที่ role = **"Worker"** (`Emp::WORKER_ROLE_NAME` + `Emp::isWorker()`) อัพเดทสถานะการผลิต **เฉพาะงานของตัวเอง** (`tb_planning.empno` = ผู้ล็อกอิน). login ใช้หน้าเดิม (guard `emp`) → `AccessControl::homeUrl()` พา Worker ไป `worker.planning.index`. **route ไม่อยู่ใน `config/menu.php`** จึง pass-through `CheckAccess` → ต้องมี middleware **`worker`** (`WorkerOnly`, ขึ้นทะเบียนใน Kernel) กันบัญชีอื่น + ทุก query/update **กรอง+ตรวจ `empno` ที่ server เสมอ** (`ownJobOrFail`). ค้นหารวมช่องเดียว (`red_bill_code`/`itemno`/`machine_no` LIKE) + กรอง `inplan`. อัพเดทแตะเฉพาะ `planning_status` (เลือกได้ทุกสถานะของแผนกงานนั้น จาก `tb_planning_status` โดย map `company`→`dept_id`) + validate ว่าสถานะอยู่ในแผนก + บันทึกประวัติทุกครั้งลง **`tb_planning_status_log`** (`old/new_status`, `changed_by`=empno, `changed_at`). ปุ่ม "ดูรายละเอียด" เป็น modal อ่านอย่างเดียว

## Data Model ของ Production Planning (parent-child)

ส่วนนี้เป็นหัวใจของระบบ และควรทำความเข้าใจก่อนแก้ไขโค้ด:

- `PlanningHeader` (`tb_planning_header`) `hasMany` `Planning` (`tb_planning`)
- `Planning` มี `parent_planning_id` แบบ self-referential และมี `semi_headers()` / `pigment_headers()` ซึ่งเป็น `PlanningHeader` ลูกที่มี `plan_type` เป็น `semi` / `pigment` และถูกสร้างอัตโนมัติจาก planning item
- `sub_headers()` / `subHeadersRecursive()` / `planningsRecursive()` ใช้ไล่ tree นี้ได้ทุกระดับความลึก
- `SemiPigment` (`tb_semi_pigment`) เป็น approval workflow: `status` เป็น ENUM `request` → `approved` / `reject` (มี constants + label ภาษาไทยอยู่ใน model) เมื่อ approve แล้ว `convertplanning` จะสร้าง `result_planning` ขึ้นมา
- `SemiPigmentController` ดูแล CRUD ของ entry จาก modal ของ Planning item, หน้ารายการรออนุมัติ และหน้ารายการที่อนุมัติแล้ว
- **การปิดงาน end_job / end_order** (`ProductionPlanController`): `end_job` = ปิดงานราย item (`tb_planning`, gate: ถ้ามีคำขอ semi แผน semi ทุกใบต้อง `end_order='Y'` ก่อน — `itemSemiJobsDone`); `end_order` = ปิดออเดอร์ราย header (`tb_planning_header`, gate: `end_job` ทุกแถวในต้นไม้ recursive ต้อง `='Y'` — `allEndJobsDone`); `end_close` = ปิดจบงาน (ไม่มี gate, บังคับ `end_order=Y` lockstep + ต้องมี `end_close_remark`)
- **ปิดออเดอร์อัตโนมัติ (auto-close end_order)** (`ProductionPlanController::saveItem`, 15/08/2569): เมื่อบันทึก item ด้วย `end_job='Y'` แล้วทำให้ `allEndJobsDone($header)` เป็น true (ครอบคลุมทั้ง header ที่มี planning เดียว และเหลือ item สุดท้ายที่เพิ่งปิด) → ตั้ง `end_order='Y'` ให้เองในทรานแซกชันเดียวกัน. ใช้ predicate เดิม (`allEndJobsDone`) จึงคงเงื่อนไขเดิม; ทำเฉพาะขา "ปิด" (ไม่ auto-ปลด) และไม่แตะ `end_close`. response แนบ `end_order_auto_closed` (bool). **UI:** modal "แผนการผลิต" (`planning-form.blade.php`) มี badge "ปิดออเดอร์แล้ว" (สีเขียว) ที่หัว modal + ข้าง checkbox End Order แสดงเมื่อ `end_order='Y'` (auto ปรากฏหลัง `reloadPlanningHeaderContent` re-render); ฝั่ง JS (`planning/index.blade.php` handler `#btn_save_planning_item`) ถ้า `end_order_auto_closed=true` เด้ง Swal แบบ info ให้กดรับทราบ (แทน toast auto-dismiss ปกติ). **หมายเหตุ:** เมื่อ auto-close แล้ว header จะถูกล็อกแก้ไข/เพิ่ม planning ทันที (gate `end_order='Y'` ที่หัว `saveItem`) — ถ้าปิดผิดต้องกดปลด end_order เองก่อน
- **หน้ารายการวางแผน** (`production.planning.index`, blade `production-planning/planning/index.blade.php`) ป้อนข้อมูลด้วย `ProductionPlanController::datatable` → `dataQuery`. ช่องค้นหา (`#searchInput` → param `search`) ค้นแบบ LIKE ข้ามหลายฟิลด์: `machine_no`, `itemno`, `red_bill_code` (เลขที่ใบเบิก Red Bill, 06/08/2569), `orderno`, `planning_code`, `custno`, และพนักงาน (`empno` / ชื่อ-นามสกุลใน `emp`)
- **หน้าแผนการผลิต (Order Plan)** (`production.orderplan.index`, `OrderPlanController::dataQuery`, blade `order-plan/index.blade.php`): แสดง 1 แถวต่อ 1 Order (header ที่ `parent_planning_id` ว่าง). ตัวกรอง: ช่องค้นหาหลัก (`#searchInput` → `search`) ค้น orderno/custno/ชื่อลูกค้า(`morder.Custname`)/พนักงานในต้นไม้, **ช่องค้นหา "รหัส Sale" (`#searchSaleno` → param `saleno`, ค้น `tb_planning_header.saleno` LIKE, 15/08/2569)**, แผนก(`company`), สถานะปิด order(`end_order`, default `N`), ช่วงวันที่ Inplan/Custwant — ทุกช่อง redraw ตาราง + รวมอยู่ในปุ่มล้างตัวกรอง
- **วันที่กำหนดทบทวน (senddate) + ประวัติ** (`tb_planning`, 06/08/2569): เมื่อแก้ senddate ใน modal แก้ไข Planning item และ**มีค่าเดิมอยู่ก่อน** → เก็บค่าเดิมต่อท้าย `senddate_log` (คั่นด้วย comma) และบันทึกเวลาที่เปลี่ยน**ล่าสุด**ทับลง `senddate_changed_at` (DATETIME, มี index) — ถ้าตอนแรกว่างแล้วเพิ่งใส่ค่าจะไม่เก็บ (ดู `ProductionPlanController::saveItem`)
- **ยืนยันก่อนบันทึกเมื่อแก้ custwant** (modal แก้ไข Planning Item, 15/08/2569): ช่อง "วันที่ต้องการรับ (custwant)" เก็บค่าเดิมไว้ที่ `data-original` (Y-m-d) — ตอนกดบันทึก (`#btn_save_planning_item` ใน `planning/index.blade.php`) ถ้าค่าปัจจุบันต่างจาก data-original จะเด้ง Swal ยืนยัน (แสดง จาก→เป็น แบบ d/m/Y) ก่อน แล้วค่อยเรียก `submitPlanningItem()`; ถ้าไม่เปลี่ยนบันทึกได้เลย
- **สถานะวิธีการผลิต** (`tb_planning_prod_method`): ตารางลูกของ planning item (1 planning → หลายแถว) เก็บ `prod_method_id` (→ `tb_prod_method`), `work_date`, `start_time`, `end_time`, `sort` — บันทึกผ่าน `ProductionPlanController::syncProdMethods` (ลบทั้งหมดแล้ว insert ใหม่). **หมายเหตุ:** เดิมมีคอลัมน์ `temp_id` (→ ตาราง `temp`) แต่**ลบออกแล้ว** 06/08/2569 (ย้าย Temp ไปจัดการที่ส่วนอื่น) — ตาราง master `temp` + `TempController` + หน้า `temp.index` ยังคงอยู่
- **แปลง Order → แผนการผลิต** (`OrderController::convertplanning`, `/production-planning/order`, 10/08/2569): สร้าง `PlanningHeader` (`plan_type='ORDER'`) + `Planning` ต่อ 1 suborder — `tb_planning_header.remark` ดึงจาก `suborder.Remark` ของทุก suborder (ตัดค่าว่าง คั่นด้วย `, `, **เก็บทุกค่าแม้ซ้ำกัน**); ระวัง: ตาราง `morder` **ไม่มี** คอลัมน์ `Remark` (หมายเหตุอยู่ที่ `suborder.Remark` เท่านั้น). แต่ละ item ยังใส่ `red_bill_code = order.Orderno` และ `remark = suborder.Remark` เหมือนเดิม
- **Modal "แผนการผลิต"** (`production-planning/planning/planning-form.blade.php`): ตาราง "รายการ Planning" มีคอลัมน์ **เลขที่ใบเบิก** (`red_bill_code` ของแต่ละ planning) อยู่ก่อน Item No. (10/08/2569)
- **หน้าพนักงาน** (`EmpController`, `employee/index.blade.php`): มี dropdown กรองแผนก (`#searchDept` → param `dept`, กรอง `emp.dept` แบบ exact — `emp.dept` เก็บเป็นชื่อแผนก) คู่กับช่องค้นหาข้อความ (10/08/2569)
- **รายงานตามเครื่องจักร + จัดคิว drag & drop** (`Production\ReportController::machine/machineOptions/machineTable/machineQueueReorder/machineExcel/machinePdf`, route `production.report.machine.*`, blade `report/machine.blade.php` + partials `machine-table`/`machine-pdf`): จัดกลุ่มงาน (`tb_planning`) **ตามเครื่องจักร (`machine_no`)** → ภายในกลุ่มเรียงตาม **วัน (`day_key`)** → **คิว (`queue_sort`)** → เวลา (`job_key`) → id. ประกอบข้อมูลใน `buildMachineReport()` (ใช้ร่วมทั้งตาราง/Excel/PDF) join `tb_planning_header`+`customer`+`tb_products` (Resin/CODE/Pack/Batch/สูตร) + subquery `machine.speed_rpm` (คู่ `machine.MBX`=`machine_no`) + แนบขั้นตอน `tb_planning_prod_method` (steps). กรอง: แผนก(`dept`=company)/เครื่อง(`machine_no`)/ช่วงวันที่(`inplan`). **day_key / job_key อิง `tb_planning.inplan` (15/08/2569)**: `day_key` = วันของ `inplan`; `job_key` = วันของ inplan + เวลา `start_time` ของขั้นตอนแรก (ไม่มีเวลา → 00:00:00); งาน `inplan`=NULL → day_key/job_key = null ไปกลุ่มท้ายสุด **และลากจัดคิวไม่ได้**. **จัดคิว (drag & drop)**: SortableJS ลากทั้งงาน (`tbody.qjob`) ผ่าน handle `.qhandle`; `onMove` อนุญาตเฉพาะ **เครื่องเดียวกัน (`data-machine`) + วันเดียวกัน (`data-day`=`day_key`)** — กันข้ามเครื่อง/ข้ามวัน (การกันอยู่ **ฝั่ง client ล้วน**); `onEnd` ถ้าไม่ขยับจริง (oldIndex==newIndex) ไม่บันทึก, ไม่งั้นรวบรวม `planning_id` ในบล็อกเดียวกันตามลำดับบนจอ → POST `machineQueueReorder` เขียน `queue_sort=1..N` (เช็คแค่ ids เป็น array ไม่ว่าง + cast int; **ไม่** validate เครื่อง/วัน/สิทธิ์/มีจริง/transaction). บันทึกล้มเหลว → reload คืนลำดับจริง. เปลี่ยน `day_key` ที่ server ที่เดียว client ตามอัตโนมัติ (data-day = day_key)
- **รายงานผลิตตามพนักงาน (time-grid รายวัน)** (`Production\ReportController::employee/employeeTable/employeeExcel/employeePdf`, route `production.report.employee.*`, 11/08/2569): ฟอร์ม "แผนและการผลิตจริง" — **1 แถวกลุ่ม = 1 พนักงาน** (`tb_planning.empno` → `emp.empname/empsur`), **คอลัมน์ = ช่วงเวลา 9 ช่อง** นิยามคงที่ใน `timeSlots()` (8-9…16-17, OT; **ข้ามพักเที่ยง 12-13** จึงไม่มีคอลัมน์นั้น). แต่ละงาน (`tb_planning`) วางลงกริดจากเวลาใน `tb_planning_prod_method` (`start_time`/`end_time` เทียบ overlap ทีละช่อง) → แสดง รหัสสี(`itemno`)/รหัสเครื่อง(`machine_no`)/วิธีการผลิต(`tb_prod_method.name`) ในทุกช่องที่ครอบครอง, **จำนวน(`quantity`) โชว์เฉพาะช่องแรกสุด**. Fallback: ถ้า step ไม่มีเวลา ใช้ `tb_planning.start_time/end_time`; ถ้ายังไม่มี → แยกไปแถว "ไม่ระบุเวลา". งานที่ `empno` ว่าง → กลุ่ม "ไม่ระบุพนักงาน" ท้ายสุด. กรองด้วย แผนก(`dept`)+พนักงาน(`empno`, cascade จาก `employeeOptions`)+วันที่(`date`, เดี่ยว ค่าเริ่มต้นวันนี้). มี export PDF (mPDF, A4-L) + Excel คู่กัน — 2 แถวล่าง (ผู้ทวนสอบ/เวลา, ผู้ผลิต) เว้นว่างให้เซ็นมือตามฟอร์มกระดาษ
- **รายงานการขาดวัตถุดิบ** (`Production\ReportController::materialShortage/materialShortageTable/materialShortageExcel/materialShortagePdf`, route `production.report.material-shortage.*`, เมนูใต้ ProductionReport, 13/08/2569): ตารางแบนราบ (ไม่จัดกลุ่ม) ของงานใน `tb_planning` ที่ **ยังไม่ปิดงาน** (`end_job != 'Y'` **รวม NULL**) — คอลัมน์: เครื่องจักร/IN PLAN(`inplan`)/รหัส-ชื่อลูกค้า(`tb_planning_header.custno`+`customer.name`)/Order No(`tb_planning_header.orderno`)/รหัสสินค้า(`itemno`)/LOT/น้ำหนัก(`quantity`)/สถานะปัจจุบัน(`planning_status`). กรองแผนกด้วย `COALESCE(tb_planning.company, tb_planning_header.company)`, เรียง `inplan` เก่า→ใหม่ (ไม่มีวันที่ท้ายสุด). โหลดตารางผ่าน AJAX (partial `material-shortage-table`, ตารางบนเว็บ = ชุดคอลัมน์ย่อ). **Export Excel/PDF ใช้ผังคอลัมน์เต็มตามฟอร์ม Access เดิม (20 คอลัมน์)**: #, MACHINE No., IN PLAN, Revise(`senddate` กำหนดส่งทบทวน, 15/08/2569), สถานะปัจจุบัน(`planning_status`), Cust Due(`custwant` item→header), Cust no, Cust Name, SaleNo(`header.saleno`), Order Date(`header.mdate`), Order No, PRODUCT NO(`itemno`), LOT, น้ำหนัก(`quantity`, เดิมเคยแยก "น้ำ"/"หนัก" → รวมเป็นคอลัมน์เดียว 20/08/2569), ส่งชั่งสี*, เริ่มผลิต(`start_date`), วันที่ส่ง QC(`qc_date`), เวลาที่ส่ง QC(`qc_time`), สถานะ QC(`qc_status`) — *คอลัมน์ น้ำ/ส่งชั่งสี ยังไม่มีฟิลด์ใน DB จึงเว้นว่างไว้ตามผัง (Revise เดิมเว้นว่าง → ผูกกับ `tb_planning.senddate` แล้ว 15/08/2569 ทั้ง Excel คอลัมน์ F และ PDF); Excel=PhpSpreadsheet A..T, PDF=mPDF A4-L `material-shortage-pdf`. **เพิ่ม 1 คอลัมน์ "ขาด semi" ต่อจาก "สถานะปัจจุบัน" ทั้ง PDF และ Excel (15/08/2569)** — PDF พื้นเหลือง (`class="status"`) เหมือนสถานะปัจจุบัน, Excel = คอลัมน์ H (เลื่อน H..V เดิม → I..W, รวม 23 คอลัมน์). ดึงคำร้องขอ semi จาก `tb_semi_pigment` (`type='semi'`, `status` ∈ request/approved) **จับคู่ด้วย `planning_id` = `tb_planning.id`** (ไม่ใช่ itemno — เพราะ `tb_semi_pigment.itemno` = รหัสของตัว semi ที่ขาด ไม่ใช่ itemno ของงาน) + **เงื่อนไขปิดออเดอร์**: `LEFT JOIN tb_planning_header as ph` (ผ่าน `tb_semi_pigment.planning_header_id`) แล้วดึงเฉพาะ semi ที่ `ph.end_order != 'Y'` (รวม NULL) — semi ที่ปิดออเดอร์แล้วจะไม่ดึง. รวม 3 ฟิลด์จาก modal แก้ไข Semi (`itemno` ของ semi + `semi_code` + `primary_color`) ไว้ใน**คอลัมน์เดียว** คั่นด้วย ", " (ตัดค่าว่าง/ซ้ำ; งานที่ไม่มีคำร้องขอ semi = เว้นว่าง). assemble ใน `buildMaterialShortageReport()` → attach `lack_semi` ต่อแถว. **เพิ่ม 1 คอลัมน์ "ขาด Pigment" ต่อจาก "ขาด semi" ทั้ง PDF และ Excel (20/08/2569)** — PDF พื้นเหลือง (`class="status"`), Excel = คอลัมน์ I (รวม 23 คอลัมน์ A..W หลังยุบ "น้ำ"/"หนัก" เป็น "น้ำหนัก" 20/08/2569). ต่างจาก "ขาด semi" ตรงที่คำขอ pigment อยู่ตาราง **`tb_pigment`** (Model `Pigment`) แยกต่างหาก (ไม่ใช่ `tb_semi_pigment`; แม้ `tb_semi_pigment` จะมี `type='pigment'` แต่ที่ PigmentController ใช้จริงคือ `tb_pigment`) — ดึงเฉพาะ `status` ∈ request/approved **จับคู่ด้วย `planning_id` = `tb_planning.id`** + เงื่อนไขปิดออเดอร์เดียวกัน (`LEFT JOIN tb_planning_header as ph` ผ่าน `tb_pigment.planning_header_id`, `ph.end_order != 'Y'` รวม NULL). **`tb_pigment` ไม่มี `semi_code`/`primary_color` → แสดงได้แค่ `itemno`** ของ pigment ที่ขอ (หลายตัวคั่น ", " ตัดค่าว่าง/ซ้ำ; ไม่มีคำขอ = เว้นว่าง) → attach `lack_pigment` ต่อแถว. **หมายเหตุ:** ยังไม่มีตารางสต๊อกวัตถุดิบผูก planning ใน DB → รายงานนี้ = "งานค้าง (ยังไม่ปิดงาน)"; หากต้องเช็คปริมาณวัตถุดิบจริงภายหลัง ให้เสริมเงื่อนไขใน `buildMaterialShortageReport()`

## ใบสั่งซื้อ (เมนู O-Order, `/order`) — 12/08/2569

หน้านี้แปลงผังมาจากฟอร์ม Access **"บันทึกคำสั่งซื้อ"** โดยตรง (`OrderController` + `routes/order.php` + `resources/views/order/{index,table,form}.blade.php`) — **อย่าสับสนกับเมนู Production → Sale Order** (`Production\OrderController`, `/production-planning/order`) ที่เป็นคนละหน้า คนละ controller

**สถานะ:** ใช้งานได้จริง — สร้าง/แก้ไขใบสั่งซื้อได้ (`OrderController::save`, 18/08/2569)

**การเดินเลขที่ใบสั่ง** (`allocateOrderno`, 18/08/2569): ค่าใน `orderrun` คือ **"เลขล่าสุดที่ใช้ไปแล้ว"** (ยืนยันจาก `orderrun.w = 24564` = ใบ `WI24564` ที่มีอยู่จริง) → เลขถัดไป = ค่านั้น **+1** แล้ว **ข้ามเลขที่มีใบสั่งอยู่จริง** เผื่อค่าใน `orderrun` ไม่ตรงกับข้อมูล (ต้องเช็คทุกประเภทที่ใช้คอลัมน์เลขรันร่วมกัน เช่น CM/CI ใช้ `c` ด้วยกัน). **จองเลขตอนกดบันทึกเท่านั้น** ภายใน transaction + `lockForUpdate()` บนแถว `orderrun` (กันสองคนกดพร้อมกันได้เลขซ้ำ) — ปุ่ม "เพิ่มใบสั่งซื้อใหม่" บนฟอร์มเรียก `nextOrderno` ที่แค่ "อ่านมาโชว์" ไม่เดินเลขจริง จึงไม่เสียเลขเมื่อผู้ใช้เปิดฟอร์มแล้วไม่บันทึก

**3 โหมดของฟอร์ม** (`setOrderFormMode()` ใน `order/index.blade.php`, 20/08/2569) — ตอนเปิดฟอร์มเปล่าจะ**ล็อกทุกช่อง** เหลือกรอกได้แค่ 2 ทาง:
- `idle` — เปิดจากปุ่ม "เพิ่มใบสั่งซื้อใหม่" บนหัวหน้ารายการ (`orderOpenNew`): กรอกได้เฉพาะ **radio ประเภทใบสั่ง** กับ **ช่องเลขที่ใบสั่ง** (ช่องอื่น `disabled` — ยังอ่านค่าด้วย `.val()` ได้ปกติ จึงไม่กระทบการบันทึก)
- `new` — กดปุ่ม "เพิ่มใบสั่งซื้อใหม่" ในฟอร์ม (`orderNew`) → ปลดล็อกทุกช่อง + ใส่เลขที่ที่คาดว่าจะได้
- `edit` — พิมพ์เลขที่ใบเดิมในช่องเลขที่ใบสั่งแล้ว **กด Enter** (หรือกดปุ่มแก้ไขในตาราง) → โหลดใบนั้นมาแก้ทันที (`fillOrderForm` ตั้งโหมดนี้ให้เอง). ไม่พบเลขที่ → เตือนแล้วอยู่ `idle` ต่อ

**ช่องเลขที่ใบสั่งพิมพ์ได้เฉพาะโหมด `idle`** — โหมด `new`/`edit` เลขที่ถูกกำหนดแล้วจึง `readonly`

**กติกาการบันทึก:**
- **ช่อง "วันที่" (`morder.Mdate`) ผู้ใช้แก้ได้** (18/08/2569) — ฟอร์มตั้งวันเวลาปัจจุบันให้เป็นค่าเริ่มต้น (flatpickr `enableTime`, รูปแบบ `d/m/Y H:i`) และบันทึกค่าที่แก้ทั้งตอน insert และ update; ว่างหรือรูปแบบผิด → ใช้ `now()` (ดู `parseDateTime()`)
- `insert` → ปล่อย `appv` ว่าง ⇒ ใบใหม่ไหลเข้าคิวอนุมัติราคาเอง (ดูฟอร์ม morderAPPV ด้านล่าง)
- `update` → **ไม่แตะ** `Orderno` / `appv` / `appvDT`
- รายการ (`syncItems`) เทียบกับของเดิมทีละแถว: มี `Runno` = แก้ไข · ไม่มี = เพิ่ม · หายไปจากฟอร์ม = ลบ — **คง `Runno` เดิมไว้เสมอ** (เป็นเลขอ้างอิงของระบบเดิม อย่าลบทั้งใบแล้วใส่ใหม่)
- แถวที่ยังไม่กรอก `Itemno` = แถวเปล่า ระบบข้ามให้ ไม่บันทึก
- ⚠ route `POST order/save` ป้องกันแค่ `auth` + สิทธิ์ `order read` จาก `config/menu.php` — **ยังไม่มีสิทธิ์แยกสำหรับการเขียน**

**ค่าที่ดึงจาก `customer`** (18/08/2569) — เติมให้ทุกครั้งที่ **ผู้ใช้เปลี่ยนรหัสลูกค้า** (`lookupOrderCustomer` ผูกกับ `oninput` เท่านั้น ไม่ถูกเรียกตอนโหลดใบเดิม ค่าที่บันทึกไว้จึงไม่โดนทับ):

| ช่องบนฟอร์ม | มาจาก | ตรงกับข้อมูลจริง |
|---|---|---|
| **รหัสผู้ขาย** (`morder.supno`) | `customer.sale` | 3,465/3,465 ใบ → ทำเป็น **readonly** |
| **RP** | `customer.RP` | 3,465/3,465 ใบ |
| **CER** | `customer.CER` | 3,464/3,465 ใบ |
| **MSDS** | `customer.MSDS` | 1,593/3,465 ใบ — เป็นแค่ค่าตั้งต้น ติ๊กทับได้ |
| ชื่อลูกค้า · itype · สถานที่ส่ง | `customer.name` · `type`→`c_type` · `naddress` | — |

**ส่งก่อนได้ (`Send`) และ SPEC (`Spec`) ไม่มีค่าประจำลูกค้า** — ตาราง `customer` ไม่มีคอลัมน์ที่ตรงกัน ผู้ใช้ต้องติ๊กเอง

ผู้บันทึก (`Emp`) เติมจากพนักงานที่ล็อกอิน

**ประเภทใบสั่ง = 2 ตัวอักษรหน้า `morder.Orderno`** (radio 12 ปุ่มบนหัวฟอร์ม) — `OrderController::ORDER_TYPES` map ประเภท → คอลัมน์เลขรันในตาราง `orderrun` (1 แถว หลายคอลัมน์): `CM/CI→c`, `HM/HI→h`, `WM/WI→w`, `CE→ce`, `HE→he`, `WE→we`, `CR→CR`, `HR→HR`, `WR→WR`

**Field mapping จากฟอร์ม Access → DB** (ยืนยันกับข้อมูลจริงของใบ `WM23946` แล้ว):

| ช่องบนฟอร์ม | คอลัมน์ |
|---|---|
| เลขที่ใบสั่ง / วันที่ | `morder.Orderno` / `Mdate` |
| ผลิตที่ / P.O. No. | `morder.Company` (CP/DB/MB/SPP) / `PO` |
| รหัสลูกค้า / ชื่อลูกค้า | `morder.Custno` / `Custname` (โชว์ `customer.name` แทน) |
| สถานที่ส่ง | `morder.DVpoint` — ตัวเลือกจาก `naddress` (Custno + DVpoint) |
| ผู้บันทึก / รหัสผู้ขาย | `morder.Emp` / `supno` |
| itype | `customer.type` → `c_type.t_namee` |
| เลขที่ใบจอง | `morder.RsvNo` |
| ส่งก่อนได้ / RP / SPEC / CER / MSDS | `morder.Send` / `RP` / `Spec` / `Cer` / `MSDS` |
| กรณีสั่งทำสต๊อก: กำหนดส่งครบ / ส่งลูกค้าภายใน (เดือน) / นน.คงเหลือ / ส่งมอบเดือนละ | `morder.sendend` / `SendCust` / `HMStore` / `sendmth` |
| น้ำหนักรวม / ราคาขาย | `morder.netqty` / `price` |
| ตารางรายการ: รหัสสินค้า / **รหัส** / ชื่อสินค้า / Lotno. | `suborder.Itemno` / **`nold`** (`O` / `N`) / `prodname` / `Lotno` |
| ตารางรายการ: **S** / **P** | `suborder.Stock` / `Production` (ยอดรวมท้ายตาราง) |
| กำหนดที่ลูกค้าต้องการ / กำหนดส่งทบทวน | `suborder.custwant` / `senddate` |
| วันที่ผลิตเสร็จ / วันที่ลูกค้าได้รับ / เลขที่ใบส่ง | `suborder.EndP` / `DVDate` / `outno` |
| หมายเหตุ (มี dropdown) | `suborder.Remark` — ตัวเลือกสำเร็จรูปจากตาราง `ordrem` |

**ตารางรายการเป็นช่องกรอก เพิ่ม/ลบแถวได้** (18/08/2569) — อ่านค่าจาก DOM ตอนบันทึก (`collectOrderItems`) ไม่เก็บ model คู่ขนาน; ช่องวันที่ในแถวต้อง `initRowPickers()` ผูก flatpickr ทุกครั้งที่สร้างแถวใหม่; ช่อง S/P ใช้ `js-comma` จึงต้องอ่านด้วย `numOf()`

**ผู้ใช้กรอกรหัสสินค้าเบอร์เดียวทั้งใบ** → กล่องราคาด้านขวาผูกกับรหัสของ**แถวแรกที่กรอก** (`syncItemnoToPrice` → `refreshOrderPrice`) และ recalc ใหม่เมื่อเปลี่ยนลูกค้า/รหัสสินค้า/น้ำหนักรวม

**แถบเตือน "สีที่สั่งซื้อล่าสุดเกิน 3 ปี จะต้อง Match ใหม่"** (`OrderController::itemLookup`, 18/08/2569): เทียบ `MAX(morder.Mdate)` ของทุกใบที่มีเบอร์นั้น (**ไม่แยกตามลูกค้า** เพราะการ Match เป็นเรื่องของสูตรสี) — เกิน 3 ปีหรือไม่เคยสั่งเลย = ขึ้นแถบแดง; endpoint เดียวกันคืน `prodname` มาเติมช่องชื่อสินค้าให้เมื่อยังว่าง

**กล่องราคา** (`OrderController::priceData`, แก้ 18/08/2569) — อ้างอิงจาก **รหัสสินค้าที่กรอกในตารางรายการ** (ผู้ใช้กรอกเบอร์เดียวทั้งใบ) คู่กับรหัสลูกค้า ทุกช่องอ่านอย่างเดียว **ยกเว้น "ราคาขาย" ที่ผู้ใช้พิมพ์เอง**:

| ช่องบนฟอร์ม | มาจาก |
|---|---|
| **ราคาที่กำหนดไว้** | **ราคาขาย 1** จาก `ProductPriceService::lookup($itemno)` |
| **ราคาช่อง 2** | **ราคาขาย 2** |
| **ราคาต้องไม่ต่ำกว่า** | **ราคาขาย 2** |
| **ราคาขาย** | **`morder.price` — ผู้ใช้พิมพ์เอง** ไม่ได้ดึงมาจากตารางราคา |
| ราคาอนุมัติ | `appvreq.price` (ใบขออนุมัติล่าสุดของคู่ custno+itemno) |
| ยืนราคาถึง | `zcustprice.enddate` |

⚠ **ราคา 1/2/3 ต้องเรียกผ่าน `ProductPriceService` เท่านั้น** (ตัวเดียวกับที่หน้า "ค้นหาราคาสินค้า" ใน `/saleinfo` ใช้) — คำนวณจากราคาทุนใน **`access_pdprice`** ตามเงื่อนไขที่จับคู่ด้วยตัวขึ้นต้นรหัส: ราคา1 = ทุน × mul ÷ div + add · ราคา2 = ราคา1 × 1.14 · ราคา3 = ราคา2 × 1.30 (ตัวคูณอยู่ที่ `config/product_price.php` → `tier`)

**ราคา 1/2/3 อ้างอิงจากรหัสสินค้าอย่างเดียว ไม่เกี่ยวกับลูกค้า** — อย่าไปดึงจาก `appvreq.price1/2/3` (นั่นคือราคาที่ "ขออนุมัติ" ของคู่ ลูกค้า+เบอร์ ซึ่งมีข้อมูลแค่ ~4% ของคู่ทั้งหมด) และอย่าใช้ตาราง `pdprice` (คนละตารางกับ `access_pdprice` ที่ระบบกำหนดราคาใช้ — มีรหัสไม่ตรงกัน)

ใต้กล่องราคามีบรรทัดบอกที่มาแบบเดียวกับหน้า /saleinfo: `ราคาทุน 0.20 · CP อื่น ๆ ทั่วไป · × 101 ÷ 100 + 6` — ถ้าคำนวณไม่ได้จะโชว์เหตุผลจาก `reason` ของ service (เช่น "ไม่พบรหัสสินค้านี้ในตารางราคาทุน") แทนที่จะปล่อยช่องว่างเงียบ ๆ

ช่อง "กลุ่มราคา" (A/B/C จาก `netqty`) ยังแสดงอยู่แต่**ไม่ได้ใช้คิดราคาขั้นต่ำ**

**กลุ่มราคา A/B/C** (นิยามอยู่ที่ `PriceApprovalController::PRICE_GROUPS`, เรียกผ่าน `groupOf($weight)`): `price1` = กลุ่ม A (1,000 kg.up) · `price2` = กลุ่ม B (500 kg.up) · `price3` = กลุ่ม C (under 500 kg.) — ฟอร์มใบสั่งซื้อใช้ `morder.netqty` หากลุ่ม แล้วโชว์ราคาของกลุ่มนั้นเป็น "ราคาต้องไม่ต่ำกว่า"

**ยังไม่ยืนยัน:** ช่อง "ขั้นต่ำ" (ทำ UI ไว้แล้วแต่ยังไม่ผูกข้อมูล)

**checkbox แบบ Access:** เก็บ `-1` = ติ๊ก, `0`/`NULL` = ไม่ติ๊ก → แปลงด้วย `OrderController::checked()` / `PriceApprovalController::checked()` ก่อนส่งให้ฟอร์ม อย่าเทียบ `== 1`

### ฟอร์มลูก: ขออนุมัติราคาพิเศษ (MD) — 12/08/2569

แปลงจากฟอร์ม Access **"MK ขออนุมัติราคาพิเศษ"** — `PriceApprovalController` + route ใต้ prefix `order/price-approval/*` + view `order/price-approval.blade.php` (modal ในหน้า `/order`) **สถานะ: บันทึก/อนุมัติ/ลบ ได้แล้ว** (20/08/2569)

**⚠ 3 จุดนี้เป็น "ค่าเดา" เพราะยังไม่ได้ข้อมูลจริงจากลูกค้า** — ทำไว้ให้ใช้งานได้ก่อน แก้ทีหลังได้ที่จุดเดียวทั้งหมด:

| เรื่อง | ที่เดาไว้ | แก้ที่ไหนเมื่อได้ข้อมูลจริง |
|---|---|---|
| **รหัสผ่าน MD** | รหัสเดียวใช้ร่วมกัน เก็บที่ `config/order.php` → `md_password` (override ด้วย `ORDER_MD_PASSWORD` ใน .env) | `PriceApprovalController::checkMdPassword()` |
| **checkbox "ต้นทุนวัตถุดิบปรับขึ้น…"** | เพิ่มคอลัมน์ `appvreq.costup` (`-1` = ติ๊ก แบบ Access) ด้วย migration `add_costup_to_appvreq_table` | ถ้าค่านี้เก็บที่อื่นจริง ให้ย้ายแล้วลบคอลัมน์ทิ้ง |
| **ช่อง "3-4 nn" / "1-2 nn"** | = ขั้น **DB 3-4 Kg. / DB 1-2 Kg.** ของระบบกำหนดราคา (ตรงกับช่อง `np_db_3_4` / `np_db_1_2` ในหน้า `/saleinfo` ที่ยังว่าง) — คูณต่อจากราคาขาย 3 ด้วย **×1.12 แล้ว ×1.19** (ถอดจากใบตัวอย่าง 718 → 802 → 952) | `config/product_price.php` → `tier.db_3_4_from_price_3` / `db_1_2_from_db_3_4` — แก้ 2 บรรทัด ทั้ง `/saleinfo` และหน้า MK ตามทันที |

**กติกาการบันทึก** (`save()`): เขียน `appvreq` (คีย์ `ReqDate` + `custno` + `itemno`) — ส่ง `ReqDate` มา = แก้ใบเดิม, ไม่ส่ง = ขึ้นใบใหม่ด้วยเวลาปัจจุบัน · **ติ๊ก "อนุมัติ" ต้องกรอกรหัสผ่าน MD ให้ถูก** ไม่งั้น 422 · เมื่ออนุมัติจะเขียน `zcustprice` (`exprice` = ราคาขายครั้งนี้, `enddate` = อนุมัติราคาถึง) ให้ด้วยในทรานแซกชันเดียวกัน · `destroy()` ลบเฉพาะแถว `appvreq` **ไม่แตะ `zcustprice`**

ราคา 3 ช่องบนฟอร์ม: ใบเดิมใช้ค่าที่บันทึกไว้ใน `appvreq` · ใบใหม่เติมจาก `ProductPriceService` ให้ก่อน · ส่วน DB 3-4/1-2 Kg. คำนวณสด ๆ เสมอ (ไม่มีที่เก็บใน `appvreq`)

**ปุ่ม "พิมพ์" ยังเป็น `window.print()`** — ยังไม่มีแบบฟอร์มกระดาษให้อ้างอิง

| ช่องบนฟอร์ม | คอลัมน์ |
|---|---|
| วันที่ขอราคา | `appvreq.ReqDate` (PK ร่วมกับ custno + itemno) |
| รหัสลูกค้า · **# 15** · ชื่อลูกค้า | `appvreq.custno` · `customer.sale` (รหัสพนักงานขาย) · `customer.name` |
| รหัสสินค้า | `appvreq.itemno` — ตัวเลือกรวมจาก `uprice.ITEMNO` + `zcustprice.colorno` ของลูกค้ารายนั้น |
| ราคา 3 ช่อง | `appvreq.price1` / `price2` / `price3` (= กลุ่ม A/B/C) |
| ราคาขายครั้งนี้ / จำนวนสั่งซื้อ | `appvreq.price` / `weight` |
| หมายเหตุ การปรับราคา | `appvreq.remark` |
| อนุมัติ | `appvreq.Appv` |
| อนุมัติราคาถึง | `zcustprice.enddate` |
| ตารางล่าง (รหัสสี · ราคาขาย · ยืนราคาถึงวันที่ · หมายเหตุ) | `zcustprice` (`colorno` · `exprice` · `enddate` · `remark`) |

ปุ่มตรวจสอบ/ประวัติทั้ง 4 ปุ่มโหลดผลลงตารางล่างตัวเดียวกัน: **ตรวจสอบเบอร์อื่น** → `zcustprice` ทุกเบอร์ของลูกค้า · **ประวัติของเบอร์นี้** → `appvreq` ทุกครั้งของคู่ custno+itemno · **ตรวจสอบเฉพาะร้าน** → `zcustprice` ของลูกค้ารายอื่นที่ใช้เบอร์นี้ · **ประวัติราคาเม็ด CP** → `cp_itemprice` ของเบอร์นี้


### ฟอร์มลูก: อนุมัติราคาใบสั่งซื้อ (morderAPPV) — 12/08/2569

แปลงจากฟอร์ม Access **"morderAPPV"** — `OrderApprovalController` + route ใต้ prefix `order/order-approval/*` + view `order/order-approval.blade.php` (modal ในหน้า `/order`, เดินทีละระเบียนเหมือนฟอร์มเดิม) **สถานะ: UI + อ่านอย่างเดียว** (ปุ่ม "อนุมัติ" ยังไม่เขียน DB)

**คิวรออนุมัติ** (`queueQuery()`) = `morder` ที่ `appv IS NULL` **ตัด**ใบจอง R (ตัวอักษรที่ 2 ของ `Orderno` = `R`) และ**ตัด**ใบสั่งทำสต๊อก (`HMStore`/`SendCust`/`sendmth` มีค่า) — ตรงกับข้อความกำกับบนหัวฟอร์มเดิม "ไม่รวมทำ STOCK + ไม่รวมใบจอง R"

| ช่องบนฟอร์ม | คอลัมน์ |
|---|---|
| วัน-เวลา / เลขที่ใบสั่ง / แผนกที่ผลิต / PO | `morder.Mdate` / `Orderno` / `Company` / `PO` |
| รหัสลูกค้า + ชื่อ / ผู้บันทึก | `morder.Custno` + `customer.name` / `morder.Emp` |
| **ผู้ขาย** | `customer.sale` (ค่าเดียวกับ `morder.supno`) |
| น.น.Stock คงเหลือปัจจุบัน / ส่งลูกค้าภายใน (เดือน) | `morder.HMStore` / `SendCust` |
| ส่งก่อนได้ / RP / Spec / Cer / สถานที่ส่ง | `morder.Send` / `RP` / `Spec` / `Cer` / `DVpoint` |
| ตารางเหลือง: รหัสสินค้า · ชื่อสินค้า · Lot No · **S** · **P** · กำหนดทบทวน · หมายเหตุ | `suborder.Itemno` · `prodname` · `Lotno` · `Stock` · `Production` · `senddate` · `Remark` |
| REM1 / REM2 / ราคาที่กำหนดไว้ | `uprice.REM1` / `REM2` / `PRICE` (ของเบอร์ที่เลือกในตาราง) |
| ราคา1 / ราคา2 / ราคา3 | `appvreq.price1` / `price2` / `price3` (= กลุ่ม A/B/C) |
| `(<เทอม> - <ส่วนลด>%)` | `customer.term` + `customer.cashdisc` |
| ราคาขายครั้งนี้ | `morder.price` |
| **อนุมัติ / วัน-เวลา อนุมัติ** | **`morder.appv`** (-1 = อนุมัติ) / **`morder.appvDT`** |

**ยังไม่ยืนยัน:** ช่อง "ผู้บริหาร" (กล่องเขียว) — ยังไม่พบคอลัมน์ที่เก็บ

## ข้อตกลงและข้อควรระวัง (Conventions & Gotchas)

- **ช่องกรอกตัวเลขแบบใส่คอมมาอัตโนมัติ** (`resources/views/layout/inc_js.blade.php`, 18/08/2569): ช่องกรอกราคา/น้ำหนัก/จำนวน **ห้ามใช้ `type="number"`** (แสดงคอมมาไม่ได้) ให้ใช้ `type="text" class="js-comma"` แทน — ตัวช่วยกลางอยู่ใน `inc_js` จึงใช้ได้ทุกหน้าโดยไม่ต้อง include เพิ่ม
  - `data-decimals="0"` = จำนวนเต็ม (ไม่ระบุ = ทศนิยม 2 ตำแหน่ง)
  - **ค่าที่แสดงในช่องมีคอมมาเสมอ** → อ่านค่าด้วย `numVal(sel)` / `numOf(value)` และเรียก `stripCommaFields(form)` **ก่อน** `serialize()` / `new FormData()` ทุกครั้ง ไม่งั้น `'1,234.50'` จะลง DB เป็น `1.00`
  - เติมค่าจาก DB ลงช่องด้วย `commaFmt(v, decimals)`
  - ที่ใช้อยู่: Order (`o_netqty`/`o_HMStore`/`o_sendmth`/`a_price`/`a_weight`), กำหนดราคา (`MOQ`/`PRICE`), เทียบสี (`Wage`), ใบเสนอราคา (ทุกคอลัมน์ที่ `colRegistry` ตั้ง `num => true`)
  - ที่**ตั้งใจไม่ใส่**: ตัวคูณ/หาร/บวกในตารางเงื่อนไขราคา, `PHR` (ทศนิยม 4 ตำแหน่ง), จำนวนเดือน, ช่องเดินระเบียน — ไม่ใช่ตัวเลขหลักพัน
- **แถบตัวกรองแบบพับได้** (21/08/2569): หน้ารายการหลักทุกหน้าที่มีแถบตัวกรอง — **Order**, **ใบเสนอราคา**, **เทียบสี**, **กำหนดราคา** — ครอบตัวกรองทั้งหมดไว้ใน `<div class="collapse" id="<หน้า>FilterBox">` โดย**ไม่ใส่ class `show`** ⇒ **ค่าเริ่มต้นคือซ่อนไว้**
  - หัวการ์ดเป็นแถบเดียว `d-flex justify-content-end gap-2`: **ปุ่ม `#btnToggleFilters` (พับ/กาง) + ปุ่ม `#btnResetFilters` (ล้างตัวกรอง) ชิดขวาทั้งคู่** — ปุ่มล้างตัวกรอง**อยู่นอกกล่องพับ** จึงกดล้างได้โดยไม่ต้องกางก่อน และ `.filter-count` บนปุ่มทำหน้าที่บอกจำนวนตัวกรองที่ใช้อยู่แม้ตอนพับซ่อน (ไม่ต้องมี badge บนปุ่มตัวกรองซ้ำอีก)
  - จำนวนตัวกรองอัปเดตใน `updateFilterButtonState()` ของแต่ละหน้า — หน้ากำหนดราคาเดิมไม่มีฟังก์ชันนี้ จึงเพิ่งเพิ่มเข้าไป
  - แถว "เรียงตาม / จำนวนรายการต่อหน้า" **อยู่นอกกล่องพับ** (เป็นการตั้งค่าแสดงผล ไม่ใช่ตัวกรอง) เช่นเดียวกับ hidden input `sort_col`/`sort_dir`
  - element ที่ถูก collapse ยังอยู่ใน DOM → `.p_search` / `collectSearchData()` / `resetFilters()` ทำงานเหมือนเดิม **ไม่ต้องแก้**; หน้าใบเสนอราคาต้องเรียก `refreshPickers()` ตอน `shown.bs.collapse` เพราะ bootstrap-select วัดความกว้างตอนซ่อนอยู่ไม่ได้ (select2 ในหน้าเทียบสีใช้ `width:'100%'` จึงไม่มีปัญหานี้)
  - หน้า **ข้อมูลสินค้า** (`/product`) มีแค่ช่องค้นหาช่องเดียว ไม่มีแถบตัวกรอง จึง**ไม่ได้ทำ**
- Model ใช้ `protected $guarded = []` (เปิด mass assignment ทั้งหมด) และระบุ `protected $table = 'tb_...'` ตรง ๆ
- Schema เป็นแบบ **database-first**: ตาราง/คอลัมน์จำนวนมากถูกสร้าง/เพิ่มมือใน DB โดยตรง migration ที่มีเป็นเพียงบางส่วน (ส่วนใหญ่เป็นการ *alter* / *create* เฉพาะจุด) — **อย่าคิดว่า migration อธิบาย schema ได้ครบ** ให้ตรวจสอบจาก database จริงเสมอ
- **Migration ต้อง idempotent** (06/08/2569): เพราะ DB มักมีตาราง/คอลัมน์อยู่ก่อนแล้ว การ `add column` / `create table` ตรง ๆ จะพัง (`Duplicate column` / table exists) ตอนรัน `php artisan migrate` — ให้ครอบด้วย `Schema::hasColumn(...)` / `Schema::hasTable(...)` เสมอ (migration เก่าหลายไฟล์ถูกแก้ให้เป็นแบบนี้แล้ว)
- **โค้ดเก่า / dead code:** template นี้เดิมเป็นระบบเช่าห้องพัก
  - `app/Http/Controllers/Controller.php` (base controller) ยังมี method `summary()` / `summary_calculate()` ขนาดใหญ่ที่อ้างถึง model ที่ไม่มีอยู่แล้ว (`Room`, `RentBill`, `Receipt`, `Renter`, `Contract`, …)
  - `App\Main\SideMenu::menu()` มี `return` ตั้งแต่ต้น method ตามด้วย template scaffolding อีกหลายร้อยบรรทัดที่ไม่มีวันถูกเรียก
  - route `report.*` บางตัวใน `web.php` (rooms, rent bills, invoices) ก็เป็นของเหลือจากระบบเดิม
  - อย่าถือว่าสิ่งเหล่านี้ยังใช้งานอยู่ ให้ตรวจสอบว่า route/method นั้นถูกเรียกใช้จริงก่อนนำไปใช้อ้างอิง
- Controller มักมี route เป็นคู่: `index` (หน้า Blade) และ endpoint `datatable` ที่ป้อนข้อมูลให้ Yajra DataTables / Tabulator ผ่าน AJAX รวมถึง route `excel` / `pdf` สำหรับ export (DomPDF + mPDF, PhpSpreadsheet)
- **การเรียงลำดับตารางด้วย Yajra DataTables** (10/08/2569): หน้าที่เปิดให้คลิกหัวคอลัมน์เพื่อ sort ได้แล้ว — **department, machine, prod-method, employee, temp, role, order, semi-pigment, pigment, planning, planning-status** (และ **order-plan** ที่ตั้งค่า sort ไว้ครบตั้งแต่เดิม — คอลัมน์ subquery/aggregate เช่น inplan/custname/item_count ใช้ `->orderColumn()` map เป็น SQL) (Yajra จะ `ORDER BY` ตาม `name` ของคอลัมน์ให้อัตโนมัติเมื่อตั้ง `orderable: true` ในฝั่ง DataTables) กติกาที่ใช้:
  - คอลัมน์ที่ render ผ่าน `addColumn`/switch/badge (เช่น สถานะ, แผนก, จำนวนพนักงาน) ต้องตั้ง `name` ให้ชี้ไปที่**คอลัมน์จริงใน DB / alias** ไม่ใช่ชื่อ data ที่แสดงผล เช่น สถานะ → `is_active`, แผนกใน prod-method → `dept`, ชื่อใน temp → `Temp1`, role พนักงาน → `employees_count` (จาก `withCount`), สถานะแผนใน order → `has_plan` (alias จาก EXISTS); หน้า employee มี join `roles` จึงต้อง qualify เป็น `emp.*` / `roles.*`
  - คอลัมน์ที่คำนวณฝั่ง PHP (เช่น `itemno_list` ในหน้า order ที่รวมจาก suborder) sort ที่ระดับ SQL ไม่ได้ → คง `orderable: false`
  - คอลัมน์เลขลำดับ และปุ่มจัดการ (`btnedit`/`btnaction`) ยังคง `orderable: false` — ส่วนใหญ่ใช้ `++$rownum` (นับ 1..N ต่อหน้า), แต่หน้า **order** ใช้ `DT_RowIndex` จาก `addIndexColumn()` (นับตามลำดับที่แสดงจริง รองรับ sort + pagination) หลังเลิกใช้ window function `ROW_NUMBER()` เดิม
  - **ต้องลบ `orderBy` ที่ hard-code ในเมธอด `datatable()`/`dataQuery()` ของ controller** แล้วย้ายลำดับเริ่มต้นไปเป็น `order: [[...]]` ใน DataTables แทน — ไม่งั้น Yajra จะเติม order ต่อท้าย ทำให้คลิก sort คอลัมน์อื่นไม่มีผล
  - **ระวัง query ที่ใช้ร่วมกับ export**: หน้า semi-pigment / pigment ใช้ `semiListQuery()`/`pigmentListQuery()` → `baseQuery()` ทั้งใน `datatable()` และ `exportExcel()` — เมื่อลบ `orderBy` ออกจาก base query ต้องไปใส่ `->orderBy('id','desc')` เองใน `exportExcel()` เพื่อให้ไฟล์ export ยังเรียงใหม่→เก่าเหมือนเดิม
  - **หน้า planning (`production.planning.index`)**: คิวรี join `tb_planning` + `tb_planning_header` จึงตั้ง `name` เป็น qualified column (`tb_planning.itemno`, `tb_planning_header.orderno` — โดยเฉพาะ `custwant`/`company` ที่มีทั้งสองตาราง), แผนกใช้ `->orderColumn('company', 'COALESCE(tb_planning.company, tb_planning_header.company) $1')` ให้ตรงกับที่แสดง, `inner_status` (รวมหลายแถวฝั่ง PHP) คง `orderable:false`. **เทคนิค default order**: ลำดับเริ่มต้นคือ `id desc` (ไม่ใช่คอลัมน์ที่แสดง) จึงใส่ orderBy default แบบมีเงื่อนไข `->when(empty(request('order')), ...)` (บวกเงื่อนไข packing) + ตั้ง `order: []` ใน DataTables — เมื่อผู้ใช้คลิก sort จะมี `order[]` ส่งมา default จึงไม่ทับ ปล่อยให้ Yajra จัดการ
  - **หน้า order-change-request เป็นข้อยกเว้น** (client-side DataTables): ตารางนี้ไม่ใช่ serverSide — controller สร้าง collection ในฝั่ง PHP (group `itemno`) แล้ว blade render ทุกแถวออกมาตรง ๆ จึง init `$('#ocrTable').DataTable()` แบบ client-side (`paging/searching/info` = false เพื่อคงพฤติกรรมเดิม) โดย **ต้อง init เฉพาะเมื่อมีแถว** (`@if($rows->isNotEmpty())`) ไม่งั้นชนกับ empty-row `colspan`; คอลัมน์วันที่/ตัวเลขใส่ `data-order` (Y-m-d / ตัวเลขดิบ) เพื่อ sort ตามค่าจริง และ renumber คอลัมน์ `#` หลัง sort ด้วย event `order.dt`
- **หน้าใบขอเปลี่ยนแปลงคำสั่งซื้อภายใน** (`OrderChangeRequestController`, `/production-planning/order-change-request`, 06/08/2569): แสดง 1 แถวต่อ 1 รหัสสินค้า (group `itemno`) กรองด้วยช่วงวันที่แบบ **OR** — order ที่ **ปิดจบงานในช่วง** (`end_close='Y'` + `end_close_date`) *หรือ* **มีรายการที่เปลี่ยน senddate ในช่วง** (`senddate_changed_at`, ไม่ต้องปิดจบงาน) โดย order ที่เข้ามาเพราะ senddate จะแสดงเฉพาะรายการที่เปลี่ยนจริง; คอลัมน์ "กำหนดเสร็จเดิม" = วันที่ล่าสุดใน `senddate_log`, "ขอเลื่อนเป็นวันที่" = `senddate`, "เลขที่ใบทบทวนคำสั่งซื้อ" = `red_bill_code` — มี export PDF (mPDF) คู่กัน
- **ค่าคงที่ของเทียบสี** อยู่ที่ `config/color_matching.php` (10/08/2569) — `test_type_options` (`testmain.TestType`: 1=CP, 2=DB สีผง, 3=MB สีเม็ด, 4=Pigment) และ `test_result_options` (`testmain.TyResp`) เป็นค่าเดิมจาก Access ทั้งคู่ ใช้ร่วมกันทั้ง dropdown และหน้าแสดงผล; ตัวแปลงค่า→ป้ายชื่ออยู่ที่ `Testmain::testTypeLabel()` / `Testmain::testResult()` (อย่า hardcode ป้ายชื่อซ้ำใน blade)
- **ผลการทดสอบตัวอย่างสี** (`color-matching`, 10/08/2569): ฟอร์มของใบส่ง ต.ย. (SD = แถว `testmain` ที่มี `Testno`) แปลงมาจากฟอร์ม Access เดิม — เก็บลง 3 คอลัมน์ legacy: `TyResp` (ตัวเลือก char(1): `0`=ยังไม่ตอบ, `9`=สั่งซื้อแล้ว, `A`–`H`=เหตุผลที่ไม่สั่งซื้อ — นิยามใน `config/color_matching.php`), `Resp` (ข้อความ "ระบุ", varchar(30)), `Respdate` (วันที่ทราบผล). UI อยู่ที่ `color-matching/modal-result.blade.php` เปิดจากปุ่มในตาราง → `viewTestResult(id)` → `POST color-matching/result/{id}` (`ColorMatchingController::saveResult` — แตะเฉพาะ 3 คอลัมน์นี้ ไม่ใช้ `update()` เพราะ `extractPayload()` ตัดค่าว่างทิ้งและบังคับ `cancel=0`)
- **ข้อมูลจากไฟล์ Access `formula_2000.mdb`** (Compo / PdPrice / TestMai) ถูกคัดลอกมาไว้บน MySQL เป็นตาราง `access_compo` / `access_pdprice` / `access_testmai` แล้ว (migration `create_access_mirror_tables`, 05/08/2569) — โค้ดทั้งหมดอ่านจาก MySQL เพราะ server ของลูกค้าไม่มีไฟล์ .mdb และไม่มี ODBC driver
  - โค้ดเดิมที่ต่อ ODBC ยัง**คอมเมนต์ไว้**ใน `AccessService`, `ProductPriceService::findPdPrice()`, `AccessModel` — เปิดคืนได้ถ้าจะกลับไปอ่านไฟล์จริง (ต้องตั้ง `ACCESS_DB_PATH` ใน `.env` ด้วย)
  - connection `access` ใน `config/database.php` + `DB::extend('access', ...)` ใน `AppServiceProvider` ยังอยู่ แต่ไม่มีใครเรียกแล้ว (resolve แบบ lazy จึงไม่พังตอน boot)
  - ดูข้อมูลทั้ง 3 ตารางได้ที่ท้ายหน้า **กำหนดราคา** (`/saleinfo`) — แท็บ Compo / PdPrice / TestMai อ่านอย่างเดียว ผ่าน `SaleinfoController::accessData()` → `saleinfo/access-table.blade.php` (05/08/2569)
- **ตารางเงื่อนไขคิดราคาขาย** (`config/product_price.php` + `ProductPriceService`, 10/08/2569): แต่ละแถวมี `key` (ห้ามเปลี่ยน/ซ้ำ), `prefix`, `suffix`, `suffix_pos` (= ตัวลงท้ายต้องเริ่มที่ตัวที่ N พอดี เช่น "MB ตัวที่ 8 ลงท้ายด้วย P/PC/K/J") และ `mul`/`div`/`add`
  - โครงเงื่อนไข (label/prefix/suffix/suffix_pos) แก้ที่ไฟล์ config เท่านั้น
  - ส่วน **คูณ/หาร/บวก ผู้ใช้แก้เองได้** จากปุ่ม "ตั้งค่าเงื่อนไขราคา" ในหน้า `/saleinfo` (modal `saleinfo/modal-pricerule.blade.php`) → เก็บลงตาราง `tb_price_rule` (`rule_key` unique) ซึ่ง **ทับ** ค่าใน config; แถวที่ค่าตรงกับค่าตั้งต้นจะถูกลบทิ้ง = กลับไปใช้ค่า config
  - `ProductPriceService::rules()` เป็นตัว merge config + override (แถวจะมี `is_custom` / `default` ติดมาด้วย) — โค้ดที่ต้องการตารางเงื่อนไข **ห้ามอ่าน `config('product_price.rules')` ตรง ๆ** ให้เรียกผ่าน `rules()` ไม่งั้นจะได้ค่าตั้งต้นเสมอ
