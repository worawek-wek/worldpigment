-- แปลงแล้ว 17/11/2569: TIS-620(CP874) -> UTF-8 · TYPE= -> ENGINE= · INSERT ระบุชื่อคอลัมน์
-- โครงสร้างตาม DB ปัจจุบัน (zwek_worldpigment_2)
-- colorno ขยายเป็น varchar(20) ตามต้นฉบับของลูกค้า (DB เดิมเป็น 12 — มีรหัสยาว 15-16 ตัว 2 แถว)
SET NAMES utf8;
SET SESSION sql_mode='';

/*
MySQL Data Transfer
Source Host: localhost
Source Database: Order
Target Host: localhost
Target Database: Order
Date: 17/11/26 10:52:48
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for zcolorrate
-- ----------------------------
DROP TABLE IF EXISTS `zcolorrate`;
CREATE TABLE `zcolorrate` (
  `colorno` varchar(20) NOT NULL DEFAULT '',
  `rate_A` float DEFAULT NULL,
  `rate_B` float DEFAULT NULL,
  `rate_C` float DEFAULT NULL,
  `RDate` datetime DEFAULT NULL,
  PRIMARY KEY (`colorno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1107492.1', '140', '144', '152', '2026-03-23 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A136A', '120', '135', '144', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3400815E', '137.75', '137.75', '137.75', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1107030', '140', '148', '160', '2026-04-22 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A215EA', '145', '155', '180', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB1B059A', '203', '213', '223', '2026-03-27 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1107230', '140', '144', '152', '2026-03-23 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A447A', '82', '88', '94', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A335A', '138', '148', '160', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A532H', '72', '96', '108', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A546B', '142', '148', '200', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M701A', '95', '125', '135', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A964A', '135', '142', '150', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M006A', '100', '110', '120', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A092A', '124', '140', '150', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A869AUV', '172', '187', '200', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M009A', '95', '105', '114', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M001A', '92', '102', '106', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M702A', '118', '128', '142', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M702T', '125', '135', '148', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8A923E', '150', '156', '172', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7M780A', '112', '120', '155', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8A408A', '68', '76', '92', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8A500A', '52', '60', '67', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8A001H', '40', '46', '50', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8M802A', '86', '99', '115', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8M852A', '72', '80', '95', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8M830B', '87', '96', '109', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1107025', '140', '144', '152', '2026-03-23 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3400815E-3MM', '137.75', '137.75', '137.75', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3400817E', '140', '140', '140', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3401027E', '254', '254', '254', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3401027E-3MM', '254', '254', '254', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM2A017E', '239.25', '239.25', '239.25', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3407020E', '144', '144', '144', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('3407020E-3MM', '144', '144', '144', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM3A017E', '247', '247', '247', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM3A017E-3MM', '247', '247', '247', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP-WPIN[NH1168L]', '93.57', '93.57', '93.57', null);
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('217E229', '85', '85', '100', null);
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM5A031E', '311', '311', '311', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM5A031E-3MM', '311', '311', '311', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM5A055E-3MM', '210', '210', '210', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM5A083E', '245.25', '245.25', '245.25', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM5A083E-3MM', '245.25', '245.25', '245.25', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB5B332BP', '164', '182', '207', null);
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('', '204', '204', '204', '2026-06-11 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB8A250A', '132', '139', '149', '2026-06-12 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A907A', '130', '140', '150', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1500105', '69', '69', '69', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1500105.1', '69', '69', '69', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1407371', '335', '335', '335', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1407368', '270', '270', '270', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1500606', '54', '54', '54', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1407173', '110', '110', '110', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1407171.3', '135', '138', '140', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1500309.1', '55', '55', '55', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1500818.3', '87', '87', '87', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1407212.3', '110', '110', '110', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('1407203.6', '80', '80', '80', '2026-07-02 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A932AEA', '157', '163', '180', '2026-07-07 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('VM8A057E', '154', '154', '154', '2026-07-10 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP-WPIN[N]', '55.57', '55.57', '55.57', '2026-08-04 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP-WPIN[NH900L]', '57.57', '57.57', '57.57', '2026-08-04 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB1A470A', '358', '358', '358', '2026-07-20 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A694DY', '124', '130', '150', '2026-08-04 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('160LD', '8', '8', '8', '2026-08-05 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('160LDFILM', '8', '8', '8', '2026-08-05 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('MB7A716B', '98', '102', '112', '2026-08-04 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP8Y161E', '65.78', '68.78', '71.78', '2026-07-06 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP8Y168E', '66.03', '69.03', '72.03', '2026-07-06 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP9Y120E', '65.63', '68.63', '71.63', '2026-07-06 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP9Y121E', '65.63', '68.63', '71.63', '2026-07-06 00:00:00');
INSERT INTO `zcolorrate` (`colorno`, `rate_A`, `rate_B`, `rate_C`, `RDate`) VALUES ('CP8Y164E', '65.73', '68.73', '71.73', '2026-07-06 00:00:00');
