-- แปลงแล้ว 17/11/2569: TIS-620(CP874) -> UTF-8 · TYPE= -> ENGINE= · INSERT ระบุชื่อคอลัมน์
-- โครงสร้างตาม DB ปัจจุบัน (zwek_worldpigment_2)
SET NAMES utf8;
SET SESSION sql_mode='';

/*
MySQL Data Transfer
Source Host: localhost
Source Database: Order
Target Host: localhost
Target Database: Order
Date: 17/11/26 10:54:47
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ordrem
-- ----------------------------
DROP TABLE IF EXISTS `ordrem`;
CREATE TABLE `ordrem` (
  `rem` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`rem`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `ordrem` (`rem`) VALUES ('ระบุรหัสสินค้าที่ข้างกล่องว่า');
INSERT INTO `ordrem` (`rem`) VALUES ('รอ SEMI / TCTR');
INSERT INTO `ordrem` (`rem`) VALUES ('รอแม่สี');
INSERT INTO `ordrem` (`rem`) VALUES ('รอวัตถุดิบลูกค้า');
INSERT INTO `ordrem` (`rem`) VALUES ('รอผล QC');
INSERT INTO `ordrem` (`rem`) VALUES ('รับเม็ดวันที่');
INSERT INTO `ordrem` (`rem`) VALUES ('รับมาแก้ไข');
INSERT INTO `ordrem` (`rem`) VALUES ('มารับเอง จ่ายสด');
INSERT INTO `ordrem` (`rem`) VALUES ('ยกเลิก ORDER');
INSERT INTO `ordrem` (`rem`) VALUES ('แจ้งเซลส์ก่อนเปิด');
INSERT INTO `ordrem` (`rem`) VALUES ('ส่วนลด 4%');
INSERT INTO `ordrem` (`rem`) VALUES ('ส่งของ แนบ PO');
INSERT INTO `ordrem` (`rem`) VALUES ('สีไม่เหมือน');
INSERT INTO `ordrem` (`rem`) VALUES ('สูตรใหม่');
INSERT INTO `ordrem` (`rem`) VALUES ('Order ส่งนอก');
INSERT INTO `ordrem` (`rem`) VALUES ('QC ไม่ทัน');
INSERT INTO `ordrem` (`rem`) VALUES ('ลูกค้าขอเลื่อน');
INSERT INTO `ordrem` (`rem`) VALUES ('ลงของ-เก็บเช็ค');
INSERT INTO `ordrem` (`rem`) VALUES ('ลงของ-เก็บสด, แจ้งเซลส์ก่อนเปิด');
INSERT INTO `ordrem` (`rem`) VALUES ('กล่องแรก แพค 0.5 x 1');
INSERT INTO `ordrem` (`rem`) VALUES ('ขอ ต.ย. ให้ลูกค้าQCจำนวน(กรัม)');
INSERT INTO `ordrem` (`rem`) VALUES ('ขอใบ Com');
INSERT INTO `ordrem` (`rem`) VALUES ('ขอแผ่น RP 2 ชุด');
INSERT INTO `ordrem` (`rem`) VALUES ('ด่วนมาก');
INSERT INTO `ordrem` (`rem`) VALUES ('ตัดจบ');
INSERT INTO `ordrem` (`rem`) VALUES ('ตัดผลิตสีด่วน');
INSERT INTO `ordrem` (`rem`) VALUES ('ติดสติกเกอร์ วันผลิต');
INSERT INTO `ordrem` (`rem`) VALUES ('ที่บิล / ที่กล่อง ไม่ใส่จุด');
INSERT INTO `ordrem` (`rem`) VALUES ('บรรจุ');
INSERT INTO `ordrem` (`rem`) VALUES ('บรรจุกล่องละ 20 ก.ก.');
INSERT INTO `ordrem` (`rem`) VALUES ('ปรับแก้ไข');
INSERT INTO `ordrem` (`rem`) VALUES ('ผลิต');
