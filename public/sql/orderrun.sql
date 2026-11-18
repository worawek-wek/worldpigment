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
Date: 17/11/26 10:53:39
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for orderrun
-- ----------------------------
DROP TABLE IF EXISTS `orderrun`;
CREATE TABLE `orderrun` (
  `c` double DEFAULT NULL,
  `ce` double DEFAULT NULL,
  `h` double DEFAULT NULL,
  `he` double DEFAULT NULL,
  `w` double DEFAULT NULL,
  `we` double DEFAULT NULL,
  `CR` double DEFAULT NULL,
  `HR` double DEFAULT NULL,
  `WR` double DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `orderrun` (`c`, `ce`, `h`, `he`, `w`, `we`, `CR`, `HR`, `WR`) VALUES ('43575', '1004', '58865', '1606', '25419', '514', '511', '294', '40');
