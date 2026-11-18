-- แปลงแล้ว 17/11/2569: TIS-620(CP874) -> UTF-8 · TYPE= -> ENGINE= · INSERT ระบุชื่อคอลัมน์
-- ตารางนี้ไม่มีใน DB — ใช้โครงสร้างจากไฟล์ต้นฉบับ
SET NAMES utf8;
SET SESSION sql_mode='';

/*
MySQL Data Transfer
Source Host: localhost
Source Database: Order
Target Host: localhost
Target Database: Order
Date: 17/11/26 10:54:54
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for serious
-- ----------------------------
DROP TABLE IF EXISTS `serious`;
CREATE TABLE `serious` (
  `JobType` char(1) NOT NULL default '',
  `JobNo` varchar(15) NOT NULL default '',
  `Prob` varchar(50) default NULL,
  `MKdate` date default '0000-00-00',
  `empMK` varchar(15) default NULL,
  `statusupd` varchar(100) default NULL,
  `empPD` varchar(15) default NULL,
  PRIMARY KEY  (`JobType`,`JobNo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62715', 'ล/คต้องการสีด่วนมาก', '2015-11-25', 'ดวงแข', 'รอผลตรวจสอบ 1', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CM08031', 'ขาดแม่สี ลูกค้าเรียกเข้าไม่มีของ', '2017-06-14', 'ประภาพรรณ', 'แม่สียังไม่เข้าเปิดงาน26/5', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67751', 'ล/คขอสีด่วนมาก', '2015-11-04', 'ดวงแข', 'รอผลิตแก้ไข 2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67749', 'ลูกค้าขอสีด่วน', '2015-11-04', 'ดวงแข', 'รอผลิตแก้ไข2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67785', 'ขาดแม่สีผลิต', '2015-11-04', 'ดวงแข', 'ขาดแม่สี', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62366', 'ลูกค้าขอด่วนมาก', '2015-11-04', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณรุ่งฤทธิ์');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62302', 'ลูกค้าขอสีด่วน', '2015-11-04', 'ดวงแข', 'รอผลิตR2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CM67677', 'ลูกค้าสีหมดStockด่วน', '2015-11-28', 'ประภาพรรณ', 'แม่สีเข้าทั้งหมดแล้วแต่ผลิตได้50KG.ส่งจันทร์', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67949', 'ล/คต้องการสีด่วน', '2015-11-12', 'ดวงแข', 'รอผลตรวจสอบ G2', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62597', 'ล/คต้องการสีด่วนมาก', '2015-11-12', 'ดวงแข', 'รอผลตรวจสอบ 1', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62552', 'สีเลยดิวล/ค,ล/คต้องการด่วน', '2015-11-12', 'ดวงแข', 'รอผลตรวจสอบ', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67937', 'ล/คต้องการสีด่วน', '2015-11-12', 'ดวงแข', 'แม่สีเข้าวันนี้รอผลิต', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67936', 'ล/คต้องการสีด่วน', '2015-11-12', 'ดวงแข', 'รอผลตรวจสอบ G2', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67837', 'สีเลยดิวล/ค,ล/คต้องการด่วนมาก', '2015-11-12', 'ดวงแข', 'รอผลิตแก้ไข 1', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67958', 'ล/คต้องการสีด่วนมาก', '2015-11-12', 'ดวงแข', 'รอผลตรวจสอบ G1', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62578', 'เลยดิวล/คาหลายวันแล้ว,ล/คขอด่วนมาก', '2015-11-16', 'ดวงแข', 'ข.TC5A171+1105041.2', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68000', 'ล/คขอด่วนมาก', '2015-11-16', 'ดวงแข', 'แก้ไข', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68019', 'ล/คต้องการสีด่วนมาก', '2015-11-16', 'ดวงแข', 'แก้ไข', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI67992', 'ล/คขอด่วนมาก', '2015-11-16', 'ดวงแข', 'แก้ไข', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CM68022', 'ลูกค้าสีหมดStockด่วน', '2015-11-28', 'ประภาพรรณ', 'แม่สีเข้าทั้งหมดแล้วแต่ผลิตได้50KG.ส่งจันทร์', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HM62231', 'สีไม่พอขายด่วน', '2015-11-23', 'สุพิชชา', 'เครื่องผลิตมีเครื่องเดียว', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62915', 'คุณปาล์มด่วนวันนี้ค่ะ', '2015-11-24', 'ประภาพรรณ', 'รอผลิตแก้ไข1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68200', 'ล/คต้องการด่วนมาก', '2015-11-25', 'ดวงแข', 'รอผลตรวจสอบG1', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68238', 'ล/คขอด่วนมาก', '2015-11-25', 'ดวงแข', 'รอผลิต', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68173', 'เลยกำหนดส่งล/คมา2วันแล้ว,ล/คขอด่วนมาก', '2015-11-25', 'ดวงแข', 'รอผลตรวจสอบG1', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63016', 'ล/คต้องการสีด่วนมาก', '2015-11-25', 'ดวงแข', 'รอผลิต', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI62939', 'ล/ค ต้องการสีด่วนมากๆ', '2015-11-26', 'ดวงดาว', 'รอแก้ไข', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68345', 'ลูกค้าขอสีด่วนมาก', '2015-12-02', 'ประภาพรรณ', 'QC G1 ไม่ผ่าน', 'คุณรุ่งฤทธิ์');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68346', 'ลูกค้าต้องการสีด่วน', '2015-12-02', 'ประภาพรรณ', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68430', 'ลูกค้าด่วนมากขอวันนี้', '2015-12-07', 'ประภาพรรณ', 'กำลังลงเครื่องปั่น', 'อาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63214', 'ล/คต้องการสีด่วน,เซลล์ส่งเองวันนี้', '2015-12-15', 'ดวงแข', 'รอผลตรวจสอบ 1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HE1033', 'ล/คขอสีด่วนมาก,เซลล์ส่งเองบ่ายนี้', '2015-12-15', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68501', 'ล/คขอสีด่วนมากพิเศษ', '2015-12-15', 'ดวงแข', 'รอSEMI2503724', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63302', 'ล/คขอด่วนมากบางส่วน', '2015-12-15', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68470', 'ล/คขอด่วนมาก', '2015-12-15', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68468', 'ล/คขอด่วนมากพิเศษ', '2015-12-15', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63670', null, '0000-00-00', null, null, null);
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68835', 'ล/คขอด่วนมากพิเศษ', '2016-01-09', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63644', 'ล/คขอด่วนมาก', '2016-01-09', 'ดวงแข', 'รอผลตรวจสอบ1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68771', 'ล/คขอสีด่วนมากพิเศษ', '2016-01-09', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI68765', 'ล/คขอด่วนมาก', '2016-01-09', 'ดวงแข', 'รอแม่สี1101562', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63555', 'ล/คต้องการสีด่วนมาก', '2016-01-09', 'ดวงแข', 'ข.3500400+1141057.4', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HM63608', 'ลูกค้าเรียกเข้าด่วนไม่มีของ', '2016-01-19', 'ประภาพรรณ', 'รอผลิต', 'คุณอารักษ์');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00320', 'ล/คต้องการสีด่วนมาก', '2016-02-02', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00499', 'ล/คต้องการสีด่วนมาก', '2016-02-02', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00115', 'ล/คต้องการสีด่วนมาก', '2016-02-02', 'ดวงแข', 'ปรับแก้ไข', 'คุณรุ่งฤทธิ์');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00367', 'ล/คต้องการสีด่วนมากพิเศษ', '2016-02-02', 'ดวงแข', 'ข.TC516', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00262', 'ล/คต้องการสีด่วนมาก', '2016-02-02', 'ดวงแข', 'ข.2500600', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00691', 'ล/คต้องการสีด่วนมากพิเศษ', '2016-02-17', 'ดวงแข', 'รอผลตรวงสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00556', 'ล/คต้องการสีด่วนมากพิเศษ', '2016-02-17', 'ดวงแข', 'รอผลิตแก้ไข 4', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00807', 'ลูกค้าขอสีด่วน', '2016-02-18', 'ดวงแข', 'รอผลิตแก้ไข', 'คุรอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00792', 'ล/คขอด่วนมากพิเศษ', '2016-02-18', 'ดวงแข', 'รอผลตรวงสอบ 1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00529', 'ล/คขอด่วนมากพิเศษ', '0000-00-00', null, null, null);
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00602', 'ลูกค้าขอสีด่วนมาก', '2016-02-18', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00599', 'ลูกค้าขอสีด่วนมาก', '2016-02-18', 'ดวงแข', 'รอผลตรวจสอบ G1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00866', 'ลูกค้าขอด่วนมาก', '2016-02-18', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00568', 'ลูกค้าขอด่วนมากพิเศษเลยดิวส่งลูกค้าแล้ว', '2016-02-18', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00366', 'ลูกค้าขอด่วนมากพิเศษ', '2016-02-18', 'ดวงแข', 'รอผลิตแก้ไข 2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00502', 'ลูกค้าขอสีด่วนมาก', '2016-02-18', 'ดวงแข', 'รอผลตรวจสอบ G1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00487', 'ล/คขอด่วนมากพิเศษ', '2016-02-18', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00660', 'ลูกค้าต้องการสีด่วนมากๆๆ', '2016-02-18', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00661', 'ลูกค้าต้องการสีด่วนมากพิเศษ', '2016-02-18', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00580', 'ลูกค้าขอด่วนมากพิเศษ', '2016-02-18', 'ดวงแข', 'รอแม่สี', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00692', 'ลูกค้าต้องสีด่วนมากพิเศษ', '2016-02-18', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HM00905', 'Orderรอ50KG.เอส.บี.เอช', '2016-02-26', 'ประภาพรรณ', 'รอTR901A', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI63737', 'เลยดิวส่งลูกค้า รอผลิต', '2016-02-26', 'สุพิชชา,สุมลฤดี', 'ยังไม่ได้ผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00870', 'เลยดิวนานหาสูตรแก้ไขไม่ได้', '2016-02-27', 'สุพิชชา,สุมลฤดี', 'ยังหาสูตรแก้ไขไม่ได้', 'คุณรุ่งฤทธิ์');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00511', 'ล/คต้องการด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอแม่สี', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01006', 'ล/คขอด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอผลิตแก้ไข 2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00614', 'ล/คขอด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอแม่สี', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00605', 'ล/คต้องการสีด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอผลิตR2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00896', 'ลูกค้าต้องการสีด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'ข.2501639+TC1A216', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01019', 'ลูกค้าต้องการด่วนมาก', '2016-02-27', 'ดวงแข', 'รอผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00974', 'ล/คต้องการสีด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'ข.TC7A035', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00812', 'ล/คขอด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอผลตรวจสอบ3', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00925', 'ล/คด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'ข.TR9133AP+1300310', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00723', 'ล/คต้องการด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอผลิตแก้ไข 1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00734', 'ล/คต้องการสีด่วนมาก', '2016-02-27', 'ดวงแข', 'รอแม่สี', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00566', 'ล/คต้องการด่วนมากพิเศษ', '2016-02-27', 'ดวงแข', 'รอผลิตแก้ไข 1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI00549', null, '0000-00-00', null, null, null);
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01091', 'ล/คต้องการด่วนมากพิเศษ,เลยดิวส่งล/คแล้ว', '2016-03-04', 'ดวงแข', 'รอผลิตแก้ไข 1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00803', 'เลยดิวส่งล/คแล้ว', '2016-03-04', 'ดวงแข', 'รอผลิตแก้ไข 2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01134', 'ล/คต้องการด่วนมากพิเศษ', '2016-03-04', 'ดวงแข', 'ข.TC503+TC1A232', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01079', 'ล/คขอด่วนมากพิเศษ', '2016-03-04', 'ดวงแข', 'รอผลิตแก้ไข 1', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01076', 'ล/คต้องการสีด่วนมากพิเศษ,เลยดิวส่งล/คมาหลายวันแล้ว', '2016-03-04', 'ดวงแข', 'รอผลิตแก้ไข 2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01058', 'ล/คขอสีด่วนมากพิเศษ', '2016-03-04', 'ดวงแข', 'ข.TC7A035+1300702+1500139.9', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00831', 'ล/คต้องการสีด่วนมากพิเศษ,เลยดิวส่งล/คแล้ว', '2016-03-04', 'ดวงแข', 'รอผลตรวจสอบ G2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI00828', 'ล/คต้องการสีด่วนมากพิเศษ,เลยดิวส่งล/คแล้ว', '2016-03-04', 'ดวงแข', 'รอผลตรวจสอบ', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01042', 'เลยดิวส่งล/คแล้ว', '2016-03-14', 'ดวงแข', 'รอสูตร', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01282', 'ลูกค้าต้องการด่วนมาก', '2016-03-14', 'ดวงแข', 'ข.1500139.9+TC106+1101140', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI01281', 'ลูกค้าต้องการด่วนมาก', '2016-03-14', 'ดวงแข', 'ข.TC106+1101140', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI01337', 'ขาดแม่สี 1109029', '2016-04-04', 'ประภาพรรณ', 'แม่สีเข้า8/4', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HM02013', 'ลูกค้าเรียกเข้า สีด่วน', '2016-05-06', 'สุพิชชา', 'เครื่องเสีย', 'คุณอารักษ์');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HI02327', 'ลูกค้าด่วนใช้สีมาก', '2016-05-10', 'ประภาพรรณ', 'รอเครื่องผลิต', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI02139', 'ลูกค้าด่วนมากๆ', '2016-05-24', 'ประภาพรรณ', 'ผลิตแก้ไข 2', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CM01778', 'ลูกค้าเรียกของเข้าไม่มีของ', '2016-05-31', 'ประภาพรรณ', 'แม่สีไม่เข้าตามกำหนด', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'HM01733', 'เครื่องเสีย ลูกค้าเรียกเข้า2700', '2016-05-25', 'สุพิชชา', 'ผลิตแจ้งว่าเครื่องเสีย', 'คุณอาทิตยา');
INSERT INTO `serious` (`JobType`, `JobNo`, `Prob`, `MKdate`, `empMK`, `statusupd`, `empPD`) VALUES ('O', 'CI02172', 'ลูกค้าด่วนสินค้า', '2016-05-28', 'ประภาพรรณ', 'ผลิตแก้ไข2 ออกแดง', 'คุณรุ่งฤทธิ์');
