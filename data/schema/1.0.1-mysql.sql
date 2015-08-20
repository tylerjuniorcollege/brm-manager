-- Updating all triggers to account for setting the time ourselves.
DROP TRIGGER `brm_auth_group_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_auth_group_bi` BEFORE INSERT ON `brm_auth_group` FOR EACH ROW
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `brm_auth_group_members_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_auth_group_members_bi` BEFORE INSERT ON `brm_auth_group_members` FOR EACH ROW
IF NEW.added IS NULL THEN
    SET NEW.added = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `brm_auth_list_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_auth_list_bi` BEFORE INSERT ON `brm_auth_list` FOR EACH ROW
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `brm_auth_list_bu`;

DROP TRIGGER `brm_campaigns_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_campaigns_bi` BEFORE INSERT ON `brm_campaigns` FOR EACH ROW
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `brm_content_version_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_content_version_bi` BEFORE INSERT ON `brm_content_version` FOR EACH ROW
BEGIN
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;
IF (SELECT `curr_ver` FROM `view_brm_version_count` WHERE `brmid` = NEW.brmid) IS NULL THEN
    SET NEW.brmversionid = 1;
ELSE
    SET NEW.brmversionid = (SELECT (`curr_ver` + 1) FROM `view_brm_version_count` WHERE `brmid` = NEW.brmid);
END IF;
END;;
DELIMITER ;

DROP TRIGGER `brm_requests_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_requests_bi` BEFORE INSERT ON `brm_requests` FOR EACH ROW
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `brm_state_change_bi`;
DELIMITER ;;
CREATE TRIGGER `brm_state_change_bi` BEFORE INSERT ON `brm_state_change` FOR EACH ROW
IF NEW.timestamp IS NULL THEN
    SET NEW.timestamp = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `campaign_bi`;
DELIMITER ;;
CREATE TRIGGER `campaign_bi` BEFORE INSERT ON `campaign` FOR EACH ROW
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `login_attempts_bi`;
DELIMITER ;;
CREATE TRIGGER `login_attempts_bi` BEFORE INSERT ON `login_attempts` FOR EACH ROW
IF NEW.timestamp IS NULL THEN
    SET NEW.timestamp = NOW();
END IF;;
DELIMITER ;

DROP TRIGGER `user_bi`;
DELIMITER ;;
CREATE TRIGGER `user_bi` BEFORE INSERT ON `user` FOR EACH ROW
IF NEW.created IS NULL THEN
    SET NEW.created = NOW();
END IF;;
DELIMITER ;

-- Request now using Versionid.
ALTER TABLE `brm_requests`
CHANGE `timestamp` `requesttime` datetime NULL AFTER `userid`,
COMMENT='';

DROP VIEW `view_brm_list`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_brm_list` AS select `brm_list`.`id` AS `id`,`brm_list`.`title` AS `title`,`brm_list`.`description` AS `description`,`brm_list`.`current_version` AS `current_version`,`brm_cv`.`brmversionid` AS `brm_current_version`,`brm_list`.`templateid` AS `templateid`,`brm_list`.`stateid` AS `stateid`,`state`.`name` AS `state`,`brm_list`.`requestid` AS `requestid`,`request`.`userid` AS `request_userid`,`request_user`.`email` AS `request_user_email`,date_format(`request`.`requesttime`,'%m-%d-%Y %h:%i:%S %p') AS `request_timestamp`,`request`.`departmentid` AS `request_departmentid`,`request_department`.`name` AS `request_department_name`,`request`.`email` AS `request_email`,date_format(`brm_list`.`launchdate`,'%m-%d-%Y %h:%i:%S %p') AS `launchdate`,`brm_list`.`population` AS `population`,`brm_list`.`listname` AS `listname`,`brm_list`.`createdby` AS `createdby`,`creator_user`.`email` AS `createdby_email`,concat(`creator_user`.`firstname`,' ',`creator_user`.`lastname`) AS `createdby_name`,date_format(`brm_list`.`created`,'%m-%d-%Y %h:%i:%S %p') AS `created`,`approval_needed`.`count` AS `approval_needed`,`approved`.`count` AS `approved`,`denied`.`count` AS `denied` from (((((((((`brm_campaigns` `brm_list` left join `view_need_approval` `approval_needed` on((`brm_list`.`id` = `approval_needed`.`brmid`))) left join `view_approved` `approved` on((`brm_list`.`id` = `approved`.`brmid`))) left join `view_deny_approval` `denied` on((`brm_list`.`id` = `denied`.`brmid`))) left join `brm_content_version` `brm_cv` on((`brm_list`.`current_version` = `brm_cv`.`id`))) left join `brm_state` `state` on((`brm_list`.`stateid` = `state`.`id`))) left join `brm_requests` `request` on((`brm_list`.`requestid` = `request`.`id`))) left join `user` `creator_user` on((`brm_list`.`createdby` = `creator_user`.`id`))) left join `user` `request_user` on((`request`.`userid` = `request_user`.`id`))) left join `departments` `request_department` on((`request`.`departmentid` = `request_department`.`id`)));

DROP TRIGGER `comments_bi`;
DELIMITER ;;
CREATE TRIGGER `comments_bi` BEFORE INSERT ON `comments` FOR EACH ROW
IF NEW.timestamp IS NULL THEN
    SET NEW.timestamp = NOW();
END IF;;
DELIMITER ;
