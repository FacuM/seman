/*
 Source Server         : localhost_3306
 Source Server Type    : MariaDB
 Source Server Version : 100325
 Source Host           : localhost:3306
 Source Schema         : seman

 Target Server Type    : MariaDB
 Target Server Version : 100325
 File Encoding         : 65001

 Date: 15/03/2021 21:22:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sm_servers
-- ----------------------------
DROP TABLE IF EXISTS `sm_servers`;
CREATE TABLE `sm_servers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `ip` varchar(40) NOT NULL COMMENT 'IPv6 max-length = 39',
  `image` varchar(70) DEFAULT NULL COMMENT 'sha256sum length + extension',
  `order` bigint(20) NOT NULL,
  `enabled` bit(1) NOT NULL DEFAULT b'1',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sm_status_history
-- ----------------------------
DROP TABLE IF EXISTS `sm_status_history`;
CREATE TABLE `sm_status_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `serverId` bigint(20) NOT NULL,
  `type` enum('PROCESSES','SESSIONS') NOT NULL,
  `value` bigint(20) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;