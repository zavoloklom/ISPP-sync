/*
  DB creation
 */

DROP DATABASE IF EXISTS `ispp-ecafe-test`;
DROP DATABASE IF EXISTS `ispp-iseduc-test`;

CREATE DATABASE IF NOT EXISTS `ispp-ecafe-test`;
CREATE DATABASE IF NOT EXISTS `ispp-iseduc-test`;

/*
  Ecafe test tables creation
 */
DROP TABLE IF EXISTS `ispp-ecafe-test`.`clients_groups`;
CREATE TABLE `ispp-ecafe-test`.`clients_groups` (
  IdOfClientsGroup BIGINT(20) NOT NULL AUTO_INCREMENT,
  Name VARCHAR(256) DEFAULT NULL,
  DisablePlanCreation BIT(1) NOT NULL DEFAULT b'0',
  Version INT(11) DEFAULT NULL,
  SyncState INT(11) NOT NULL,
  CreatedDate DATETIME NOT NULL,
  LastUpdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  GroupType INT(11) DEFAULT 0,
  AccessLevel INT(11) NOT NULL DEFAULT 0,
  BindingToOrg BIGINT(20) DEFAULT NULL,
  PRIMARY KEY (IdOfClientsGroup),
  INDEX IX_clients_groups_SyncState (SyncState)
)
ENGINE = INNODB
AUTO_INCREMENT = 1100000131
AVG_ROW_LENGTH = 94
CHARACTER SET utf8
COLLATE utf8_general_ci;

/*
  ISEduC test tables creation
 */
