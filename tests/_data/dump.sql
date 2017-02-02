-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 02 2017 г., 23:27
-- Версия сервера: 10.0.28-MariaDB-0+deb8u1
-- Версия PHP: 5.6.29-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `gameap_test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_actions`
--

DROP TABLE IF EXISTS `gameap_actions`;
CREATE TABLE IF NOT EXISTS `gameap_actions` (
  `id` tinytext NOT NULL,
  `action` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_cron`
--

DROP TABLE IF EXISTS `gameap_cron`;
CREATE TABLE IF NOT EXISTS `gameap_cron` (
  `id` int(16) NOT NULL,
  `name` tinytext NOT NULL,
  `code` varchar(32) NOT NULL,
  `command` tinytext NOT NULL,
  `server_id` int(16) NOT NULL,
  `user_id` int(16) NOT NULL,
  `started` int(1) NOT NULL,
  `date_perform` int(32) NOT NULL,
  `date_performed` int(32) NOT NULL,
  `time_add` int(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_dedicated_servers`
--

DROP TABLE IF EXISTS `gameap_dedicated_servers`;
CREATE TABLE IF NOT EXISTS `gameap_dedicated_servers` (
  `id` int(16) NOT NULL,
  `name` tinytext NOT NULL,
  `disabled` int(1) NOT NULL,
  `os` tinytext NOT NULL,
  `location` tinytext NOT NULL,
  `provider` tinytext NOT NULL,
  `ip` text NOT NULL,
  `ram` tinytext NOT NULL,
  `cpu` tinytext NOT NULL,
  `work_path` varchar(256) NOT NULL,
  `steamcmd_path` tinytext NOT NULL,
  `gdaemon_host` tinytext NOT NULL,
  `gdaemon_login` varchar(128) NOT NULL,
  `gdaemon_password` text NOT NULL,
  `gdaemon_privkey` varchar(256) NOT NULL,
  `gdaemon_pubkey` varchar(256) NOT NULL,
  `gdaemon_keypass` text NOT NULL,
  `script_start` text NOT NULL,
  `script_stop` text NOT NULL,
  `script_restart` text NOT NULL,
  `script_status` text NOT NULL,
  `script_get_console` text NOT NULL,
  `script_send_command` text NOT NULL,
  `modules_data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_ds_stats`
--

DROP TABLE IF EXISTS `gameap_ds_stats`;
CREATE TABLE IF NOT EXISTS `gameap_ds_stats` (
  `id` int(11) NOT NULL,
  `ds_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `loa` tinytext NOT NULL,
  `ram` tinytext NOT NULL,
  `cpu` tinytext NOT NULL,
  `ifstat` tinytext NOT NULL,
  `ping` int(4) NOT NULL,
  `drvspace` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_ds_users`
--

DROP TABLE IF EXISTS `gameap_ds_users`;
CREATE TABLE IF NOT EXISTS `gameap_ds_users` (
  `id` int(11) NOT NULL,
  `ds_id` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_games`
--

DROP TABLE IF EXISTS `gameap_games`;
CREATE TABLE IF NOT EXISTS `gameap_games` (
  `code` varchar(16) NOT NULL,
  `start_code` varchar(16) NOT NULL,
  `name` tinytext NOT NULL,
  `engine` tinytext NOT NULL,
  `engine_version` varchar(32) NOT NULL DEFAULT '1',
  `app_id` int(16) NOT NULL,
  `app_set_config` varchar(64) NOT NULL DEFAULT '',
  `remote_repository` text NOT NULL,
  `local_repository` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_game_types`
--

DROP TABLE IF EXISTS `gameap_game_types`;
CREATE TABLE IF NOT EXISTS `gameap_game_types` (
  `id` int(16) NOT NULL,
  `game_code` varchar(16) NOT NULL,
  `name` tinytext NOT NULL,
  `fast_rcon` text NOT NULL,
  `aliases` text NOT NULL,
  `remote_repository` text NOT NULL,
  `local_repository` text NOT NULL,
  `kick_cmd` varchar(64) NOT NULL DEFAULT '',
  `ban_cmd` varchar(64) NOT NULL DEFAULT '',
  `chname_cmd` varchar(64) NOT NULL DEFAULT '',
  `srestart_cmd` varchar(64) NOT NULL DEFAULT '',
  `chmap_cmd` varchar(64) NOT NULL DEFAULT '',
  `sendmsg_cmd` varchar(64) NOT NULL DEFAULT '',
  `passwd_cmd` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_gdaemon_tasks`
--

DROP TABLE IF EXISTS `gameap_gdaemon_tasks`;
CREATE TABLE IF NOT EXISTS `gameap_gdaemon_tasks` (
  `id` int(11) NOT NULL,
  `run_aft_id` int(11) NOT NULL,
  `time_create` int(11) NOT NULL,
  `time_stchange` int(11) NOT NULL,
  `ds_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `task` varchar(8) NOT NULL,
  `data` mediumtext NOT NULL,
  `cmd` text NOT NULL,
  `output` mediumtext NOT NULL,
  `status` enum('waiting','working','error','success') NOT NULL DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_logs`
--

DROP TABLE IF EXISTS `gameap_logs`;
CREATE TABLE IF NOT EXISTS `gameap_logs` (
  `id` int(16) NOT NULL,
  `date` int(32) NOT NULL,
  `type` tinytext NOT NULL,
  `command` varchar(32) NOT NULL,
  `user_name` tinytext NOT NULL,
  `server_id` int(32) NOT NULL,
  `ip` tinytext NOT NULL,
  `msg` tinytext NOT NULL,
  `log_data` mediumtext NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_modules`
--

DROP TABLE IF EXISTS `gameap_modules`;
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

DROP TABLE IF EXISTS `gameap_servers`;
CREATE TABLE IF NOT EXISTS `gameap_servers` (
  `id` int(16) NOT NULL,
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
  `cpu_limit` int(11) NOT NULL,
  `ram_limit` int(11) NOT NULL,
  `net_limit` int(11) NOT NULL,
  `status` text NOT NULL,
  `script_start` tinytext NOT NULL,
  `start_command` text NOT NULL,
  `start_after_crash` int(1) NOT NULL,
  `process_active` int(1) NOT NULL,
  `last_process_check` int(11) NOT NULL,
  `aliases` text NOT NULL,
  `modules_data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_servers_privileges`
--

DROP TABLE IF EXISTS `gameap_servers_privileges`;
CREATE TABLE IF NOT EXISTS `gameap_servers_privileges` (
  `user_id` int(16) NOT NULL,
  `server_id` int(16) NOT NULL,
  `privileges` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `gameap_servers_sessions`
--

DROP TABLE IF EXISTS `gameap_servers_sessions`;
CREATE TABLE IF NOT EXISTS `gameap_servers_sessions` (
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

DROP TABLE IF EXISTS `gameap_settings`;
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

DROP TABLE IF EXISTS `gameap_users`;
CREATE TABLE IF NOT EXISTS `gameap_users` (
  `id` int(16) NOT NULL,
  `login` tinytext NOT NULL,
  `password` text NOT NULL,
  `hash` tinytext NOT NULL,
  `is_admin` int(16) NOT NULL DEFAULT '0',
  `group` int(11) NOT NULL,
  `recovery_code` tinytext NOT NULL,
  `confirm_code` tinytext NOT NULL,
  `action` tinytext NOT NULL,
  `balance` tinytext NOT NULL,
  `reg_date` varchar(32) NOT NULL,
  `last_auth` int(32) NOT NULL,
  `name` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `privileges` text NOT NULL,
  `modules_data` tinytext NOT NULL,
  `filters` mediumtext NOT NULL,
  `notices` mediumtext NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `gameap_cron`
--
ALTER TABLE `gameap_cron`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_dedicated_servers`
--
ALTER TABLE `gameap_dedicated_servers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_ds_stats`
--
ALTER TABLE `gameap_ds_stats`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_ds_users`
--
ALTER TABLE `gameap_ds_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_games`
--
ALTER TABLE `gameap_games`
  ADD PRIMARY KEY (`code`);

--
-- Индексы таблицы `gameap_game_types`
--
ALTER TABLE `gameap_game_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_gdaemon_tasks`
--
ALTER TABLE `gameap_gdaemon_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_logs`
--
ALTER TABLE `gameap_logs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_modules`
--
ALTER TABLE `gameap_modules`
  ADD PRIMARY KEY (`short_name`);

--
-- Индексы таблицы `gameap_servers`
--
ALTER TABLE `gameap_servers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gameap_users`
--
ALTER TABLE `gameap_users`
  ADD PRIMARY KEY (`id`);


--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `gameap_cron`
--
ALTER TABLE `gameap_cron`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_dedicated_servers`
--
ALTER TABLE `gameap_dedicated_servers`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_ds_stats`
--
ALTER TABLE `gameap_ds_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_ds_users`
--
ALTER TABLE `gameap_ds_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_game_types`
--
ALTER TABLE `gameap_game_types`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_gdaemon_tasks`
--
ALTER TABLE `gameap_gdaemon_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_logs`
--
ALTER TABLE `gameap_logs`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_servers`
--
ALTER TABLE `gameap_servers`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `gameap_users`
--
ALTER TABLE `gameap_users`
  MODIFY `id` int(16) NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;