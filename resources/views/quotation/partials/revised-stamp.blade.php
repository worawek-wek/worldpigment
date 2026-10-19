{{-- ตราประทับ REVISED + วันที่แก้ไข (19/10/2569) — ใช้ใน quotation/print ทั้งหัวไทยและอังกฤษ
     $revisedStamp = ข้อความวันที่ที่จัดรูปแบบแล้ว (null = ใบนี้ไม่มี Revise Date → ไม่แสดงตรา) --}}
@if (!empty($revisedStamp))
    <div class="revised-stamp">
        <div class="word">REVISED</div>
        <div class="date">{{ $revisedStamp }}</div>
    </div>
@endif
