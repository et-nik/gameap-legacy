SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Структура таблицы `gameap_actions`
--

CREATE TABLE IF NOT EXISTS `gameap_actions` (
  `id` tinytext NOT NULL,
  `action` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_captcha`
--

CREATE TABLE IF NOT EXISTS `gameap_captcha` (
`captcha_id` int(16) NOT NULL,
  `captcha_time` int(32) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `word` varchar(64) NOT NULL,
  PRIMARY KEY (`captcha_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_cron`
--

CREATE TABLE IF NOT EXISTS `gameap_cron` (
`id` int(16) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `code` varchar(32) NOT NULL,
  `command` tinytext NOT NULL,
  `server_id` int(16) NOT NULL,
  `user_id` int(16) NOT NULL,
  `started` int(1) NOT NULL,
  `date_perform` int(32) NOT NULL,
  `date_performed` int(32) NOT NULL,
  `time_add` int(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_dedicated_servers`
--

CREATE TABLE IF NOT EXISTS `gameap_dedicated_servers` (
`id` int(16) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `disabled` int(1) NOT NULL,
  `os` tinytext NOT NULL,
  `control_protocol` varchar(8) NOT NULL,
  `location` tinytext NOT NULL,
  `provider` tinytext NOT NULL,
  `ip` text NOT NULL,
  `ram` tinytext NOT NULL,
  `cpu` tinytext NOT NULL,
  `stats` text NOT NULL,
  `steamcmd_path` tinytext NOT NULL,
  `gdaemon_host` tinytext NOT NULL,
  `gdaemon_key` text NOT NULL,
  `ssh_host` tinytext NOT NULL,
  `ssh_login` tinytext NOT NULL,
  `ssh_password` tinytext NOT NULL,
  `ssh_path` tinytext NOT NULL,
  `telnet_host` tinytext NOT NULL,
  `telnet_login` tinytext NOT NULL,
  `telnet_password` tinytext NOT NULL,
  `telnet_path` tinytext NOT NULL,
  `ftp_host` tinytext NOT NULL,
  `ftp_login` tinytext NOT NULL,
  `ftp_password` tinytext NOT NULL,
  `ftp_path` tinytext NOT NULL,
  `script_send_command` text NOT NULL,
  `script_get_console` text NOT NULL,
  `script_status` text NOT NULL,
  `script_restart` text NOT NULL,
  `script_stop` text NOT NULL,
  `script_start` text NOT NULL,
  `modules_data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_games`
--

CREATE TABLE IF NOT EXISTS `gameap_games` (
  `code` char(16) NOT NULL,
  `start_code` char(16) NOT NULL,
  `name` tinytext NOT NULL,
  `engine` tinytext NOT NULL,
  `engine_version` varchar(16) NOT NULL,
  `app_id` int(16) NOT NULL,
  `app_set_config` char(64) NOT NULL,
  `local_repository` text NOT NULL,
  `remote_repository` text NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_game_types`
--

CREATE TABLE IF NOT EXISTS `gameap_game_types` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `game_code` char(16) NOT NULL,
  `name` tinytext NOT NULL,
  `config_files` text NOT NULL,
  `content_dirs` text NOT NULL,
  `log_dirs` text NOT NULL,
  `fast_rcon` text NOT NULL,
  `aliases` text NOT NULL,
  `disk_size` int(11) NOT NULL,
  `local_repository` text NOT NULL,
  `remote_repository` text NOT NULL,
  `passwd_cmd` varchar(64) NOT NULL DEFAULT '',
  `sendmsg_cmd` varchar(64) NOT NULL DEFAULT '',
  `chmap_cmd` varchar(64) NOT NULL DEFAULT '',
  `srestart_cmd` varchar(64) NOT NULL DEFAULT '',
  `chname_cmd` varchar(64) NOT NULL DEFAULT '',
  `ban_cmd` varchar(64) NOT NULL DEFAULT '',
  `kick_cmd` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_logs`
--

CREATE TABLE IF NOT EXISTS `gameap_logs` (
`id` int(16) NOT NULL AUTO_INCREMENT,
  `date` int(32) NOT NULL,
  `type` tinytext NOT NULL,
  `command` varchar(32) NOT NULL,
  `user_name` tinytext NOT NULL,
  `server_id` int(32) NOT NULL,
  `ip` tinytext NOT NULL,
  `msg` tinytext NOT NULL,
  `log_data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_migrations`
--

CREATE TABLE IF NOT EXISTS `gameap_migrations` (
  `version` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_modules`
--

CREATE TABLE IF NOT EXISTS `gameap_modules` (
  `short_name` varchar(32) NOT NULL,
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `cron_script` tinytext NOT NULL,
  `version` varchar(64) NOT NULL,
  `update_info` tinytext NOT NULL,
  `show_in_menu` int(1) NOT NULL,
  `access` tinytext NOT NULL,
  `developer` varchar(64) NOT NULL,
  `site` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `copyright` tinytext NOT NULL,
  `license` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_servers`
--

CREATE TABLE IF NOT EXISTS `gameap_servers` (
`id` int(16) NOT NULL AUTO_INCREMENT,
  `screen_name` varchar(64) NOT NULL DEFAULT '',
  `game` varchar(16) NOT NULL,
  `game_type` int(16) NOT NULL,
  `name` tinytext NOT NULL,
  `expires` int(32) NOT NULL,
  `ds_id` int(16) NOT NULL,
  `enabled` int(1) NOT NULL DEFAULT '1',
  `installed` int(1) NOT NULL,
  `server_ip` tinytext NOT NULL,
  `server_port` int(5) NOT NULL,
  `query_port` int(5) NOT NULL,
  `rcon_port` int(5) NOT NULL,
  `rcon` tinytext NOT NULL,
  `maps_path` tinytext NOT NULL,
  `maps_list` text NOT NULL,
  `dir` tinytext NOT NULL,
  `su_user` varchar(32) NOT NULL DEFAULT '',
  `status` text NOT NULL,
  `net_limit` int(11) NOT NULL,
  `ram_limit` int(11) NOT NULL,
  `cpu_limit` int(11) NOT NULL,
  `script_start` tinytext NOT NULL,
  `start_command` text NOT NULL,
  `aliases` text NOT NULL,
  `modules_data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_servers_privileges`
--

CREATE TABLE IF NOT EXISTS `gameap_servers_privileges` (
  `user_id` int(16) NOT NULL,
  `server_id` int(16) NOT NULL,
  `privileges` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_sessions`
--

CREATE TABLE IF NOT EXISTS `gameap_sessions` (
  `user_id` int(11) NOT NULL,
  `hash` tinytext NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `user_agent` tinytext NOT NULL,
  `expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_settings`
--

CREATE TABLE IF NOT EXISTS `gameap_settings` (
  `sett_id` varchar(32) NOT NULL,
  `user_id` int(16) NOT NULL,
  `server_id` int(16) NOT NULL,
  `value` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_users`
--

CREATE TABLE IF NOT EXISTS `gameap_users` (
`id` int(16) NOT NULL AUTO_INCREMENT,
  `login` tinytext NOT NULL,
  `password` text NOT NULL,
  `hash` tinytext NOT NULL,
  `is_admin` int(16) NOT NULL DEFAULT '0',
  `recovery_code` tinytext NOT NULL,
  `confirm_code` tinytext NOT NULL,
  `action` tinytext NOT NULL,
  `balance` tinytext NOT NULL,
  `reg_date` varchar(32) NOT NULL,
  `last_auth` int(32) NOT NULL,
  `name` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `privileges` text NOT NULL,
  `modules_data` mediumtext NOT NULL,
  `filters` tinytext NOT NULL,
  `notices` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

