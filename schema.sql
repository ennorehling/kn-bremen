CREATE TABLE `passwd` (
  `host` varchar(32) NOT NULL DEFAULT '',
  `pass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`host`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB;
CREATE TABLE `mappings` (
  `host` varchar(32) NOT NULL DEFAULT '',
  `ip` varchar(64) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`host`),
  UNIQUE KEY `host` (`host`),
  UNIQUE KEY `ip` (`ip`,`host`) USING BTREE
) ENGINE=InnoDB;
CREATE TABLE `mappings6` (
  `host` varchar(32) NOT NULL DEFAULT '',
  `ipv6` varchar(64) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`host`),
  UNIQUE KEY `host` (`host`),
  UNIQUE KEY `ipv6` (`ipv6`,`host`) USING BTREE
) ENGINE=InnoDB;
