-- แปลงแล้ว 17/11/2569: TIS-620(CP874) -> UTF-8 · TYPE= -> ENGINE= · INSERT ระบุชื่อคอลัมน์
-- โครงสร้างตาม DB ปัจจุบัน (zwek_worldpigment_2)
SET NAMES utf8;
SET SESSION sql_mode='';

/*
MySQL Data Transfer
Source Host: localhost
Source Database: ColorTEST
Target Host: localhost
Target Database: ColorTEST
Date: 16/11/26 15:18:50
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for color
-- ----------------------------
DROP TABLE IF EXISTS `color`;
CREATE TABLE `color` (
  `code` char(3) NOT NULL DEFAULT '',
  `color` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `color` (`code`, `color`) VALUES ('A1', 'AC RED');
INSERT INTO `color` (`code`, `color`) VALUES ('A11', 'AC PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('A2', 'AC ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('A3', 'AC YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('A4', 'AC GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('A5', 'AC BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('A6', 'AC VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('A7', 'AC WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('A71', 'AC IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('A8', 'AC BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('A81', 'AC GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('A82', 'AC SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('A9', 'AC BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('A91', 'AC GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('C1', 'CP RED');
INSERT INTO `color` (`code`, `color`) VALUES ('C11', 'CP PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('C2', 'CP ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('C3', 'CP YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('C4', 'CP GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('C5', 'CP BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('C6', 'CP VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('C7', 'CP WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('C71', 'CP IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('C8', 'CP BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('C81', 'CP GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('C82', 'CP SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('C9', 'CP BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('C91', 'CP GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('D1', 'DB RED');
INSERT INTO `color` (`code`, `color`) VALUES ('D11', 'DB PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('D2', 'DB ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('D3', 'DB YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('D4', 'DB GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('D5', 'DB BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('D6', 'DB VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('D7', 'DB WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('D71', 'DB IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('D8', 'DB BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('D81', 'DB GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('D82', 'DB SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('D9', 'DB BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('D91', 'DB GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('M1', 'MB RED');
INSERT INTO `color` (`code`, `color`) VALUES ('M11', 'MB PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('M2', 'MB ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('M3', 'MB YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('M4', 'MB GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('M5', 'MB BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('M6', 'MB VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('M7', 'MB WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('M71', 'MB IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('M8', 'MB BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('M81', 'MB GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('M82', 'MB SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('M9', 'MB BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('M91', 'MB GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('O1', 'PO RED');
INSERT INTO `color` (`code`, `color`) VALUES ('O11', 'PO PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('O2', 'PO ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('O3', 'PO YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('O4', 'PO GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('O5', 'PO BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('O6', 'PO VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('O7', 'PO WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('O71', 'PO IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('O8', 'PO BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('O81', 'PO GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('O82', 'PO SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('O9', 'PO BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('O91', 'PO GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('P1', 'MP RED');
INSERT INTO `color` (`code`, `color`) VALUES ('P11', 'MP PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('P2', 'MP ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('P3', 'MP YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('P4', 'MP GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('P5', 'MP BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('P6', 'MP VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('P7', 'MP WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('P71', 'MP IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('P8', 'MP BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('P81', 'MP GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('P82', 'MP SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('P9', 'MP BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('P91', 'MP GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('R1', 'RB RED');
INSERT INTO `color` (`code`, `color`) VALUES ('R11', 'RB PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('R2', 'RB ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('R3', 'RB YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('R4', 'RB GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('R5', 'RB BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('R6', 'RB VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('R7', 'RB WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('R71', 'RB IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('R8', 'RB BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('R81', 'RB GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('R82', 'RB SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('R9', 'RB BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('R91', 'RB GOLD');
INSERT INTO `color` (`code`, `color`) VALUES ('V1', 'VM RED');
INSERT INTO `color` (`code`, `color`) VALUES ('V11', 'VM PINK');
INSERT INTO `color` (`code`, `color`) VALUES ('V2', 'VM ORANGE');
INSERT INTO `color` (`code`, `color`) VALUES ('V3', 'VM YELLOW');
INSERT INTO `color` (`code`, `color`) VALUES ('V4', 'VM GREEN');
INSERT INTO `color` (`code`, `color`) VALUES ('V5', 'VM BLUE');
INSERT INTO `color` (`code`, `color`) VALUES ('V6', 'VM VIOLET');
INSERT INTO `color` (`code`, `color`) VALUES ('V7', 'VM WHITE');
INSERT INTO `color` (`code`, `color`) VALUES ('V71', 'VM IVORY');
INSERT INTO `color` (`code`, `color`) VALUES ('V8', 'VM BLACK');
INSERT INTO `color` (`code`, `color`) VALUES ('V81', 'VM GRAY');
INSERT INTO `color` (`code`, `color`) VALUES ('V82', 'VM SILVER');
INSERT INTO `color` (`code`, `color`) VALUES ('V9', 'VM BROWN');
INSERT INTO `color` (`code`, `color`) VALUES ('V91', 'VM GOLD');
