DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `post` varchar(32) NOT NULL,
  `body` text NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `post` (`post`),
  KEY `id` (`id`),
  KEY `created_at` (`created_at`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `group_permissions`;
CREATE TABLE `group_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission` varchar(32) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_2` (`permission`),
  KEY `permission` (`permission`),
  KEY `permission_3` (`permission`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `group_permissions` VALUES (1,'access',1),(2,'use_admin',0),(3,'publicate',1),(4,'comment',1),(5,'edit_comments',0),(6,'edit_publications',0),(7,'premoderate_publ',1),(8,'like',1);

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL,
  `icon` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `groups` VALUES (1,'Пользователь','/static/groups/user.png'),(2,'Постоянный','/static/groups/superuser.png'),(3,'Модератор','/static/groups/moderator.png'),(4,'Администратор','/static/groups/admin.ico'),(5,'Директор','/static/groups/director.png');

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Untitled',
  `content` longtext NOT NULL,
  `slug` varchar(255) NOT NULL DEFAULT 'untitled',
  `template` varchar(255) NOT NULL DEFAULT 'default',
  `author_id` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `post_category`;
CREATE TABLE `post_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `post_twitter` int(10) unsigned DEFAULT NULL,
  `post_facebook` int(10) unsigned DEFAULT NULL,
  `post_vkontakte` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(24) NOT NULL,
  `email` varchar(255) NOT NULL,
  `confirm_code` varchar(32) DEFAULT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `password` varchar(32) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `rating` int(10) NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '1',
  `ban_user_id` int(10) unsigned DEFAULT NULL,
  `ban_time` int(10) DEFAULT NULL,
  `ban_reason` varchar(256) DEFAULT NULL,
  `last_visit` int(10) unsigned NOT NULL,
  `register_date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `password` (`password`),
  KEY `group_id` (`group_id`),
  KEY `ban_user` (`ban_user_id`),
  KEY `username` (`username`),
  KEY `session_id` (`session_id`),
  KEY `rating` (`rating`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `visits`;
CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned NOT NULL,
  `ua` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `page` varchar(255) NOT NULL DEFAULT '/',
  `referer` varchar(255) DEFAULT NULL,
  `internal` tinyint(1) NOT NULL DEFAULT '1',
  `domain` varchar(255) DEFAULT NULL,
  `search` varchar(255) DEFAULT NULL,
  `browser` varchar(32) NOT NULL DEFAULT 'Bot',
  `version` varchar(16) NOT NULL DEFAULT 'Unknown',
  `platform` varchar(32) DEFAULT NULL,
  `time` int(10) unsigned NOT NULL,
  `time_start` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`,`browser`),
  KEY `visit_date` (`time`),
  KEY `page` (`page`),
  KEY `query_term` (`search`),
  KEY `internal` (`internal`),
  KEY `domain` (`domain`),
  KEY `time` (`time`),
  KEY `time_start` (`time_start`),
  KEY `browser` (`browser`),
  KEY `search` (`search`),
  KEY `referer` (`referer`),
  KEY `platform` (`platform`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `slug` varchar(255) NOT NULL DEFAULT 'undefined',
  `keywords` varchar(255) DEFAULT NULL,
  `moderate` enum('0','1') NOT NULL DEFAULT '0',
  `post_vkontakte` int(10) unsigned DEFAULT NULL,
  `post_twitter` int(10) unsigned DEFAULT NULL,
  `post_facebook` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  KEY `category` (`category_id`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `post_facebook` (`post_facebook`),
  KEY `post_twitter` (`post_twitter`),
  KEY `post_vkontakte` (`post_vkontakte`),
  KEY `moderate` (`moderate`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `post_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `vote` tinyint(1) NOT NULL DEFAULT '1',
  `post` varchar(32) NOT NULL DEFAULT 'unknown',
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`,`vote`,`post`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;