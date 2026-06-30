# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ระบบบริหารจัดการสำหรับโรงงานผลิตสีและพิกเมนต์ ครอบคลุมตั้งแต่การเทียบสี การวางแผนการผลิต การออกใบเสนอราคา ไปจนถึงระบบรายงานครบวงจร

## Project

World Pigment (`worldpigment`) — a management system for a paint/pigment factory: color matching, production planning, quotations, orders, customers, equipment, and reports. UI text, comments, and domain terms are in **Thai**. Laravel 9 / PHP 8 backend, Blade + Tailwind + Alpine/jQuery frontend on the Left4code "Enigma" admin template.

The repo lives under XAMPP (`C:\xampp\htdocs\worldpigment`); local DB is MySQL via XAMPP.

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
│   │   ├── OrderController.php
│   │   ├── QuotationController.php
│   │   ├── CustomerController.php
│   │   ├── ReportController.php
│   │   ├── UserController.php
│   │   ├── PermissionController.php
│   │   ├── PDFController.php
│   │   └── ExportExcelController.php
│   └── Models/
├── config/
│   └── menu.php                 # โครงสร้างเมนู
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
│   ├── order.php
│   ├── customer.php
│   ├── report.php
│   └── permission.php
└── public/
```

## Codeing Roules
- หลีกเลี่ยง any ถ้าไม่จำเป็น
- เขียนโค้ชให้อ่านง่ายมากกว่าสั้นเกินไป
- ห้ามลบโค๊ดเดิมถ้าไม่เข้าใจหน้าที่ของมัน

## Workflow
ก่อนแก้ไขโค๊ดให้ทำตามนี้:
- อ่านไฟล์ที่เกี่ยวข้องก่อน
- อธิบายแผนการแก้แบบสั้นๆ
- แก้ไขเฉพาะไฟล์ที่จำเป็น
- ตรวจว่าโค๊ดไม่กระทบส่วนอื่น
- สรุปสิ่งที่แก้หลังทำเสร็จ


## Commands

```bash
composer install          # PHP deps
npm install               # JS deps
php artisan serve         # dev server -> http://localhost:8000

# Frontend build (two steps — Mix does NOT compile resources/css/app.css)
npm run dev               # one-off Mix build
npm run watch             # concurrently runs postcss (app.css) + mix watch
npm run prod              # production build (postcss app.css + mix --production)

# Tests
vendor/bin/phpunit                                  # all tests
vendor/bin/phpunit --testsuite Unit                 # one suite (Unit | Feature)
vendor/bin/phpunit --filter testMethodName          # single test

php artisan migrate --seed
```

There is a `GET /clc` route that clears + rebuilds all caches (cache/config/view/route) — use it after editing `config/` or routes when changes don't appear.

Lint/style is enforced by **StyleCI** (`.styleci.yml`, Laravel preset, PHP 8; `no_unused_imports` disabled) — there is no local lint command.

## Routing architecture

`routes/web.php` is the entry point. Most feature routes live in **separate per-feature files** in `routes/` and are pulled in with `@include_once(...)` from *inside* the `Route::middleware('auth')` group in `web.php`:

- `color-matching.php`, `quotation.php`, `order.php`, `customer.php`, `report.php`, `permission.php`, `production.php`

So a route in those files is implicitly auth-protected by where it's included — don't re-wrap it in `auth`. When adding a new module, create a `routes/<module>.php` and `@include_once` it in the same group. `production.php` is included last in `web.php` and groups everything under the `production-planning` URL prefix with `production.*` route names.

`GET /` redirects authenticated users to `production.planning.index` (the app's home), guests to `login.index`. Auth is session-based; the custom `loggedin` middleware (`app/Http/Middleware/LoggedIn.php`) bounces already-authenticated users away from login/register.

## Menu & permission model

The visible navigation is defined declaratively in **`config/menu.php`** as a nested array; each item carries `route_name` and a `permission` string of the form `"<module> read"` (e.g. `color_matching read`, `productionplanning read`). Menus are injected into every view by `ViewServiceProvider` via `View::composer('*', ...)` → `App\Http\View\Composers\MenuComposer`, which pulls from `App\Main\{SideMenu,TopMenu,SimpleMenu}` and computes the active-menu indices by matching the current route name.

Multi-branch authorization is keyed on `session("branch_id")`: see `User::permission_group_has_user_branch()`, `user_has_branch()`, and `branch()`.

## Production planning data model (parent-child)

This is the core domain and the part most worth understanding before editing:

- `PlanningHeader` (`tb_planning_header`) `hasMany` `Planning` (`tb_planning`).
- `Planning` has a self-referential `parent_planning_id` and exposes `semi_headers()` / `pigment_headers()` — child `PlanningHeader`s with `plan_type` of `semi` / `pigment` that are auto-created from a planning item. `sub_headers()` / `subHeadersRecursive()` / `planningsRecursive()` walk this tree to arbitrary depth.
- `SemiPigment` (`tb_semi_pigment`) is an approval workflow: `status` ENUM `request` → `approved` / `reject` (constants + Thai labels on the model). On approval, `convertplanning` produces a `result_planning`. The `SemiPigmentController` handles entry CRUD from the Planning-item modal, the pending-approval list, and the approved list.

## Conventions & gotchas

- Models use `protected $guarded = []` (mass assignment fully open) and explicit `protected $table = 'tb_...'`. The schema is largely **database-first**: only 11 migrations exist and the recent ones only *alter* `tb_planning` / `tb_semi_pigment` / add `tb_planning_status`. Do not assume migrations describe the full schema — inspect the live DB.
- **Legacy/dead code:** this template was previously a room/rental system. `app/Http/Controllers/Controller.php` (the base controller) still contains large `summary()`/`summary_calculate()` methods referencing models that no longer exist (`Room`, `RentBill`, `Receipt`, `Renter`, `Contract`, …), and `App\Main\SideMenu::menu()` has an early `return` followed by hundreds of lines of unreachable template scaffolding. Some `report.*` routes in `web.php` (rooms, rent bills, invoices) are also leftovers. Don't treat these as live wiring; verify a route/method is actually reachable before relying on it.
- Controllers commonly expose paired routes: an `index` (Blade page) plus a `datatable` endpoint feeding Yajra DataTables / Tabulator via AJAX, and often `excel`/`pdf` export routes (DomPDF + mPDF, PhpSpreadsheet).
