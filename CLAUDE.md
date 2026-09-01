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
│   │   ├── OrderApprovalController.php  # ฟอร์มลูกของ O-Order — อนุมัติใบสั่งซื้อ morder.appv (12/08/2569)
│   │   ├── QuotationController.php
│   │   ├── CustomerController.php   # ฐานข้อมูลลูกค้า customer/contact/naddress/engname — CRUD (24/08/2569)
│   │   ├── ReportController.php
│   │   ├── UserController.php
│   │   ├── PermissionController.php
│   │   ├── PDFController.php
│   │   ├── SaleinfoController.php   # กำหนดราคา + ค้นหาราคาสินค้า — เขียนลง `uprice` (04/08/2569, ย้ายตาราง 29/08/2569)
│   │   ├── PriceRuleController.php  # ตั้งค่าเงื่อนไขราคา คูณ/หาร/บวก — เมนูแยก /price-rule (21/08/2569)
│   │   ├── ProductController.php    # ข้อมูลสินค้า tb_products — CRUD (07/08/2569)
│   │   ├── HolidayController.php    # วันหยุดนักขัตฤกษ์ tb_holiday — CRUD + ปฏิทินรายปี (01/09/2569)
│   │   └── ExportExcelController.php
│   ├── Models/
│   └── Services/                # AccessService, ProductPriceService (04/08/2569), HolidayService (01/09/2569)
├── config/
│   ├── menu.php                 # โครงสร้างเมนู
│   ├── product_price.php        # ตารางเงื่อนไขคิดราคาขายจากราคาทุน (04/08/2569)
│   │                            # — mul/div/add เป็นแค่ "ค่าตั้งต้น" ผู้ใช้แก้ทับได้ (10/08/2569)
│   ├── color_matching.php       # ตัวเลือกผลการทดสอบตัวอย่างสี (testmain.TyResp) (10/08/2569)
│   └── holiday.php              # วันหยุดประจำสัปดาห์ของ HolidayService (01/09/2569)
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
│   ├── customer.php             # ฐานข้อมูลลูกค้า customer/contact/naddress (24/08/2569)
│   ├── report.php
│   ├── permission.php
│   ├── product.php              # ข้อมูลสินค้า tb_products (07/08/2569)
│   ├── holiday.php              # วันหยุดนักขัตฤกษ์ tb_holiday (01/09/2569)
│   └── pricerule.php            # ตั้งค่าเงื่อนไขราคา tb_price_rule (21/08/2569)
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

- `color-matching.php`, `quotation.php`, `saleinfo.php`, `pricerule.php`, `order.php`, `customer.php`, `report.php`, `permission.php`, `product.php`, `holiday.php`, `worker.php`, `production.php`

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
- **⚠ ส่วน Pigment ในฟอร์มแก้ไข Planning Item ถูกยกเลิกชั่วคราว (25/08/2569):** ใน `planning-item-form.blade.php` ทั้ง 3 บล็อกของ Pigment (UI ตาราง `#table_pigment` + เส้นคั่น `<hr>` เหนือมัน, Modal `#pigment_entry_modal`, และ JS IIFE ของ Pigment) ถูกครอบด้วย `@if(false) … @endif` (มี comment กำกับที่หัว/ท้ายทุกบล็อก) จึงไม่แสดงในฟอร์ม เหลือแต่ส่วน Semi. **เปิดคืน:** เปลี่ยน `@if(false)` เป็น `@if(true)` หรือถอด `@if(false)`/`@endif` ออกทั้ง 3 คู่. **backend ไม่ถูกแตะ** — `PigmentController`, routes `production.pigment.entry.*`, ตาราง `tb_pigment`, Model `Pigment`, partials (`pigment/partials/entry-fields`, `planning/partials/pigment-row`) ยังอยู่ครบ และ controller ยังส่ง `$pigment_list` มาเหมือนเดิม (แค่ไม่ถูกใช้ระหว่างปิด); ข้อมูลใน `tb_pigment` ปลอดภัย
- **การปิดงาน end_job / end_order** (`ProductionPlanController`): `end_job` = ปิดงานราย item (`tb_planning`, gate: ถ้ามีคำขอ semi แผน semi ทุกใบต้อง `end_order='Y'` ก่อน — `itemSemiJobsDone`); `end_order` = ปิดออเดอร์ราย header (`tb_planning_header`, gate: `end_job` ทุกแถวในต้นไม้ recursive ต้อง `='Y'` — `allEndJobsDone`); `end_close` = ปิดจบงาน (ไม่มี gate, บังคับ `end_order=Y` lockstep + ต้องมี `end_close_remark`)
- **ปิดออเดอร์อัตโนมัติ (auto-close end_order)** (`ProductionPlanController::saveItem`, 15/08/2569): เมื่อบันทึก item ด้วย `end_job='Y'` แล้วทำให้ `allEndJobsDone($header)` เป็น true (ครอบคลุมทั้ง header ที่มี planning เดียว และเหลือ item สุดท้ายที่เพิ่งปิด) → ตั้ง `end_order='Y'` ให้เองในทรานแซกชันเดียวกัน. ใช้ predicate เดิม (`allEndJobsDone`) จึงคงเงื่อนไขเดิม; ทำเฉพาะขา "ปิด" (ไม่ auto-ปลด) และไม่แตะ `end_close`. response แนบ `end_order_auto_closed` (bool). **UI:** modal "แผนการผลิต" (`planning-form.blade.php`) มี badge "ปิดออเดอร์แล้ว" (สีเขียว) ที่หัว modal + ข้าง checkbox End Order แสดงเมื่อ `end_order='Y'` (auto ปรากฏหลัง `reloadPlanningHeaderContent` re-render); ฝั่ง JS (`planning/index.blade.php` handler `#btn_save_planning_item`) ถ้า `end_order_auto_closed=true` เด้ง Swal แบบ info ให้กดรับทราบ (แทน toast auto-dismiss ปกติ). **หมายเหตุ:** เมื่อ auto-close แล้ว header จะถูกล็อกแก้ไข/เพิ่ม planning ทันที (gate `end_order='Y'` ที่หัว `saveItem`) — ถ้าปิดผิดต้องกดปลด end_order เองก่อน
- **หน้ารายการวางแผน** (`production.planning.index`, blade `production-planning/planning/index.blade.php`) ป้อนข้อมูลด้วย `ProductionPlanController::datatable` → `dataQuery`. ช่องค้นหา (`#searchInput` → param `search`) ค้นแบบ LIKE ข้ามหลายฟิลด์: `machine_no`, `itemno`, `red_bill_code` (เลขที่ใบเบิก Red Bill, 06/08/2569), `orderno`, `planning_code`, `custno`, และพนักงาน (`empno` / ชื่อ-นามสกุลใน `emp`)
  - **คอลัมน์ "สถานะภายใน" = `planning_status` ของ item แถวนั้นเอง** (แก้ 01/09/2569): เดิม `datatable()` และ `buildReportRows()` (export) **รวม** `planning_status` ของทุก item ใน header (`planning_header_id`) เดียวกันมาโชว์ทุกแถว ⇒ item ที่ไม่มีสถานะของตัวเอง (เช่น R1/R2 ที่ปิดงานแล้ว) ก็ขึ้นสถานะของ item อื่น (เช่น R3 = `V.MIX`) ผิด ๆ. ตอนนี้แต่ละแถวแสดงสถานะของตัวเอง (ว่าง = `-`); คิวรีย่อยต่อแถว (`datatable`) + `status_map` (`buildReportRows`) ถูกตัดออก. บรรทัดที่ 2 ยังเป็น badge ปิดงาน/ยังไม่ปิดงานจาก `end_job` เหมือนเดิม
- **หน้าแผนการผลิต (Order Plan)** (`production.orderplan.index`, `OrderPlanController::dataQuery`, blade `order-plan/index.blade.php`): แสดง 1 แถวต่อ 1 Order (header ที่ `parent_planning_id` ว่าง). ตัวกรอง: ช่องค้นหาหลัก (`#searchInput` → `search`) ค้น orderno/custno/ชื่อลูกค้า(`morder.Custname`)/พนักงานในต้นไม้, **ช่องค้นหา "รหัส Sale" (`#searchSaleno` → param `saleno`, ค้น `tb_planning_header.saleno` LIKE, 15/08/2569)**, แผนก(`company`), สถานะปิด order(`end_order`, default `N`), ช่วงวันที่ Inplan/Custwant — ทุกช่อง redraw ตาราง + รวมอยู่ในปุ่มล้างตัวกรอง
- **วันที่กำหนดทบทวน (senddate) + ประวัติ** (`tb_planning`, 06/08/2569): เมื่อแก้ senddate ใน modal แก้ไข Planning item และ**มีค่าเดิมอยู่ก่อน** → เก็บค่าเดิมต่อท้าย `senddate_log` (คั่นด้วย comma) และบันทึกเวลาที่เปลี่ยน**ล่าสุด**ทับลง `senddate_changed_at` (DATETIME, มี index) — ถ้าตอนแรกว่างแล้วเพิ่งใส่ค่าจะไม่เก็บ (ดู `ProductionPlanController::saveItem`)
- **ยืนยันก่อนบันทึกเมื่อแก้ custwant** (modal แก้ไข Planning Item, 15/08/2569): ช่อง "วันที่ต้องการรับ (custwant)" เก็บค่าเดิมไว้ที่ `data-original` (Y-m-d) — ตอนกดบันทึก (`#btn_save_planning_item` ใน `planning/index.blade.php`) ถ้าค่าปัจจุบันต่างจาก data-original จะเด้ง Swal ยืนยัน (แสดง จาก→เป็น แบบ d/m/Y) ก่อน แล้วค่อยเรียก `submitPlanningItem()`; ถ้าไม่เปลี่ยนบันทึกได้เลย
- **สถานะวิธีการผลิต** (`tb_planning_prod_method`): ตารางลูกของ planning item (1 planning → หลายแถว) เก็บ `prod_method_id` (→ `tb_prod_method`), `work_date`, `start_time`, `end_time`, `sort` — บันทึกผ่าน `ProductionPlanController::syncProdMethods` (ลบทั้งหมดแล้ว insert ใหม่). **หมายเหตุ:** เดิมมีคอลัมน์ `temp_id` (→ ตาราง `temp`) แต่**ลบออกแล้ว** 06/08/2569 (ย้าย Temp ไปจัดการที่ส่วนอื่น) — ตาราง master `temp` + `TempController` + หน้า `temp.index` ยังคงอยู่
- **แปลง Order → แผนการผลิต** (`OrderController::convertplanning`, `/production-planning/order`, 10/08/2569): สร้าง `PlanningHeader` (`plan_type='ORDER'`) + `Planning` ต่อ 1 suborder — `tb_planning_header.remark` ดึงจาก `suborder.Remark` ของทุก suborder (ตัดค่าว่าง คั่นด้วย `, `, **เก็บทุกค่าแม้ซ้ำกัน**); ระวัง: ตาราง `morder` **ไม่มี** คอลัมน์ `Remark` (หมายเหตุอยู่ที่ `suborder.Remark` เท่านั้น). แต่ละ item ยังใส่ `red_bill_code = order.Orderno` และ `remark = suborder.Remark` เหมือนเดิม
- **Modal "แผนการผลิต"** (`production-planning/planning/planning-form.blade.php`): ตาราง "รายการ Planning" มีคอลัมน์ **เลขที่ใบเบิก** (`red_bill_code` ของแต่ละ planning) อยู่ก่อน Item No. (10/08/2569)
- **หน้าพนักงาน** (`EmpController`, `employee/index.blade.php`): มี dropdown กรองแผนก (`#searchDept` → param `dept`, กรอง `emp.dept` แบบ exact — `emp.dept` เก็บเป็นชื่อแผนก) คู่กับช่องค้นหาข้อความ (10/08/2569)
- **รายงานตามเครื่องจักร + จัดคิว drag & drop** (`Production\ReportController::machine/machineOptions/machineTable/machineQueueReorder/machineExcel/machinePdf`, route `production.report.machine.*`, blade `report/machine.blade.php` + partials `machine-table`/`machine-pdf`): จัดกลุ่มงาน (`tb_planning`) **ตามเครื่องจักร (`machine_no`)** → ภายในกลุ่มเรียงตาม **วัน (`day_key`)** → **คิว (`queue_sort`)** → เวลา (`job_key`) → id. ประกอบข้อมูลใน `buildMachineReport()` (ใช้ร่วมทั้งตาราง/Excel/PDF) join `tb_planning_header`+`customer`+`tb_products` (Resin/CODE/Pack/Batch/สูตร) + subquery `machine.speed_rpm` (คู่ `machine.MBX`=`machine_no`) + แนบขั้นตอน `tb_planning_prod_method` (steps). กรอง: แผนก(`dept`=company)/เครื่อง(`machine_no`)/ช่วงวันที่(`inplan`). **day_key / job_key อิง `tb_planning.inplan` (15/08/2569)**: `day_key` = วันของ `inplan`; `job_key` = วันของ inplan + เวลา `start_time` ของขั้นตอนแรก (ไม่มีเวลา → 00:00:00); งาน `inplan`=NULL → day_key/job_key = null ไปกลุ่มท้ายสุด **และลากจัดคิวไม่ได้**. **จัดคิว (drag & drop)**: SortableJS ลากทั้งงาน (`tbody.qjob`) ผ่าน handle `.qhandle`; `onMove` อนุญาตเฉพาะ **เครื่องเดียวกัน (`data-machine`) + วันเดียวกัน (`data-day`=`day_key`)** — กันข้ามเครื่อง/ข้ามวัน (การกันอยู่ **ฝั่ง client ล้วน**); `onEnd` ถ้าไม่ขยับจริง (oldIndex==newIndex) ไม่บันทึก, ไม่งั้นรวบรวม `planning_id` ในบล็อกเดียวกันตามลำดับบนจอ → POST `machineQueueReorder` เขียน `queue_sort=1..N` (เช็คแค่ ids เป็น array ไม่ว่าง + cast int; **ไม่** validate เครื่อง/วัน/สิทธิ์/มีจริง/transaction). บันทึกล้มเหลว → reload คืนลำดับจริง. เปลี่ยน `day_key` ที่ server ที่เดียว client ตามอัตโนมัติ (data-day = day_key)
- **รายงานผลิตตามพนักงาน (time-grid รายวัน)** (`Production\ReportController::employee/employeeTable/employeeExcel/employeePdf`, route `production.report.employee.*`, 11/08/2569): ฟอร์ม "แผนและการผลิตจริง" — **1 แถวกลุ่ม = 1 พนักงาน** (`tb_planning.empno` → `emp.empname/empsur`), **คอลัมน์ = ช่วงเวลา 9 ช่อง** นิยามคงที่ใน `timeSlots()` (8-9…16-17, OT; **ข้ามพักเที่ยง 12-13** จึงไม่มีคอลัมน์นั้น). แต่ละงาน (`tb_planning`) วางลงกริดจากเวลาใน `tb_planning_prod_method` (`start_time`/`end_time` เทียบ overlap ทีละช่อง) → แสดง รหัสสี(`itemno`)/รหัสเครื่อง(`machine_no`)/วิธีการผลิต(`tb_prod_method.name`) ในทุกช่องที่ครอบครอง, **จำนวน(`quantity`) โชว์เฉพาะช่องแรกสุด**. Fallback: ถ้า step ไม่มีเวลา ใช้ `tb_planning.start_time/end_time`; ถ้ายังไม่มี → แยกไปแถว "ไม่ระบุเวลา". งานที่ `empno` ว่าง → กลุ่ม "ไม่ระบุพนักงาน" ท้ายสุด. กรองด้วย แผนก(`dept`)+พนักงาน(`empno`, cascade จาก `employeeOptions`)+วันที่(`date`, เดี่ยว ค่าเริ่มต้นวันนี้). มี export PDF (mPDF, A4-L) + Excel คู่กัน — 2 แถวล่าง (ผู้ทวนสอบ/เวลา, ผู้ผลิต) เว้นว่างให้เซ็นมือตามฟอร์มกระดาษ
- **รายงานการขาดวัตถุดิบ** (`Production\ReportController::materialShortage/materialShortageTable/materialShortageExcel/materialShortagePdf`, route `production.report.material-shortage.*`, เมนูใต้ ProductionReport, 13/08/2569): ตารางแบนราบ (ไม่จัดกลุ่ม) ของงานใน `tb_planning` ที่ **ยังไม่ปิดงาน** (`end_job != 'Y'` **รวม NULL**) — คอลัมน์: เครื่องจักร/IN PLAN(`inplan`)/รหัส-ชื่อลูกค้า(`tb_planning_header.custno`+`customer.name`)/Order No(`tb_planning_header.orderno`)/รหัสสินค้า(`itemno`)/LOT/น้ำหนัก(`quantity`)/สถานะปัจจุบัน(`planning_status`). กรองแผนกด้วย `COALESCE(tb_planning.company, tb_planning_header.company)`, เรียง `inplan` เก่า→ใหม่ (ไม่มีวันที่ท้ายสุด). โหลดตารางผ่าน AJAX (partial `material-shortage-table`, ตารางบนเว็บ = ชุดคอลัมน์ย่อ). **Export Excel/PDF ใช้ผังคอลัมน์เต็มตามฟอร์ม Access เดิม (20 คอลัมน์)**: #, MACHINE No., IN PLAN, Revise(`senddate` กำหนดส่งทบทวน, 15/08/2569), สถานะปัจจุบัน(`planning_status`), Cust Due(`custwant` item→header), Cust no, Cust Name, SaleNo(`header.saleno`), Order Date(`header.mdate`), Order No, PRODUCT NO(`itemno`), LOT, น้ำหนัก(`quantity`, เดิมเคยแยก "น้ำ"/"หนัก" → รวมเป็นคอลัมน์เดียว 20/08/2569), ส่งชั่งสี*, เริ่มผลิต(`start_date`), วันที่ส่ง QC(`qc_date`), เวลาที่ส่ง QC(`qc_time`), สถานะ QC(`qc_status`) — *คอลัมน์ น้ำ/ส่งชั่งสี ยังไม่มีฟิลด์ใน DB จึงเว้นว่างไว้ตามผัง (Revise เดิมเว้นว่าง → ผูกกับ `tb_planning.senddate` แล้ว 15/08/2569 ทั้ง Excel คอลัมน์ F และ PDF); Excel=PhpSpreadsheet A..T, PDF=mPDF A4-L `material-shortage-pdf`. **เพิ่ม 1 คอลัมน์ "ขาด semi" ต่อจาก "สถานะปัจจุบัน" ทั้ง PDF และ Excel (15/08/2569)** — PDF พื้นเหลือง (`class="status"`) เหมือนสถานะปัจจุบัน, Excel = คอลัมน์ H (เลื่อน H..V เดิม → I..W, รวม 23 คอลัมน์). ดึงคำร้องขอ semi จาก `tb_semi_pigment` (`type='semi'`, `status` ∈ request/approved) **จับคู่ด้วย `planning_id` = `tb_planning.id`** (ไม่ใช่ itemno — เพราะ `tb_semi_pigment.itemno` = รหัสของตัว semi ที่ขาด ไม่ใช่ itemno ของงาน) + **เงื่อนไขปิดออเดอร์**: `LEFT JOIN tb_planning_header as ph` (ผ่าน `tb_semi_pigment.planning_header_id`) แล้วดึงเฉพาะ semi ที่ `ph.end_order != 'Y'` (รวม NULL) — semi ที่ปิดออเดอร์แล้วจะไม่ดึง. รวม 3 ฟิลด์จาก modal แก้ไข Semi (`itemno` ของ semi + `semi_code` + `primary_color`) ไว้ใน**คอลัมน์เดียว** คั่นด้วย ", " (ตัดค่าว่าง/ซ้ำ; งานที่ไม่มีคำร้องขอ semi = เว้นว่าง). assemble ใน `buildMaterialShortageReport()` → attach `lack_semi` ต่อแถว. **เพิ่ม 1 คอลัมน์ "ขาดวัตถุดิบ" ต่อจาก "ขาด semi" ทั้ง PDF และ Excel (20/08/2569; เปลี่ยนที่มา + เปลี่ยนชื่อหัวคอลัมน์ 25/08/2569)** — PDF พื้นเหลือง (`class="status"`), Excel = คอลัมน์ I (รวม 23 คอลัมน์ A..W หลังยุบ "น้ำ"/"หนัก" เป็น "น้ำหนัก" 20/08/2569). **เดิม (20/08/2569) หัวคอลัมน์ชื่อ "ขาด Pigment" และดึง `itemno` ของคำขอ pigment จากตาราง `tb_pigment` — ยกเลิกแล้ว**. ตอนนี้แสดง **ข้อความอิสระจาก `tb_planning.shortage_remark`** ของงานแถวนั้นตรง ๆ (ค่าที่ผู้ใช้พิมพ์เองในช่อง "ขาดวัตถุดิบ (Shortage Remark)" ของฟอร์มแก้ไข planning item — `planning-item-form.blade.php`, บันทึกผ่าน `ProductionPlanController::saveItem`): `buildMaterialShortageReport()` เพิ่ม `tb_planning.shortage_remark` ใน select แล้ว `$row->lack_pigment = trim((string) $row->shortage_remark)` (ชื่อตัวแปร `lack_pigment` คงเดิมเพื่อไม่ต้องแก้ blade/Excel ที่อ่านค่า). **คิวรี `tb_pigment` (Model `Pigment`) ในเมธอดนี้ถูกลบออกแล้ว** พร้อม import `App\Models\Pigment` (ฟีเจอร์ pigment เดิม/ตาราง `tb_pigment` ไม่ถูกแตะ ยังใช้ที่ `PigmentController`). **หมายเหตุ:** ยังไม่มีตารางสต๊อกวัตถุดิบผูก planning ใน DB → รายงานนี้ = "งานค้าง (ยังไม่ปิดงาน)"; หากต้องเช็คปริมาณวัตถุดิบจริงภายหลัง ให้เสริมเงื่อนไขใน `buildMaterialShortageReport()`

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
- `insert` → ปล่อย `appv` ว่าง ⇒ ใบใหม่ไหลเข้าคิวอนุมัติเอง (ดูฟอร์ม morderAPPV ด้านล่าง)
- `update` → **ไม่แตะ** `Orderno` / `appv` / `appvDT`
- รายการ (`syncItems`) เทียบกับของเดิมทีละแถว: มี `Runno` = แก้ไข · ไม่มี = เพิ่ม · หายไปจากฟอร์ม = ลบ — **คง `Runno` เดิมไว้เสมอ** (เป็นเลขอ้างอิงของระบบเดิม อย่าลบทั้งใบแล้วใส่ใหม่)
- แถวที่ยังไม่กรอก `Itemno` = แถวเปล่า ระบบข้ามให้ ไม่บันทึก
- ⚠ route `POST order/save` ป้องกันแค่ `auth` + สิทธิ์ `order read` จาก `config/menu.php` — **ยังไม่มีสิทธิ์แยกสำหรับการเขียน**
- **ข้อความยาวเกินคอลัมน์ถูกตัดให้พอดีอัตโนมัติ** (`clampToColumns()`, 25/08/2569) — ครอบทั้ง `headerPayload()` (morder) และ `syncItems()` (suborder). ตาราง legacy คอลัมน์สั้นมาก (`suborder.prodname` = **varchar(20)**) แต่ชื่อสินค้าที่ `itemLookup()` เติมให้มาจาก `uprice.Label` = varchar(85) ซึ่ง **ยาวเกิน 20 อยู่ 1,466 จาก 9,453 แถว (15.5%)** และ MySQL เปิด `STRICT_TRANS_TABLES` ⇒ เดิมจะได้ **error 1406 Data too long** บันทึกไม่ได้เลย (ระบบ Access เดิมตัดให้เงียบ ๆ — ค่าที่มีอยู่ใน `suborder.prodname` ยาวสุดพอดี 20 ตัว). ตัวช่วยอ่านความยาวจริงจาก schema (ไม่ hardcode) แล้วตัดด้วย `mb_substr` กันตัวอักษรไทยขาดกลางตัว — คอลัมน์ `text`/ตัวเลข/วันที่ไม่แตะ. ฝั่งจอใส่ `maxlength` ให้ช่อง Itemno/prodname/Lotno/outno (20) และตัดค่าที่เติมอัตโนมัติเหลือ 20 ตัวก่อนใส่ช่อง

### ช่อง itype: เปลี่ยนความหมาย + ใบที่ขึ้นต้นด้วย W ต้องเลือก — 29/08/2569

🔴 **ยังทำแค่ UI — ค่าที่เลือกไม่ได้บันทึกลง DB** (ผู้ใช้สั่งให้ทำ UI ก่อน) ⇒ ปิดฟอร์มแล้วค่าหาย และเปิดใบเดิมขึ้นมาช่องนี้จะว่างเสมอ

**ความหมายเปลี่ยนไปจากเดิมทั้งหมด** — อย่าเอาโค้ด/เอกสารเก่ามาอ้าง:

| | เดิม | ตอนนี้ |
|---|---|---|
| คืออะไร | ประเภทอุตสาหกรรมของ**ลูกค้า** | **ประเภทสินค้าที่สั่ง** ของใบนั้น |
| ที่มา | `customer.type` → `c_type.t_namee` (อ่านอย่างเดียว) | ผู้ใช้กดที่ช่องแล้วติ๊กเลือกเอง |
| ตัวเลือก | ตาราง `c_type` (15 แถว) | **`config/order.php` → `itypes`** (6 ข้อ) |
| จำนวนที่เลือกได้ | — | **ข้อเดียว** (ติ๊กข้อใหม่ → ปลดข้อเก่าให้เอง) |

- **ตาราง `c_type` ไม่เกี่ยวกับช่องนี้แล้ว** — `customerLookup()` ยังคืน `type`/`type_name` มาเหมือนเดิม แต่ฝั่งจอเลิกเอาไปเติม `#o_itype` แล้ว
- **UI — ติ๊กเลือกบนฟอร์มได้เลย (01/09/2569)**: `order/form.blade.php` แสดง checkbox ทั้ง 6 ข้อ (`.o-itype-opt` ตาม `config('order.itypes')`) เรียงเป็นแถวใน `<div id="o_itype_box" class="of-itype-box">` + `.of-checkrow` เต็มความกว้าง (`col-12`) · **เดิม (29/08/2569) เป็น input readonly หน้าตาเหมือน select ที่ `data-bs-toggle="dropdown"` กดแล้วกาง `<ul id="o_itype_menu">` — ถอดออกแล้ว** พร้อมช่อง `#o_itype` และฟังก์ชัน `syncItypeText()` ที่เอาข้อที่ติ๊กมาโชว์ในช่องนั้น · ฝั่ง JS (`order/index.blade.php`): handler `.o-itype-opt` ยัง**บังคับให้ติ๊กได้ข้อเดียว**เหมือนเดิม (ติ๊กข้อใหม่ → ปลดข้อเก่า) แล้วเรียก `syncItypeRequired()` ตรง ๆ · `syncItypeRequired()` เลิกอ่านค่าจากช่องข้อความ เปลี่ยนไปนับ `.o-itype-opt:checked` และขึ้นกรอบแดงที่ `#o_itype_box` (CSS `.of-itype-box.is-invalid` ใน `order/index.blade.php`) แทนที่จะใส่ `is-invalid` ที่ input
- **key ใน config = รหัสที่จะเอาไปเก็บลง DB ตอนได้ที่เก็บแล้ว — ห้ามเปลี่ยน/ใช้ซ้ำ**
- ⚠ **ยังไม่มีที่เก็บ**: ค้นทั้ง DB แล้ว**ไม่มีตารางไหนเก็บตัวเลือกชุดนี้** และ `morder` **ไม่มีคอลัมน์รองรับ** (25 คอลัมน์ ไม่มีตัวไหนใช้ได้) ⇒ ต้องตกลงกันก่อนว่าจะเพิ่มคอลัมน์ `morder.itype` หรือทำตารางลูก

**กติกา "ใบ W ต้องเลือก itype"** (WM / WI / WE / WR) — ตัวอักษรแรกของเลขที่ใบสั่ง:
- ฝั่งจอ (`order/index.blade.php`): `orderPrefix()` / `itypeRequired()` / `syncItypeRequired()` — โชว์ `*` แดงข้างป้าย itype เฉพาะตอนขึ้นต้นด้วย W + ขึ้นกรอบแดง (`is-invalid`) เมื่อยังไม่ได้เลือก, และ `saveOrder()` เตือนด้วย Swal ก่อนยิง request. `syncItypeRequired()` ถูกเรียกทุกจุดที่ prefix หรือ itype เปลี่ยน (เลือกประเภท / รับเลขที่ใบใหม่ / เปิดใบเดิม / ล้างฟอร์ม / ติ๊กตัวเลือก)
- 🔴 **ด่านฝั่ง server ถูกคอมเมนต์ปิดไว้** ใน `OrderController::save()` — ของเดิมตรวจกับ `customer.type` ซึ่งคนละความหมายกับช่องนี้แล้ว ถ้าเปิดทิ้งไว้ผู้ใช้จะโดน 422 เพราะค่าที่มองไม่เห็นและแก้ในฟอร์มนี้ไม่ได้ (ลูกค้า **2,244 จาก 4,094 ราย ไม่มี `customer.type`**) ⇒ **ตอนนี้บังคับได้แค่ฝั่งจอ** ยิง POST ตรงยังผ่าน. เปิดคืนเมื่อได้ที่เก็บ itype แล้ว โดยเปลี่ยนไปตรวจค่าที่ส่งมาจากฟอร์มแทน `customer.type`

### ด่านราคา: ราคาขายต้องไม่ต่ำกว่าราคาช่อง 2 — 21/08/2569

ลำดับงานจริงที่ระบบบังคับ: **บันทึกใบสั่งซื้อไม่ผ่านเพราะราคา → ทำใบขออนุมัติราคาพิเศษ → MD อนุมัติ → กลับมาบันทึกใบสั่งซื้อได้ → ใบไหลเข้าคิวอนุมัติใบสั่งซื้อ**

`OrderController::checkPriceFloor()` เรียกก่อนเข้าทรานแซกชันใน `save()` — **บังคับทั้ง `insert` และ `update`**:

| กรณี | ผล |
|---|---|
| **มีราคาอนุมัติที่ยังยืนราคาอยู่** · ราคาขาย ≥ ราคาอนุมัติ | ผ่าน |
| **มีราคาอนุมัติที่ยังยืนราคาอยู่** · ราคาขาย < ราคาอนุมัติ | **422** + `price_blocked: true` (แม้ราคาขายจะ ≥ ราคาช่อง 2) |
| ไม่มีราคาอนุมัติที่ใช้ได้ · ราคาขาย ≥ ราคาช่อง 2 | ผ่าน |
| ไม่มีราคาอนุมัติที่ใช้ได้ · ราคาขาย < ราคาช่อง 2 | **422** + `price_blocked: true` |
| ไม่กรอกราคาขาย (แต่คำนวณเกณฑ์ได้) | **422** |

- **เกณฑ์ = "ราคาอนุมัติ" ถ้ายังยืนราคาอยู่ · ไม่งั้นใช้ราคาช่อง 2** (`approvedFloor()`, เปลี่ยน 28/08/2569 ตามที่ผู้ใช้สั่ง)
  - **ราคาอนุมัติ = `appvreq.price`** ของใบล่าสุดของคู่ (ลูกค้า, เบอร์) ที่ **ติ๊ก `Appv` แล้ว** — ตรงกับช่อง "ราคาอนุมัติ" ที่ผู้ใช้เห็นในกล่องราคา · ⚠ ต้องกรอง `Appv` เสมอ ไม่งั้นแค่ "ขอ" ราคาต่ำ ๆ ก็ปลดล็อกด่านได้เองโดยไม่ผ่าน MD
  - **ยืนราคาถึง = `zcustprice.enddate`** ของคู่เดียวกัน — เลยวันแล้ว = ใช้ไม่ได้ · `enddate` ว่าง = ไม่กำหนดวันหมดอายุ (ใช้ได้) · **ไม่มีแถวใน `zcustprice` เลย = ยังไม่ได้ยืนราคา ⇒ ใช้ไม่ได้**
  - **ยึดราคาอนุมัติเสมอเมื่อใช้ได้ แม้จะสูงกว่าราคาช่อง 2** (ผู้ใช้เลือกไว้) ⇒ กรณีนั้นเกณฑ์ *เข้มขึ้น* กว่าเดิม — จากการสุ่มเทียบ 279 คู่ พบราคาอนุมัติสูงกว่าราคาช่อง 2 อยู่ **76 คู่ (27%)**
  - เดิม (21/08/2569) ใช้ราคาช่อง 2 เป็นหลักแล้วให้ `zcustprice.exprice` เป็น *ทางรอดสำรอง* — เมธอด `activeApprovedPrice()` ที่ทำหน้าที่นั้น **ยังอยู่ในไฟล์แต่ไม่ถูกเรียกจาก `checkPriceFloor()` แล้ว**
  - `checkPriceFloor()` แนบ `floor_from` (`approved` / `price_2`) มาใน response และข้อความ error เรียกชื่อเกณฑ์ให้ตรงกับที่ใช้จริง
  - **กล่องราคาบนจอใช้ตัวเดียวกัน** — `priceData()` คำนวณ `min_price` ด้วย `approvedFloor()` ตัวเดียวกัน + ส่ง `min_from` ไปให้จอ (ช่อง "ราคาต้องไม่ต่ำกว่า" มี `title` บอกที่มา และบรรทัดใต้กล่องราคาขึ้นข้อความ "ราคาต้องไม่ต่ำกว่า = ราคาอนุมัติ … (ยืนราคาถึง …)" เมื่อเกณฑ์มาจากราคาอนุมัติ) — ผู้ใช้จะได้ไม่โดนบล็อกด้วยตัวเลขที่ไม่เห็นบนจอ
  - ⚠ **ตอนนี้ยังไม่มีผลกับข้อมูลจริงเลย** — สำรวจ 28/08/2569: คู่ที่มีราคาอนุมัติ 2,577 คู่ แต่ **หมดอายุแล้ว 2,569 คู่ · ยังไม่หมดอายุ 0 คู่** (`enddate` ล่าสุดในระบบคือ 30/05/2026) ⇒ ทุกใบยังเทียบกับราคาช่อง 2 เหมือนเดิม จะเห็นผลก็ต่อเมื่อ MD อนุมัติราคาใหม่พร้อมใส่ "อนุมัติราคาถึง" เป็นวันในอนาคต
- ⚠ **คำนวณราคาช่อง 2 ไม่ได้ = ปล่อยผ่าน** — เดิม (21/08/2569) รหัสสินค้าที่ใช้จริงในใบสั่ง **1,362 จาก 1,382 รหัส (98.6%) ไม่มีใน `access_pdprice`** ⇒ ด่านนี้ทำงานกับแค่ ~20 รหัส จึงตั้งใจปล่อยผ่านตอนคำนวณไม่ได้ **ตรรกะข้อนี้ยังอยู่เหมือนเดิม แต่เงื่อนไขข้อมูลเปลี่ยนไปแล้ว**
- 🔴 **ด่านราคาเริ่มบังคับจริงเกือบทั้งระบบแล้ว หลังอัพเดทราคาทุน 27/08/2569** — นำเข้า `access_pdprice` จาก `formula_2000_AddData.mdb` (297 → 47,461 แถว) ทำให้ **คำนวณราคาช่อง 2 ได้ 1,340 จาก 1,385 รหัส (96.8%)** (ที่เหลือ 45: 38 รหัสตกเงื่อนไข "ขึ้นต้นด้วย 1 อื่น ๆ" ที่ตั้งสูตรไว้ 0/0/0 + 7 รหัสไม่มีราคาทุน)
  - **ประเมินจากใบสั่งซื้อ 500 ใบล่าสุด: ~32% (155/479) มีราคาขายต่ำกว่าราคาช่อง 2** ⇒ ถ้าบันทึกซ้ำวันนี้จะโดน 422 `price_blocked` และต้องไปทำใบขออนุมัติราคาพิเศษก่อน
  - ยิ่งหนักเพราะ `zcustprice` ที่ใช้ปลดล็อก **หมดอายุทั้ง 5,405 แถว** (ดูหัวข้อราคาอนุมัติพิเศษ) ⇒ ของเก่าปลดล็อกไม่ได้สักใบ ต้องอนุมัติใหม่พร้อมใส่ "อนุมัติราคาถึง" เป็นวันในอนาคต
  - **ยังไม่ได้แก้โค้ดใด ๆ รองรับ** — ถ้าผู้ใช้รับภาระนี้ไม่ไหว ทางเลือกคือผ่อนด่าน (เช่น เตือนแทนบล็อก / บล็อกเฉพาะบางกลุ่มรหัส) ที่ `OrderController::checkPriceFloor()` จุดเดียว
- ⚠ **ตรวจกับรหัสสินค้าแถวแรกที่กรอกเท่านั้น** — ให้ตรงกับกล่องราคาบนฟอร์ม (ผู้ใช้กรอกรหัสเบอร์เดียวทั้งใบอยู่แล้ว) ไม่งั้นจะเตือนด้วยตัวเลขที่ผู้ใช้ไม่เห็นบนจอ
- **ราคาอนุมัติพิเศษอ่านจาก `zcustprice`** (`activeApprovedPrice()`): ต้องมี `exprice` (ไม่ใช่ 0) และ **ยังไม่เลย `enddate`** (`enddate` ว่าง = ไม่กำหนดวันหมดอายุ). ⚠ ข้อมูลปัจจุบัน `zcustprice` **ทั้ง 5,405 แถวหมดอายุหมดแล้ว** (`enddate` ล่าสุด 30/05/2026) ⇒ ราคาอนุมัติเก่าใช้ปลดล็อกไม่ได้สักใบ ต้องให้ MD อนุมัติใหม่พร้อมใส่ "อนุมัติราคาถึง" เป็นวันในอนาคต
- ฝั่งจอ (`showPriceBlocked()` ใน `order/index.blade.php`): 422 ที่มี `price_blocked` จะเด้ง Swal พร้อมปุ่ม **"ขออนุมัติราคาพิเศษ"** → ปิด modal ใบสั่งซื้อแล้วเปิด `approvalOpen(custno, itemno)` ให้ต่อได้ทันที (ไม่ใช่แค่บอกว่าบันทึกไม่ได้)
- response ตอนบล็อกแนบ `custno` / `itemno` / `min_price` / `price` / `approved` มาด้วย เผื่อเอาไปแสดงเพิ่ม

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

**ช่องในตารางรายการโตตามข้อความที่พิมพ์** (`oiAutoGrow()` / `oiAutoGrowAll()` ใน `order/index.blade.php`, 28/08/2569) — วัดความกว้างข้อความจริงด้วย canvas `measureText` แล้วตั้ง **`min-width` แบบ inline** ที่ตัว input (จงใจไม่ตั้ง `width` เพราะ `form-control` เป็น `width:100%` อยู่แล้ว — ตั้ง min-width ทำให้ `<td>` ถูกบังคับให้กว้างตาม โดย input ยังเต็มช่องเหมือนเดิม) ขอบเขต `OI_MIN_W` 80px – `OI_MAX_W` 420px
  - เรียกที่: `renderOrderItems()` · `addOrderItem()` · `applyItemLookup()` (เติม prodname ด้วย `.val()` ไม่ยิง event ต้องสั่งเอง) และ delegated event `input change blur` (`change` = flatpickr, `blur` = js-comma จัดรูปแบบตัวเลขใหม่)
  - ⚠ ต้องเรียกซ้ำตอน **`shown.bs.modal`** ด้วย — ตอน modal ซ่อนอยู่ `getComputedStyle` วัดไม่ได้ (ฟังก์ชันมี guard `fontSize === 0` กันไว้ด้วย)
  - ผลที่เห็นถูกจำกัดด้วย 2 อย่างที่ยังคงไว้ตามเดิม: `maxlength="20"` ของช่อง Itemno/prodname/Lotno/outno (ตามขนาดคอลัมน์จริง ดู `clampToColumns()`) และ `min-width` ที่ `<th>` ใน `order/form.blade.php` (เช่น หมายเหตุ 260px) ⇒ คอลัมน์**ขยายได้แต่ไม่หด** ถ้าต้องการให้หดตามด้วยต้องลด min-width ที่ `<th>`

**ปฏิทินของช่องวันที่ในตารางรายการใช้ `static:false` — ต่างจากทั้งระบบที่ใช้ `static:true`** (`initRowPickers()`, 28/08/2569) เพราะตารางนี้อยู่ใน `.table-responsive` ที่มี `overflow-x:auto` — ตามสเปก CSS พอแกนหนึ่งไม่ใช่ `visible` อีกแกนที่เป็น `visible` จะถูกบังคับเป็น `auto` ด้วย ⇒ ปฏิทินที่ฝังไว้ใน `<td>` (`static:true`) ถูก clip เหลือแค่แถบหัวเดือน. `static:false` ให้ flatpickr ย้ายปฏิทินไป `<body>` แล้วคำนวณตำแหน่งเอง จึงไม่โดนตัด
  - ⚠ **ต้องมาคู่กับ CSS `.flatpickr-calendar { z-index: 1092 }` เสมอ** — `flatpickr.css` ของ theme ตั้งไว้แค่ **999** ซึ่งต่ำกว่า modal (`--bs-modal-zindex: 1090`) ปฏิทินจะจมอยู่ใต้ modal มองไม่เห็นเลย. เลือก 1092 ให้อยู่เหนือ modal/popover(1091) แต่ยังต่ำกว่า toast(1095)/tooltip(1099)
  - ผลข้างเคียงของการย้ายไป `<body>`: ปฏิทินไม่เลื่อนตามเวลาผู้ใช้เลื่อนตารางหรือ modal → `onOpen`/`onClose` ผูก-ถอด `scroll.wpfp` บนกล่องเลื่อนของตาราง + ตัว modal เพื่อ **ปิดปฏิทินให้เองเมื่อมีการเลื่อน** กันปฏิทินค้างลอยผิดที่
  - ช่องวันที่**นอก**ตาราง (`.flatpickr-date` / `.flatpickr-datetime`) ยังใช้ `static:true` ตามเดิม ไม่ได้แก้

**ผู้ใช้กรอกรหัสสินค้าเบอร์เดียวทั้งใบ** → กล่องราคาด้านขวาผูกกับรหัสของ**แถวแรกที่กรอก** (`syncItemnoToPrice` → `refreshOrderPrice`) และ recalc ใหม่เมื่อเปลี่ยนลูกค้า/รหัสสินค้า/น้ำหนักรวม

**เติมชื่อสินค้าอัตโนมัติทุกครั้งที่เปลี่ยนรหัสสินค้า** (`applyItemLookup()`, 28/08/2569) — เดิมเช็คแค่ `!$name.val()` (เติมเฉพาะตอนช่องว่าง) จึงเติมได้**ครั้งเดียว** เปลี่ยนรหัสรอบต่อไปชื่อค้างของเบอร์เก่า. ตอนนี้จำชื่อที่ระบบเติมไว้ที่ `data-autofill` ของช่อง แล้วเติมทับเมื่อ **ช่องว่าง** หรือ **ค่าปัจจุบัน = ค่าที่ระบบเติมไว้เอง** เท่านั้น
  - ผู้ใช้พิมพ์/แก้ชื่อสินค้าเอง → delegated event `input` ลบ `data-autofill` ทิ้ง ⇒ ชื่อนั้นจะไม่ถูกทับอีก
  - **ชื่อที่โหลดมาจากใบเดิม (DB) ไม่มี `data-autofill` จึงไม่ถูกทับ** — ถ้าต้องการให้ทับด้วยเมื่อเปลี่ยนรหัส ต้องเพิ่ม `data-autofill` ตอน render ใน `orderItemRow()`
  - แก้คู่กัน: ตัวจับเวลา debounce ย้ายจากตัวแปรกลางตัวเดียว (`itemLookupTimer`) ไปเก็บที่ช่องแต่ละช่อง (`el._lookupTimer`) — เดิมพิมพ์แถวถัดไปเร็ว ๆ จะไป `clearTimeout` ของแถวก่อนหน้าทิ้ง แถวนั้นเลยไม่ถูกดึงข้อมูล (อาการเหมือนกัน: "ดึงได้แค่ครั้งแรก")

**แถบเตือน Match ใหม่ + กล่องราคา เช็คใหม่เมื่อรหัสอ้างอิงหายไป** (`refreshItemContext()`, 28/08/2569) — ทั้งสองอย่างผูกกับ "รหัสสินค้าของแถวแรกที่กรอกไว้" ถ้ารหัสนั้นหายไปต้องคำนวณใหม่ ไม่งั้นค้างเป็นของเบอร์ที่ไม่มีในใบแล้ว
  - เรียกจาก 2 ทาง: **กดลบแถว** (`removeOrderItem()` — เดิมไม่ได้เรียกอะไรเลย ทั้งแถบเตือนและกล่องราคาจึงค้าง) และ **ลบข้อความในช่องรหัสจนว่าง** (`applyItemLookup()` — เดิมเรียกแค่ `syncItemnoToPrice()` แถบเตือนไม่ถูกซ่อน)
  - ตรรกะ: หาช่อง `Itemno` แรกที่ยังมีค่า → มี = `applyItemLookup()` ของช่องนั้น (จัดการแถบเตือน+ราคาให้ในตัว) · ไม่มีเลย = `showMatchWarning(null)` ซ่อนแถบ + `syncItemnoToPrice()` ล้างกล่องราคา
  - **ไม่วนซ้ำ** เพราะ `refreshItemContext()` เลือกเฉพาะช่องที่ *มีค่า* — ช่องว่างที่เรียกเข้ามาจะไม่ถูกเลือกซ้ำ

**แถบเตือน "สีที่สั่งซื้อล่าสุดเกิน 3 ปี จะต้อง Match ใหม่"** (`OrderController::itemLookup`, 18/08/2569): เทียบ `MAX(morder.Mdate)` ของทุกใบที่มีเบอร์นั้น (**ไม่แยกตามลูกค้า** เพราะการ Match เป็นเรื่องของสูตรสี) — เกิน 3 ปีหรือไม่เคยสั่งเลย = ขึ้นแถบแดง; endpoint เดียวกันคืน `prodname` มาเติมช่องชื่อสินค้าให้เมื่อยังว่าง

**เติมชื่อสินค้า + หมายเหตุ อัตโนมัติตอนกรอกรหัสสินค้า** (`OrderController::itemLookup`, เพิ่มหมายเหตุ 28/08/2569): endpoint เดียวกันคืน `remark` = **`uprice.Label`** มาเติมช่อง **หมายเหตุ (`suborder.Remark`)** ของแถวนั้นด้วย
- รับพารามิเตอร์ `custno` เพิ่ม → เลือกแถว `uprice` ของ**ลูกค้ารายนั้นก่อน** (`CustNo`+`ITEMNO`, `DATE` ล่าสุด) ไม่มีค่อย fallback เป็นแถวล่าสุดของลูกค้าใดก็ได้ เพราะเบอร์เดียวกันของคนละลูกค้า `Label` ต่างกันได้ (**1,177 จาก 36,432 เบอร์**) — แถวเดียวกันนี้ยังเป็น fallback ของ `prodname` เหมือนเดิม
- ฝั่งจอ (`applyItemLookup()` ใน `order/index.blade.php`) ใช้กติกา **`data-autofill`** ชุดเดียวกับช่องชื่อสินค้า: เติมทับได้เฉพาะตอนช่องว่าง หรือค่าปัจจุบัน = ค่าที่ระบบเติมไว้เอง ⇒ **ไม่ทับหมายเหตุที่ผู้ใช้พิมพ์เอง / ที่บันทึกไว้ในใบเดิม**
- `suborder.Remark` เป็น `text` จึงไม่ต้องตัดความยาว (ต่างจาก `prodname` = varchar(20) ที่ยังตัดที่ 20 ตัว)

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

**2 ขั้นตอนของฟอร์ม** (21/08/2569) — ฟอร์มเดียวใช้ทั้งขาขอและขาอนุมัติ สลับด้วย "โหมด":

| โหมด | เข้าอย่างไร | ทำอะไรได้ |
|---|---|---|
| **ขอราคา** — ค่าเริ่มต้น | เปิดฟอร์มมาก็อยู่โหมดนี้ | เลือกลูกค้า+สินค้า แล้วกรอก/แก้ใบขอราคา · ช่อง **"อนุมัติ"** และ **"อนุมัติราคาถึง"** ถูก `disabled` |
| **อนุมัติ** | กรอกรหัสที่หัวฟอร์ม → กดปุ่ม **"เข้าสู่โหมดอนุมัติ"** (หรือกด Enter) | เลือกลูกค้า+สินค้าเพื่อดูใบที่ขอไว้ แล้วติ๊กอนุมัติ + ใส่วันที่ยืนราคา |

⚠ **บนจอไม่เรียกรหัสนี้ว่า "รหัสผ่าน MD" แล้ว** (21/08/2569) — ฟอร์ม Access เดิมเขียนว่า "รหัสผ่าน MD (เพื่ออนุมัติ)" แต่ยังยืนยันไม่ได้ว่าเป็นของ MD โดยเฉพาะ ป้ายบนจอจึงเป็น **"กรอกรหัสเพื่อเข้าสู่โหมดอนุมัติ"** และข้อความเตือน/badge ทั้งหมดตัดคำว่า MD ออก · ฝั่งโค้ดยังใช้ชื่อ `md_password` / `checkMdPassword()` / `MD_SESSION_KEY` ตามเดิม (ได้ที่เก็บจริงเมื่อไหร่ค่อยเปลี่ยนชื่อทีเดียว) · **input ในฟอร์มนี้ไม่ใส่ `placeholder` เลย**

**การปลดล็อกเก็บที่ session ฝั่ง server ไม่ใช่แค่ซ่อน/โชว์ปุ่ม** — `unlock()` ตรวจรหัสแล้วเขียน session `price_approval_md_until` (อายุ `MD_UNLOCK_MINUTES` = 30 นาที), `lock()` ล้างทิ้ง, `mdState()` ให้ฟอร์มถามตอนเปิด, และ `data()` แนบ `md_unlocked` มาทุกครั้งเพื่อ sync จอกับ server (session หมดอายุระหว่างเปิดฟอร์มค้างไว้ → จอล็อกกลับเอง). `save()` ตรวจ **session** เสมอ — เปิด devtools ปลด `disabled` เองก็อนุมัติไม่ได้ (ได้ 422 + `md_locked: true` แล้วจอล็อกกลับ)

**กติกาการบันทึก** (`save()`): เขียน `appvreq` (คีย์ `ReqDate` + `custno` + `itemno`) — **1 คู่ (ลูกค้า, เบอร์) = 1 ใบที่แก้ได้**: server หา `ReqDate` ของ**ใบล่าสุด**ของคู่นั้นเอง (มี = แก้ทับ, ไม่มี = ขึ้นใบใหม่ด้วยเวลาปัจจุบัน) — **client ไม่ส่ง `ReqDate` มาแล้ว** กันเผลอสร้างใบซ้ำ/ใบผิดวัน · **ติ๊ก "อนุมัติ" ต้องอยู่ในโหมดอนุมัติ** ไม่งั้น 422 — **ยกเว้นใบที่อนุมัติไปแล้ว** ฝั่ง MK แก้หมายเหตุ/ราคาต่อได้โดยไม่ต้องปลดล็อก (checkbox ถูก disabled ค่าที่ส่งมาจึงเป็นสถานะเดิม ไม่ใช่การอนุมัติใหม่) · เขียน `zcustprice` (`exprice` = ราคาขายครั้งนี้, `enddate` = อนุมัติราคาถึง) **เฉพาะตอนอนุมัติจริงในโหมด MD** — ไม่งั้น MK ที่แก้ใบซึ่งอนุมัติแล้วจะเผลอทับ `enddate` ด้วยช่องที่ถูกล็อกไว้ (ค่าว่าง) · `destroy()` ลบเฉพาะแถว `appvreq` **ไม่แตะ `zcustprice`**

**ช่อง "อนุมัติราคาถึง" (`zcustprice.enddate`) มีค่าเริ่มต้น = "วันทำการถัดไป" ข้ามวันหยุด** (29/08/2569 = วันนี้+1 · **เปลี่ยนเป็นข้ามวันหยุด 01/09/2569** ตามที่ผู้ใช้สั่ง)
- **ตรรกะอยู่ที่เดียว: `App\Services\HolidayService::nextWorkingDay()`** — วันหยุด = **วันอาทิตย์** (`config/holiday.php` → `weekly_off`, ผู้ใช้ยืนยันว่า**ไม่หยุดเสาร์**) **+ `tb_holiday` ที่ `is_active='Y'`** · เช่น วันนี้ที่ 1 · วันที่ 2 หยุด → ได้วันที่ 3 · ก่อนสงกรานต์ 12 เม.ย. 2026 → ได้ 16 เม.ย.
- 🔴 **JS คำนวณเองไม่ได้แล้ว เพราะไม่รู้จักวันหยุด** — ฟังก์ชัน `tomorrowYmd()` เดิมใน `order/index.blade.php` **ถูกแทนด้วย `defaultValidToYmd()` ที่อ่านค่าที่ server ส่งมา**:
  - ตอนโหลดหน้า: `OrderController::index` ส่ง `$default_valid_to` → blade ฝังเป็นตัวแปร `APPROVAL_DEFAULT_VALID_TO`
  - ทุกครั้งที่เลือกลูกค้า+เบอร์: `PriceApprovalController::data()` แนบ `default_valid_to` มา → `fillApprovalData()` เขียนทับตัวแปรนั้น (กันค่าค้างเมื่อเปิดฟอร์มทิ้งไว้ข้ามวัน/ข้ามวันหยุด)
  - `defaultValidToYmd()` มี fallback เป็น "พรุ่งนี้ตรง ๆ" เฉพาะกรณี server ไม่ได้ส่งค่ามา — **อย่าใช้ค่านี้เป็นทางหลัก** เพราะไม่ข้ามวันหยุด
  - ใช้ที่ `clearApprovalForm()` (เปิดฟอร์มใหม่) และ `fillApprovalData()` **เฉพาะเมื่อเบอร์นั้นยังไม่เคยยืนราคา** (`res.rows[0].enddate` ว่าง) — ถ้ามีค่าเดิมอยู่ยังโชว์ค่าเดิมตามปกติ
- **ฝั่ง server** `PriceApprovalController::defaultValidTo()` (public static — `OrderController::index` เรียกด้วย) → `save()` เติมให้เองเมื่อ `valid_to` ที่ส่งมาว่างหรือรูปแบบผิด (เผลอลบวันที่ทิ้ง) จึงไม่ต้องพึ่งจอ
- `HolidayService` ครอบด้วย `Schema::hasTable('tb_holiday')` — server ที่ยังไม่ได้รัน migration/SQL จะเหลือแค่ข้ามวันอาทิตย์ ไม่ throw ทำให้ฟอร์มพัง · cache รายชื่อวันหยุดต่อ 1 request (`flush()` ไว้ใช้ในสคริปต์/เทสต์ที่แก้ข้อมูลแล้วคำนวณต่อ)
- ⚠ **ห้ามปล่อย `enddate` เป็น null** — `activeApprovedPrice()` ถือว่า "ว่าง = ไม่กำหนดวันหมดอายุ" ⇒ ราคาพิเศษใบนั้นจะปลดล็อกด่านราคาใน `OrderController::checkPriceFloor()` ได้ตลอดไป (ปัจจุบัน `zcustprice` มีแถวที่ `enddate` ว่างอยู่ 1 จาก 5,406 แถว)
- ⚠ **ยังไม่ได้ทำ: เบอร์ที่มี `enddate` เดิมซึ่ง "หมดอายุไปแล้ว" ฟอร์มยังโชว์วันเดิมนั้น ไม่ได้เด้งเป็นพรุ่งนี้ให้** — ข้อมูลจริงตอนนี้ `zcustprice` **หมดอายุครบทุกแถว** (`enddate` ล่าสุด 30/05/2026) ⇒ เปิดใบเก่าจะเจอวันที่หมดอายุค้างอยู่ ต้องแก้เอง (วันเดิมยังดูได้ที่ตารางล่างของฟอร์ม) — ถ้าต้องการให้เด้งเป็นพรุ่งนี้เมื่อวันเดิมเลยมาแล้ว ให้เพิ่มเงื่อนไขที่ `fillApprovalData()` จุดเดียว

**แถบบอกขั้นตอน `#a_reqState`** ใต้หัวฟอร์ม (`renderApprovalReqState()`): เทา = ยังไม่เคยขอราคาคู่นี้ (จะขึ้นใบใหม่) · เหลือง = มีใบเดิม รออนุมัติ · เขียว = อนุมัติแล้ว — ทั้งสองแบบหลังบอกว่ากดบันทึกจะแก้ทับใบเดิม

**ราคา 3 ช่องบนฟอร์ม = ราคาขาย 1/2/3 จากเมนู "กำหนดราคา" คำนวณสด ๆ จากรหัสสินค้าเสมอ** (21/08/2569)

🔴 **กับดักหลังอัพเดทราคาทุน 27/08/2569 — เปิดใบเก่าแล้วกดบันทึก = สำเนาราคาตอนที่ขอถูกทับ** เดิมใบเก่าส่วนใหญ่คำนวณสดไม่ได้ (ไม่มีรหัสใน `access_pdprice`) ฟอร์มจึงโชว์ค่าที่บันทึกไว้ใน `appvreq.price1/2/3` แล้วบันทึกทับด้วยค่าเดิม = ไม่เปลี่ยนอะไร. ตอนราคาทุนครบแล้ว **คำนวณสดได้เกือบทุกใบ ⇒ ฟอร์มโชว์ราคาปัจจุบัน และ `save()` (บรรทัด ~398) เขียนค่าที่โชว์นั้นลง `price1/2/3` ตรง ๆ** ⇒ MK เปิดใบเก่ามาแก้แค่หมายเหตุแล้วกดบันทึก สำเนาราคาตอนที่ขอก็หาย
  - สำรวจ `appvreq` 400 ใบที่มี `price2`: **คำนวณสดได้ 365 ใบ และ ไม่มีสักใบที่ตรงกับค่าที่บันทึกไว้** (เช่น `25114`/`MB8A778CY` บันทึก 101 → ฟอร์มจะโชว์ 115.96) ⇒ ตัวเลขบนฟอร์มจะไม่เหมือนที่ผู้ใช้เคยเห็นตอนขอ
  - **ยังไม่ได้แก้** — ถ้าต้องรักษาประวัติ ทางเลือกคือให้ `save()` เขียน `price1/2/3` เฉพาะตอนขึ้นใบใหม่ (ไม่เขียนตอนแก้ใบเดิม) หรือแยกช่อง "ราคาตอนที่ขอ" ออกจาก "ราคาปัจจุบัน" บนฟอร์ม — ทั้งใบเดิมและใบใหม่ ไม่ได้เอาค่าที่บันทึกไว้ใน `appvreq.price1/2/3` มาโชว์ (คอลัมน์นั้นกลายเป็น "สำเนาราคาตอนที่ขอ" เก็บไว้ดูย้อนหลัง — `save()` ยังเขียนค่าที่โชว์ลงไปเหมือนเดิม) · ค่าที่เคยบันทึกจะถูกหยิบมาแสดง **เฉพาะตอนคำนวณไม่ได้** (ไม่พบรหัสในตารางราคาทุน ฯลฯ) พร้อมข้อความบอกว่าเป็นค่าเดิม · ส่วน DB 3-4/1-2 Kg. คำนวณสด ๆ เสมอเช่นกัน (ไม่มีที่เก็บใน `appvreq`) · โค้ดอยู่ที่ `fillApprovalPrices()` ใน `order/index.blade.php` และมีบรรทัดบอกที่มา (`#a_price_note` — ราคาทุน · เงื่อนไขที่เข้า · สูตร) ใต้กล่องราคาแบบเดียวกับใบสั่งซื้อ/หน้า `/saleinfo`

**ปุ่ม "พิมพ์" ยังเป็น `window.print()`** — ยังไม่มีแบบฟอร์มกระดาษให้อ้างอิง

| ช่องบนฟอร์ม | คอลัมน์ |
|---|---|
| วันที่ขอราคา | `appvreq.ReqDate` (PK ร่วมกับ custno + itemno) — อ่านอย่างเดียว server กำหนดให้ |
| รหัสลูกค้า · **# 15** · ชื่อลูกค้า | `appvreq.custno` · `customer.sale` (รหัสพนักงานขาย) · `customer.name` |
| รหัสสินค้า | `appvreq.itemno` — ตัวเลือกรวมจาก `uprice.ITEMNO` + `zcustprice.colorno` ของลูกค้ารายนั้น (25/08/2569, ถอด `tb_saleinfo` ออก 29/08/2569) |
| ราคา 3 ช่อง | คำนวณจาก `ProductPriceService::lookup($itemno)` (= กลุ่ม A/B/C) แล้วเก็บสำเนาลง `appvreq.price1` / `price2` / `price3` |
| ราคาขายครั้งนี้ / จำนวนสั่งซื้อ | `appvreq.price` / `weight` |
| หมายเหตุ การปรับราคา | `appvreq.remark` |
| อนุมัติ | `appvreq.Appv` |
| อนุมัติราคาถึง | `zcustprice.enddate` |
| ตารางล่าง (รหัสสี · ราคาขาย · ยืนราคาถึงวันที่ · หมายเหตุ) | `zcustprice` (`colorno` · `exprice` · `enddate` · `remark`) |

**ที่มาของ dropdown "รหัสสินค้า" (`items()`) — 25/08/2569:** union 3 ตาราง กรองด้วยรหัสลูกค้าที่เลือก แล้ว unique + sort natural
- `uprice` (`CustNo`+`ITEMNO`) = ข้อมูลยกมาจากระบบเก่า 60,603 แถว — **เมนู "กำหนดราคา" (`/saleinfo`) เขียนลงตารางนี้แล้ว** (29/08/2569) ⇒ เบอร์ที่เพิ่งตั้งราคาโผล่ในช่องนี้ทันที
- `zcustprice` (`custno`+`colorno`) = เขียนได้ที่เดียวคือตอน **อนุมัติในฟอร์มนี้เอง** ⇒ ลำพังตัวเดียวเป็นไก่กับไข่ (ต้องเลือกเบอร์ได้ก่อนถึงอนุมัติได้)
- ~~`tb_saleinfo`~~ **ถอดออกจาก union แล้ว 29/08/2569** — เดิม (25/08/2569) union เข้ามาเป็นที่ที่ 3 เพราะตอนนั้น `/saleinfo` เขียนลงตารางแยก ตอนนี้เขียนลง `uprice` ตัวเดียวกันแล้วจึงซ้ำซ้อน (และตารางนั้นเหลือแต่ข้อมูลทดสอบ)

⚠ **ราคาที่ตั้งใหม่จาก `/saleinfo` จะมีข้อมูลในกล่อง "ราคาที่ตกลงไว้ล่าสุด" ด้วยแล้ว** (กล่องนั้นอ่าน `uprice`) — ต่างจากเดิมที่เบอร์ซึ่งมาจาก `tb_saleinfo` อย่างเดียวจะได้ `uprice: null` ช่องว่าง · ส่วนราคา 1/2/3 ยังคำนวณจาก `ProductPriceService` ตามปกติ (เบอร์ที่ไม่มีใน `access_pdprice` = โชว์เหตุผลแทน ตามเดิม)

ปุ่มตรวจสอบ/ประวัติทั้ง 4 ปุ่มโหลดผลลงตารางล่างตัวเดียวกัน: **ตรวจสอบเบอร์อื่น** → `zcustprice` ทุกเบอร์ของลูกค้า · **ประวัติของเบอร์นี้** → `appvreq` ทุกครั้งของคู่ custno+itemno · **ตรวจสอบเฉพาะร้าน** → `zcustprice` ของลูกค้ารายอื่นที่ใช้เบอร์นี้ · **ประวัติราคาเม็ด CP** → `cp_itemprice` ของเบอร์นี้


### ฟอร์มลูก: อนุมัติใบสั่งซื้อ (morderAPPV) — 12/08/2569

แปลงจากฟอร์ม Access **"morderAPPV"** — `OrderApprovalController` + route ใต้ prefix `order/order-approval/*` + view `order/order-approval.blade.php` (modal ในหน้า `/order`) **สถานะ: อนุมัติ / ยกเลิกอนุมัติ ได้แล้ว** (21/08/2569)

**2 มุมมองใน modal เดียว — เปิดมาเจอรายการก่อน (25/08/2569)** เดิมเปิดฟอร์มมาแล้วเด้งเข้าใบแรกของคิวทันที (เดินทีละระเบียนแบบ Access) ตอนนี้เป็น **รายการ → คลิกใบ → ฟอร์มอนุมัติ**:
- `#oaListView` — ตารางคิวรออนุมัติ (ลำดับ · เลขที่ใบสั่ง · วัน-เวลา · แผนก · รหัส/ชื่อลูกค้า · ราคาขาย) **คลิกทั้งแถว** → `oaOpenOrder(i)` เข้าฟอร์มใบนั้น · มีช่องค้นหา `#oa_search` กรอง **ฝั่งจอ** (`oaRenderQueue()` — เลขที่ใบ/รหัส/ชื่อลูกค้า/แผนก) เพราะคิวสั้น ไม่ต้องค้นที่ server
- `#oaDetailView` — ฟอร์มอนุมัติผังเดิม + ปุ่ม **"กลับไปรายการที่รออนุมัติ"** (`oaShowList()`) · **ตัวเดินระเบียนของ Access ยังอยู่ครบ** (`oaGo`/`oaStep`, "ระเบียน N จาก M") ไล่ใบต่อกันในฟอร์มได้เหมือนเดิม
- **backend ไม่ได้แก้เลย** — `queue()` คืนข้อมูลที่รายการต้องใช้ครบอยู่แล้ว (เพิ่มแค่การวาดฝั่งจอ)
- `oaLoadQueue()` **เลิกรับพารามิเตอร์ `openAfter`** แล้ว (เดิม `oaLoadQueue(true)` = โหลดเสร็จเด้งเข้าใบแรก) — ตอนนี้โหลดคิว + วาดรายการ + อัปเดต badge อย่างเดียว ไม่เปิดใบไหนให้เอง
- `orderApprovalRefresh()` ดูว่าอยู่มุมมองไหน: หน้ารายการ = โหลดรายการใหม่ · ในฟอร์ม = โหลดใบที่ค้างอยู่ใหม่ และถ้าใบนั้น**หลุดจากคิวไปแล้ว** (มีคนอนุมัติก่อน) จะเด้งกลับหน้ารายการให้
- ค่าในตารางคิว escape ด้วย **`escHtml()`** (ไม่ใช่ `esc()` ซึ่งแทนแค่ `"`) เพราะชื่อลูกค้ามาจาก DB

**คิวรออนุมัติ** (`queueQuery()`) = `approvableQuery()` + `appv IS NULL` โดย `approvableQuery()` = **ตัดใบจอง R** (ตัวอักษรที่ 2 ของ `Orderno` = `R`) เท่านั้น. แยก `approvableQuery()` ออกมาเพราะ `approve()` ใช้ตรวจสิทธิ์ด้วย (ใบที่ไม่ต้องอนุมัติ กดผ่าน endpoint ตรง ๆ ไม่ได้)

⚠ **เงื่อนไข "ไม่รวมใบสั่งทำสต๊อก" ปิดไว้ชั่วคราว** (25/08/2569 ตามที่ผู้ใช้สั่ง "ยังไม่ต้องสนเรื่องสต็อก") — เดิมตัดใบที่มีค่าใน `HMStore`/`SendCust`/`sendmth` ออกตามข้อความกำกับบนหัวฟอร์ม Access เดิม "ไม่รวมทำ STOCK + ไม่รวมใบจอง R" ทำให้ใบอย่าง `CM42287` ไม่ขึ้นในฟอร์ม. **ข้อมูลจริงสวนทางกับข้อความนั้น**: ใบสั่งทำสต๊อกมี 98 ใบ (2.8% ของทั้งหมด) และ **อนุมัติไปแล้ว 96 ใบ** ⇒ ระบบเดิมก็อนุมัติใบสต๊อกเหมือนกัน แค่คงไม่ได้ทำผ่านฟอร์มนี้ — **รอลูกค้ายืนยันว่าใบสต๊อกต้องอนุมัติที่ไหน**. โค้ดเงื่อนไขเดิมคอมเมนต์ค้างไว้ใน `approvableQuery()` เปิดคืนได้ทันที

**การกดอนุมัติ** (`approve()`, `POST order/order-approval/approve`, 21/08/2569): รับ `orderno` + `appv` (1 = อนุมัติ, 0 = ยกเลิก) → เขียน **`morder.appv` + `morder.appvDT`** เท่านั้น
- **ค่าที่เขียน: `-1` + เวลาปัจจุบัน = อนุมัติ · `NULL` + `NULL` = รออนุมัติ** — ยึดตามข้อมูลจริงที่ตรวจแล้ว (`appv` มีแค่ `-1` 3,463 ใบ กับ `NULL` **ไม่มีแถวไหนเป็น `0` เลย** และทุกใบที่อนุมัติมี `appvDT` ครบ) ⇒ **ยกเลิกอนุมัติต้องคืนเป็น `NULL` ไม่ใช่ `0`** ไม่งั้นใบจะไม่ไหลกลับเข้าคิว (`queueQuery` ใช้ `whereNull`)
- ตรวจ 3 ชั้นก่อนเขียน: มีใบจริง · อยู่ใน `approvableQuery()` · สถานะยังไม่ตรงกับที่ขอมา (กันกดซ้ำ / เปิดฟอร์มค้างไว้แล้วมีคนอนุมัติไปก่อน) — ไม่ผ่าน = 422
- **`morder` ไม่มีคอลัมน์เก็บ "ใครอนุมัติ"** → ระบบไม่รู้ว่าใครกด (ดูหัวข้อความเสี่ยงด้านล่าง)
- ฝั่งจอ (`orderApprovalApprove()` ใน `order/index.blade.php`): checkbox ถูก `preventDefault()` ไว้ — ติ๊กเองไม่มีผล ต้องผ่าน Swal ยืนยันก่อน แล้วโหลดสถานะจริงกลับมา · สถานะที่จะเปลี่ยนไปอ่านจาก `oaCurrent` (ระเบียนที่โหลดมา) **ไม่พึ่งค่า `:checked` ตอนคลิก** ซึ่งขึ้นกับจังหวะ toggle ของเบราว์เซอร์ · อนุมัติเสร็จใบหลุดจากคิว → `oaAfterApprove()` โหลดคิวใหม่แล้ว **กลับไปหน้ารายการ** (25/08/2569 — เดิมเลื่อนไปใบถัดไปในตำแหน่งเดิม) พร้อมอัปเดต badge บนปุ่มหน้าหลัก
- **ยกเลิกการอนุมัติทำได้ที่ฝั่ง server แต่ UI ยังไปไม่ถึง** — ทั้งรายการและตัวเดินระเบียนแสดงเฉพาะคิวที่ยังไม่อนุมัติ ใบที่อนุมัติแล้วจึงเปิดขึ้นมาไม่ได้ ถ้าต้องการให้ยกเลิกได้จริงต้องเพิ่มทางเปิดใบที่อนุมัติแล้วก่อน (เช่น ตัวกรอง "อนุมัติแล้ว" ในหน้ารายการ)

⚠ **ความเสี่ยงที่ยังค้างอยู่** (เหมือน `POST order/save`): route นี้ป้องกันแค่ `auth` + สิทธิ์ `order read` จาก `config/menu.php` — **ไม่มีสิทธิ์แยกสำหรับการอนุมัติ** ใครเข้าเมนู O-Order ได้ก็กดอนุมัติได้ และ **ไม่มี audit trail ว่าใครกด** (ต่างจากฟอร์มขออนุมัติราคาพิเศษที่มีรหัสปลดล็อกโหมด). ฟอร์ม Access เดิมไม่มีช่องรหัสผ่านตรงนี้ จึงยังไม่ได้ใส่ — ถ้าลูกค้าต้องการ ให้เพิ่ม gate ที่ `approve()` จุดเดียว

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

**ยังไม่ยืนยัน:** ช่อง "ผู้บริหาร" (กล่องเขียว, `#oa_mdnote`) — ตรวจ `SHOW COLUMNS FROM morder` แล้ว **ไม่มีคอลัมน์ไหนรองรับ** จึงพิมพ์ได้แต่ยังไม่บันทึก (มี `title` กำกับไว้บนช่อง) — ได้ที่เก็บจริงเมื่อไหร่ค่อยเพิ่มลง `approve()`

## ฐานข้อมูลลูกค้า (เมนู C-ฐานข้อมูลลูกค้า, `/customer`) — 24/08/2569

แปลงผังมาจากฟอร์ม Access **"ข้อมูลลูกค้า"** — `CustomerController` + `routes/customer.php` + view `customer/{index,table,customer-form}.blade.php` (หน้ารายการ + modal ฟอร์ม) **สถานะ: เพิ่ม/แก้ไข/ลบ ได้แล้ว**

**4 ตารางที่ฟอร์มนี้ดูแล** (legacy ทั้งหมด, MyISAM):

| ตาราง | เก็บอะไร | คีย์ |
|---|---|---|
| `customer` | ข้อมูลลูกค้า | `code` (varchar 6 — **ผู้ใช้กรอกเอง ไม่มีตัวรันเลข**) |
| `contact` | ผู้ติดต่อ (ตารางล่างของฟอร์มเดิม) | `code` + `contactname` |
| `naddress` | สถานที่ส่ง (ตัวเลือกช่อง "สถานที่ส่ง" ในใบสั่งซื้อ) | `Custno` + `DVpoint` |
| `engname` | **ชื่อลูกค้าภาษาอังกฤษ** | `code` |

⚠ **ชื่ออังกฤษอยู่ที่ `engname` ไม่ใช่ `customer.nameEN`** — `engname` มี 1,661 แถว ส่วน `customer.nameEN` มีข้อมูลแค่ **1 แถวจาก 4,094** แต่หน้าใบเสนอราคา (`QuotationController`) อ่าน `customer.nameEN` อยู่ ⇒ `save()` จึง**เขียนทั้งสองที่** (`syncEngName()` + คอลัมน์ `nameEN`) และตอนอ่านใช้ `engname` ก่อน ค่อย fallback ไป `nameEN`

**ตารางลูกบันทึกแบบ "ลบทิ้งทั้งหมดแล้ว insert ใหม่"** (`syncContacts` / `syncDeliveryPoints`) เพราะ `contact` / `naddress` เป็นคีย์ผสมและ**ไม่มี id** จึง update ทีละแถวไม่ได้ — แถวที่ไม่กรอก `contactname` (หรือ `DVpoint` ว่าง) ถือเป็นแถวเปล่า ระบบข้ามให้ และชื่อซ้ำกันในฟอร์มเดียวเก็บได้แถวเดียว (PK ซ้ำไม่ได้)

**ลบลูกค้าได้เฉพาะรายที่ไม่มีธุรกรรมผูกอยู่** (`relatedCounts()`): ตรวจ `morder.Custno` / `qmast.Custid` / `testmain.custno` / `zcustprice.custno` / `appvreq.custno` / `tb_planning_header.custno` — เจอแม้แถวเดียว = 422 พร้อมบอกว่าติดที่อะไรกี่รายการ (ตาราง legacy ไม่มี foreign key ถ้าลบทิ้งเอกสารเก่าจะไร้เจ้าของ). ลบผ่านแล้วจะลบ `contact` / `naddress` / `engname` ของรายนั้นตามไปด้วย

**Field mapping จากฟอร์ม Access → DB** (ยืนยันกับข้อมูลจริงของลูกค้า `40028` แล้ว):

| ช่องบนฟอร์ม | คอลัมน์ |
|---|---|
| รหัส / ชื่อ | `customer.code` / `name` |
| ชื่อ (อังกฤษ) | `engname.name` (+ เขียนสำเนาลง `customer.nameEN`) |
| เลขที่ / ถนน / อำเภอ-เขต / จังหวัด / รหัสไปรษณีย์ | `no` / `road` / `amphur` / `city` / `zip` |
| โทร / Fax | `tel` / `fax` |
| เครดิต / ส่วนลด % / รหัสผู้ขาย | `term` / `cashdisc` / `sale` |
| ประเภทลูกค้า | `type` → `c_type.t_namee` |
| เลขที่ผู้เสียภาษี / สาขา (เพื่อเปิดบิล) / เลขผู้เสียภาษีเดิม 10 หลัก | `taxid` / `Branch` / `legal` |
| RP / CER / MSDS / PO | `RP` / `CER` / `MSDS` / `PO` (checkbox แบบ Access: `-1` = ติ๊ก) |
| ใบกำกับ / สำเนา | `CopyINV` (เป็น**ข้อความอิสระ** เช่น "ใบกำกับ 2", "RP 6 ชุด" ไม่ใช่จำนวนชุด) |
| Nickname (ชื่อที่ติดข้างกล่อง) | `nickname` |
| เวลารับของ / ลงของ เก็บเงิน-เช็ค | `custTime` / `CashChq` |
| คำสั่งขณะส่งมอบ / หมายเหตุ / หมายเหตุภายใน | `condition` / `remark` / `cust_desc` |
| ตารางผู้ติดต่อ: ชื่อ · ตำแหน่ง · เบอร์โทร · fax · หมายเหตุ | `contact.contactname` · `position` · `tel` · `fax` · `remark` |
| สถานที่ส่ง | `naddress.DVpoint` |

**`customer.sale` = `emp.supno`** (ยืนยันแล้ว — ค่าเดียวกับ `morder.supno` ที่ฟอร์มใบสั่งซื้อดึงไปเติมช่อง "รหัสผู้ขาย") แต่ **`emp` มี `supno` แค่ 3 คน** ⇒ รหัสผู้ขายเก่าส่วนใหญ่แปลงเป็นชื่อไม่ได้ — `saleOptions()` จึงประกอบตัวเลือกจาก **ค่าที่เคยใช้จริงใน `customer.sale`** แล้วแนบชื่อให้เฉพาะรหัสที่จับคู่ได้ (ฟอร์มยังเติม option ของค่าที่บันทึกไว้เองด้วย กันค่าหายตอนบันทึกทับ)

**คอลัมน์ที่ `save()` จงใจไม่แตะ:** `black` / `blackdate` / `blackrem` (Blacklist), `zone` / `DV` / `flprt`
- **Blacklist แสดงอย่างเดียว** (กล่องแดงบนหัวฟอร์ม + กล่องสรุปท้ายฟอร์ม + badge ในตาราง) เพราะค่าใน DB มีทั้ง `-1` (220 ราย — ตรงกับ convention Access และเป็นกลุ่มเดียวที่มี `blackrem` เป็นเรื่องเป็นราว), `-3` (2,734 ราย), `0`, `2`, `5` ⇒ **ยังไม่รู้ความหมายของ `-3`/`2`/`5`** และสถานะเครดิต/blacklist มีตารางขออนุมัติของตัวเองอยู่แล้ว (`appvcredit`) — ตัวกรอง "สถานะ" บนหน้ารายการจึงตัดสินจาก `black = -1` เท่านั้น
- `zone` / `DV` ว่างทั้ง 4,094 แถว, `flprt` เป็น `0` ทั้งหมด → ไม่ทำ UI ให้

**ยังไม่ทำ:** กล่อง **"AList / ในเครือ"** (subform ขวาบนของฟอร์ม Access) — ไล่ดูทุกตารางใน DB แล้ว**ยังหาที่เก็บไม่เจอ** (ตาราง `table1` ที่ชื่อคล้ายเป็นรายการสถานะการผลิต ไม่เกี่ยวกัน) ⇒ ต้องถามลูกค้าว่าข้อมูล "บริษัทในเครือ" เก็บที่ไหน

⚠ **ความเสี่ยงที่ยังค้างอยู่** (เหมือน `POST order/save`): route `customer/save` + `customer/delete` ป้องกันแค่ `auth` + สิทธิ์ระดับเมนู (`Customer`) — **ไม่มีสิทธิ์แยกสำหรับการเขียน/ลบ** ใครเข้าเมนูนี้ได้ก็แก้ข้อมูลลูกค้าและลบได้ และ **ไม่มี audit trail** ว่าใครแก้ (ตาราง `customer` ไม่มีคอลัมน์ผู้แก้ไข/เวลาแก้ไข)

## ระบบกำหนดราคา (เมนู "กำหนดราคา", `/saleinfo`) — ย้ายมาเขียนลง `uprice` — 29/08/2569

🔴 **เปลี่ยนตารางปลายทาง: `tb_saleinfo` → `uprice`** (ผู้ใช้สั่ง "ต้องใช้ uprice") — อย่าเอาเอกสาร/โค้ดเก่ามาอ้าง

เดิมเมนูนี้เขียนลง `tb_saleinfo` ที่สร้างใหม่ (ตั้งชื่อคอลัมน์ตาม `uprice` เพื่อ copy ข้อมูลได้ตรง ๆ) โดยตั้งใจไม่แตะตาราง legacy — ผลคือ **ราคาอยู่ 2 ที่ที่ไม่ sync กัน** และหน้าอื่นที่อ่าน `uprice` อยู่มองไม่เห็นราคาที่ตั้งจากเมนูนี้เลย

**ผลข้างเคียงที่ตั้งใจ — ตั้งราคาที่นี่แล้วมีผลกับ 3 หน้านี้ทันที:**
- `OrderController::itemLookup` — เติมชื่อสินค้า (`prodname`) + หมายเหตุ (`suborder.Remark`) จาก `uprice.Label` ในฟอร์มใบสั่งซื้อ
- `OrderApprovalController` — ช่อง REM1 / REM2 / ราคาที่กำหนดไว้ ในฟอร์มอนุมัติใบสั่งซื้อ (`uprice.REM1/REM2/PRICE`)
- `PriceApprovalController` — กล่อง "ราคาที่ตกลงไว้ล่าสุด" + dropdown รหัสสินค้าในใบขออนุมัติราคาพิเศษ

**`uprice` เดิมไม่มี primary key เลย** (ไม่มี `id` ไม่มี index สักตัว, MyISAM, 60,603 แถว) จึงระบุแถวเพื่อแก้ไข/ลบไม่ได้ → migration **`2026_08_29_100000_add_id_to_uprice_table`** เพิ่ม `id INT AUTO_INCREMENT PRIMARY KEY FIRST` ให้
- ตรวจแล้วปลอดภัยกับโค้ดเดิม — ทุก query ในระบบ select ชื่อคอลัมน์ตรง ๆ ไม่มีที่ไหนพึ่งลำดับคอลัมน์
- migration idempotent (`Schema::hasColumn`) และ `down()` ถอดคืนได้
- ⚠ MyISAM ⇒ คำสั่งนี้ rebuild ตารางทั้งใบ **สำรองก่อนรันบน production**
- หลังรัน: 60,603 แถวครบ `id` = 1..60603 ไม่ซ้ำ

**Model `Saleinfo` ชี้ที่ `uprice` และ `public $timestamps = false`** — ตารางนี้ไม่มี `created_at`/`updated_at` (เวลาบันทึกล่าสุดเก็บที่คอลัมน์ `AuthDate` ของเดิม ซึ่ง `insert()`/`update()` เขียน `now()` ให้อยู่แล้ว)

🔴 **2 ช่องที่ยังบันทึกไม่ได้ — `uprice` ไม่มีคอลัมน์รองรับ** (รอลูกค้ายืนยัน):

| ช่องบนฟอร์ม | คอลัมน์ที่เคยใช้ | สถานะตอนนี้ |
|---|---|---|
| วันที่แจ้งปรับ | `tb_saleinfo.NotifyDate` | ช่องยังอยู่ แปะ class `wip` + `title` บอกว่ายังไม่บันทึก |
| MOQ (kg) | `tb_saleinfo.MOQ` | เหมือนกัน — คอลัมน์ในตารางประวัติก็แปะ `wip` (จะว่างเสมอ) |

- ถอดออกจาก `SaleinfoController::COLUMNS` และ `extractForm()` แล้ว (คอมเมนต์ค้างไว้ทั้งคู่) — **ถ้าไม่ถอดจะกลายเป็นคอลัมน์ที่ไม่มีจริงตอน insert/update = SQL error**
- `history()` เปลี่ยน `orderByRaw('COALESCE(NotifyDate, DATE, AuthDate) DESC')` → **`COALESCE(\`DATE\`, AuthDate)`** (อ้าง NotifyDate จะได้ SQL error) และคืน `NotifyDate` = `''` / `MOQ` = `null` เพื่อให้ JS ฝั่งจอไม่ต้องแยกเคส
- `edit()` ส่ง `NotifyDate` = `''` / `MOQ` = `null` ด้วยเหตุผลเดียวกัน
- **เปิดใช้เมื่อได้ข้อสรุป:** เพิ่ม 2 คอลัมน์เข้า `uprice` → คืนชื่อเข้า `COLUMNS` + ปลดคอมเมนต์ใน `extractForm()` + ลบคำว่า `wip` ในฟอร์ม

⚠ **`uprice.CustNo` เป็น varchar(5) — แคบกว่า `tb_saleinfo` เดิมที่เป็น varchar(6)** (`customer.code` ยาวสุด 5 ตัวพอดี ไม่มีรายไหนเกิน) ⇒ ใส่ `maxlength` ให้ช่องในฟอร์มตามความกว้างจริงแล้ว: CustNo 5 · st_code/ITEMNO 17 · REM1 100 · PackRem/Label 85 · Author 50 — กัน **error 1406 Data too long** (MySQL เปิด `STRICT_TRANS_TABLES`)

⚠ **`uprice` เป็น MyISAM + charset `utf8` (3 ไบต์)** — ต่างจาก `tb_saleinfo` ที่เป็น InnoDB/utf8mb4 ⇒ ไม่มีทรานแซกชัน (ดูหัวข้อ MyISAM ด้านล่าง) และเก็บ emoji ไม่ได้ (ภาษาไทยปกติเก็บได้ ทดสอบแล้ว)

**ตาราง `tb_saleinfo` ยังอยู่ ไม่ได้ลบ** — มี 6 แถวและ**เป็นข้อมูลทดสอบทั้งหมด** (`Test`, `12345`, `78945`) ไม่มีของจริงต้องย้าย. ตอนนี้ไม่มีโค้ดไหนอ่าน/เขียนแล้ว — จะ drop ทิ้งเมื่อไหร่ก็ได้ (พร้อม migration 2 ไฟล์ที่สร้างมัน) แต่ยังไม่ทำเพราะเป็นการลบข้อมูล

## ตารางวันหยุดนักขัตฤกษ์ (เมนู "วันหยุดนักขัตฤกษ์", `/holiday`) — 01/09/2569

`HolidayController` + `routes/holiday.php` + view `holiday/{index,holiday-form,calendar}.blade.php` + Model `Holiday` (`tb_holiday`) — หน้าจัดการข้อมูล (master)

**ที่เอาไปใช้แล้ว (01/09/2569):** ช่อง **"อนุมัติราคาถึง"** ในฟอร์มขออนุมัติราคาพิเศษ ใช้ `App\Services\HolidayService::nextWorkingDay()` หา "วันทำการถัดไป" (ดูหัวข้อฟอร์ม MK ขออนุมัติราคาพิเศษ) — **ที่อื่นยังไม่ผูก** (เตือนตอนเลือก `inplan`/`custwant`/`senddate` ตรงวันหยุด ฯลฯ ยังไม่ได้ทำ) จะเพิ่มก็เรียก `HolidayService` ตัวเดิม ไม่ต้องแก้ตาราง

**`App\Services\HolidayService`** — ตรรกะวันทำการอยู่ที่นี่ที่เดียว: `isHoliday()` / `isWorkingDay()` / `nextWorkingDay($from = null, $days = 1)` / `weeklyOff()` / `flush()` · วันหยุดประจำสัปดาห์ตั้งที่ **`config/holiday.php` → `weekly_off`** (ค่าปัจจุบัน `[0]` = หยุดเฉพาะอาทิตย์ · จะหยุดเสาร์ด้วยให้เป็น `[0, 6]`) · **วันหยุดที่ `is_active='N'` ไม่ถูกนับ**

**ตาราง `tb_holiday` เป็นของใหม่ (InnoDB)** สร้างด้วย migration `2026_09_01_100000_create_tb_holiday_table` (idempotent ตามกติกาโปรเจกต์) — ค้นทั้ง DB แล้ว**ไม่มีตาราง legacy ไหนเก็บวันหยุด**

| คอลัมน์ | เก็บอะไร |
|---|---|
| `holiday_date` | วันที่ (DATE, **unique** = 1 วัน 1 แถว) เก็บเป็น ค.ศ. แสดงผลเป็น พ.ศ. |
| `name` | ชื่อวันหยุด |
| `type` | `public` = นักขัตฤกษ์ · `substitute` = ชดเชย · `company` = วันหยุดบริษัท — **key ห้ามเปลี่ยน** (นิยาม + ป้ายไทย + สี badge อยู่ที่ `Holiday::TYPES` / `TYPE_BADGES`) |
| `remark` | หมายเหตุ |
| `is_active` | `Y`/`N` — ปิดใช้งาน = เก็บแถวไว้แต่ไม่นับเป็นวันหยุด (สลับจากตารางได้เลยด้วย switch) |

- **หน้าเดียว 2 มุมมอง**: แท็บ **ปฏิทินรายปี** (12 เดือน, วันหยุดเป็นวงกลมสีตามประเภท, **คลิกวันที่ = เปิดฟอร์มแก้ไข**) และแท็บ **ตารางวันหยุด** (DataTables serverSide, กรอง ปี/ประเภท/สถานะ + ค้นหาชื่อ-หมายเหตุ, sort ได้ทุกคอลัมน์ที่เป็นคอลัมน์จริง)
- **ชื่อวันหยุดในปฏิทินเป็น tooltip ของ Bootstrap** (`data-bs-toggle="tooltip"` + `data-bs-title`, ไม่ใช่ `title` ธรรมดา — เปลี่ยน 01/09/2569 ตามที่ผู้ใช้สั่ง): `main.js` ของ theme init tooltip **ครั้งเดียวตอนโหลดหน้า** แต่ปฏิทินมาทีหลังผ่าน AJAX ⇒ ต้องเรียก **`initCalendarTooltips()`** เองทุกครั้งหลัง `$box.html(res.data)` — ฟังก์ชันนี้ `dispose()` ตัวเก่าก่อน init ใหม่ ไม่งั้น tooltip ของปฏิทินปีก่อนค้างลอย · `hideCalendarTooltips()` ถูกเรียกตอนคลิกวันที่ กัน tooltip ค้างบนจอเมื่อ modal เปิดทับ
- **คลิกวันที่ในปฏิทินใช้ class `btn_edit_calendar`** (ไม่ใช่ `btn_edit` ของปุ่มในตาราง) → `openHolidayForm(id, 'calendar')` ส่ง **`?from=calendar`** ไปที่ `edit()` → controller ตั้ง `$showDelete = $holiday && request('from') === 'calendar'` ⇒ **ฟอร์มมีปุ่ม "ลบวันหยุดนี้" เฉพาะตอนเปิดจากปฏิทิน** (หน้าปฏิทินไม่มีปุ่มลบของตัวเอง ส่วนตารางมีปุ่มลบในแถวอยู่แล้ว) · ปุ่มนั้นใช้ class `btn_delete` ตัวเดียวกับในตาราง จึงได้ Swal ยืนยัน + โหลดตาราง/ปฏิทินใหม่ในตัว (handler เพิ่ม `modal('hide')` ให้แล้ว) · Swal เด้งทับ modal ได้เพราะ `layout/inc_header` ตั้ง `.swal2-container { z-index: 9999 }`
- **ฟอร์มนี้ไม่ใช้ `placeholder` เลย** — ตัวอย่างที่ต้องกรอกอยู่ในวงเล็บข้าง label แทน (`ชื่อวันหยุด * (เช่น วันขึ้นปีใหม่)`, `หมายเหตุ (เช่น ชดเชยวันวิสาขบูชา)`) เพราะ placeholder ดูเหมือนค่าที่กรอกไว้แล้ว · **ช่องวันที่ไม่มีวงเล็บบอกรูปแบบ** (ถอด `(วว/ดด/ปปปป)` ออกตามที่ผู้ใช้สั่ง 01/09/2569 — เลือกจากปฏิทิน flatpickr อยู่แล้ว) — แนวเดียวกับฟอร์มขออนุมัติราคาพิเศษ (01/09/2569) · ⚠ ช่องค้นหาบนหน้ารายการยังใช้ placeholder อยู่ เพราะไม่มี label กำกับ
  - **ค่าเริ่มต้นคือแท็บปฏิทินรายปี** (01/09/2569 ตามที่ผู้ใช้สั่ง — เดิมเปิดมาเจอแท็บตาราง): ปฏิทินเป็นแท็บแรกใน `nav-tabs` + `pane_calendar` ถือ `show active` และ `loadCalendar()` ถูกเรียกใน `$(document).ready` ตรง ๆ ไม่ต้องรอ `shown.bs.tab`
  - ⚠ **DataTables ถูก init ตอนแท็บตารางยังซ่อนอยู่ → วัดความกว้างคอลัมน์ไม่ได้** จึงต้องเรียก `oTable.columns.adjust()` (+ `responsive.recalc()` ถ้ามี) ใน `shown.bs.tab` ของ `#tab_table` ไม่งั้นหัวตารางกับเนื้อตารางเหลื่อมกัน
  - ปฏิทินโหลดผ่าน AJAX (`holiday.calendar` → partial `holiday/calendar.blade.php`) — บันทึก/ลบ/สลับสถานะจะสั่งโหลดใหม่ถ้าแท็บปฏิทินเปิดอยู่ ไม่งั้นตั้งธง `calendarLoaded=false` ไว้โหลดตอนกลับมาที่แท็บ
  - ตัวช่วยแปลงวันที่ไทยอยู่ที่ Model: `Holiday::thaiDate()` (`1 ม.ค. 2569`) / `thaiWeekday()` / `MONTHS_FULL` / `MONTHS_SHORT` — **อย่า hardcode ชื่อเดือน/วันซ้ำใน blade**
- **ช่องวันที่ในฟอร์มเป็น flatpickr `d/m/Y` (ค.ศ. ตามทั้งระบบ)** — ฟอร์มโหลดผ่าน AJAX เข้า modal จึงต้องเรียก `initHolidayPicker()` ทุกครั้งหลังโหลด และหน้ามี CSS `.flatpickr-calendar { z-index: 1092 }` เหมือนหน้า Order (ของ theme ตั้งไว้ 999 ปฏิทินจะจมใต้ modal)
  - `HolidayController::parseDate()` แปลง `d/m/Y` → `Y-m-d` (รับ `Y-m-d` ด้วย) + `checkdate()` กันวันที่ไม่มีจริง (31/02) · แปลงไม่ได้ = คืน null ให้ validator ตีกลับ 422
  - กันบันทึกวันซ้ำด้วย `Rule::unique(...)->ignore($request->id)` ให้ตรงกับ unique index ของตาราง (แก้แถวเดิมไม่ชนตัวเอง)
- **ข้อมูลตั้งต้นปี 2569 (21 วัน) อยู่ที่ `database/seeders/HolidaySeeder.php`** (ลงทะเบียนใน `DatabaseSeeder` แล้ว) — ใช้ `firstOrCreate` คีย์ด้วย `holiday_date` ⇒ **รันซ้ำได้และไม่ทับค่าที่ผู้ใช้แก้เอง**
  - ⚠ วันหยุดที่อิงจันทรคติ (มาฆบูชา 3 มี.ค. · วิสาขบูชา 31 พ.ค. · อาสาฬหบูชา 29 ก.ค. · เข้าพรรษา 30 ก.ค.) **ต้องตรวจกับประกาศราชการอีกครั้ง** — ใส่หมายเหตุกำกับไว้ในแถวแล้ว
  - รวมวันหยุดชดเชย 2 วันที่คำนวณจากวันที่ตรงเสาร์-อาทิตย์: **1 มิ.ย.** (ชดเชยวิสาขบูชา วันอาทิตย์) และ **7 ธ.ค.** (ชดเชย 5 ธ.ค. วันเสาร์) · **ยังไม่รวม "วันหยุดพิเศษ" ที่ ครม. ประกาศเพิ่มรายปี**
- ⚠ **ความเสี่ยงแบบเดียวกับเมนูอื่น**: route `holiday/store` · `holiday/delete` · `holiday/toggle-status` ป้องกันแค่ `auth` + สิทธิ์ระดับเมนู (menu key **`Holiday`** → namespace `holiday`) — **ไม่มีสิทธิ์แยกสำหรับการเขียน/ลบ และไม่มี audit trail** ใครเข้าเมนูนี้ได้ก็แก้/ลบได้ · พนักงาน (guard `emp`) ต้องไปติ๊กเมนูนี้ให้ role ที่หน้า "จัดการสิทธิ์" ก่อน (admin เข้าได้อยู่แล้ว)

## ข้อตกลงและข้อควรระวัง (Conventions & Gotchas)

- **ช่องกรอกตัวเลขแบบใส่คอมมาอัตโนมัติ** (`resources/views/layout/inc_js.blade.php`, 18/08/2569): ช่องกรอกราคา/น้ำหนัก/จำนวน **ห้ามใช้ `type="number"`** (แสดงคอมมาไม่ได้) ให้ใช้ `type="text" class="js-comma"` แทน — ตัวช่วยกลางอยู่ใน `inc_js` จึงใช้ได้ทุกหน้าโดยไม่ต้อง include เพิ่ม
  - `data-decimals="0"` = จำนวนเต็ม (ไม่ระบุ = ทศนิยม 2 ตำแหน่ง)
  - **ค่าที่แสดงในช่องมีคอมมาเสมอ** → อ่านค่าด้วย `numVal(sel)` / `numOf(value)` และเรียก `stripCommaFields(form)` **ก่อน** `serialize()` / `new FormData()` ทุกครั้ง ไม่งั้น `'1,234.50'` จะลง DB เป็น `1.00`
  - เติมค่าจาก DB ลงช่องด้วย `commaFmt(v, decimals)`
  - ที่ใช้อยู่: Order (`o_netqty`/`o_HMStore`/`o_sendmth`/`a_price`/`a_weight`), กำหนดราคา (`MOQ`/`PRICE`), เทียบสี (`Wage`), ใบเสนอราคา (ทุกคอลัมน์ที่ `colRegistry` ตั้ง `num => true`)
  - ที่**ตั้งใจไม่ใส่**: ตัวคูณ/หาร/บวกในตารางเงื่อนไขราคา, `PHR` (ทศนิยม 4 ตำแหน่ง), จำนวนเดือน, ช่องเดินระเบียน — ไม่ใช่ตัวเลขหลักพัน
- **ยกระดับ `<select>` อัตโนมัติ — select2 / bootstrap-select** (`resources/views/layout/inc_js.blade.php`, 25/08/2569): ตัวช่วยกลาง `enhanceSelects(scope)` เลือกชนิดให้เอง **โดยนับจำนวน `<option>` ตอนรันไทม์** — ตั้งแต่ `SELECT_SEARCH_MIN` (= **10**) ขึ้นไป → **select2** (พิมพ์ค้นหาได้) · ต่ำกว่านั้น → **bootstrap-select** (basic). ที่นับตอนรันไทม์เพราะรายการส่วนใหญ่มาจาก DB (จังหวัด 113 · Temp 180 · Model 83) จำนวนโต/ลดได้เอง ไม่ต้องตามแก้ class ทีละหน้า
  - **ใช้แบบ opt-in ต่อหน้า** — แต่ละหน้าเรียก `enhanceSelects()` เองใน `$(function(){...})` (และเรียกซ้ำหลังโหลด HTML จาก AJAX เช่น `enhanceSelects('#customerFormBody')`) จงใจไม่ให้ทำงานอัตโนมัติทั้งระบบ เพราะยังมีหน้าที่ไม่ได้อยู่ในขอบเขตนี้. เรียกซ้ำได้ ไม่ init ทับของเดิม
  - หน้าที่เปิดใช้แล้ว: **เทียบสี · ใบเสนอราคา · Order (+ฟอร์มลูก) · ฐานข้อมูลลูกค้า · กำหนดราคา · ข้อมูลสินค้า** (หน้า `/price-rule` ไม่มี select)
  - **class กำกับที่ `<select>`**: `no-enhance` = ปล่อยเป็น select ธรรมดา · `force-select2` = บังคับค้นหาได้แม้ option น้อย (ใช้กับช่องที่รู้ว่าจะยาวหลังโหลด เช่น `#a_itemno` รหัสสินค้าในใบขออนุมัติราคา) · `force-picker` = บังคับ bootstrap-select · **`select2-tags`** = select2 ที่**พิมพ์ค่านอกรายการเองได้** — ใช้กับฟอร์มเทียบสี (`modal-cm`: Type_Work/Adj/ColorChar/pop/Model/ChemSafety) ที่เป็นช่อง "อื่นๆ ระบุ" ตามฟอร์มกระดาษ + กันค่าเดิมใน DB หาย; **TestType ไม่ใส่** เพราะเป็นค่าตายตัว 1-4
  - **hook กลาง 4 ตัว — ไม่ต้องตามสั่ง refresh เองในแต่ละหน้า**: (1) `MutationObserver` เฝ้า option ที่ JS เติมทีหลัง (`.html()`/`.append()`) แล้ว**สลับชนิดให้เมื่อข้ามเกณฑ์** (เช่น `#o_DVpoint` เริ่มมี option เดียว = picker → ลูกค้าที่มีสถานที่ส่งเยอะก็กลายเป็น select2) · (2) ครอบ `$.fn.val` ให้ sync หน้าปุ่มอัตโนมัติเมื่อโค้ดเดิมตั้งค่าด้วย `.val()` · (3) `form.reset()` · (4) `shown.bs.collapse` / `shown.bs.modal` — วาดใหม่ตอนโผล่จากที่ซ่อน (ตอนซ่อนอยู่วัดความกว้างไม่ได้)
  - 🔴 **bootstrap-select อ่าน `<option>` เข้าไปทำรายการของตัวเองแค่ "ตอน init ครั้งเดียว" → option ที่ JS เติมทีหลังต้อง init ใหม่ทั้งตัว** (แก้ 29/08/2569): ในไลบรารี**ไม่มี `MutationObserver` เลย** และ `'render'` อัปเดตแค่ข้อความบนปุ่ม — ตัวที่สร้างรายการใหม่คือ `buildList()` ซึ่งเรียกจาก `'refresh'` ที่ห้ามใช้ ⇒ เดิม hook (1) จัดการได้แค่กรณี**ข้ามเกณฑ์ไปเป็น select2** ส่วนกรณีที่ยังเป็น picker เหมือนเดิมจะไปลงทาง `syncSelect()` → `'render'` **ดรอปดาวน์จึงค้างรายการเก่า**
    - อาการที่เจอ: **"สถานที่ส่ง" ในฟอร์มใบสั่งซื้อกางออกมาเจอแต่ "— ไม่ระบุ —"** ทั้งที่ `naddress` มีข้อมูล — กระทบ **ลูกค้า 554 จาก 560 ราย (98.9%)** ที่มีสถานที่ส่งน้อยกว่า 9 แห่ง (จึงไม่ข้ามเกณฑ์ 10 option ไปเป็น select2) และ **417 จากใบสั่งซื้อ 500 ใบล่าสุด**; select2 ไม่มีปัญหานี้เพราะอ่าน `<option>` สด ๆ ทุกครั้งที่กาง
    - วิธีแก้: `optionsSig(el)` ทำ "ลายเซ็นชุด option" เก็บไว้ที่ `el._selOptSig` ตอน init — `enhanceSelect()` เทียบลายเซ็นก่อน ถ้าเป็น picker แล้วรายการเปลี่ยนไป (`stale`) จะ **destroy + init ใหม่** แทนการ `'render'` เฉย ๆ · เทียบด้วยลายเซ็นจึงไม่วนซ้ำ (init เสร็จลายเซ็นตรงแล้ว mutation ที่เกิดจากตัว init เองไม่ทำอะไรต่อ) · `refreshSelects()` ได้พฤติกรรมนี้ตามไปด้วยเพราะเรียก `enhanceSelect()` ตัวเดียวกัน
  - ⚠ **bootstrap-select ไม่ตามสถานะ `disabled` ของ `<select>` ให้เอง** → `syncPickerDisabled()` ปิด/เปิดปุ่มเองผ่าน observer ที่เฝ้า attribute `disabled` (สำคัญกับโหมดฟอร์มใบสั่งซื้อ `setOrderFormMode()` ที่ล็อกทุกช่องตอน idle) — **ห้ามใช้ `selectpicker('refresh')` แก้**: มันสร้าง option list ใหม่ทั้งชุดแล้ววาดซ้อนกันบน BS 5.3 (ใช้ `'render'` + init ใหม่เมื่อ option เปลี่ยน ตามข้อบน)
  - ความกว้างยึด inline style เดิมของ select (`style="width:90px"` → `data-width`), `form-select-sm` → ปุ่ม `btn-sm`, `<option hidden>` → แปลงเป็น `data-hidden` ให้ bootstrap-select รู้จัก
  - หน้าใบเสนอราคาเดิมใส่ `.selectpicker` + `data-style` ไว้ที่ตัว `<select>` — **ถอดออกแล้ว** ให้ตัวช่วยกลางตัดสินแทน; ฟังก์ชัน `refreshPickers()` ของหน้านั้นยังอยู่ (ถูกเรียกหลายจุด) แต่เปลี่ยนเป็นส่งต่อให้ `refreshSelects()`
- **แถบตัวกรองแบบพับได้** (21/08/2569, เพิ่มหน้าลูกค้า 24/08/2569): หน้ารายการหลักทุกหน้าที่มีแถบตัวกรอง — **Order**, **ใบเสนอราคา**, **เทียบสี**, **กำหนดราคา**, **ฐานข้อมูลลูกค้า** — ครอบตัวกรองทั้งหมดไว้ใน `<div class="collapse" id="<หน้า>FilterBox">` โดย**ไม่ใส่ class `show`** ⇒ **ค่าเริ่มต้นคือซ่อนไว้**
  - หัวการ์ดเป็นแถบเดียว `d-flex justify-content-end gap-2`: **ปุ่ม `#btnToggleFilters` (พับ/กาง) + ปุ่ม `#btnResetFilters` (ล้างตัวกรอง) ชิดขวาทั้งคู่** — ปุ่มล้างตัวกรอง**อยู่นอกกล่องพับ** จึงกดล้างได้โดยไม่ต้องกางก่อน และ `.filter-count` บนปุ่มทำหน้าที่บอกจำนวนตัวกรองที่ใช้อยู่แม้ตอนพับซ่อน (ไม่ต้องมี badge บนปุ่มตัวกรองซ้ำอีก)
  - จำนวนตัวกรองอัปเดตใน `updateFilterButtonState()` ของแต่ละหน้า — หน้ากำหนดราคาเดิมไม่มีฟังก์ชันนี้ จึงเพิ่งเพิ่มเข้าไป
  - **แถบสรุปเงื่อนไขที่กรองอยู่ `#filterSummary`** (ชิดซ้ายของแถบหัว): `renderFilterSummary()` ไล่อ่านค่าจาก `.p_search` แล้ววาดเป็น badge `"<ป้ายชื่อ>: <ค่า>"` — โชว์**เฉพาะตอนพับตัวกรอง** (สลับ `d-none` ด้วย event `show/hide.bs.collapse`) — ไม่มีตัวกรองเลย = ปล่อยว่าง ไม่ต้องขึ้นข้อความใด ๆ
    - ป้ายชื่อมาจาก map `FILTER_LABELS` (name ของ input → ป้ายไทย) ที่ประกาศไว้ต้นสคริปต์ของแต่ละหน้า — **เพิ่มช่องกรองใหม่ต้องเพิ่มบรรทัดใน map ด้วย** ไม่งั้นจะไม่ขึ้นในแถบสรุป; `select` เอา**ข้อความของ option** ไม่ใช่ value
    - เรียกจากท้าย `updateFilterButtonState()` (ซึ่ง `loadData()` เรียกอยู่แล้วทุกครั้ง) จึงอัปเดตเองอัตโนมัติ ไม่ต้องไปแทรกตามจุดที่แก้ค่า
    - ค่าที่ผู้ใช้พิมพ์ถูก escape ด้วย `escHtml()` ก่อนต่อเป็น HTML — **อย่าเอาค่าดิบยัด `.html()` ตรง ๆ**
    - หน้าเทียบสีมี checkbox "ประเภทเอกสาร" ที่ default = ติ๊กครบ (แปลว่าไม่ได้กรอง) จึงสรุปแยกจาก loop และขึ้น badge เฉพาะเมื่อติ๊กไม่ครบ
  - แถว "เรียงตาม / จำนวนรายการต่อหน้า" **อยู่นอกกล่องพับ** (เป็นการตั้งค่าแสดงผล ไม่ใช่ตัวกรอง) เช่นเดียวกับ hidden input `sort_col`/`sort_dir`
  - element ที่ถูก collapse ยังอยู่ใน DOM → `.p_search` / `collectSearchData()` / `resetFilters()` ทำงานเหมือนเดิม **ไม่ต้องแก้**; หน้าใบเสนอราคาต้องเรียก `refreshPickers()` ตอน `shown.bs.collapse` เพราะ bootstrap-select วัดความกว้างตอนซ่อนอยู่ไม่ได้ (select2 ในหน้าเทียบสีใช้ `width:'100%'` จึงไม่มีปัญหานี้)
  - หน้า **ข้อมูลสินค้า** (`/product`) มีแค่ช่องค้นหาช่องเดียว ไม่มีแถบตัวกรอง จึง**ไม่ได้ทำ**
- ⚠ **ตาราง legacy เป็น MyISAM ⇒ `DB::transaction()` และ `lockForUpdate()` ไม่ทำงาน** (ตรวจพบ 21/08/2569): ฐานข้อมูลมี **InnoDB 25 ตาราง / MyISAM 33 ตาราง** — ตารางของระบบเดิมที่ใช้บ่อยเป็น MyISAM ทั้งหมด (`morder`, `suborder`, `orderrun`, `appvreq`, `zcustprice`, …) ส่วนตาราง `tb_*` ที่สร้างใหม่เป็น InnoDB
  - MyISAM **ไม่รองรับทรานแซกชัน** — คำสั่ง `DB::transaction()` รันผ่านโดยไม่ error แต่ `rollBack()` **ไม่คืนค่าอะไรเลย** ⇒ โค้ดที่เขียนหลายตารางต่อกัน (`OrderController::save()` = `morder` + `suborder`, `PriceApprovalController::save()` = `appvreq` + `zcustprice`) **ไม่ atomic จริง** ถ้าพังกลางคันจะเหลือข้อมูลค้างครึ่ง ๆ
  - `lockForUpdate()` ก็เป็น no-op ⇒ การจองเลขที่ใบสั่งใน `allocateOrderno()` **ไม่ได้กันสองคนกดพร้อมกันจริง** อย่างที่ตั้งใจไว้ (ยังมี `->exists()` ข้ามเลขซ้ำช่วยอยู่ แต่ไม่ใช่การกันแบบล็อก)
  - **เวลาเขียนสคริปต์ทดสอบ อย่าพึ่ง `DB::beginTransaction()` + `rollBack()` เพื่อกันข้อมูลจริงเปลี่ยน** — มันไม่คืนค่า ต้องจดค่าเดิมไว้แล้วเขียนกลับเอง
  - จะแก้ให้ atomic จริงต้อง `ALTER TABLE ... ENGINE=InnoDB` ซึ่งกระทบข้อมูลลูกค้าโดยตรง — **ต้องถามก่อน** ยังไม่ทำ
- ⚠ **`@include_once` ใน `routes/web.php` ทำให้ Feature test หลายตัวใน process เดียวได้ 404** (พบ 24/08/2569): ไฟล์ route แยกตาม feature ถูกดึงด้วย `@include_once` ⇒ ใน 1 process ของ phpunit ไฟล์เหล่านั้นถูก include **แค่ครั้งแรก** ที่ boot application — test ตัวถัดไปที่ boot ใหม่จะไม่มี route ของเมนูใด ๆ เลย (ได้ 404 ทั้งที่ route ถูกต้อง) กระทบทุกเมนูเท่ากัน ไม่ใช่ปัญหาของหน้าใดหน้าหนึ่ง
  - เวลาเทสต์ ให้รันทีละ test (`--filter <ชื่อ test>`) หรือทดสอบ controller ตรง ๆ ด้วย `Request::create()` แทนการยิงผ่าน route
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
  - **หน้า planning (`production.planning.index`)**: คิวรี join `tb_planning` + `tb_planning_header` จึงตั้ง `name` เป็น qualified column (`tb_planning.itemno`, `tb_planning_header.orderno` — โดยเฉพาะ `custwant`/`company` ที่มีทั้งสองตาราง), แผนกใช้ `->orderColumn('company', 'COALESCE(tb_planning.company, tb_planning_header.company) $1')` ให้ตรงกับที่แสดง, `inner_status` (แสดง `planning_status` ของแถวนั้นเอง + badge ปิดงาน/ยังไม่ปิดงาน, render ผ่าน `addColumn`) คง `orderable:false`. **เทคนิค default order**: ลำดับเริ่มต้นคือ `id desc` (ไม่ใช่คอลัมน์ที่แสดง) จึงใส่ orderBy default แบบมีเงื่อนไข `->when(empty(request('order')), ...)` (บวกเงื่อนไข packing) + ตั้ง `order: []` ใน DataTables — เมื่อผู้ใช้คลิก sort จะมี `order[]` ส่งมา default จึงไม่ทับ ปล่อยให้ Yajra จัดการ
  - **หน้า order-change-request เป็นข้อยกเว้น** (client-side DataTables): ตารางนี้ไม่ใช่ serverSide — controller สร้าง collection ในฝั่ง PHP (group `itemno`) แล้ว blade render ทุกแถวออกมาตรง ๆ จึง init `$('#ocrTable').DataTable()` แบบ client-side (`paging/searching/info` = false เพื่อคงพฤติกรรมเดิม) โดย **ต้อง init เฉพาะเมื่อมีแถว** (`@if($rows->isNotEmpty())`) ไม่งั้นชนกับ empty-row `colspan`; คอลัมน์วันที่/ตัวเลขใส่ `data-order` (Y-m-d / ตัวเลขดิบ) เพื่อ sort ตามค่าจริง และ renumber คอลัมน์ `#` หลัง sort ด้วย event `order.dt`
- **หน้าใบขอเปลี่ยนแปลงคำสั่งซื้อภายใน** (`OrderChangeRequestController`, `/production-planning/order-change-request`, 06/08/2569): แสดง 1 แถวต่อ 1 รหัสสินค้า (group `itemno`) กรองด้วยช่วงวันที่แบบ **OR** — order ที่ **ปิดจบงานในช่วง** (`end_close='Y'` + `end_close_date`) *หรือ* **มีรายการที่เปลี่ยน senddate ในช่วง** (`senddate_changed_at`, ไม่ต้องปิดจบงาน) โดย order ที่เข้ามาเพราะ senddate จะแสดงเฉพาะรายการที่เปลี่ยนจริง; คอลัมน์ "กำหนดเสร็จเดิม" = วันที่ล่าสุดใน `senddate_log`, "ขอเลื่อนเป็นวันที่" = `senddate`, "เลขที่ใบทบทวนคำสั่งซื้อ" = `red_bill_code` — มี export PDF (mPDF) คู่กัน
- **ค่าคงที่ของเทียบสี** อยู่ที่ `config/color_matching.php` (10/08/2569) — `test_type_options` (`testmain.TestType`: 1=CP, 2=DB สีผง, 3=MB สีเม็ด, 4=Pigment) และ `test_result_options` (`testmain.TyResp`) เป็นค่าเดิมจาก Access ทั้งคู่ ใช้ร่วมกันทั้ง dropdown และหน้าแสดงผล; ตัวแปลงค่า→ป้ายชื่ออยู่ที่ `Testmain::testTypeLabel()` / `Testmain::testResult()` (อย่า hardcode ป้ายชื่อซ้ำใน blade)
- **Chemical Safety เลือกได้หลายค่า + เพิ่มค่าใหม่เองได้** (`modal-cm.blade.php` ช่อง `ChemSafety`, 29/08/2569): เปลี่ยนจาก select เดี่ยวเป็น **`<select name="ChemSafety[]" multiple class="select2-tags">`** — เลือกหลายมาตรฐานพร้อมกันได้ และพิมพ์ค่าที่ไม่มีในรายการเองได้ (tags ของ select2 เหมือนเดิม)
  - **เก็บที่ `testmain.ChemSafety` คอลัมน์เดิม (varchar 255) คั่นด้วย `", "`** — ไม่ได้สร้างตารางลูก (คอลัมน์นี้ยังไม่มีข้อมูลจริงสักแถวตอนแก้ จึงไม่มีข้อมูลเดิมต้อง migrate)
  - `ColorMatchingController::joinMulti()` = array → string (trim, ตัดซ้ำ, **แทนคอมมาในแต่ละค่าด้วยช่องว่าง** ไม่งั้นตอนอ่านกลับจะแตกผิดตำแหน่ง, ตัดยาวเกิน 255) · `splitMulti()` = string → array (static, เผื่อที่อื่นเรียกใช้) · `distinctMultiOptions()` แตกค่าที่เก็บรวมกันให้เป็น `<option>` รายตัว (ถ้าใช้ `distinctOptions()` เดิมจะได้ option ที่เป็นค่ารวมทั้งชุด)
  - **`extractPayload()` เซ็ต `ChemSafety` หลังขั้น reject ค่าว่าง** เพราะช่องนี้ต้อง "ล้างค่า" ได้ — คู่กับ **hidden `<input name="ChemSafety[]" value="">`** ในฟอร์ม (select multiple ที่ไม่เลือกอะไรเลยจะไม่ส่ง key มา) · ฟอร์มใบส่ง ต.ย. (`modal-sd`) ไม่มีช่องนี้ → ไม่ส่ง key → ไม่แตะค่าเดิม
  - ฝั่งจอ (`color-matching/index.blade.php`): `fillForm()` หา element แบบ `[name="col[]"]` เพิ่มเมื่อหาชื่อตรงตัวไม่เจอ, split ค่าด้วยคอมมา แล้ว **เพิ่ม `<option>` ให้ค่าที่ยังไม่มีในรายการ** (ค่าที่ผู้ใช้เคยพิมพ์เอง) ก่อน `.val([...])` · `resetColorMatchingForm()` ล้าง multiple ด้วย `[]` และลบ `option[data-select2-tag]` ทิ้ง กัน tag สะสมข้ามใบ
  - ตัวช่วยกลาง `enhanceSelects` รับ **`data-placeholder`** ส่งต่อให้ select2 แล้ว (multiple ไม่มี option "-- เลือก --" จึงต้องมี placeholder เป็น hint)
- **ผลการทดสอบตัวอย่างสี** (`color-matching`, 10/08/2569): ฟอร์มของใบส่ง ต.ย. (SD = แถว `testmain` ที่มี `Testno`) แปลงมาจากฟอร์ม Access เดิม — เก็บลง 3 คอลัมน์ legacy: `TyResp` (ตัวเลือก char(1): `0`=ยังไม่ตอบ, `9`=สั่งซื้อแล้ว, `A`–`H`=เหตุผลที่ไม่สั่งซื้อ — นิยามใน `config/color_matching.php`), `Resp` (ข้อความ "ระบุ", varchar(30)), `Respdate` (วันที่ทราบผล). UI อยู่ที่ `color-matching/modal-result.blade.php` เปิดจากปุ่มในตาราง → `viewTestResult(id)` → `POST color-matching/result/{id}` (`ColorMatchingController::saveResult` — แตะเฉพาะ 3 คอลัมน์นี้ ไม่ใช้ `update()` เพราะ `extractPayload()` ตัดค่าว่างทิ้งและบังคับ `cancel=0`)
- **ข้อมูลจากไฟล์ Access `formula_2000.mdb`** (Compo / PdPrice / TestMai) ถูกคัดลอกมาไว้บน MySQL เป็นตาราง `access_compo` / `access_pdprice` / `access_testmai` แล้ว (migration `create_access_mirror_tables`, 05/08/2569) — โค้ดทั้งหมดอ่านจาก MySQL เพราะ server ของลูกค้าไม่มีไฟล์ .mdb และไม่มี ODBC driver
  - **อัพเดทข้อมูลจาก `public/formula_2000_AddData.mdb` แล้ว — 27/08/2569** (ไฟล์ที่ลูกค้าส่งมาเพิ่ม มี 2 ตาราง ชื่อ **`P_PdPrice` / `P_TestMai`** — ไม่ใช่ `PdPrice`/`TestMai` เหมือนไฟล์เดิม)
    - `access_pdprice`: **297 → 47,461 แถว** · `access_testmai`: **44 → 2,460 แถว** (ไฟล์ใหม่ครอบคลุมของเดิมครบทุกแถว) · `access_compo` **ไม่มีในไฟล์นี้ จึงไม่ถูกแตะ**
    - วิธีนำเข้า: **แทนที่ทั้งตารางด้วยไฟล์ใหม่ แต่ยกแถวเดิมที่ key ไม่มีในไฟล์ใหม่มาเก็บไว้** — ปัจจุบันคือ `PdCode` **2800103 / 2800104** (ราคา 985,498.35 / 812,715.17 สูงผิดปกติ น่าจะเป็นข้อมูลเสีย แต่ยังไม่ลบ) · `id` เดินใหม่ทั้งตาราง (ไม่มีโค้ดไหนอ้าง `id` — มีแค่ tiebreaker ตอนเรียงใน `SaleinfoController::testPrice`)
    - ⚠ **ราคาทุนในไฟล์ใหม่เป็นจำนวนเต็มล้วน** (ตรวจแล้ว 0 จาก 47,459 แถวมีทศนิยม — ต้นทางปัดมาเอง) ⇒ ค่าเดิมที่มีทศนิยมถูกทับไป 285 รหัส เช่น `1107550` 137.36 → 137 · บางรหัสเปลี่ยนจริงไม่ใช่แค่ปัด เช่น `1500102` 71.40 → 64 (**ผู้ใช้ยืนยันแล้วว่าให้ยึดไฟล์ใหม่**)
    - เครื่องนี้อ่าน .mdb ได้ผ่าน ODBC (`Microsoft Access Driver (*.mdb, *.accdb)` + PHP ext `odbc`) — **ต่างจากเครื่อง server ของลูกค้าที่ไม่มี** จึงยังต้องนำเข้ามาไว้บน MySQL เหมือนเดิม ไม่ใช่อ่านสด
    - สคริปต์นำเข้าเป็นงานครั้งเดียว อยู่ใน scratchpad ของ session ไม่ได้เก็บเข้าโปรเจกต์ (ถ้าไฟล์ .mdb จะมาเป็นรอบ ๆ ควรทำเป็น artisan command)
  - 🔴 **ภาษาไทยจากไฟล์ Access ไม่ได้เสียถาวร — ต้องอ่านด้วย OLEDB ไม่ใช่ PHP `ext/odbc`** (ค้นพบ 28/08/2569)
    - ข้อความเดิมทั้งหมดที่บอกว่า "ภาษาไทยในไฟล์ Access อ่านออกมาเป็น `?` ตั้งแต่ต้นทาง (system codepage)" **ผิด** — สแกนไฟล์ `.mdb` ระดับไบต์แล้วพบ **คู่ไบต์อักษรไทยแบบ UTF-16LE 230,253 ตัว** เทียบกับ `?` ที่เก็บจริงแค่ 351 ตัว ⇒ ข้อมูลไทยอยู่ในไฟล์ครบ
    - สาเหตุ: **PHP `ext/odbc` เรียก ODBC ผ่าน ANSI API** ⇒ driver แปลงข้อความเป็น system ANSI codepage ของเครื่อง (ไม่ใช่ 874) อักษรไทยจึงกลายเป็น `?` **ตอนอ่าน** ไม่ใช่ตอนเก็บ
    - **วิธีที่ถูกต้อง: ACE OLEDB ผ่าน PowerShell** (`Microsoft.ACE.OLEDB.12.0` — เครื่องนี้มีทั้ง 12.0 และ 16.0, PowerShell 64-bit) → `System.Data.OleDb` คืน string เป็น .NET Unicode ตรง ๆ ได้ไทยครบ แล้วค่อยเขียนเป็น JSON UTF-8 ให้ PHP นำเข้า MySQL ต่อ
    - ตัวอย่างที่กู้ได้: `CName` "โพลาร์" / "ทานตะวัน" · `Matcher` "ศักดิ์ดา" / "อดิเทพ" · `access_compo.PdCodes` "น้ำหอมกลิ่นส้มล/ค"
    - **ซ่อมข้อมูลใน DB แล้ว 28/08/2569**: `access_testmai` นำเข้าใหม่ทั้ง 2,460 แถว (ค่าตัวเลข/ธง/วันที่ตรงกับชุดเดิมทุกแถว ต่างแค่ข้อความที่ได้ไทยคืน — `Matcher` 2,459 แถว · `CName` 2,230 · `Resin` 2,200 · `TDecs` 1,017 · `CResin` 1,196) · `access_compo.PdCodes` ซ่อมแบบเจาะจง 8 แถว (ดึงค่าที่ถูกจากไฟล์เก่า `C:\AccessDB\formula_2000.mdb` ซึ่ง**ยังอยู่ในเครื่อง**) · `access_pdprice` ไม่ต้องแก้ (`PdCode` เป็น ASCII ล้วน)
    - `Othe` เหลือ `?` อยู่ 3 แถว — อันนั้นเป็น `?` จริงในไฟล์ ไม่ใช่ข้อมูลเสีย
    - ⚠ **ครั้งหน้าที่นำเข้าไฟล์ .mdb ห้ามใช้ `odbc_connect`/`PDO_ODBC` ของ PHP กับตารางที่มีข้อความไทย** — ใช้ OLEDB ผ่าน PowerShell เสมอ
    - ⚠ **อย่า INSERT ภาษาไทยผ่าน `mysql -e "..."` บน Windows console** — console codepage ทำให้ข้อความเสียตั้งแต่ก่อนถึง MySQL (เจอตอนสร้างข้อมูลทดสอบ) ให้เขียนผ่านไฟล์ .sql หรือ PHP/PDO แทน
  - โค้ดเดิมที่ต่อ ODBC ยัง**คอมเมนต์ไว้**ใน `AccessService`, `ProductPriceService::findPdPrice()`, `AccessModel` — เปิดคืนได้ถ้าจะกลับไปอ่านไฟล์จริง (ต้องตั้ง `ACCESS_DB_PATH` ใน `.env` ด้วย)
  - connection `access` ใน `config/database.php` + `DB::extend('access', ...)` ใน `AppServiceProvider` ยังอยู่ แต่ไม่มีใครเรียกแล้ว (resolve แบบ lazy จึงไม่พังตอน boot)
  - ดูข้อมูลทั้ง 3 ตารางได้ที่ท้ายหน้า **กำหนดราคา** (`/saleinfo`) — แท็บ Compo / PdPrice / TestMai อ่านอย่างเดียว ผ่าน `SaleinfoController::accessData()` → `saleinfo/access-table.blade.php` (05/08/2569)
- **ตารางเงื่อนไขคิดราคาขาย** (`config/product_price.php` + `ProductPriceService`, 10/08/2569): แต่ละแถวมี `key` (ห้ามเปลี่ยน/ซ้ำ), `prefix`, `suffix`, `suffix_pos` (= ตัวลงท้ายต้องเริ่มที่ตัวที่ N พอดี เช่น "MB ตัวที่ 8 ลงท้ายด้วย P/PC/K/J") และ `mul`/`div`/`add`
  - โครงเงื่อนไข (label/prefix/suffix/suffix_pos) แก้ที่ไฟล์ config เท่านั้น
  - ส่วน **คูณ/หาร/บวก ผู้ใช้แก้เองได้** → เก็บลงตาราง `tb_price_rule` (`rule_key` unique) ซึ่ง **ทับ** ค่าใน config; แถวที่ค่าตรงกับค่าตั้งต้นจะถูกลบทิ้ง = กลับไปใช้ค่า config
  - **หน้าจอแก้ค่าย้ายออกมาเป็นเมนูของตัวเองแล้ว: "ตั้งค่าเงื่อนไขราคา" `/price-rule` (21/08/2569)** — `PriceRuleController` (`index` / `data` → JSON / `update`) + `routes/pricerule.php` + view `pricerule/index.blade.php`; menu key **`PriceRule`** อยู่ใต้หัวข้อ Settings (ต่อจาก "สถานะ Planning"). เดิมเป็น modal `saleinfo/modal-pricerule.blade.php` ในหน้า `/saleinfo` — **ลบทิ้งแล้ว** พร้อม method `SaleinfoController::priceRules/priceRulesUpdate` + route `saleinfo/price-rules*` (ย้ายโค้ดมาทั้งชุด ตรรกะเดิมทุกอย่าง); **ปุ่ม "ตั้งค่าเงื่อนไขราคา" บนหัวหน้า `/saleinfo` ปิดไปแล้ว** เข้าได้ทางเมนูอย่างเดียว
  - **หน้าจอไม่แสดงเรื่อง "ค่าตั้งต้น" เลย** (21/08/2569) — ไม่มีคอลัมน์ค่าตั้งต้น ไม่มีปุ่มคืนค่า และไม่มีป้าย "แก้แล้ว" เพราะค่าจริงถูกแก้อยู่ตลอด ค่าใน config จึงไม่ใช่ค่าอ้างอิงที่มีความหมายกับผู้ใช้ · ตาราง 4 คอลัมน์ = เงื่อนไข / คูณ / หาร / บวก เท่านั้น (ฝั่ง server ยังลบแถว `tb_price_rule` เมื่อค่าตรงกับ config เหมือนเดิม — ผู้ใช้ไม่เห็นความต่าง). มีบรรทัดบอกตัวคูณระหว่างขั้นราคา (`tier.price_2_from_price_1` / `price_3_from_price_2` — อ่านอย่างเดียว แก้ที่ config)
  - ⚠ สิทธิ์คุมด้วย **menu key** (`AccessControl` เทียบ namespace ของ route name = `pricerule`) ไม่ใช่ค่าในช่อง `permission` → พนักงาน (guard `emp`) ต้องไปติ๊กเมนู "ตั้งค่าเงื่อนไขราคา" ให้ role ที่หน้า "จัดการสิทธิ์" ก่อน ถึงจะเข้าได้ (admin เข้าได้อยู่แล้ว)
  - **ฟอร์ม "กำหนดราคา" (`/saleinfo` → modal `saleinfo/modal-price.blade.php`) คำนวณกล่อง "คำนวนราคา" แล้ว (25/08/2569)** — ราคา 1/2/3 + DB 3-4 / 1-2 Kg. ยิง `saleinfo/price-lookup` (endpoint เดียวกับจอ "ค้นหาราคาสินค้า") ตอนพิมพ์ **รหัสสินค้า (`ITEMNO`)** โดยหน่วง 400ms · ยังไม่กรอก `ITEMNO` → ใช้ **ชื่อสินค้า (`st_code`)** แทน (ตรงกับ `SaleinfoController::extractForm` ที่ ITEMNO ว่าง = ใช้ st_code) · โหมดแก้ไขคำนวณให้ทันทีหลังเติมฟอร์ม · มีบรรทัดบอกที่มา `#saleinfo_price_note` (ราคาทุน · เงื่อนไขที่เข้า · สูตร) และขึ้นกรอบแดงพร้อมเหตุผลเมื่อคำนวณไม่ได้ · **ช่อง `db_price_*` เป็นแค่การแสดงผล ไม่ได้บันทึก** (ไม่มีใน `SaleinfoController::COLUMNS` และ `uprice` ไม่มีคอลัมน์นี้) · ⚠ กล่อง **"ค่าสีทั้งสิ้น / % สี" ยังไม่รู้สูตร** จึงคง class `wip` ไว้เฉพาะกล่องนั้น
  - **จอ Test Price (`/saleinfo` → modal `saleinfo/modal-testprice.blade.php`) ต่อข้อมูลแล้ว (25/08/2569)** — อ่านอย่างเดียว ไม่มีบันทึก ผ่าน `GET saleinfo/test-price` → `SaleinfoController::testPrice()`
    - **ใบเทส = `access_testmai`** (สำเนา TestMai ของไฟล์ Access): `TestNo` = ช่อง Test No. (รูปแบบ `26/0055/1`, **ไม่ซ้ำในตาราง**) · `Lotno` = Lot Test (**ซ้ำได้**) · `TDecs` = Sample · `CResin` = Resin ที่ลูกค้าใช้ · `Resin` = Resin (Match) · `CCode` = รหัสลูกค้า
    - **`Test No.` กับ `Lot Test` เป็นคีย์ค้นเท่าเทียมกัน — กรอกช่องไหนก็ได้ (25/08/2569)** ส่วน `Customer` เป็นตัวกรองเสริม; **ช่องที่ผู้ใช้ไม่ได้กรอก ระบบเติมให้เองจากใบที่เจอ** (ค้นด้วย Lot → ได้ Test No. · ค้นด้วย Test No. → ได้ Lot) ช่องที่ระบบเติมขึ้นพื้นเหลือง (class `tp-autofill`) + มี tooltip
    - ⚠ **ค่าที่ระบบเติมต้องไม่ถูกส่งกลับไปเป็นเงื่อนไขค้นรอบถัดไป** (ธง `tpAuto.testno` / `tpAuto.lottest` ในหน้า index) — ไม่งั้นพอผู้ใช้แก้อีกช่อง ผลจะค้างอยู่ใบเดิม · กติกา: พิมพ์ช่องไหน = ช่องนั้นเป็นของผู้ใช้ ช่องที่ระบบเติมไว้ให้ทิ้งทันที (`tpUserTyped()`); แก้ Customer = ทิ้งทั้งสองช่อง
    - **ค้นแบบตรงตัวก่อน ไม่เจอค่อยค้นแบบใกล้เคียง (`LIKE %...%`) — ใช้กับทั้ง Test No. และ Lot** แล้วติดธง `loose` กลับมาให้จอบอกผู้ใช้ (จอนี้อ่านอย่างเดียว ผู้ใช้มักพิมพ์มาไม่ครบท่อน เช่น `26/0058` → เจอทั้ง `/3` และ `/4`)
    - เจอหลายใบ = แสดงใบล่าสุด (`Tdate` → `id`) แล้วบอกจำนวน + เลขใบอื่นบนหัวการ์ด (response แนบ `testnos` มาสูงสุด 20 ใบ) เพื่อให้พิมพ์ Test No. เจาะจงต่อได้ — **ข้อมูลจริง: `TestNo` ไม่ซ้ำเลย ส่วน `Lotno` ซ้ำอยู่ 5 คู่** (เช่น `690618/58` = `26/0058/3` + `26/0058/4` — เทสหลายทางเลือกใน Lot เดียวกัน) จึงยังต้องมีทางออกตรงนี้ไว้
    - **"ตั้งเบอร์เป็น" = `access_compo.PdCode`** ของ `TestNo` นั้น (1 ใบตั้งได้หลายเบอร์ → คั่นด้วย ", ") — ⚠ **ไม่ใช่** `testmain.CodeNo` (คนละตาราง คนละชุดเลขที่)
    - **ชื่อลูกค้าอ่านจาก `customer.name`** ไม่ใช่ `access_testmai.CName` — เดิมเพราะภาษาไทยในคอลัมน์นั้นเป็น `?` **แต่ซ่อมแล้ว 28/08/2569** (ดูหัวข้อ "ภาษาไทยจากไฟล์ Access" ท้ายไฟล์) ตอนนี้ `CName`/`Resin`/`Matcher` มีภาษาไทยครบ · ยังคงอ่าน `customer.name` ต่อไปเพราะเป็นชื่อทางการที่ผูกกับรหัสลูกค้า ส่วน `CName` เป็นชื่อย่อที่พิมพ์ไว้ในใบเทส
    - **ราคา 1/2/3 + DB 3-4 / 1-2 Kg. = `ProductPriceService::quote(เบอร์ที่ตั้ง, TNet)`** — เมธอด `quote()` เพิ่มใหม่ (25/08/2569) = สูตรเดียวกับ `lookup()` ทุกอย่าง ต่างแค่ **รับราคาทุนมาเอง** แทนการอ่าน `access_pdprice` (`lookup()` ถูก refactor ให้เรียก `quote()` ต่อ) เพราะเบอร์ที่เพิ่งตั้งจากใบเทสมักยังไม่มีในตารางราคาทุน
    - **ต้นทุน = `access_testmai.TNet`** — ตรวจกับข้อมูลจริงแล้วว่า `TNet` = ผลรวม `CNet` ของสูตรใน `access_compo` และตรงกับ `access_pdprice.Price` ของเบอร์นั้น 8 ใน 9 ใบที่ตั้งเบอร์แล้ว (อีกใบต่างเพราะราคาทุนถูกปรับทีหลัง)
    - ใบที่ **ยังไม่ได้ตั้งเบอร์** → เลือกเงื่อนไขราคาไม่ได้ ช่องราคาเว้นว่าง + ขึ้นเหตุผลที่ `#tp_price_note` (บรรทัดบอกที่มา: ต้นทุนสูตร · เบอร์ · เงื่อนไข · สูตร)
    - ⚠ **section "Price Quotation" ยังไม่ทำ** (การ์ดนั้นยังเป็น `wip` — ปุ่ม SEARCH ในนั้นทำหน้าที่ค้นใหม่เฉย ๆ ไม่ได้เติมค่าช่อง `#tp_quotation`)
  - `ProductPriceService::rules()` เป็นตัว merge config + override (แถวจะมี `is_custom` / `default` ติดมาด้วย) — โค้ดที่ต้องการตารางเงื่อนไข **ห้ามอ่าน `config('product_price.rules')` ตรง ๆ** ให้เรียกผ่าน `rules()` ไม่งั้นจะได้ค่าตั้งต้นเสมอ
