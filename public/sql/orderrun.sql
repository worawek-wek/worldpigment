/*
MySQL Data Transfer
Source Host: localhost
Source Database: Order
Target Host: localhost
Target Database: Order
Date: 14/10/26 10:15:44
*/

SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8;
SET SESSION sql_mode='';
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
INSERT INTO `orderrun` (`c`, `ce`, `h`, `he`, `w`, `we`, `CR`, `HR`, `WR`) VALUES ('43366', '1000', '58455', '1604', '25220', '512', '511', '294', '40');
