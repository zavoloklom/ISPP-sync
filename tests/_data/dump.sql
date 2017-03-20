--
-- DB creation
--
DROP DATABASE IF EXISTS `ispp-ecafe-test`;
DROP DATABASE IF EXISTS `ispp-iseduc-test`;

CREATE DATABASE IF NOT EXISTS `ispp-ecafe-test`;
CREATE DATABASE IF NOT EXISTS `ispp-iseduc-test`;


--
-- Ecafe test tables creation
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`clients_groups`;
CREATE TABLE `ispp-ecafe-test`.`clients_groups` (
  IdOfClientsGroup BIGINT(20) NOT NULL AUTO_INCREMENT,
  Name VARCHAR(256) DEFAULT NULL,
  DisablePlanCreation BIT(1) NOT NULL DEFAULT b'0' COMMENT 'Исключить из плана питания',
  Version INT(11) DEFAULT NULL,
  SyncState INT(11) NOT NULL,
  CreatedDate DATETIME NOT NULL,
  LastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  GroupType INT(11) DEFAULT 0 COMMENT 'Тип группы',
  AccessLevel INT(11) NOT NULL DEFAULT 0,
  BindingToOrg BIGINT(20) DEFAULT NULL COMMENT 'привязка группы к организации',
  PRIMARY KEY (IdOfClientsGroup),
  INDEX IX_clients_groups_SyncState (SyncState)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1100000131
  AVG_ROW_LENGTH = 94
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Описание для таблицы clients
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`clients`;
CREATE TABLE `ispp-ecafe-test`.`clients` (
  IdOfClient BIGINT(20) NOT NULL AUTO_INCREMENT,
  OneSCode BIGINT(20) DEFAULT NULL,
  ClientsGroupId BIGINT(20) DEFAULT 1100000050 COMMENT 'Ссылка на группу',
  ParentId BIGINT(20) DEFAULT 0,
  ClassId INT(11) DEFAULT NULL,
  Flags INT(11) DEFAULT 1,
  Surname VARCHAR(35) DEFAULT NULL,
  Name VARCHAR(35) DEFAULT NULL,
  SecondName VARCHAR(35) DEFAULT NULL,
  phone VARCHAR(24) DEFAULT NULL,
  mobile VARCHAR(24) DEFAULT NULL,
  fax VARCHAR(24) DEFAULT NULL,
  email VARCHAR(50) DEFAULT NULL,
  notifyViaEmail BIT(1) DEFAULT NULL,
  notifyViaSMS BIT(1) DEFAULT NULL,
  notifyViaFax BIT(1) DEFAULT NULL,
  notifyViaPush BIT(1) NOT NULL DEFAULT b'0',
  Image MEDIUMBLOB DEFAULT NULL,
  ImageDate DATETIME DEFAULT NULL,
  Remarks MEDIUMTEXT DEFAULT NULL,
  address VARCHAR(90) DEFAULT NULL,
  CreatedDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  LastUpdate DATETIME DEFAULT NULL,
  IDDocument VARCHAR(128) DEFAULT NULL,
  ContractState INT(11) DEFAULT NULL,
  Version INT(12) DEFAULT NULL,
  FreePayMaxCount INT(2) NOT NULL DEFAULT 0,
  FreePayCount INT(2) NOT NULL DEFAULT 0,
  FreePayLastTime DATETIME DEFAULT NULL,
  SyncState INT(1) NOT NULL DEFAULT 0,
  DiscountMode INT(1) NOT NULL DEFAULT 0,
  AutocheckId INT(11) DEFAULT NULL,
  ClientType INT(11) NOT NULL DEFAULT 0,
  CategoriesDiscounts VARCHAR(60) NOT NULL DEFAULT '',
  ContractId BIGINT(20) DEFAULT NULL,
  Guid VARCHAR(36) DEFAULT NULL,
  CanConfirmGroupPayment BIT(1) NOT NULL DEFAULT b'0',
  ClassTeacherId BIGINT(20) DEFAULT NULL,
  DisablePlanCreation BIT(1) NOT NULL DEFAULT b'0',
  IsTempClient BIT(1) NOT NULL DEFAULT b'0',
  OrgOwner BIGINT(20) DEFAULT NULL,
  IsUseLastEnterEventModeForPlan BIT(1) NOT NULL DEFAULT b'0',
  Gender INT(11) DEFAULT NULL COMMENT 'Пол (0 - М, 1 - Ж)',
  BirthDate DATE DEFAULT NULL COMMENT 'Дата рождения',
  DiscountOnEntrance VARCHAR(3000) DEFAULT NULL COMMENT 'Льгота при поступлении',
  IsAlien BIT(1) NOT NULL DEFAULT b'0' COMMENT 'Признак мигранта',
  PRIMARY KEY (IdOfClient),
  INDEX Index_2 (ParentId),
  UNIQUE INDEX OneSCode (OneSCode),
  INDEX UK_clients_Flags (Flags),
  CONSTRAINT clients_FK3 FOREIGN KEY (ClassTeacherId)
  REFERENCES `ispp-ecafe-test`.`clients`(IdOfClient) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_clients_IdOfClientsGroup FOREIGN KEY (ClientsGroupId)
  REFERENCES `ispp-ecafe-test`.`clients_groups`(IdOfClientsGroup) ON DELETE RESTRICT ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 3884533
  AVG_ROW_LENGTH = 8685
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Структура таблицы `clients_photo`
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`clients_photo`;
CREATE TABLE `ispp-ecafe-test`.`clients_photo` (
  IdOfRecord BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор записи',
  IdOfClient BIGINT(20) NOT NULL COMMENT 'Ид клиента',
  ImageBytes MEDIUMBLOB DEFAULT NULL COMMENT 'Фотография',
  ImageDate DATETIME DEFAULT NULL COMMENT 'Дата фотографии',
  SyncState INT(1) NOT NULL DEFAULT 0 COMMENT 'Статус синхронизации',
  Version BIGINT(20) DEFAULT NULL COMMENT 'Версия',
  PRIMARY KEY (IdOfRecord),
  UNIQUE INDEX UNIQ_IDX_client_photo (IdOfClient)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2735
  AVG_ROW_LENGTH = 17218
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Описание для таблицы organizations
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`organizations`;
CREATE TABLE `ispp-ecafe-test`.`organizations` (
  IdOfRecord BIGINT(20) NOT NULL AUTO_INCREMENT,
  OrgId INT(11) NOT NULL,
  OrgType INT(11) NOT NULL,
  Name VARCHAR(128) NOT NULL,
  FullName VARCHAR(512) DEFAULT NULL,
  DirectorName VARCHAR(255) DEFAULT NULL,
  Version BIGINT(20) DEFAULT NULL,
  Address VARCHAR(512) DEFAULT NULL,
  NameForProvider VARCHAR(512) DEFAULT NULL COMMENT 'Наименование для поставщика питания',
  ConfigurationId BIGINT(20) DEFAULT NULL,
  SupplierId BIGINT(20) DEFAULT NULL,
  UseSubsctiptionFeeding BIT(1) NOT NULL DEFAULT b'0' COMMENT 'признак использования абонем. питания',
  CategoryOrgs VARCHAR(512) DEFAULT NULL COMMENT 'содержит перечисление категорий организаций',
  IsFriendlyOrg BIT(1) NOT NULL DEFAULT b'0' COMMENT 'Признак дружественной организации. 1 - дружественная, 0 - нет',
  NameOfCounty VARCHAR(45) DEFAULT NULL COMMENT 'Наименование округа',
  IsActive BIT(1) NOT NULL COMMENT 'Признак активной организации. 0 - не активна, 1 - активна',
  PRIMARY KEY (IdOfRecord),
  UNIQUE INDEX OrgId (OrgId)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5384
  AVG_ROW_LENGTH = 717
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Описание для таблицы classes_teachers
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`classes_teachers`;
CREATE TABLE `ispp-ecafe-test`.`classes_teachers` (
  IdOfClassTeacher BIGINT(20) NOT NULL AUTO_INCREMENT,
  IdOfClientsGroup BIGINT(20) NOT NULL COMMENT 'Идентификатор группы',
  IdOfClient BIGINT(20) NOT NULL COMMENT 'Идентификатор клиента',
  CreatedDate DATETIME NOT NULL,
  LastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  Version BIGINT(20) DEFAULT NULL,
  OrgOwner BIGINT(20) NOT NULL DEFAULT 0,
  DeletedState BIT(1) NOT NULL DEFAULT b'0' COMMENT 'признак удаленного',
  SyncState INT(11) NOT NULL DEFAULT 0 COMMENT 'статус синхронизации',
  PRIMARY KEY (IdOfClassTeacher),
  CONSTRAINT FK_classes_teachers_IdOfClient FOREIGN KEY (IdOfClient)
  REFERENCES `ispp-ecafe-test`.`clients`(IdOfClient) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FK_classes_teachers_IdOfClientsGroup FOREIGN KEY (IdOfClientsGroup)
  REFERENCES `ispp-ecafe-test`.`clients_groups`(IdOfClientsGroup) ON DELETE CASCADE ON UPDATE CASCADE
)
  ENGINE = INNODB
  AUTO_INCREMENT = 129
  AVG_ROW_LENGTH = 159
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Описание для таблицы guardians
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`guardians`;
CREATE TABLE `ispp-ecafe-test`.`guardians` (
  IdOfRecord BIGINT(20) NOT NULL AUTO_INCREMENT,
  GuardianClientId BIGINT(20) NOT NULL,
  ChildClientId BIGINT(20) NOT NULL,
  DeletedState BIT(1) NOT NULL DEFAULT b'0' COMMENT 'признак удаленного',
  SyncState INT(1) NOT NULL DEFAULT 0 COMMENT 'статус синхронизации',
  Version BIGINT(20) DEFAULT NULL COMMENT 'версия',
  Type INT(1) DEFAULT NULL COMMENT 'тип связи',
  IsDisabled BIT(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (IdOfRecord),
  UNIQUE INDEX Guardian_Child_Unique_Indx (GuardianClientId, ChildClientId),
  CONSTRAINT ChildClient_fk FOREIGN KEY (ChildClientId)
  REFERENCES clients(IdOfClient) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT GuardianClient_fk FOREIGN KEY (GuardianClientId)
  REFERENCES clients(IdOfClient) ON DELETE NO ACTION ON UPDATE NO ACTION
)
  ENGINE = INNODB
  AUTO_INCREMENT = 3995
  AVG_ROW_LENGTH = 72
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Описание для таблицы enterevents
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`enterevents`;
CREATE TABLE `ispp-ecafe-test`.`enterevents` (
  IdOfEnterEvent BIGINT(20) NOT NULL AUTO_INCREMENT,
  EnterName VARCHAR(60) NOT NULL,
  TurnstileAddr VARCHAR(100) DEFAULT NULL,
  PassDirection INT(11) NOT NULL,
  EventCode INT(11) NOT NULL,
  IdOfCard BIGINT(20) DEFAULT NULL,
  IdOfClient BIGINT(20) DEFAULT NULL,
  IdOfTempCard BIGINT(20) DEFAULT NULL,
  EvtDateTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FlagProcessed SMALLINT(6) NOT NULL DEFAULT 0,
  IdOfVisitor BIGINT(20) DEFAULT NULL,
  PassWithGuardian BIGINT(20) DEFAULT NULL,
  ChildPassChecker BIGINT(20) DEFAULT NULL,
  ChildPassCheckerId BIGINT(20) DEFAULT NULL,
  EventHash BIGINT(20) DEFAULT NULL,
  PRIMARY KEY (IdOfEnterEvent),
  INDEX enterevents_idofclient_fk (IdOfClient),
  UNIQUE INDEX EventHash_UNIQUE (EventHash),
  INDEX IX_enterevents_EvtDateTime (EvtDateTime),
  CONSTRAINT ChildPassChecker_FK1 FOREIGN KEY (ChildPassCheckerId)
  REFERENCES clients(IdOfClient) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT PassWithGuardian_FK1 FOREIGN KEY (PassWithGuardian)
  REFERENCES clients(IdOfClient) ON DELETE NO ACTION ON UPDATE NO ACTION
)
  ENGINE = INNODB
  AUTO_INCREMENT = 745349
  AVG_ROW_LENGTH = 91
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Описание для таблицы cards
--
DROP TABLE IF EXISTS `ispp-ecafe-test`.`cards`;
CREATE TABLE `ispp-ecafe-test`.`cards` (
  IdOfCard BIGINT(20) NOT NULL,
  CardType INT(2) NOT NULL,
  CreatedDate DATETIME NOT NULL,
  LastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ForbidExit TINYINT(4) NOT NULL DEFAULT 0,
  State INT(11) NOT NULL,
  LockReason VARCHAR(64) DEFAULT NULL,
  ValidDate DATETIME DEFAULT NULL,
  IdOfClient BIGINT(20) DEFAULT NULL,
  IssueDate DATETIME DEFAULT NULL,
  CardPrintedNo BIGINT(20) DEFAULT NULL COMMENT 'номер напечатанный на карте',
  IdOfAccount BIGINT(20) DEFAULT NULL,
  IdOfVisitor BIGINT(20) DEFAULT NULL,
  OrgOwner INT(11) DEFAULT NULL,
  IsTempCard BIT(1) NOT NULL DEFAULT b'0',
  IsAlien BIT(1) NOT NULL DEFAULT b'0' COMMENT 'Признак карты мигранта',
  PRIMARY KEY (IdOfCard),
  CONSTRAINT cards_FK1 FOREIGN KEY (IdOfClient)
  REFERENCES clients(IdOfClient) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_cards_accounts_IdOfAccount FOREIGN KEY (IdOfAccount)
  REFERENCES accounts(IdOfAccount) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT FK_cards_visitors_IdOfVisitor FOREIGN KEY (IdOfVisitor)
  REFERENCES visitors(IdOfVisitor) ON DELETE RESTRICT ON UPDATE CASCADE
)
  ENGINE = INNODB
  AVG_ROW_LENGTH = 282
  CHARACTER SET utf8
  COLLATE utf8_general_ci;


--
-- ISEduC test tables creation
--

--
-- Структура таблицы `ispp_branch`
--
DROP TABLE IF EXISTS `ispp-iseduc-test`.`ispp_branch`;
CREATE TABLE IF NOT EXISTS `ispp-iseduc-test`.`ispp_branch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(128) NOT NULL,
  `short_name` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `img` varchar(255) NOT NULL,
  `bg_color` varchar(32) NOT NULL,
  `head_id` int(11) DEFAULT NULL COMMENT 'FK for #__profile table',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_by` int(11) NOT NULL DEFAULT '0',
  `state` smallint(3) NOT NULL DEFAULT '1' COMMENT 'Visibility: 0 - hide, 1 - publish',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_idx_head_id` (`head_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Структура таблицы `ispp_group`
--
DROP TABLE IF EXISTS `ispp-iseduc-test`.`ispp_group`;
CREATE TABLE IF NOT EXISTS `ispp-iseduc-test`.`ispp_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) unsigned DEFAULT NULL COMMENT 'clients_groups.IdOfClientsGroup',
  `name` varchar(45) NOT NULL,
  `description` text NOT NULL,
  `branch_id` int(11) DEFAULT NULL COMMENT 'FK for #__ispp_branch table',
  `teacher_id` int(11) DEFAULT NULL COMMENT 'FK for #__profile table',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
DROP TABLE IF EXISTS `ispp-iseduc-test`.`ispp_student`;
CREATE TABLE IF NOT EXISTS `ispp-iseduc-test`.`ispp_student` (
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
DROP TABLE IF EXISTS `ispp-iseduc-test`.`ispp_event`;
CREATE TABLE IF NOT EXISTS `ispp-iseduc-test`.`ispp_event` (
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
DROP TABLE IF EXISTS `ispp-iseduc-test`.`ispp_sync`;
CREATE TABLE IF NOT EXISTS `ispp-iseduc-test`.`ispp_sync` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL COMMENT 'Console command',
  `errors` int(11) NOT NULL COMMENT 'Number of errors',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_system_action` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;