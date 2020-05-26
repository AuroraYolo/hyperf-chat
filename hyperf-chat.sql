

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for friend_chat_history
-- ----------------------------
DROP TABLE IF EXISTS `friend_chat_history`;
CREATE TABLE `friend_chat_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '消息ID',
  `from_uid` int unsigned NOT NULL DEFAULT '0' COMMENT '发送方',
  `to_uid` int unsigned NOT NULL DEFAULT '0' COMMENT '接收好友',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '消息内容',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `reception_state` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '接收状态 0未接收 1接收',
  PRIMARY KEY (`id`),
  KEY `from_uid_idx` (`from_uid`) USING BTREE,
  KEY `message_id_idx` (`message_id`) USING BTREE,
  KEY `to_group_id_idx` (`to_uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for friend_group
-- ----------------------------
DROP TABLE IF EXISTS `friend_group`;
CREATE TABLE `friend_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `friend_group_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '分组名',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_idx` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for friend_relation
-- ----------------------------
DROP TABLE IF EXISTS `friend_relation`;
CREATE TABLE `friend_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `friend_id` int NOT NULL COMMENT '好友ID',
  `friend_group_id` int unsigned NOT NULL DEFAULT '0' COMMENT '好友所属分组ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_idx` (`uid`) USING BTREE,
  KEY `friend_idx` (`friend_id`) USING BTREE,
  KEY `friend_group_id_idx` (`friend_group_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for group
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `group_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '群名称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '头像',
  `size` int unsigned NOT NULL DEFAULT '0' COMMENT '群规模 200 500 1000',
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '群介绍',
  `validation` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '加群是否需要验证 0 不需要 1需要',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid_idx` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for group_chat_history
-- ----------------------------
DROP TABLE IF EXISTS `group_chat_history`;
CREATE TABLE `group_chat_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '唯一消息ID',
  `from_uid` int NOT NULL COMMENT '发送方',
  `to_group_id` int NOT NULL COMMENT '接收群',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT '消息内容',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `reception_state` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '接收状态 0未接收 1接收',
  PRIMARY KEY (`id`),
  KEY `msg_idx` (`message_id`) USING BTREE,
  KEY `from_uid_idx` (`from_uid`) USING BTREE,
  KEY `to_group_id_idx` (`to_group_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for group_relation
-- ----------------------------
DROP TABLE IF EXISTS `group_relation`;
CREATE TABLE `group_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `group_id` int unsigned NOT NULL DEFAULT '0' COMMENT '群ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_idx` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户主账号:邮箱',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户密码',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '用户在线状态 0离线 1在线',
  `sign` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户签名',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '用户头像',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_idx` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- ----------------------------
-- Table structure for user_application
-- ----------------------------
DROP TABLE IF EXISTS `user_application`;
CREATE TABLE `user_application` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `receiver_id` int unsigned NOT NULL DEFAULT '0' COMMENT '接收方',
  `group_id` int NOT NULL COMMENT '好友分组ID || 群',
  `application_type` enum('friend','group') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'friend' COMMENT '申请类型 1好友 2群',
  `application_status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '申请状态 0创建 1同意 2拒绝',
  `application_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '申请原因',
  `read_state` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '读取状态 0 未读 1已读',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid_idx` (`uid`) USING BTREE,
  KEY `receiver_id` (`receiver_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- ----------------------------
-- Table structure for user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `user_login_log`;
CREATE TABLE `user_login_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int unsigned NOT NULL DEFAULT '0',
  `user_login_ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '0' COMMENT '用户登录IP',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid_idx` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

SET FOREIGN_KEY_CHECKS = 1;
