-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version   5.1.39-log


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema stages
--

CREATE DATABASE IF NOT EXISTS stages;
USE stages;

--
-- Definition of table `core_config_data`
--

DROP TABLE IF EXISTS `core_config_data`;
CREATE TABLE `core_config_data` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scope` enum('default','website','config') NOT NULL DEFAULT 'default',
  `scope_id` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT 'general',
  `value` text NOT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `config_scope` (`scope`,`scope_id`,`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `core_session`
--

DROP TABLE IF EXISTS `core_session`;
CREATE TABLE `core_session` (
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `session_expires` int(10) unsigned NOT NULL DEFAULT '0',
  `session_data` mediumblob NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Session data store';

--
-- Definition of table `core_url_rewrite`
--

DROP TABLE IF EXISTS `core_url_rewrite`;
CREATE TABLE `core_url_rewrite` (
  `url_rewrite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` int(11) unsigned DEFAULT NULL,
  `id_path` varchar(255) NOT NULL DEFAULT '',
  `request_path` varchar(255) NOT NULL DEFAULT '',
  `target_path` varchar(255) NOT NULL DEFAULT '',
  `is_system` tinyint(1) unsigned DEFAULT '1',
  `options` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`url_rewrite_id`),
  UNIQUE KEY `UNQ_REQUEST_PATH` (`entity_id`,`request_path`),
  KEY `IDX_ID_PATH` (`id_path`),
  KEY `FK_CORE_URL_REWRITE_ENTITY` (`entity_id`),
  KEY `IDX_CATEGORY_REWRITE` (`entity_id`,`id_path`),
  CONSTRAINT `FK_CORE_URL_REWRITE_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `core_user`
--

DROP TABLE IF EXISTS `core_user`;
CREATE TABLE `core_user` (
  `user_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(32) NOT NULL DEFAULT '',
  `lastname` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `username` varchar(40) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `role_id` smallint(5) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `logdate` datetime DEFAULT NULL,
  `lognum` smallint(5) unsigned NOT NULL DEFAULT '0',
  `reload_acl_flag` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `extra` text,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Core Users';

--
-- Dumping data for table `core_user`
--

/*!40000 ALTER TABLE `core_user` DISABLE KEYS */;
INSERT INTO `core_user` (`user_id`,`firstname`,`lastname`,`email`,`username`,`password`,`role_id`,`created`,`modified`,`logdate`,`lognum`,`reload_acl_flag`,`is_active`,`extra`) VALUES 
 (1,'Administrator','','admin@stages.com','admin','ef883fb88621ccdefcd408b96bf69cc5:vA ',0,'2010-11-03 00:28:55',NULL,'2011-03-23 15:45:18',187,0,1,NULL);
/*!40000 ALTER TABLE `core_user` ENABLE KEYS */;

--
-- Definition of table `core_user_notification_inbox`
--

DROP TABLE IF EXISTS `core_user_notification_inbox`;
CREATE TABLE `core_user_notification_inbox` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) unsigned NOT NULL,
  `severity` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `is_read` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_remove` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`notification_id`),
  KEY `FK_INBOX_USER` (`user_id`),
  KEY `IDX_SEVERITY` (`severity`),
  KEY `IDX_IS_READ` (`is_read`),
  KEY `IDX_IS_REMOVE` (`is_remove`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

--
-- Definition of table `core_website`
--

DROP TABLE IF EXISTS `core_website`;
CREATE TABLE `core_website` (
  `website_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT '',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) unsigned DEFAULT '0',
  `status` varchar(10) NOT NULL DEFAULT 'live',
  PRIMARY KEY (`website_id`),
  UNIQUE KEY `code` (`code`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Websites';

--
-- Dumping data for table `core_website`
--

/*!40000 ALTER TABLE `core_website` DISABLE KEYS */;
INSERT INTO `core_website` (`website_id`,`code`,`name`,`sort_order`,`is_default`,`status`) VALUES 
 (1,'default','default',0,1,'live');
/*!40000 ALTER TABLE `core_website` ENABLE KEYS */;


--
-- Definition of table `cron_job_entity`
--

DROP TABLE IF EXISTS `cron_job_entity`;
CREATE TABLE `cron_job_entity` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_name` varchar(255) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT '1',
  `cron_expr_string` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `action` varchar(45) NOT NULL,
  `params` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastrun_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`job_id`),
  KEY `task_name` (`job_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `cron_job_schedule`
--

DROP TABLE IF EXISTS `cron_job_schedule`;
CREATE TABLE `cron_job_schedule` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned DEFAULT NULL,
  `job_code` varchar(255) NOT NULL DEFAULT '',
  `status` enum('pending','running','success','missed','error','waiting') NOT NULL DEFAULT 'pending',
  `messages` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `scheduled_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `executed_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finished_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`schedule_id`),
  KEY `task_name` (`job_id`),
  KEY `scheduled_at` (`scheduled_at`,`status`),
  CONSTRAINT `FK_CRON_JOB_SCHEDULE_JOB` FOREIGN KEY (`job_id`) REFERENCES `cron_job_entity` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `eav_attribute`
--

DROP TABLE IF EXISTS `eav_attribute`;
CREATE TABLE `eav_attribute` (
  `attribute_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_code` varchar(255) NOT NULL DEFAULT '',
  `backend_type` enum('static','datetime','decimal','int','text','varchar') NOT NULL DEFAULT 'static',
  `frontend_input` varchar(50) DEFAULT NULL,
  `frontend_label` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_searchable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_unique` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `value_prepend` varchar(45) DEFAULT NULL,
  `value_append` varchar(45) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`attribute_id`),
  UNIQUE KEY `IDX_EAV_ATTRIBUTE_ENTITYTYPE_ATTRIBUTE` (`entity_type_id`,`attribute_code`),
  KEY `IDX_USED_FOR_SORT_BY` (`entity_type_id`),
  CONSTRAINT `FK_EAV_ATTRIBUTE_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `eav_attribute_option_value`
--

DROP TABLE IF EXISTS `eav_attribute_option_value`;
CREATE TABLE `eav_attribute_option_value` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`value_id`),
  KEY `FK_ATTRIBUTE_OPTION_VALUE_ATTRIBUTE` (`attribute_id`),
  CONSTRAINT `FK_ATTRIBUTE_OPTION_VALUE_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute option values';

--
-- Definition of table `eav_entity`
--

DROP TABLE IF EXISTS `eav_entity`;
CREATE TABLE `eav_entity` (
  `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '2',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `is_active` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`entity_id`),
  KEY `FK_EAV_ENTITY_ENTITY_TYPE` (`entity_type_id`),
  CONSTRAINT `FK_EAV_ENTITY_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Entities';

--
-- Definition of table `eav_entity_attribute`
--

DROP TABLE IF EXISTS `eav_entity_attribute`;
CREATE TABLE `eav_entity_attribute` (
  `entity_attribute_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`entity_attribute_id`),
  KEY `FK_EAV_ENTITY_ATTRIVUTE_ATTRIBUTE` (`attribute_id`),
  KEY `FK_EAV_ENTITY_ATTRIBUTE_ENTITYTYPE` (`entity_type_id`),
  KEY `UNQ_EAV_ENTITY_ATTRIBUTE` (`entity_type_id`,`attribute_id`),
  CONSTRAINT `FK_EAV_ENTITY_ATTRIBUTE_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_ATTRIBUTE_ENTITYTYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `eav_entity_datetime`
--

DROP TABLE IF EXISTS `eav_entity_datetime`;
CREATE TABLE `eav_entity_datetime` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `entity_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` datetime DEFAULT NULL,
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`),
  KEY `FK_EAV_ENTITY_DATETIME_ENTITY` (`entity_id`) USING BTREE,
  KEY `FK_EAV_ENTITY_DATETIME_ATTRIBUTE` (`attribute_id`) USING BTREE,
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='stores entity datetime values';

--
-- Definition of table `eav_entity_decimal`
--

DROP TABLE IF EXISTS `eav_entity_decimal`;
CREATE TABLE `eav_entity_decimal` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `entity_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` decimal(12,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`),
  KEY `FK_EAV_ENTITY_DECIMAL_ENTITY` (`entity_id`) USING BTREE,
  KEY `FK_EAV_RENTITY_DECIMAL_ATTRIBUTE` (`attribute_id`) USING BTREE,
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='store the entity decimal values';

--
-- Definition of table `eav_entity_int`
--

DROP TABLE IF EXISTS `eav_entity_int`;
CREATE TABLE `eav_entity_int` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `entity_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`),
  KEY `FK_EAV_ENTITY_INT_ENTITY` (`entity_id`) USING BTREE,
  KEY `FK_EAV_ENTITY_INT_ATTRIBUTE` (`attribute_id`) USING BTREE,
  CONSTRAINT `FK_EAV_ENTITY_INT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_INT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='store the entity integer values';

--
-- Definition of table `eav_entity_text`
--

DROP TABLE IF EXISTS `eav_entity_text`;
CREATE TABLE `eav_entity_text` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `entity_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`),
  KEY `FK_EAV_ENTITY_TEXT_ENTITY` (`entity_id`) USING BTREE,
  KEY `FK_EAV_ENTITY_TEXT_ATTRIBUTE` (`attribute_id`) USING BTREE,
  CONSTRAINT `FK_EAV_ENTITY_TEXT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_TEXT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='stores the entity text data';

--
-- Definition of table `eav_entity_type`
--

DROP TABLE IF EXISTS `eav_entity_type`;
CREATE TABLE `eav_entity_type` (
  `entity_type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entity_type_code` varchar(50) NOT NULL DEFAULT '',
  `entity_model` varchar(255) NOT NULL,
  `attribute_model` varchar(255) NOT NULL,
  `entity_table` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`entity_type_id`),
  KEY `EAV_ENTITY_TYPE_CODE` (`entity_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='entity types used in the system';

--
-- Dumping data for table `eav_entity_type`
--

/*!40000 ALTER TABLE `eav_entity_type` DISABLE KEYS */;
INSERT INTO `eav_entity_type` (`entity_type_id`,`entity_type_code`,`entity_model`,`attribute_model`,`entity_table`) VALUES 
 (1,'core_system','core/config','Core_Model_Eav','eav_entity');
/*!40000 ALTER TABLE `eav_entity_type` ENABLE KEYS */;

--
-- Definition of table `eav_entity_varchar`
--

DROP TABLE IF EXISTS `eav_entity_varchar`;
CREATE TABLE `eav_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `entity_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`),
  KEY `FK_EAV_ENTITY_VARCHAR_ENTITY` (`entity_id`) USING BTREE,
  KEY `FK_EAV_ENTITY_VARCHAR_ATTRIBUTE` (`attribute_id`) USING BTREE,
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='store the entity varchar data';

--
-- Definition of table `connect_fb_test_user`
--

DROP TABLE IF EXISTS `connect_fb_test_user`;
CREATE TABLE `connect_fb_test_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT 'no name',
  `fb_app_id` bigint(20) unsigned NOT NULL,
  `fb_user_id` bigint(20) unsigned NOT NULL,
  `fb_access_token` varchar(255) NOT NULL,
  `fb_login_url` varchar(255) NOT NULL,
  `fb_user_name` varchar(45) DEFAULT NULL,
  `fb_password` varchar(45) DEFAULT NULL,
  `friends_count` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `connect_fb_wall_post`
--

DROP TABLE IF EXISTS `connect_fb_wall_post`;
CREATE TABLE `connect_fb_wall_post` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_type` enum('own','friend') NOT NULL DEFAULT 'own',
  `is_ebabled` int(1) unsigned NOT NULL DEFAULT '1',
  `locale` varchar(5) NOT NULL DEFAULT 'en_US',
  `module` varchar(45) NOT NULL,
  `added_date` datetime NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `connect_fb_wall_post_text`
--

DROP TABLE IF EXISTS `connect_fb_wall_post_text`;
CREATE TABLE `connect_fb_wall_post_text` (
  `post_text_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute` varchar(45) NOT NULL,
  `post_text` text NOT NULL,
  `variables` varchar(45) DEFAULT NULL,
  `post_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`post_text_id`) USING BTREE,
  KEY `FK_CONNECT_FB_WALL_POST_TEXT_POST` (`post_id`),
  CONSTRAINT `FK_CONNECT_FB_WALL_POST_TEXT_POST` FOREIGN KEY (`post_id`) REFERENCES `connect_fb_wall_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `log_crontab`
--

DROP TABLE IF EXISTS `log_crontab`;
CREATE TABLE `log_crontab` (
  `id` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `cronjob_id` int(7) unsigned NOT NULL DEFAULT '0',
  `last_run_status` int(3) unsigned NOT NULL DEFAULT '0',
  `start_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Crontab Log and que';

--
-- Definition of table `log_fb_api`
--

DROP TABLE IF EXISTS `log_fb_api`;
CREATE TABLE `log_fb_api` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `api_call_id` bigint(20) unsigned DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `api_arguments` varchar(10000) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `data` varchar(4000) DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `log_fb_wall_post`
--

DROP TABLE IF EXISTS `log_fb_wall_post`;
CREATE TABLE `log_fb_wall_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fb_user_id` bigint(20) unsigned NOT NULL,
  `fb_profile_id` bigint(20) unsigned DEFAULT NULL,
  `fb_app_id` bigint(20) unsigned NOT NULL,
  `fb_post_id` varchar(45) NOT NULL,
  `post_id` int(10) unsigned DEFAULT NULL,
  `added_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `log_url`
--

DROP TABLE IF EXISTS `log_url`;
CREATE TABLE `log_url` (
  `url_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `visitor_id` bigint(20) unsigned DEFAULT NULL,
  `visit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`url_id`),
  KEY `IDX_VISITOR` (`visitor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='URL visiting history';


--
-- Definition of table `log_url_info`
--

DROP TABLE IF EXISTS `log_url_info`;
CREATE TABLE `log_url_info` (
  `url_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `referer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`url_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Detailed information about url visit';

--
-- Definition of table `log_user`
--

DROP TABLE IF EXISTS `log_user`;
CREATE TABLE `log_user` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logout_at` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `IDX_VISITOR` (`visitor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Users log information';

--
-- Definition of table `log_visitor`
--

DROP TABLE IF EXISTS `log_visitor`;
CREATE TABLE `log_visitor` (
  `visitor_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` char(64) NOT NULL DEFAULT '',
  `first_visit_at` datetime DEFAULT NULL,
  `last_visit_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_url_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `website_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`visitor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='System visitors log';

--
-- Definition of table `log_visitor_info`
--

DROP TABLE IF EXISTS `log_visitor_info`;
CREATE TABLE `log_visitor_info` (
  `visitor_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `http_referer` varchar(255) DEFAULT NULL,
  `http_user_agent` varchar(255) DEFAULT NULL,
  `http_accept_charset` varchar(255) DEFAULT NULL,
  `http_accept_language` varchar(255) DEFAULT NULL,
  `server_addr` bigint(20) DEFAULT NULL,
  `remote_addr` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`visitor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Additional information by visitor';

--
-- Definition of table `system_error`
--

DROP TABLE IF EXISTS `system_error`;
CREATE TABLE `system_error` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) DEFAULT NULL,
  `error_code` varchar(10) DEFAULT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `error_stack` varchar(1024) DEFAULT NULL,
  `extra` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `connect_blogger`
--

DROP TABLE IF EXISTS `connect_blogger`;
CREATE TABLE `connect_blogger` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) NOT NULL DEFAULT 'bwin',
  `badge_code` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'blog',
  `track_traffic` smallint(1) unsigned DEFAULT '1',
  `is_active` tinyint(1) unsigned DEFAULT '1',
  `added_date` datetime NOT NULL,
  `last_traffic_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_CONNECT_BLOGGER` (`badge_code`,`name`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `connect_tweet_mentions`
--

DROP TABLE IF EXISTS `connect_tweet_mentions`;
CREATE TABLE `connect_tweet_mentions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tweet_id` varchar(30) DEFAULT NULL,
  `user` varchar(50) DEFAULT NULL,
  `text` varchar(150) DEFAULT NULL,
  `mention_time` datetime DEFAULT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `connect_prefinery_beta_tester`
--

DROP TABLE IF EXISTS `connect_prefinery_beta_tester`;
CREATE TABLE `connect_prefinery_beta_tester` (
  `beta_user_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `prefinery_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `extra` varchar(255) DEFAULT NULL,
  `email` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`beta_user_id`),
  KEY `FK_PREFINERY_ENTITY_TESTER_USER` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Definition of table `gift_coupon`
--

DROP TABLE IF EXISTS `gift_coupon`;
CREATE TABLE `gift_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `provider` varchar(10) NOT NULL DEFAULT 'bwin',
  `type` varchar(10) NOT NULL DEFAULT 'one-use',
  `issue_count` int(10) unsigned DEFAULT '0',
  `max_issue_count` int(10) unsigned DEFAULT '1',
  `is_valid` tinyint(1) unsigned DEFAULT '1',
  `is_test_coupon` tinyint(1) unsigned DEFAULT '0',
  `created_at` datetime NOT NULL,
  `last_issued_date` datetime DEFAULT NULL,
  `error` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_GIFT_COUPON` (`code`,`provider`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Definition of table `stages_user_entity`
--

DROP TABLE IF EXISTS `stages_user_entity`;
CREATE TABLE `stages_user_entity` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(90) NOT NULL,
  `lastname` varchar(90) NOT NULL,
  `email` varchar(90) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL DEFAULT '',
  `phone_home` varchar(20) DEFAULT NULL,
  `phone_mobile` varchar(20) DEFAULT NULL,
  `phone_office` varchar(20) DEFAULT NULL,
  `phone_fax` varchar(20) DEFAULT NULL,
  `username` varchar(90) DEFAULT NULL,
  `password` varchar(90) DEFAULT NULL,
  `bc_host` VARCHAR(255) NOT NULL,
  `bc_id` bigint(20) NOT NULL,
  `bc_client_id` int(10) NOT NULL DEFAULT 0,
  `bc_company_id` int(10) NOT NULL DEFAULT 0,
  `bc_avatar` varchar(255) DEFAULT NULL DEFAULT '',
  `bc_profile_url` varchar(255) NOT NULL DEFAULT '',
  `bc_auth_token` varchar(64) DEFAULT NULL,
  `bc_project_loaded_at` DATETIME DEFAULT '0000-00-00 00:00:00',
  `type` smallint(1) NOT NULL,
  `is_app_user` smallint(1) unsigned NOT NULL DEFAULT '0',
  `is_test_account` smallint(1) unsigned DEFAULT '0',
  `prompt_password_change` smallint(1) unsigned NOT NULL DEFAULT '0',
  `added_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  `last_visited` datetime DEFAULT NULL,
  `extra` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `stages`.`stages_milestone_entity`;
CREATE TABLE  `stages`.`stages_milestone_entity` (
  `milestone_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_responsible` varchar(45) NOT NULL,
  `milestone_date` datetime NOT NULL,
  `type` VARCHAR(1) NOT NULL DEFAULT 'd',
  `project_id` varchar(45) NOT NULL,
  `bc_id` varchar(45) NOT NULL,
  `bc_created_date` datetime NOT NULL,
  `bc_company_id` varchar(45) NOT NULL,
  `bc_client_id` varchar(45) NOT NULL,
  `bc_status` smallint(1) unsigned NOT NULL,
  `added_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`milestone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `stages`.`stages_project_entity`;
CREATE TABLE  `stages`.`stages_project_entity` (
  `project_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `m_lead` varchar(45) NOT NULL,
  `d_lead` varchar(45) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `bc_id` varchar(45) NOT NULL,
  `bc_created_date` datetime NOT NULL,
  `bc_company_id` varchar(45) NOT NULL,
  `bc_client_id` varchar(45) NOT NULL,
  `bc_status` smallint(1) unsigned NOT NULL,
  `bc_milestone_loaded_at` DATETIME DEFAULT '0000-00-00 00:00:00',
  `bc_todolist_loaded_at` DATETIME DEFAULT '0000-00-00 00:00:00',
  `bc_todo_loaded_at`  DATETIME DEFAULT '0000-00-00 00:00:00',
  `added_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `stages`.`stages_todolist_entity`;
CREATE TABLE  `stages`.`stages_todolist_entity` (
  `todolist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `milestone_id` varchar(45) NOT NULL,
  `project_id` varchar(45) NOT NULL,
  `bc_id` varchar(45) NOT NULL,
  `todo_count` smallint(3) unsigned NOT NULL,
  `todo_completed` smallint(3) unsigned NOT NULL,
  `todo_uncompleted` smallint(3) unsigned NOT NULL,
  `bc_status` smallint(1) unsigned NOT NULL,
  `description` text NOT NULL,
  `added_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`todolist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `stages`.`stages_todo_entity` (
  `todo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `todolist_id` varchar(45) NOT NULL,
  `project_id` varchar(45) NOT NULL,
  `bc_id` varchar(45) NOT NULL,
  `bc_status` smallint(1) unsigned NOT NULL,
  `comment_count` smallint(3) unsigned DEFAULT NULL,
  `bc_added_date` datetime NOT NULL,
  `bc_updated_date` datetime NOT NULL,
  `added_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`todo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `stages`.`stages_time_entity` (
  `time_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `project_id` varchar(45) NOT NULL,
  `person_id` varchar(45) NOT NULL,
  `todo_id` varchar(45) NOT NULL,
  `bc_id` varchar(45) NOT NULL,
  `bc_date` datetime NOT NULL,
  `bc_hours` varchar(10) NOT NULL,
  `added_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`time_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
