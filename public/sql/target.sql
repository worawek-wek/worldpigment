/*
MySQL Data Transfer
Source Host: localhost
Source Database: Orange
Target Host: localhost
Target Database: Orange
Date: 21/09/26 16:09:16
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for target
-- ----------------------------
DROP TABLE IF EXISTS `target`;
CREATE TABLE `target` (
  `saleNo` char(2) default NULL,
  `custid` varchar(10) default NULL,
  `custname` varchar(25) default NULL,
  `1` double default NULL,
  `2` double default NULL,
  `3` double default NULL,
  `4` double default NULL,
  `5` double default NULL,
  `6` double default NULL,
  `7` double default NULL,
  `8` double default NULL,
  `9` double default NULL,
  `10` double default NULL,
  `11` double default NULL,
  `12` double default NULL,
  `TotYear` double default NULL
) ENGINE=MyISAM;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `target` VALUES ('1', '00447', 'Lampoon Plaspack', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '00473', 'Khun Sutha', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '10004', 'Kij Paiboon Chemi', '207855', '207855', '207855', '207855', '207855', '207855', '207855', '207855', '207855', '207855', '207855', '207855', '2494260');
INSERT INTO `target` VALUES ('15', '10011', 'Krasob', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '10015', 'BDI', '3148', '3148', '3148', '3148', '3148', '3148', '3148', '3148', '3148', '3148', '3148', '3148', '37771');
INSERT INTO `target` VALUES ('13', '10018', 'KYE', '297432', '297432', '297432', '297432', '297432', '297432', '297432', '297432', '297432', '297432', '297432', '297432', '3569185');
INSERT INTO `target` VALUES ('15', '10021', 'KIJ Taworn', '7489', '7489', '7489', '7489', '7489', '7489', '7489', '7489', '7489', '7489', '7489', '7489', '89870');
INSERT INTO `target` VALUES ('5', '10023', 'Krungthai Plas', '1417', '1417', '1417', '1417', '1417', '1417', '1417', '1417', '1417', '1417', '1417', '1417', '17000');
INSERT INTO `target` VALUES ('8', '10026', 'Krungthep Union', '6449', '6449', '6449', '6449', '6449', '6449', '6449', '6449', '6449', '6449', '6449', '6449', '77390');
INSERT INTO `target` VALUES ('10', '10077', 'Gold Mint', '68833', '68833', '68833', '68833', '68833', '68833', '68833', '68833', '68833', '68833', '68833', '68833', '826000');
INSERT INTO `target` VALUES ('15', '10082', 'Krungthep Thai', '5450', '5450', '5450', '5450', '5450', '5450', '5450', '5450', '5450', '5450', '5450', '5450', '65400');
INSERT INTO `target` VALUES ('10', '10114', 'Global Plast', '48092', '48092', '48092', '48092', '48092', '48092', '48092', '48092', '48092', '48092', '48092', '48092', '577102');
INSERT INTO `target` VALUES ('8', '11002', 'Khon Kaen Fishnet', '1833', '1833', '1833', '1833', '1833', '1833', '1833', '1833', '1833', '1833', '1833', '1833', '22000');
INSERT INTO `target` VALUES ('8', '12001', 'Kongsak', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '12009', 'Custom Pack', '16000', '16000', '16000', '16000', '16000', '16000', '16000', '16000', '16000', '16000', '16000', '16000', '192000');
INSERT INTO `target` VALUES ('5', '12072', 'K M Interlab', '1031', '1031', '1031', '1031', '1031', '1031', '1031', '1031', '1031', '1031', '1031', '1031', '12375');
INSERT INTO `target` VALUES ('5', '14019', 'Chit-Hing', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '14049', 'J C J', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '14098', 'SL Plastic', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '14114', 'Jeerakasem Plastic', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '16013', 'Rope Pasichareon', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '16082', 'Chinnaworn Plast', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '40470', 'SPP', '289053', '289053', '289053', '289053', '289053', '289053', '289053', '289053', '289053', '289053', '289053', '289053', '3468632');
INSERT INTO `target` VALUES ('8', '17005', 'CP Bangplee', '60400', '60400', '60400', '60400', '60400', '60400', '60400', '60400', '60400', '60400', '60400', '60400', '724800');
INSERT INTO `target` VALUES ('8', '17036', 'C TECH', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '17060', 'Sunpack', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '17062', 'CP SAHA INDUS', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '17071', 'Summit Steering', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '17079', 'Central Worldwide', '17558', '17558', '17558', '17558', '17558', '17558', '17558', '17558', '17558', '17558', '17558', '17558', '210700');
INSERT INTO `target` VALUES ('5', '17095', 'CSS Plastic', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('5', '17099', 'Silver Gold', '10038', '10038', '10038', '10038', '10038', '10038', '10038', '10038', '10038', '10038', '10038', '10038', '120458');
INSERT INTO `target` VALUES ('8', '17100', 'CP Rayong', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '17111', 'CS RUBBER (MIMI)', '45618', '45618', '45618', '45618', '45618', '45618', '45618', '45618', '45618', '45618', '45618', '45618', '547410');
INSERT INTO `target` VALUES ('8', '19038', 'Dainichi', '450167', '450167', '450167', '450167', '450167', '450167', '450167', '450167', '450167', '450167', '450167', '450167', '5402000');
INSERT INTO `target` VALUES ('5', '19052', 'Danudej', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '20020', 'Toshiba Consumer', '245582', '245582', '245582', '245582', '245582', '245582', '245582', '245582', '245582', '245582', '245582', '245582', '2946986');
INSERT INTO `target` VALUES ('8', '20023', 'Ta-wan-ook Polymer', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '20110', 'Tawansamut', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '20113', 'Toyo Saikan(Rojana)', '196724', '196724', '196724', '196724', '196724', '196724', '196724', '196724', '196724', '196724', '196724', '196724', '2360688');
INSERT INTO `target` VALUES ('10', '20115', 'Toyo Saikan(Chol)', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '1162462', '13949548');
INSERT INTO `target` VALUES ('15', '22006', 'Songsawad', '106518', '106518', '106518', '106518', '106518', '106518', '106518', '106518', '106518', '106518', '106518', '106518', '1278216');
INSERT INTO `target` VALUES ('1', '22016', 'Thantawan', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '3253575', '39042899');
INSERT INTO `target` VALUES ('8', '22037', 'Thaitoy', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '22038', 'Thainam Rubber', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('5', '22039', 'Thai Ballpoint', '41723', '41723', '41723', '41723', '41723', '41723', '41723', '41723', '41723', '41723', '41723', '41723', '500675');
INSERT INTO `target` VALUES ('15', '22044', 'Tacktick', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('11', '22046', 'Thai Watana', '15875', '15875', '15875', '15875', '15875', '15875', '15875', '15875', '15875', '15875', '15875', '15875', '190500');
INSERT INTO `target` VALUES ('10', '22054', 'Thai Merry', '75429', '75429', '75429', '75429', '75429', '75429', '75429', '75429', '75429', '75429', '75429', '75429', '905150');
INSERT INTO `target` VALUES ('15', '22061', 'Thai Poly Knitting', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '22063', 'Thai Rotary', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '22068', 'TKT', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('11', '22069', 'Thai Vinyl', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '120000');
INSERT INTO `target` VALUES ('8', '22100', 'Thai Toshiba', '5043', '5043', '5043', '5043', '5043', '5043', '5043', '5043', '5043', '5043', '5043', '5043', '60520');
INSERT INTO `target` VALUES ('8', '22106', 'Poly Acrylic', '111700', '111700', '111700', '111700', '111700', '111700', '111700', '111700', '111700', '111700', '111700', '111700', '1340397');
INSERT INTO `target` VALUES ('11', '22110', 'Thai Asia Polymer', '89750', '89750', '89750', '89750', '89750', '89750', '89750', '89750', '89750', '89750', '89750', '89750', '1077000');
INSERT INTO `target` VALUES ('5', '22115', 'Tong samut', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '22122', 'Thai Prasit', '27018', '27018', '27018', '27018', '27018', '27018', '27018', '27018', '27018', '27018', '27018', '27018', '324214');
INSERT INTO `target` VALUES ('10', '22134', 'TopTrend', '436029', '436029', '436029', '436029', '436029', '436029', '436029', '436029', '436029', '436029', '436029', '436029', '5232343');
INSERT INTO `target` VALUES ('11', '22139', 'Thai Plastic', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('11', '22178', 'Fukuvi', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('11', '22273', 'W Corporate', '17304', '17304', '17304', '17304', '17304', '17304', '17304', '17304', '17304', '17304', '17304', '17304', '207648');
INSERT INTO `target` VALUES ('1', '22314', 'Thai Offset', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '22321', 'Thai Offset', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '22329', 'TPA', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '22341', 'Thai Inter', '4210', '4210', '4210', '4210', '4210', '4210', '4210', '4210', '4210', '4210', '4210', '4210', '50520');
INSERT INTO `target` VALUES ('15', '23001', 'Thonburi', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '24001', 'New Group Pack', '5167', '5167', '5167', '5167', '5167', '5167', '5167', '5167', '5167', '5167', '5167', '5167', '62000');
INSERT INTO `target` VALUES ('10', '24004', 'Nanmee', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '24005', 'Narai Pack', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('11', '24013', 'Nylon Chaisiri', '114149', '114149', '114149', '114149', '114149', '114149', '114149', '114149', '114149', '114149', '114149', '114149', '1369785');
INSERT INTO `target` VALUES ('10', '24050', 'Nam Ngai Hong', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '24056', 'OR Rungrueng', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '24072', 'Nagase', '929596', '929596', '929596', '929596', '929596', '929596', '929596', '929596', '929596', '929596', '929596', '929596', '11155154');
INSERT INTO `target` VALUES ('11', '25001', 'Bangkok PVC', '11511', '11511', '11511', '11511', '11511', '11511', '11511', '11511', '11511', '11511', '11511', '11511', '138128');
INSERT INTO `target` VALUES ('11', '22294', 'Thai Nam Plastics', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '120000');
INSERT INTO `target` VALUES ('15', '25004', 'BKK Polysack', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '25020', 'BATA', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '25023', 'Best Bag', '29568', '29568', '29568', '29568', '29568', '29568', '29568', '29568', '29568', '29568', '29568', '29568', '354817');
INSERT INTO `target` VALUES ('5', '25026', 'Berli Dynaplast', '6125', '6125', '6125', '6125', '6125', '6125', '6125', '6125', '6125', '6125', '6125', '6125', '73500');
INSERT INTO `target` VALUES ('10', '25036', 'Bandai', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '25038', 'B.O.', '432093', '432093', '432093', '432093', '432093', '432093', '432093', '432093', '432093', '432093', '432093', '432093', '5185116');
INSERT INTO `target` VALUES ('15', '25049', 'Bangkok Polybulk', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '25066', 'BB', '2333', '2333', '2333', '2333', '2333', '2333', '2333', '2333', '2333', '2333', '2333', '2333', '28000');
INSERT INTO `target` VALUES ('15', '25079', 'Bangkok Foil', '56550', '56550', '56550', '56550', '56550', '56550', '56550', '56550', '56550', '56550', '56550', '56550', '678600');
INSERT INTO `target` VALUES ('1', '25087', 'Bags & Golves', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '25113', 'Bandai Namko', '2607', '2607', '2607', '2607', '2607', '2607', '2607', '2607', '2607', '2607', '2607', '2607', '31280');
INSERT INTO `target` VALUES ('10', '26009', 'First Toothbuush', '135856', '135856', '135856', '135856', '135856', '135856', '135856', '135856', '135856', '135856', '135856', '135856', '1630271');
INSERT INTO `target` VALUES ('15', '26026', 'Pacific', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '26036', 'Panjawatana', '76600', '76600', '76600', '76600', '76600', '76600', '76600', '76600', '76600', '76600', '76600', '76600', '919200');
INSERT INTO `target` VALUES ('5', '26045', 'Praditkorn', '5750', '5750', '5750', '5750', '5750', '5750', '5750', '5750', '5750', '5750', '5750', '5750', '69000');
INSERT INTO `target` VALUES ('10', '27007', 'Thai Flashlight', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '29002', 'Panthong', '16375', '16375', '16375', '16375', '16375', '16375', '16375', '16375', '16375', '16375', '16375', '16375', '196500');
INSERT INTO `target` VALUES ('10', '29006', 'Pioneer', '63792', '63792', '63792', '63792', '63792', '63792', '63792', '63792', '63792', '63792', '63792', '63792', '765500');
INSERT INTO `target` VALUES ('5', '29015', 'Plastech Industry', '162396', '162396', '162396', '162396', '162396', '162396', '162396', '162396', '162396', '162396', '162396', '162396', '1948750');
INSERT INTO `target` VALUES ('5', '29038', 'Pack Printer', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('5', '29076', 'Premium', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '29120', 'Plastic & Package', '493552', '493552', '493552', '493552', '493552', '493552', '493552', '493552', '493552', '493552', '493552', '493552', '5922625');
INSERT INTO `target` VALUES ('1', '29156', 'PI-Industry', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '29163', 'PornChareon', '12865', '12865', '12865', '12865', '12865', '12865', '12865', '12865', '12865', '12865', '12865', '12865', '154375');
INSERT INTO `target` VALUES ('15', '29172', 'Polwat', '583', '583', '583', '583', '583', '583', '583', '583', '583', '583', '583', '583', '7000');
INSERT INTO `target` VALUES ('15', '29179', 'Pongpara Kodan', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '29185', 'PermPoon', '2522', '2522', '2522', '2522', '2522', '2522', '2522', '2522', '2522', '2522', '2522', '2522', '30260');
INSERT INTO `target` VALUES ('10', '29203', 'Polar', '43207', '43207', '43207', '43207', '43207', '43207', '43207', '43207', '43207', '43207', '43207', '43207', '518480');
INSERT INTO `target` VALUES ('15', '29213', 'Polymer', '1875', '1875', '1875', '1875', '1875', '1875', '1875', '1875', '1875', '1875', '1875', '1875', '22500');
INSERT INTO `target` VALUES ('5', '30019', 'Fore Major', '6714', '6714', '6714', '6714', '6714', '6714', '6714', '6714', '6714', '6714', '6714', '6714', '80570');
INSERT INTO `target` VALUES ('1', '30026', 'Focus Mold', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '30034', 'Fuji Chemi', '125554', '125554', '125554', '125554', '125554', '125554', '125554', '125554', '125554', '125554', '125554', '125554', '1506645');
INSERT INTO `target` VALUES ('11', '30041', 'PhelpDodge', '8492', '8492', '8492', '8492', '8492', '8492', '8492', '8492', '8492', '8492', '8492', '8492', '101900');
INSERT INTO `target` VALUES ('1', '32008', 'Malawee', '8231', '8231', '8231', '8231', '8231', '8231', '8231', '8231', '8231', '8231', '8231', '8231', '98775');
INSERT INTO `target` VALUES ('5', '32011', 'Meng Seng', '18864', '18864', '18864', '18864', '18864', '18864', '18864', '18864', '18864', '18864', '18864', '18864', '226370');
INSERT INTO `target` VALUES ('5', '32019', 'Maiky', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '32065', 'MitSiam', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '32086', 'MalaPlast', '9215', '9215', '9215', '9215', '9215', '9215', '9215', '9215', '9215', '9215', '9215', '9215', '110575');
INSERT INTO `target` VALUES ('10', '33007', 'Unico', '2188', '2188', '2188', '2188', '2188', '2188', '2188', '2188', '2188', '2188', '2188', '2188', '26250');
INSERT INTO `target` VALUES ('5', '33026', 'UT Plastic', '4708', '4708', '4708', '4708', '4708', '4708', '4708', '4708', '4708', '4708', '4708', '4708', '56500');
INSERT INTO `target` VALUES ('13', '33035', 'Union Nifco', '302', '302', '302', '302', '302', '302', '302', '302', '302', '302', '302', '302', '3629');
INSERT INTO `target` VALUES ('8', '33074', 'US PolyTrade', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('5', '34008', 'RuangWa', '1975', '1975', '1975', '1975', '1975', '1975', '1975', '1975', '1975', '1975', '1975', '1975', '23700');
INSERT INTO `target` VALUES ('13', '34010', 'Rehau', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '34049', 'Rakchart Panich', '144788', '144788', '144788', '144788', '144788', '144788', '144788', '144788', '144788', '144788', '144788', '144788', '1737450');
INSERT INTO `target` VALUES ('13', '34064', 'RungTaworn', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('1', '35013', 'Light Spot Plastic', '3708', '3708', '3708', '3708', '3708', '3708', '3708', '3708', '3708', '3708', '3708', '3708', '44500');
INSERT INTO `target` VALUES ('15', '36006', 'V P', '187623', '187623', '187623', '187623', '187623', '187623', '187623', '187623', '187623', '187623', '187623', '187623', '2251472');
INSERT INTO `target` VALUES ('8', '36017', 'Wanawit', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '1058159', '12697912');
INSERT INTO `target` VALUES ('8', '36018', 'Waraporn', '53913', '53913', '53913', '53913', '53913', '53913', '53913', '53913', '53913', '53913', '53913', '53913', '646950');
INSERT INTO `target` VALUES ('1', '36031', 'WTD', '154016', '154016', '154016', '154016', '154016', '154016', '154016', '154016', '154016', '154016', '154016', '154016', '1848195');
INSERT INTO `target` VALUES ('15', '36169', 'V T J', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '37001', 'Sattawat', '19206', '19206', '19206', '19206', '19206', '19206', '19206', '19206', '19206', '19206', '19206', '19206', '230475');
INSERT INTO `target` VALUES ('8', '37008', 'Srithai', '396', '396', '396', '396', '396', '396', '396', '396', '396', '396', '396', '396', '4750');
INSERT INTO `target` VALUES ('8', '37009', 'Srithai', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '38020', 'Siam United Rubber', '15879', '15879', '15879', '15879', '15879', '15879', '15879', '15879', '15879', '15879', '15879', '15879', '190550');
INSERT INTO `target` VALUES ('13', '38029', '3K Plastic', '3271', '3271', '3271', '3271', '3271', '3271', '3271', '3271', '3271', '3271', '3271', '3271', '39250');
INSERT INTO `target` VALUES ('8', '38115', 'Supaporn', '19767', '19767', '19767', '19767', '19767', '19767', '19767', '19767', '19767', '19767', '19767', '19767', '237200');
INSERT INTO `target` VALUES ('15', '38150', 'SITTHI', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('11', '38161', 'Thai Yazaki พระประแดง', '19827', '19827', '19827', '19827', '19827', '19827', '19827', '19827', '19827', '19827', '19827', '19827', '237925');
INSERT INTO `target` VALUES ('5', '38171', 'SuanLuang', '101231', '101231', '101231', '101231', '101231', '101231', '101231', '101231', '101231', '101231', '101231', '101231', '1214775');
INSERT INTO `target` VALUES ('11', '38173', 'Siam Pacific', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '38211', 'Siam Plastic', '216056', '216056', '216056', '216056', '216056', '216056', '216056', '216056', '216056', '216056', '216056', '216056', '2592676');
INSERT INTO `target` VALUES ('13', '38244', 'Sunantha', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '38274', 'Somchai', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '38287', 'SangRung', '550095', '550095', '550095', '550095', '550095', '550095', '550095', '550095', '550095', '550095', '550095', '550095', '6601141');
INSERT INTO `target` VALUES ('1', '38297', 'Siam Attakorn', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '39010', 'RianThai', '134131', '134131', '134131', '134131', '134131', '134131', '134131', '134131', '134131', '134131', '134131', '134131', '1609572');
INSERT INTO `target` VALUES ('8', '39023', 'RianThong', '29060', '29060', '29060', '29060', '29060', '29060', '29060', '29060', '29060', '29060', '29060', '29060', '348725');
INSERT INTO `target` VALUES ('8', '39031', 'Universal', '13500', '13500', '13500', '13500', '13500', '13500', '13500', '13500', '13500', '13500', '13500', '13500', '162000');
INSERT INTO `target` VALUES ('8', '40003', 'American', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('8', '40006', 'Mongkol Paisal', '228730', '228730', '228730', '228730', '228730', '228730', '228730', '228730', '228730', '228730', '228730', '228730', '2744765');
INSERT INTO `target` VALUES ('8', '40028', 'Agro Pack', '148021', '148021', '148021', '148021', '148021', '148021', '148021', '148021', '148021', '148021', '148021', '148021', '1776249');
INSERT INTO `target` VALUES ('5', '40030', 'Ampast', '15452', '15452', '15452', '15452', '15452', '15452', '15452', '15452', '15452', '15452', '15452', '15452', '185425');
INSERT INTO `target` VALUES ('15', '40079', 'UdomSap', '4308', '4308', '4308', '4308', '4308', '4308', '4308', '4308', '4308', '4308', '4308', '4308', '51700');
INSERT INTO `target` VALUES ('10', '40096', 'ImcoPack', '52734', '52734', '52734', '52734', '52734', '52734', '52734', '52734', '52734', '52734', '52734', '52734', '632810');
INSERT INTO `target` VALUES ('11', '40128', 'Apex Plastic', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '40138', 'IndraPorn', '15583', '15583', '15583', '15583', '15583', '15583', '15583', '15583', '15583', '15583', '15583', '15583', '187000');
INSERT INTO `target` VALUES ('10', '40173', 'N J Industry', '7042', '7042', '7042', '7042', '7042', '7042', '7042', '7042', '7042', '7042', '7042', '7042', '84500');
INSERT INTO `target` VALUES ('1', '40243', 'SBH Thailand', '71653', '71653', '71653', '71653', '71653', '71653', '71653', '71653', '71653', '71653', '71653', '71653', '859835');
INSERT INTO `target` VALUES ('11', '40278', 'SCS Sports Wear', '43395', '43395', '43395', '43395', '43395', '43395', '43395', '43395', '43395', '43395', '43395', '43395', '520740');
INSERT INTO `target` VALUES ('8', '40252', 'S A S', '40056', '40056', '40056', '40056', '40056', '40056', '40056', '40056', '40056', '40056', '40056', '40056', '480668');
INSERT INTO `target` VALUES ('5', '40277', 'RM Pack', '49635', '49635', '49635', '49635', '49635', '49635', '49635', '49635', '49635', '49635', '49635', '49635', '595625');
INSERT INTO `target` VALUES ('1', '40283', 'Ekachai Plastic', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '40319', 'EamChareon', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '40326', 'Integrate Polymer', '17306', '17306', '17306', '17306', '17306', '17306', '17306', '17306', '17306', '17306', '17306', '17306', '207670');
INSERT INTO `target` VALUES ('1', '40329', 'Asahi', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('15', '40339', 'SMR (2000)', '19362', '19362', '19362', '19362', '19362', '19362', '19362', '19362', '19362', '19362', '19362', '19362', '232340');
INSERT INTO `target` VALUES ('8', '40361', 'N C R-TRB', '14750', '14750', '14750', '14750', '14750', '14750', '14750', '14750', '14750', '14750', '14750', '14750', '177000');
INSERT INTO `target` VALUES ('8', '40377', 'Inter Fishnet', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('18', '40412', 'OKAYA', '204099', '204099', '204099', '204099', '204099', '204099', '204099', '204099', '204099', '204099', '204099', '204099', '2449193');
INSERT INTO `target` VALUES ('10', '41027', 'Hoover', '273907', '273907', '273907', '273907', '273907', '273907', '273907', '273907', '273907', '273907', '273907', '273907', '3286885');
INSERT INTO `target` VALUES ('11', '41035', 'Hitachi BKK Cable', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '41047', 'Hitachi', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '10374046', '124488556');
INSERT INTO `target` VALUES ('13', '41053', 'Hitachi', '7125', '7125', '7125', '7125', '7125', '7125', '7125', '7125', '7125', '7125', '7125', '7125', '85500');
INSERT INTO `target` VALUES ('13', '41062', 'HAIER', '95253', '95253', '95253', '95253', '95253', '95253', '95253', '95253', '95253', '95253', '95253', '95253', '1143030');
INSERT INTO `target` VALUES ('1', '22348', 'THAI ARROW', '716951', '716951', '716951', '716951', '716951', '716951', '716951', '716951', '716951', '716951', '716951', '716951', '8603416');
INSERT INTO `target` VALUES ('1', '12091', 'Camematch', '183292', '183292', '183292', '183292', '183292', '183292', '183292', '183292', '183292', '183292', '183292', '183292', '2199500');
INSERT INTO `target` VALUES ('1', '41065', 'Honda Trading', '226525', '226525', '226525', '226525', '226525', '226525', '226525', '226525', '226525', '226525', '226525', '226525', '2718300');
INSERT INTO `target` VALUES ('5', 'OTHER', null, '221805', '221805', '221805', '221805', '221805', '221805', '221805', '221805', '221805', '221805', '221805', '221805', '2661654');
INSERT INTO `target` VALUES ('1', 'OTHER', null, '229384', '229384', '229384', '229384', '229384', '229384', '229384', '229384', '229384', '229384', '229384', '229384', '2752609');
INSERT INTO `target` VALUES ('8', 'OTHER', null, '905126', '905126', '905126', '905126', '905126', '905126', '905126', '905126', '905126', '905126', '905126', '905126', '10861513');
INSERT INTO `target` VALUES ('10', 'OTHER', null, '18160', '18160', '18160', '18160', '18160', '18160', '18160', '18160', '18160', '18160', '18160', '18160', '217925');
INSERT INTO `target` VALUES ('11', 'OTHER', null, '83663', '83663', '83663', '83663', '83663', '83663', '83663', '83663', '83663', '83663', '83663', '83663', '1003958');
INSERT INTO `target` VALUES ('13', 'OTHER', null, '105822', '105822', '105822', '105822', '105822', '105822', '105822', '105822', '105822', '105822', '105822', '105822', '1269858');
INSERT INTO `target` VALUES ('15', 'OTHER', null, '207769', '207769', '207769', '207769', '207769', '207769', '207769', '207769', '207769', '207769', '207769', '207769', '2493227');
INSERT INTO `target` VALUES ('11', '30042', 'PHELPS DODGE', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('10', '22346', 'THAI WORLD WARE', '41188', '41188', '41188', '41188', '41188', '41188', '41188', '41188', '41188', '41188', '41188', '41188', '494250');
INSERT INTO `target` VALUES ('10', '32108', 'Ma Win Plastic', '16800', '16800', '16800', '16800', '16800', '16800', '16800', '16800', '16800', '16800', '16800', '16800', '201600');
INSERT INTO `target` VALUES ('11', '38306', 'THAI YAZAKI (วัดแค)', '441217', '441217', '441217', '441217', '441217', '441217', '441217', '441217', '441217', '441217', '441217', '441217', '5294600');
INSERT INTO `target` VALUES ('11', '12028', 'Cotco Plastics', '3875', '3875', '3875', '3875', '3875', '3875', '3875', '3875', '3875', '3875', '3875', '3875', '46500');
INSERT INTO `target` VALUES ('11', '24002', 'Nakorn Luang', '65121', '65121', '65121', '65121', '65121', '65121', '65121', '65121', '65121', '65121', '65121', '65121', '781450');
INSERT INTO `target` VALUES ('13', '22360', 'THAI MATTO', '398416', '398416', '398416', '398416', '398416', '398416', '398416', '398416', '398416', '398416', '398416', '398416', '4780992');
INSERT INTO `target` VALUES ('13', '30032', 'PDTL', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('13', '22124', 'Thai Co-poly', '2026', '2026', '2026', '2026', '2026', '2026', '2026', '2026', '2026', '2026', '2026', '2026', '24310');
INSERT INTO `target` VALUES ('13', '25114', 'Beko Thai', '351299', '351299', '351299', '351299', '351299', '351299', '351299', '351299', '351299', '351299', '351299', '351299', '4215586');
INSERT INTO `target` VALUES ('13', '29207', 'Poly Vision', '68956', '68956', '68956', '68956', '68956', '68956', '68956', '68956', '68956', '68956', '68956', '68956', '827467');
INSERT INTO `target` VALUES ('13', '40469', 'L-ARABY', '90368', '90368', '90368', '90368', '90368', '90368', '90368', '90368', '90368', '90368', '90368', '90368', '1084417');
INSERT INTO `target` VALUES ('13', '40425', 'APICO', '7700', '7700', '7700', '7700', '7700', '7700', '7700', '7700', '7700', '7700', '7700', '7700', '92400');
INSERT INTO `target` VALUES ('15', '29231', 'Panasonic Electric', '380809', '380809', '380809', '380809', '380809', '380809', '380809', '380809', '380809', '380809', '380809', '380809', '4569705');
INSERT INTO `target` VALUES ('15', '40041', 'LLH Printing', '4279', '4279', '4279', '4279', '4279', '4279', '4279', '4279', '4279', '4279', '4279', '4279', '51350');
INSERT INTO `target` VALUES ('15', '12101', 'K H I', '2220', '2220', '2220', '2220', '2220', '2220', '2220', '2220', '2220', '2220', '2220', '2220', '26641');
INSERT INTO `target` VALUES ('15', '36170', 'V.N. Specialize', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('18', '17123', 'SPCT', '88513', '88513', '88513', '88513', '88513', '88513', '88513', '88513', '88513', '88513', '88513', '88513', '1062150');
INSERT INTO `target` VALUES ('18', '41008', 'Honda Trading', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '2232934', '26795208');
INSERT INTO `target` VALUES ('18', '40445', 'S.P.P. Powder', '267544', '267544', '267544', '267544', '267544', '267544', '267544', '267544', '267544', '267544', '267544', '267544', '3210523');
INSERT INTO `target` VALUES ('18', '33081', 'Yasuda Sangyo', '75773', '75773', '75773', '75773', '75773', '75773', '75773', '75773', '75773', '75773', '75773', '75773', '909280');
INSERT INTO `target` VALUES ('18', '17126', 'SUMIPEX', '206250', '206250', '206250', '206250', '206250', '206250', '206250', '206250', '206250', '206250', '206250', '206250', '2475000');
INSERT INTO `target` VALUES ('18', '32100', 'MITSUI', '127379', '127379', '127379', '127379', '127379', '127379', '127379', '127379', '127379', '127379', '127379', '127379', '1528542');
INSERT INTO `target` VALUES ('18', '29235', 'PANEFRI INDUSTRIAL', '236122', '236122', '236122', '236122', '236122', '236122', '236122', '236122', '236122', '236122', '236122', '236122', '2833464');
INSERT INTO `target` VALUES ('18', '33083', 'Yochida', '396725', '396725', '396725', '396725', '396725', '396725', '396725', '396725', '396725', '396725', '396725', '396725', '4760705');
INSERT INTO `target` VALUES ('18', '36168', 'YPC  Precision', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('18', '12115', 'KASEI', '125894', '125894', '125894', '125894', '125894', '125894', '125894', '125894', '125894', '125894', '125894', '125894', '1510732');
INSERT INTO `target` VALUES ('18', '22351', 'TECHNOPLAS INDUSTRY', null, null, null, null, null, null, null, null, null, null, null, null, null);
INSERT INTO `target` VALUES ('18', '22333', 'THAI USUI', '36884', '36884', '36884', '36884', '36884', '36884', '36884', '36884', '36884', '36884', '36884', '36884', '442609');
INSERT INTO `target` VALUES ('18', '22372', 'TOPLAX', '73618', '73618', '73618', '73618', '73618', '73618', '73618', '73618', '73618', '73618', '73618', '73618', '883421');
INSERT INTO `target` VALUES ('18', '37014', 'Srithai Bangpu', '429571', '429571', '429571', '429571', '429571', '429571', '429571', '429571', '429571', '429571', '429571', '429571', '5154856');
INSERT INTO `target` VALUES ('5', '22080', 'Thai Wiwat', '146067', '146067', '146067', '146067', '146067', '146067', '146067', '146067', '146067', '146067', '146067', '146067', '1752800');
INSERT INTO `target` VALUES ('5', '29214', 'Premier Pack', '37292', '37292', '37292', '37292', '37292', '37292', '37292', '37292', '37292', '37292', '37292', '37292', '447500');
INSERT INTO `target` VALUES ('5', '29226', 'Premium Pack Group', '76285', '76285', '76285', '76285', '76285', '76285', '76285', '76285', '76285', '76285', '76285', '76285', '915415');
INSERT INTO `target` VALUES ('8', '40415', 'Interpro plast', '283452', '283452', '283452', '283452', '283452', '283452', '283452', '283452', '283452', '283452', '283452', '283452', '3401418');
INSERT INTO `target` VALUES ('8', '40370', 'Eastern Polypack', '206194', '206194', '206194', '206194', '206194', '206194', '206194', '206194', '206194', '206194', '206194', '206194', '2474325');
INSERT INTO `target` VALUES ('8', '40465', 'N C R Rubber', '183449', '183449', '183449', '183449', '183449', '183449', '183449', '183449', '183449', '183449', '183449', '183449', '2201390');
INSERT INTO `target` VALUES ('8', '34045', 'Ribbon Bell', '102267', '102267', '102267', '102267', '102267', '102267', '102267', '102267', '102267', '102267', '102267', '102267', '1227204');
INSERT INTO `target` VALUES ('8', '36040', 'Watchara', '207727', '207727', '207727', '207727', '207727', '207727', '207727', '207727', '207727', '207727', '207727', '207727', '2492725');
INSERT INTO `target` VALUES ('8', '25024', 'Bowling Star', '70242', '70242', '70242', '70242', '70242', '70242', '70242', '70242', '70242', '70242', '70242', '70242', '842900');
INSERT INTO `target` VALUES ('8', '38249', 'Khun Somkid', '20652', '20652', '20652', '20652', '20652', '20652', '20652', '20652', '20652', '20652', '20652', '20652', '247818');
INSERT INTO `target` VALUES ('11', '38122', 'Bangkok Cable ฉะเชิงเทรา', '288820', '288820', '288820', '288820', '288820', '288820', '288820', '288820', '288820', '288820', '288820', '288820', '3465841');
INSERT INTO `target` VALUES ('11', '38258', 'Bangkok Cable บ้านโพธิ์', '55365', '55365', '55365', '55365', '55365', '55365', '55365', '55365', '55365', '55365', '55365', '55365', '664375');
INSERT INTO `target` VALUES ('11', '38326', 'Thai Yazaki สุวรรณภูมิ', '203917', '203917', '203917', '203917', '203917', '203917', '203917', '203917', '203917', '203917', '203917', '203917', '2447000');
INSERT INTO `target` VALUES ('1', '22347', 'Thai Honda', '56396', '56396', '56396', '56396', '56396', '56396', '56396', '56396', '56396', '56396', '56396', '56396', '676747');
INSERT INTO `target` VALUES ('18', '32118', 'MARUBENI', '141667', '141667', '141667', '141667', '141667', '141667', '141667', '141667', '141667', '141667', '141667', '141667', '1700000');
INSERT INTO `target` VALUES ('18', 'OTHER', null, '70424', '70424', '70424', '70424', '70424', '70424', '70424', '70424', '70424', '70424', '70424', '70424', '845086');
INSERT INTO `target` VALUES ('5', '29209', 'Perfect Inter Products', '49055', '49055', '49055', '49055', '49055', '49055', '49055', '49055', '49055', '49055', '49055', '49055', '588660');
INSERT INTO `target` VALUES ('5', '36147', 'Warm Pack', '36548', '36548', '36548', '36548', '36548', '36548', '36548', '36548', '36548', '36548', '36548', '36548', '438580');
INSERT INTO `target` VALUES ('5', '40146', 'M E Meditec', '35708', '35708', '35708', '35708', '35708', '35708', '35708', '35708', '35708', '35708', '35708', '35708', '428500');
INSERT INTO `target` VALUES ('1', '24090', 'Neotech Asia Pacific', '37535', '37535', '37535', '37535', '37535', '37535', '37535', '37535', '37535', '37535', '37535', '37535', '450424');
INSERT INTO `target` VALUES ('1', '29221', 'P.I. Industry', '40616', '40616', '40616', '40616', '40616', '40616', '40616', '40616', '40616', '40616', '40616', '40616', '487390');
INSERT INTO `target` VALUES ('1', '37019', 'Siriwattana', '35238', '35238', '35238', '35238', '35238', '35238', '35238', '35238', '35238', '35238', '35238', '35238', '422853');
INSERT INTO `target` VALUES ('11', '29182', 'Pen siri', '166240', '166240', '166240', '166240', '166240', '166240', '166240', '166240', '166240', '166240', '166240', '166240', '1994884');
INSERT INTO `target` VALUES ('13', '20118', 'Toshiba Consumer', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '1027745', '12332939');
INSERT INTO `target` VALUES ('13', '38331', 'Stibel (Asia-Pacific)', '212924', '212924', '212924', '212924', '212924', '212924', '212924', '212924', '212924', '212924', '212924', '212924', '2555092');
INSERT INTO `target` VALUES ('13', '40024', 'Asada Chemical', '63577', '63577', '63577', '63577', '63577', '63577', '63577', '63577', '63577', '63577', '63577', '63577', '762925');
INSERT INTO `target` VALUES ('13', '41067', 'HIGASKET', '93117', '93117', '93117', '93117', '93117', '93117', '93117', '93117', '93117', '93117', '93117', '93117', '1117404');
INSERT INTO `target` VALUES ('15', '12128', 'K M T', '173583', '173583', '173583', '173583', '173583', '173583', '173583', '173583', '173583', '173583', '173583', '173583', '2083000');
INSERT INTO `target` VALUES ('15', '14109', 'J S Industry', '37800', '37800', '37800', '37800', '37800', '37800', '37800', '37800', '37800', '37800', '37800', '37800', '453600');
INSERT INTO `target` VALUES ('15', '17083', 'Summit Autoseat', '134540', '134540', '134540', '134540', '134540', '134540', '134540', '134540', '134540', '134540', '134540', '134540', '1614475');
INSERT INTO `target` VALUES ('15', '29254', 'PongPaRa Rubber', '75629', '75629', '75629', '75629', '75629', '75629', '75629', '75629', '75629', '75629', '75629', '75629', '907553');
INSERT INTO `target` VALUES ('15', '31028', 'Southern Plastic', '43658', '43658', '43658', '43658', '43658', '43658', '43658', '43658', '43658', '43658', '43658', '43658', '523900');
INSERT INTO `target` VALUES ('18', '22367', 'Taisho Seiki', '34607', '34607', '34607', '34607', '34607', '34607', '34607', '34607', '34607', '34607', '34607', '34607', '415286');
INSERT INTO `target` VALUES ('18', '22385', 'THAI HAYAKAWA', '862858', '862858', '862858', '862858', '862858', '862858', '862858', '862858', '862858', '862858', '862858', '862858', '10354300');
