CREATE TABLE `client_name_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL COMMENT '房间号',
  `name` varchar(128) CHARACTER SET utf8mb4 NOT NULL COMMENT '昵称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `chat_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) CHARACTER SET utf8 NOT NULL,
  `name` varchar(32) NOT NULL,
  `content` varchar(256) NOT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;