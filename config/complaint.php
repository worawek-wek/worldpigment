<?php

/*
 * ระบบ Complaint & NCR — ค่าคงที่ (26/11/2569)
 */
return [

    // ข้อ 14 สรุปสาเหตุ (nc.CauseType) — key = ตัวอักษรที่เก็บใน DB ห้ามเปลี่ยน
    // ⚠ ยังไม่ได้ความหมายจากผู้ใช้ — เติมคำอธิบายแทน null ได้เลย (จะขึ้นใน dropdown + Excel)
    'cause_types' => [
        'A' => null,
        'B' => null,
        'C' => null,
        'D' => null,
        'E' => null,
        'F' => null,
        'G' => null,
        'H' => null,
        'I' => null,
    ],

];
