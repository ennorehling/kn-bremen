CREATE TABLE IF NOT EXISTS `passwd` (
  `host` varchar(32) NOT NULL DEFAULT '',
  `pass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`host`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS `mappings` (
  `host` varchar(32) NOT NULL DEFAULT '',
  `ip` varchar(64) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`host`),
  UNIQUE KEY `host` (`host`),
  UNIQUE KEY `ip` (`ip`,`host`) USING BTREE
) ENGINE=InnoDB;
ALTER TABLE `mappings`
  ADD COLUMN IF NOT EXISTS `ipv6` varchar(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `ipv4` varchar(64) DEFAULT NULL,
  MODIFY `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;

CREATE UNIQUE INDEX IF NOT EXISTS `ipv4` ON mappings (`ipv4`,`host`) USING BTREE;
CREATE UNIQUE INDEX IF NOT EXISTS `ipv6` ON mappings (`ipv6`,`host`) USING BTREE;
UPDATE `mappings` SET `ipv4` = `ip`, `ip` = NULL WHERE `ip` IS NOT NULL;
INSERT INTO `mappings` (`host`, `ipv6`, `created`) 
  SELECT `host`, `ipv6`, `created` FROM `mappings6`
  ON DUPLICATE KEY UPDATE
    `ipv6` = VALUES(`ipv6`),
    `created` = VALUES(`created`);
    -- LEAST(GREATEST(NOW(), `created`), GREATEST(NOW(), VALUES(`m6.created`)));
DROP TABLE mappings6;
