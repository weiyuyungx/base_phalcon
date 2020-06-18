/*
Navicat MySQL Data Transfer

Source Server         : localhost本地
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2020-06-18 15:00:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `bh_user`
-- ----------------------------
DROP TABLE IF EXISTS `bh_user`;
CREATE TABLE `bh_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(16) NOT NULL COMMENT '手机号',
  `unionid` varchar(32) NOT NULL,
  `createtime` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别',
  `headimg` varchar(32) DEFAULT NULL,
  `passwd` varchar(32) NOT NULL,
  `salt` varchar(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`) USING BTREE,
  UNIQUE KEY `unionid` (`unionid`) USING BTREE,
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


