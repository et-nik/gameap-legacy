ALTER TABLE `dedicated_servers` ADD `steamcmd_path` TINYTEXT NOT NULL DEFAULT '' AFTER `cpu` ;

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` char(64) NOT NULL DEFAULT '',
  `file` tinytext NOT NULL DEFAULT '',
  `enabled` int(1) NOT NULL DEFAULT 1,
  `version` char(64) NOT NULL DEFAULT '',
  `developer` char(64) NOT NULL DEFAULT '',
  `site` tinytext NOT NULL DEFAULT '',
  `information` tinytext NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
