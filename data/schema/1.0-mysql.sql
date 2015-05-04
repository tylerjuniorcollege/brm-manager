-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `brm_auth_group`;
CREATE TABLE `brm_auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_auth_group`;

DELIMITER ;;

CREATE TRIGGER `brm_auth_group_bi` BEFORE INSERT ON `brm_auth_group` FOR EACH ROW
SET NEW.created = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `brm_auth_group_members`;
CREATE TABLE `brm_auth_group_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` int(11) NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupid` (`groupid`),
  KEY `userid` (`userid`),
  CONSTRAINT `brm_auth_group_members_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `brm_auth_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_auth_group_members_ibfk_5` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_auth_group_members`;

DELIMITER ;;

CREATE TRIGGER `brm_auth_group_members_bi` BEFORE INSERT ON `brm_auth_group_members` FOR EACH ROW
SET NEW.added = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `brm_auth_list`;
CREATE TABLE `brm_auth_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `brmid` bigint(20) unsigned NOT NULL,
  `versionid` bigint(20) unsigned NOT NULL,
  `permission` int(3) NOT NULL,
  `approved` int(1) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci,
  `timestamp` datetime DEFAULT NULL,
  `viewedtime` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `brmid` (`brmid`),
  KEY `versionid` (`versionid`),
  CONSTRAINT `brm_auth_list_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_auth_list_ibfk_2` FOREIGN KEY (`brmid`) REFERENCES `brm_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_auth_list_ibfk_3` FOREIGN KEY (`versionid`) REFERENCES `brm_content_version` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_auth_list`;

DELIMITER ;;

CREATE TRIGGER `brm_auth_list_bi` BEFORE INSERT ON `brm_auth_list` FOR EACH ROW
SET NEW.created = NOW();;

CREATE TRIGGER `brm_auth_list_bu` BEFORE UPDATE ON `brm_auth_list` FOR EACH ROW
BEGIN
IF NEW.comment IS NOT NULL THEN
SET NEW.timestamp = NOW();
END IF;
END;;

DELIMITER ;

DROP TABLE IF EXISTS `brm_campaigns`;
CREATE TABLE `brm_campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `description` tinytext COLLATE utf8_unicode_ci,
  `current_version` bigint(20) unsigned DEFAULT NULL,
  `templateid` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `campaignid` int(10) unsigned DEFAULT NULL,
  `stateid` int(11) NOT NULL DEFAULT '0',
  `requestid` int(10) unsigned DEFAULT NULL,
  `launchdate` datetime DEFAULT NULL,
  `population` int(11) DEFAULT NULL,
  `listname` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdby` int(10) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `campaignid` (`campaignid`),
  KEY `stateid` (`stateid`),
  KEY `requestid` (`requestid`),
  KEY `createdby` (`createdby`),
  KEY `current_version` (`current_version`),
  CONSTRAINT `brm_campaigns_ibfk_1` FOREIGN KEY (`campaignid`) REFERENCES `campaign` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_campaigns_ibfk_2` FOREIGN KEY (`stateid`) REFERENCES `brm_state` (`id`),
  CONSTRAINT `brm_campaigns_ibfk_3` FOREIGN KEY (`requestid`) REFERENCES `brm_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_campaigns_ibfk_4` FOREIGN KEY (`createdby`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_campaigns_ibfk_5` FOREIGN KEY (`current_version`) REFERENCES `brm_content_version` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_campaigns`;

DELIMITER ;;

CREATE TRIGGER `brm_campaigns_bi` BEFORE INSERT ON `brm_campaigns` FOR EACH ROW
SET NEW.created = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `brm_content_version`;
CREATE TABLE `brm_content_version` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brmid` bigint(20) unsigned DEFAULT NULL,
  `brmversionid` int(10) unsigned DEFAULT NULL,
  `userid` int(10) unsigned NOT NULL,
  `subject` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brmid` (`brmid`),
  KEY `userid` (`userid`),
  CONSTRAINT `brm_content_version_ibfk_1` FOREIGN KEY (`brmid`) REFERENCES `brm_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_content_version_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_content_version`;

DELIMITER ;;

CREATE TRIGGER `brm_content_version_bi` BEFORE INSERT ON `brm_content_version` FOR EACH ROW
BEGIN
SET NEW.created = NOW(); 
SET NEW.brmversionid = (SELECT (`curr_ver` + 1) FROM `view_brm_version_count` WHERE `brmid` = NEW.brmid);
END;;

DELIMITER ;

DROP TABLE IF EXISTS `brm_requests`;
CREATE TABLE `brm_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `departmentid` int(10) unsigned DEFAULT NULL,
  `email` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `departmentid` (`departmentid`),
  CONSTRAINT `brm_requests_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_requests_ibfk_2` FOREIGN KEY (`departmentid`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_requests`;

DELIMITER ;;

CREATE TRIGGER `brm_requests_bi` BEFORE INSERT ON `brm_requests` FOR EACH ROW
SET NEW.created = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `brm_state`;
CREATE TABLE `brm_state` (
  `id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_state`;
INSERT INTO `brm_state` (`id`, `name`, `description`) VALUES
(-1,	'Hidden',	'BRM Email is saved in system but hidden.'),
(0,	'Saved',	'BRM Email is Saved'),
(1,	'Sent For Approval',	'BRM Email was sent to auth list for approval'),
(2,	'Approved',	'BRM Email has met approval standards and is ready to insert in to BRM.'),
(3,	'Approved and Template Created',	'BRM Email Template was created and is waiting the sent date.'),
(4,	'Sent',	'BRM Email has been sent to the list.'),
(5,	'Ended',	'BRM Email Campaign has ended.'),
(6,	'Denied',	'This BRM has been Denied');

DROP TABLE IF EXISTS `brm_state_change`;
CREATE TABLE `brm_state_change` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `brmid` bigint(20) unsigned NOT NULL,
  `versionid` bigint(20) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `stateid` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `brmid` (`brmid`),
  KEY `versionid` (`versionid`),
  KEY `userid` (`userid`),
  KEY `stateid` (`stateid`),
  CONSTRAINT `brm_state_change_ibfk_1` FOREIGN KEY (`brmid`) REFERENCES `brm_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_state_change_ibfk_2` FOREIGN KEY (`versionid`) REFERENCES `brm_content_version` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_state_change_ibfk_3` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brm_state_change_ibfk_4` FOREIGN KEY (`stateid`) REFERENCES `brm_state` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `brm_state_change`;

DELIMITER ;;

CREATE TRIGGER `brm_state_change_bi` BEFORE INSERT ON `brm_state_change` FOR EACH ROW
SET NEW.timestamp = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `campaign`;
CREATE TABLE `campaign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `startdate` datetime DEFAULT NULL,
  `enddate` datetime DEFAULT NULL,
  `freezedate` datetime NOT NULL,
  `createdby` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `campaign_ibfk_1` FOREIGN KEY (`createdby`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `campaign`;

DELIMITER ;;

CREATE TRIGGER `campaign_bi` BEFORE INSERT ON `campaign` FOR EACH ROW
SET NEW.created = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `brmid` bigint(20) unsigned NOT NULL,
  `versionid` bigint(20) unsigned NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `brmid` (`brmid`),
  KEY `versionid` (`versionid`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`brmid`) REFERENCES `brm_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`versionid`) REFERENCES `brm_content_version` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `comments`;

DELIMITER ;;

CREATE TRIGGER `comments_bi` BEFORE INSERT ON `comments` FOR EACH ROW
SET NEW.timestamp = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `departments`;
INSERT INTO `departments` (`id`, `name`) VALUES
(1,	'IT (Information Technology)'),
(2,	'Student Success'),
(3,	'Recruitment'),
(4,	'Advising'),
(5,	'Housing'),
(6,	'Registrar'),
(7,	'Business Services'),
(8,	'Scholarships');

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `hash` varchar(160) COLLATE utf8_unicode_ci NOT NULL,
  `authid` bigint(20) unsigned DEFAULT NULL,
  `emailid` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `result` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `authid` (`authid`),
  CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `login_attempts_ibfk_2` FOREIGN KEY (`authid`) REFERENCES `brm_auth_list` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

TRUNCATE `login_attempts`;

DELIMITER ;;

CREATE TRIGGER `login_attempts_bi` BEFORE INSERT ON `login_attempts` FOR EACH ROW
SET NEW.timestamp = NOW();;

DELIMITER ;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `permissions` int(11) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DELIMITER ;;

CREATE TRIGGER `user_bi` BEFORE INSERT ON `user` FOR EACH ROW
SET NEW.created = NOW();;

DELIMITER ;

DROP VIEW IF EXISTS `view_approved`;
CREATE TABLE `view_approved` (`brmid` bigint(20) unsigned, `count` bigint(21));


DROP VIEW IF EXISTS `view_auth_list`;
CREATE TABLE `view_auth_list` (`id` bigint(20) unsigned, `title` varchar(250), `description` tinytext, `current_version` bigint(20) unsigned, `templateid` varchar(250), `campaignid` int(10) unsigned, `stateid` int(11), `requestid` int(10) unsigned, `launchdate` datetime, `population` int(11), `listname` varchar(250), `createdby` int(10) unsigned, `created` datetime, `auth_user` int(10) unsigned, `auth_permission` int(3), `auth_approved` int(1));


DROP VIEW IF EXISTS `view_brm_comments`;
CREATE TABLE `view_brm_comments` (`userid` int(11) unsigned, `brmid` bigint(20) unsigned, `versionid` bigint(20) unsigned, `brmversionid` int(11) unsigned, `approved` bigint(20), `comment` text, `timestamp` datetime, `useremail` varchar(250), `userfirstname` varchar(160), `userlastname` varchar(160));


DROP VIEW IF EXISTS `view_brm_list`;
CREATE TABLE `view_brm_list` (`id` bigint(20) unsigned, `title` varchar(250), `description` tinytext, `current_version` bigint(20) unsigned, `brm_current_version` int(10) unsigned, `templateid` varchar(250), `stateid` int(11), `state` varchar(250), `requestid` int(10) unsigned, `request_userid` int(10) unsigned, `request_user_email` varchar(250), `request_timestamp` varchar(86), `request_departmentid` int(10) unsigned, `request_department_name` varchar(250), `request_email` varchar(250), `launchdate` varchar(86), `population` int(11), `listname` varchar(250), `createdby` int(10) unsigned, `createdby_email` varchar(250), `createdby_name` varchar(321), `created` varchar(86), `approval_needed` bigint(21), `approved` bigint(21), `denied` bigint(21));


DROP VIEW IF EXISTS `view_brm_version_count`;
CREATE TABLE `view_brm_version_count` (`brmid` bigint(20) unsigned, `curr_ver` bigint(21));


DROP VIEW IF EXISTS `view_deny_approval`;
CREATE TABLE `view_deny_approval` (`brmid` bigint(20) unsigned, `count` bigint(21));


DROP VIEW IF EXISTS `view_need_approval`;
CREATE TABLE `view_need_approval` (`brmid` bigint(20) unsigned, `count` bigint(21));


DROP TABLE IF EXISTS `view_approved`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_approved` AS select `auth_list`.`id` AS `brmid`,count(`auth_list`.`auth_approved`) AS `count` from `view_auth_list` `auth_list` where (`auth_list`.`auth_approved` = 1) group by `auth_list`.`id`;

DROP TABLE IF EXISTS `view_auth_list`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_auth_list` AS select `cbrm_list`.`id` AS `id`,`cbrm_list`.`title` AS `title`,`cbrm_list`.`description` AS `description`,`cbrm_list`.`current_version` AS `current_version`,`cbrm_list`.`templateid` AS `templateid`,`cbrm_list`.`campaignid` AS `campaignid`,`cbrm_list`.`stateid` AS `stateid`,`cbrm_list`.`requestid` AS `requestid`,`cbrm_list`.`launchdate` AS `launchdate`,`cbrm_list`.`population` AS `population`,`cbrm_list`.`listname` AS `listname`,`cbrm_list`.`createdby` AS `createdby`,`cbrm_list`.`created` AS `created`,`auth`.`userid` AS `auth_user`,`auth`.`permission` AS `auth_permission`,`auth`.`approved` AS `auth_approved` from (`brm_campaigns` `cbrm_list` left join `brm_auth_list` `auth` on(((`cbrm_list`.`id` = `auth`.`brmid`) and (`cbrm_list`.`current_version` = `auth`.`versionid`))));

DROP TABLE IF EXISTS `view_brm_comments`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_brm_comments` AS select `brm_auth`.`userid` AS `userid`,`brm_auth`.`brmid` AS `brmid`,`brm_auth`.`versionid` AS `versionid`,`brm_cv`.`brmversionid` AS `brmversionid`,`brm_auth`.`approved` AS `approved`,`brm_auth`.`comment` AS `comment`,`brm_auth`.`timestamp` AS `timestamp`,`user`.`email` AS `useremail`,`user`.`firstname` AS `userfirstname`,`user`.`lastname` AS `userlastname` from ((`brm_auth_list` `brm_auth` left join `user` on((`brm_auth`.`userid` = `user`.`id`))) left join `brm_content_version` `brm_cv` on((`brm_auth`.`versionid` = `brm_cv`.`id`))) where ((`brm_auth`.`comment` is not null) and (`brm_auth`.`comment` <> '')) union select `c`.`userid` AS `userid`,`c`.`brmid` AS `brmid`,`c`.`versionid` AS `versionid`,`brm_cv`.`brmversionid` AS `brmversionid`,0 AS `approved`,`c`.`comment` AS `comment`,`c`.`timestamp` AS `timestamp`,`user`.`email` AS `useremail`,`user`.`firstname` AS `userfirstname`,`user`.`lastname` AS `userlastname` from ((`comments` `c` left join `user` on((`c`.`userid` = `user`.`id`))) left join `brm_content_version` `brm_cv` on((`c`.`versionid` = `brm_cv`.`id`))) where (`c`.`comment` is not null);

DROP TABLE IF EXISTS `view_brm_list`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_brm_list` AS select `brm_list`.`id` AS `id`,`brm_list`.`title` AS `title`,`brm_list`.`description` AS `description`,`brm_list`.`current_version` AS `current_version`,`brm_cv`.`brmversionid` AS `brm_current_version`,`brm_list`.`templateid` AS `templateid`,`brm_list`.`stateid` AS `stateid`,`state`.`name` AS `state`,`brm_list`.`requestid` AS `requestid`,`request`.`userid` AS `request_userid`,`request_user`.`email` AS `request_user_email`,date_format(`request`.`timestamp`,'%m-%d-%Y %H:%M:%S') AS `request_timestamp`,`request`.`departmentid` AS `request_departmentid`,`request_department`.`name` AS `request_department_name`,`request`.`email` AS `request_email`,date_format(`brm_list`.`launchdate`,'%m-%d-%Y %h:%i:%S %p') AS `launchdate`,`brm_list`.`population` AS `population`,`brm_list`.`listname` AS `listname`,`brm_list`.`createdby` AS `createdby`,`creator_user`.`email` AS `createdby_email`,concat(`creator_user`.`firstname`,' ',`creator_user`.`lastname`) AS `createdby_name`,date_format(`brm_list`.`created`,'%m-%d-%Y %h:%i:%S %p') AS `created`,`approval_needed`.`count` AS `approval_needed`,`approved`.`count` AS `approved`,`denied`.`count` AS `denied` from (((((((((`brm_campaigns` `brm_list` left join `view_need_approval` `approval_needed` on((`brm_list`.`id` = `approval_needed`.`brmid`))) left join `view_approved` `approved` on((`brm_list`.`id` = `approved`.`brmid`))) left join `view_deny_approval` `denied` on((`brm_list`.`id` = `denied`.`brmid`))) left join `brm_content_version` `brm_cv` on((`brm_list`.`current_version` = `brm_cv`.`id`))) left join `brm_state` `state` on((`brm_list`.`stateid` = `state`.`id`))) left join `brm_requests` `request` on((`brm_list`.`requestid` = `request`.`id`))) left join `user` `creator_user` on((`brm_list`.`createdby` = `creator_user`.`id`))) left join `user` `request_user` on((`request`.`userid` = `request_user`.`id`))) left join `departments` `request_department` on((`request`.`departmentid` = `request_department`.`id`)));

DROP TABLE IF EXISTS `view_brm_version_count`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_brm_version_count` AS select `brm_content_version`.`brmid` AS `brmid`,count(`brm_content_version`.`id`) AS `curr_ver` from `brm_content_version` group by `brm_content_version`.`brmid`;

DROP TABLE IF EXISTS `view_deny_approval`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_deny_approval` AS select `auth_list`.`id` AS `brmid`,count(`auth_list`.`auth_approved`) AS `count` from `view_auth_list` `auth_list` where (`auth_list`.`auth_approved` = -(1)) group by `auth_list`.`id`;

DROP TABLE IF EXISTS `view_need_approval`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_need_approval` AS select `auth_list`.`id` AS `brmid`,count(`auth_list`.`auth_approved`) AS `count` from `view_auth_list` `auth_list` where (`auth_list`.`auth_approved` = 0) group by `auth_list`.`id`;

-- 2015-05-04 04:43:37
