--
-- DB creation
--
DROP DATABASE IF EXISTS `ispp_iseduc_test`;
CREATE DATABASE IF NOT EXISTS `ispp_iseduc_test`;

--
-- ISEduC test tables creation
--

--
-- Структура таблицы `ispp_branch`
--
DROP TABLE IF EXISTS `ispp_iseduc_test`.`ispp_branch`;
CREATE TABLE `ispp_iseduc_test`.`ispp_branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(128) NOT NULL,
  `short_name` varchar(128) NOT NULL,
  `description` text,
  `img` varchar(255),
  `bg_color` varchar(32),
  `head_id` int(11) DEFAULT NULL COMMENT 'FK for #__profile table',
  `created` datetime,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked` datetime,
  `checked_by` int(11) NOT NULL DEFAULT '0',
  `state` smallint(3) NOT NULL DEFAULT '1' COMMENT 'Visibility: 0 - hide, 1 - publish',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_idx_head_id` (`head_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Структура таблицы `ispp_group`
--
DROP TABLE IF EXISTS `ispp_iseduc_test`.`ispp_group`;
CREATE TABLE `ispp_iseduc_test`.`ispp_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) unsigned DEFAULT NULL COMMENT 'clients_groups.IdOfClientsGroup',
  `name` varchar(45) NOT NULL,
  `description` text,
  `branch_id` int(11) DEFAULT NULL COMMENT 'FK for #__ispp_branch table',
  `teacher_id` int(11) DEFAULT NULL COMMENT 'FK for #__profile table',
  `created` datetime,
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime,
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked` datetime,
  `checked_by` int(11) NOT NULL DEFAULT '0',
  `state` smallint(3) NOT NULL DEFAULT '1' COMMENT 'Visibility: 0 - hide, 1 - publish',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_system_id` (`system_id`),
  KEY `fk_idx_branch_id` (`branch_id`),
  KEY `fk_idx_teacher_id` (`teacher_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Структура таблицы `ispp_student`
--
DROP TABLE IF EXISTS `ispp_iseduc_test`.`ispp_student`;
CREATE TABLE `ispp_iseduc_test`.`ispp_student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) unsigned NOT NULL COMMENT 'clients.IdOfClient',
  `system_group_id` int(11) unsigned NOT NULL COMMENT 'clients.ClientsGroupId',
  `name` varchar(45) NOT NULL,
  `middlename` varchar(45) NOT NULL,
  `lastname` varchar(45) NOT NULL,
  `photo` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Is student has photo: 0 - no, 1 - yes',
  `notify` smallint(1) NOT NULL DEFAULT '0' COMMENT 'Is notification set for student: 0 - no, 1 - yes',
  `state` smallint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_system_id` (`system_id`),
  KEY `idx_system_group_id` (`system_group_id`),
  KEY `idx_full_name` (`lastname`,`middlename`,`name`),
  KEY `idx_notify` (`notify`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Структура таблицы `ispp_event`
--
DROP TABLE IF EXISTS `ispp_iseduc_test`.`ispp_event`;
CREATE TABLE `ispp_iseduc_test`.`ispp_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) unsigned NOT NULL COMMENT 'enterevents.IdOfEnterEvent',
  `student_system_id` int(11) unsigned DEFAULT NULL COMMENT 'enterevents.IdOfClient',
  `branch_id` int(11) DEFAULT NULL COMMENT 'FK for #__ispp_branch table; Relationship to the branch office where the event occurred',
  `turnstile` varchar(100) DEFAULT NULL COMMENT 'enterevents.TurnstileAddr',
  `direction` int(11) NOT NULL COMMENT 'enterevents.PassDirection',
  `code` int(11) NOT NULL COMMENT 'enterevents.EventCode',
  `datetime` datetime NOT NULL COMMENT 'enterevents.EvtDateTime',
  `type` smallint(3) NOT NULL DEFAULT '0' COMMENT 'Type of event: 0 - default, 1 - late come',
  PRIMARY KEY (`id`),
  KEY `idx_system_id` (`system_id`),
  KEY `idx_student_system_id` (`student_system_id`),
  KEY `idx_direction_code` (`direction`,`code`),
  KEY `idx_datetime_type` (`datetime`,`type`),
  KEY `fk_idx_branch_id` (`branch_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Структура таблицы `ispp_sync`
--
DROP TABLE IF EXISTS `ispp_iseduc_test`.`ispp_sync`;
CREATE TABLE `ispp_iseduc_test`.`ispp_sync` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL COMMENT 'Console command',
  `errors` int(11) NOT NULL COMMENT 'Number of errors',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_system_action` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;