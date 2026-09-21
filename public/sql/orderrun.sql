/*
MySQL Data Transfer
Source Host: localhost
Source Database: Orange
Target Host: localhost
Target Database: Orange
Date: 21/09/26 16:09:35
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for orderrun
-- ----------------------------
DROP TABLE IF EXISTS `orderrun`;
CREATE TABLE `orderrun` (
  `c` double default NULL,
  `ce` double default NULL,
  `h` double default NULL,
  `he` double default NULL,
  `w` double default NULL,
  `we` double default NULL,
  `CR` double default NULL,
  `HR` double default NULL,
  `WR` double default NULL
) ENGINE=MyISAM;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `orderrun` VALUES ('43206', '997', '58180', '1602', '25122', '511', '511', '294', '40');
