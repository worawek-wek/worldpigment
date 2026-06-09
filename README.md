# World Pigment — ระบบบริหารจัดการโรงงานสี

ระบบบริหารจัดการสำหรับโรงงานผลิตสีและพิกเมนต์ ครอบคลุมตั้งแต่การเทียบสี การวางแผนการผลิต การออกใบเสนอราคา ไปจนถึงระบบรายงานครบวงจร

---

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

---

## ฟีเจอร์หลัก

### เทียบสี (Color Matching)
- บันทึกข้อมูลการทดสอบสี (SendNo, TestDate, TestType, Model, Lotno)
- ติดตามสถานะ: วันรับตัวอย่าง → วันเทียบสี → วันส่งสำเร็จ
- บันทึกค่าคุณสมบัติสี เช่น Density, VR, Hardness

### วางแผนการผลิต (Production Planning)
- จัดการ Sale Order และ Production Plan
- รองรับโครงสร้างแบบ Parent-Child (Semi-Pigment / Pigment)
- ติดตามสถานะการผลิตแต่ละขั้นตอน

### ใบเสนอราคา & คำสั่งซื้อ
- สร้างและจัดการใบเสนอราคา (Quotation)
- บริหารคำสั่งซื้อ (Order / Sub-Order)
- Export PDF และ Excel

### ลูกค้า (Customer)
- ฐานข้อมูลลูกค้าแบบรวมศูนย์
- รองรับหลายสาขา (Multi-branch)

### อุปกรณ์ (Equipment)
- จัดการคลังอุปกรณ์
- ติดตามประวัติการเคลื่อนไหว (Stock History)

### รายงาน (Reports)
- รายงานรายได้
- ใบกำกับภาษี
- หนี้สูญ (Bad Debt)
- รายงานรายเดือน
- รายงานรับ-คืน สินค้า
- และอื่นๆ อีกกว่า 12 ประเภท

### ผู้ใช้ & สิทธิ์
- Role-based permission system
- รองรับหลายสาขา (Multi-branch)
- Dark mode และ color scheme switcher

---

## การติดตั้ง

### ความต้องการของระบบ

- PHP >= 8.0
- Composer
- Node.js >= 14 + npm
- MySQL >= 5.7
- XAMPP / Laragon (หรือ web server อื่นๆ)

### ขั้นตอนติดตั้ง

```bash
# 1. Clone โปรเจกต์
git clone https://github.com/worawek-wek/worldpigment.git
cd worldpigment

# 2. ติดตั้ง PHP dependencies
composer install

# 3. ติดตั้ง Node.js dependencies
npm install

# 4. คัดลอกไฟล์ environment
cp .env.example .env

# 5. สร้าง application key
php artisan key:generate

# 6. ตั้งค่าฐานข้อมูลใน .env
# DB_DATABASE=worldpigment
# DB_USERNAME=root
# DB_PASSWORD=

# 7. รัน migration และ seeder
php artisan migrate --seed

# 8. Build assets
npm run dev
# หรือสำหรับ production
npm run prod
```

### เปิดใช้งาน

```bash
php artisan serve
```

เข้าใช้งานที่ `http://localhost:8000`

---

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

---

## Environment Variables หลัก

```env
APP_NAME=WorldPigment
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=worldpigment
DB_USERNAME=root
DB_PASSWORD=
```

---

## License

This project is proprietary software. All rights reserved.
