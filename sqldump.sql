-- MySQL dump 10.13  Distrib 5.6.27, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: nano
-- ------------------------------------------------------
-- Server version	5.6.27-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `group_permissions`
--

DROP TABLE IF EXISTS `group_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission` varchar(32) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_2` (`permission`),
  KEY `permission` (`permission`),
  KEY `permission_3` (`permission`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_permissions`
--

LOCK TABLES `group_permissions` WRITE;
/*!40000 ALTER TABLE `group_permissions` DISABLE KEYS */;
INSERT INTO `group_permissions` VALUES (1,'access',1),(2,'use_admin',0),(3,'publicate',1),(4,'comment',1),(5,'edit_comments',0),(6,'edit_publications',0),(7,'premoderate_publ',1);
/*!40000 ALTER TABLE `group_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) NOT NULL,
  `icon` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Пользователь','/static/groups/user.png'),(2,'Постоянный','/static/groups/superuser.png'),(3,'Модератор','/static/groups/moderator.png'),(4,'Администратор','/static/groups/admin.ico'),(5,'Директор','/static/groups/director.png');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'Название ','<p>asdasd as dasd asd asd asd</p>','nazvanie_','default.twig',1,1452675975,1452676178),(3,'Название','','nazvanie','default.twig',1,1453130141,1453130141);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_category`
--

DROP TABLE IF EXISTS `post_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `post_twitter` int(10) unsigned DEFAULT NULL,
  `post_facebook` int(10) unsigned DEFAULT NULL,
  `post_vkontakte` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_category`
--

LOCK TABLES `post_category` WRITE;
/*!40000 ALTER TABLE `post_category` DISABLE KEYS */;
INSERT INTO `post_category` VALUES (4,'Категория',0,0,NULL,0),(5,'Категория',0,0,NULL,0);
/*!40000 ALTER TABLE `post_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `views` (`views`),
  KEY `author_id_2` (`author_id`),
  KEY `category_id` (`category_id`),
  KEY `post_facebook` (`post_facebook`),
  KEY `post_twitter` (`post_twitter`),
  KEY `post_vkontakte` (`post_vkontakte`),
  KEY `moderate` (`moderate`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `post_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (14,'Название test 12 3123 123 123 12 3123 123 123 123','<p>asd asd asd asd&nbsp;</p>','nazvanie_test_12_3123_123_123_12_3123_123_123_123','test','0',NULL,NULL,NULL,4,1,1,1453283272,1453283272),(15,'Название','<p><img src=\"../../../static/14px.png\" alt=\"\" /></p>\n<p>asd asd asd asd asd&nbsp;</p>','nazvanie','','0',NULL,NULL,NULL,4,1,4,1453127913,1453450693),(16,'Название','','nazvanie','','0',NULL,NULL,NULL,4,1,1,1453450667,1453450667),(17,'Название','','nazvanie','','0',NULL,NULL,NULL,4,1,0,1453450665,1453450665),(18,'Название','<p><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><a href=\"../../../static/678136-shield-warning-128.png\">/static/678136-shield-warning-128.png</a><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /><img src=\"../../../static/678136-shield-warning-128.png\" alt=\"\" /></p>','nazvanie','','0',NULL,NULL,NULL,4,1,0,1453450634,1453452241),(19,'Название','','nazvanie','','0',NULL,NULL,NULL,4,1,0,1453450649,1453450649),(20,'Название','<p><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /><img src=\"/static/user/1/avatar.png\" alt=\"\" /></p>','nazvanie','','0',NULL,NULL,NULL,4,1,2,1453450664,1453454463),(21,'Название','<p>as dasd asd asdasd<img src=\"/static/14px.png\" alt=\"\" width=\"1342\" height=\"949\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /><img src=\"../../../static/14px.png\" alt=\"\" /></p>','nazvanie','asd asd','1',NULL,NULL,NULL,5,1,1,1453452313,1453453659);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  UNIQUE KEY `username` (`username`,`session_id`),
  UNIQUE KEY `confirm_code` (`confirm_code`),
  KEY `password` (`password`),
  KEY `group_id` (`group_id`),
  KEY `email_2` (`email`),
  KEY `ban_user` (`ban_user_id`),
  KEY `username_2` (`username`),
  KEY `email_3` (`email`),
  KEY `confirm_code_2` (`confirm_code`),
  KEY `session_id` (`session_id`),
  KEY `password_2` (`password`),
  KEY `group_id_2` (`group_id`),
  KEY `rating` (`rating`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Max','cods.max@gmail.com',NULL,'3d8401fc746b67f26adc07ba091e71e9','36c1f684cc8d96843dc8dba0291ab79f','/static/user/1/avatar.png',30,5,NULL,NULL,NULL,1453473623,0),(2,'Eugen','eugen@mail.com',NULL,'ed2f502db504443c85a552374301d90e','36c1f684cc8d96843dc8dba0291ab79f','',0,1,1,1452158736,'Reason unknown',1452000984,0),(3,'Maxik2','123123@123.com',NULL,'d3ef12f36d95e0c0598b68839a9bc3bd','da392947fb16ee2b19c4865851d30528','',0,1,NULL,NULL,NULL,0,1452075779),(5,'Test user','super.email@gmail.com',NULL,'4bf5d0c19626622f0c69ec99a808ade3','da392947fb16ee2b19c4865851d30528','',0,1,NULL,NULL,NULL,0,1452852009),(6,'testsss','maxp.job@gmail.com22',NULL,'f081bcddbc017880da868035326f84a6','b0cb958ba8e54e443172518af1d69d90','',0,1,NULL,NULL,NULL,0,1452852078),(7,'asdasd','asd@ssss32.com',NULL,'b49eaeb6ffc099ebf72fe11a7f4b6177','bfb0a43c9d4c8f7acf6a753152f7d75f','',0,1,NULL,NULL,NULL,0,1452852312),(8,'root22','maxp.jo33b@gmail.com',NULL,'dadaf8cdd71b7dcb4ed1f7d74a3d6cee','1fbae74c005808a52b743e5dcfe45740','',0,1,NULL,NULL,NULL,0,1452852482),(9,'maxik22','maxp.jobd@gmail.com',NULL,'80b5999ac1d1792749a34dce3d4d9dc3','83c838e9051a2a84148f32c50d2d0085','',0,1,NULL,NULL,NULL,0,1452852583),(10,'NanoLab Web','',NULL,'97a3258d356a893ba1e604a59aca00b7','33d642616e0a81a47827b8011f8b64db','',0,5,NULL,NULL,NULL,1453385602,1452861481),(12,'Максим Папежук','http://vk.com/id159732810',NULL,'b43582f087e456b6a7583bc1a55fb257','7b541ec95aaf39f9e2e60a183304b6c1','',0,5,NULL,NULL,NULL,1452863336,1452861586);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visits`
--

DROP TABLE IF EXISTS `visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned NOT NULL,
  `ua` varchar(255) DEFAULT NULL,
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
  KEY `page_2` (`page`),
  KEY `ip_2` (`ip`),
  KEY `time` (`time`),
  KEY `time_start` (`time_start`),
  KEY `browser` (`browser`),
  KEY `search` (`search`),
  KEY `domain_2` (`domain`),
  KEY `internal_2` (`internal`),
  KEY `referer` (`referer`),
  KEY `page_3` (`page`),
  KEY `platform` (`platform`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visits`
--

LOCK TABLES `visits` WRITE;
/*!40000 ALTER TABLE `visits` DISABLE KEYS */;
INSERT INTO `visits` VALUES (18,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/','http://design.loc/admin/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471676,1453469816),(19,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/678136-shield-warning-128.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471676,1453469816),(20,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/14px.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471676,1453469816),(21,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/post/new/','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471680,1453469816),(22,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/','http://design.loc/post/new/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471681,1453469816),(23,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/678136-shield-warning-128.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471681,1453469816),(24,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/14px.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73',NULL,1453471681,1453469816),(25,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/','http://design.loc/post/new/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453472737,1453469816),(26,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/678136-shield-warning-128.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453472737,1453469816),(27,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/14px.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453472737,1453469816),(28,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/678136-shield-warning-128.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453472738,1453469816),(29,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/','http://design.loc/post/new/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453473577,1453469816),(30,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/678136-shield-warning-128.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453473577,1453469816),(31,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/14px.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453473578,1453469816),(32,2130706433,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36','/static/678136-shield-warning-128.png','http://design.loc/',1,'design.loc',NULL,'Google Chrome','47.0.2526.73','linux',1453473578,1453469816);
/*!40000 ALTER TABLE `visits` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-01-22 16:46:33
