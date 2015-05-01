<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2015, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource	
 */

/**
 * Создание стандартной базы игровых модификаций
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9
 */

/* ----------------------------------------------------- */
/* 						Игры							 */
/* ----------------------------------------------------- */
			
				
$data = array(
		'code' => "ag",
		'start_code' => "ag",
		'name' => "Adrenaline Gamer",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "arma2",
		'start_code' => "arma2",
		'name' => "Arma 2",
		'engine' => "armedassault2",
		'engine_version' => "1",
		'app_id' => "33905",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "arma3",
		'start_code' => "arma3",
		'name' => "Arma 3",
		'engine' => "armedassault3",
		'engine_version' => "",
		'app_id' => "233780",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "cod4",
		'start_code' => "cod4",
		'name' => "Call of Duty 4",
		'engine' => "cod4",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "cstrike",
		'start_code' => "cstrike",
		'name' => "Counter-Strike 1.6",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "czero",
		'start_code' => "czero",
		'name' => "Counter-Strike: Condition Zero",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "90 mod czero",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "csgo",
		'start_code' => "csgo",
		'name' => "Counter-Strike: Global Offensive",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "740",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "cssource",
		'start_code' => "cstrike",
		'name' => "Counter-Strike: Source",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "232330",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "cssv34",
		'start_code' => "cstrike",
		'name' => "Counter-Strike: Source v34",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "/root/repository/cssv34",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "dod",
		'start_code' => "dod",
		'name' => "Day of Defeat",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "90 mod dod",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "dods",
		'start_code' => "dods",
		'name' => "Day of Defeat: Source",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "232290",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "dmc",
		'start_code' => "dmc",
		'name' => "Deathmatch Classic",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "garrysmod",
		'start_code' => "garrysmod",
		'name' => "Garry's Mod",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "4020",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "mta",
		'start_code' => "mta",
		'name' => "GTA: Multi Theft Auto",
		'engine' => "mta",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "samp",
		'start_code' => "samp",
		'name' => "GTA: San-Andreas Multiplayer",
		'engine' => "samp",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "valve",
		'start_code' => "valve",
		'name' => "Half-Life 1",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "hl2mp",
		'start_code' => "hl2mp",
		'name' => "Half-Life 2",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "232370",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "gearbox",
		'start_code' => "gearbox",
		'name' => "Half-Life: Opposing Force",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "90 mod gearbox",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "l4d",
		'start_code' => "l4d",
		'name' => "Left 4 Dead",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "l4d2",
		'start_code' => "l4d2",
		'name' => "Left 4 Dead 2",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "222860",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "minecraft",
		'start_code' => "minecraft",
		'name' => "Minecraft",
		'engine' => "minecraft",
		'engine_version' => "1",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "ricochet",
		'start_code' => "ricochet",
		'name' => "Ricochet",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "90 mod ricochet",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "rust_exp",
		'start_code' => "rust_exp",
		'name' => "Rust Experimental",
		'engine' => "rust",
		'engine_version' => "experimental",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "C:\servers\Files\Rust\Release",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "rust",
		'start_code' => "rust",
		'name' => "Rust Legacy",
		'engine' => "rust",
		'engine_version' => "legacy",
		'app_id' => "0",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "C:\servers\Files\Rust\rust_01",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "svencoop",
		'start_code' => "svencoop",
		'name' => "Sven Co-op",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "synergy",
		'start_code' => "synergy",
		'name' => "Synergy",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "17525",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "tf2",
		'start_code' => "tf",
		'name' => "Team Fortress 2",
		'engine' => "Source",
		'engine_version' => "1",
		'app_id' => "232250",
		'app_set_config' => "",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);
			
$data = array(
		'code' => "tfc",
		'start_code' => "tfc",
		'name' => "Team Fortress Classic",
		'engine' => "GoldSource",
		'engine_version' => "1",
		'app_id' => "90",
		'app_set_config' => "90 mod tfc",
		'remote_repository' => "",
		'local_repository' => "",
);
$this->games->add_game($data);

/* ----------------------------------------------------- */
/* 						Модификации						 */
/* ----------------------------------------------------- */
			
$data = array(
		'id' => "1",
		'game_code' => "cstrike",
		'name' => "Classic",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban #{id} {time} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "sv_password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "2",
		'game_code' => "valve",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":true},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban \"{name}\" {time} \"{reason}\"",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "3",
		'game_code' => "hl2mp",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":false}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "4",
		'game_code' => "svencoop",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hlds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayer\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "5",
		'game_code' => "tf2",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "6",
		'game_code' => "valve",
		'name' => "No AmxBans",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":true},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "7",
		'game_code' => "czero",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "8",
		'game_code' => "csgo",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "9",
		'game_code' => "valve",
		'name' => "No AMX MOD X",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"amx_who\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "10",
		'game_code' => "l4d",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "11",
		'game_code' => "cssource",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "12",
		'game_code' => "garrysmod",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "13",
		'game_code' => "cstrike",
		'name' => "No AMX MOD X",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayer\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "14",
		'game_code' => "svencoop",
		'name' => "No AMX MOD X",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayer\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "15",
		'game_code' => "gearbox",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "16",
		'game_code' => "gearbox",
		'name' => "No AMX MOD X",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayer\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "17",
		'game_code' => "dod",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "18",
		'game_code' => "dods",
		'name' => "No SourceMod",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "19",
		'game_code' => "dmc",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "20",
		'game_code' => "ricochet",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "21",
		'game_code' => "minecraft",
		'name' => "CraftBukkit",
		'fast_rcon' => "",
		'aliases' => "",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "reload",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say {msg}",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "22",
		'game_code' => "ag",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "23",
		'game_code' => "cstrike",
		'name' => "No AmxBans",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":true},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "26",
		'game_code' => "cstrike",
		'name' => "Classic (Build 5758)",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "27",
		'game_code' => "valve",
		'name' => "Standart (Build 5758)",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":true},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "28",
		'game_code' => "cssource",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "29",
		'game_code' => "csgo",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "30",
		'game_code' => "ag",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "31",
		'game_code' => "ag",
		'name' => "Standart (Build 5758)",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx Who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"fps\",\"desc\":\"\u0421\u0435\u0440\u0432\u0435\u0440\u043d\u044b\u0439 FPS\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":false}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "32",
		'game_code' => "cstrike",
		'name' => "GunGame",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "33",
		'game_code' => "cstrike",
		'name' => "DeathMatch (CSDM)",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban {time} #{id} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "34",
		'game_code' => "cssv34",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true},{\"alias\":\"tickrate\",\"desc\":\"TickRate\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "35",
		'game_code' => "tf2",
		'name' => "Standart",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"srcds_run\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e srcds_run)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true}]",
		'disk_size' => "50000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "36",
		'game_code' => "samp",
		'name' => "Standart",
		'fast_rcon' => "",
		'aliases' => "",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "37",
		'game_code' => "mta",
		'name' => "DeathMatch",
		'fast_rcon' => "",
		'aliases' => "",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "38",
		'game_code' => "cstrike",
		'name' => "Kreedz",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban #{id} {time} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "39",
		'game_code' => "cstrike",
		'name' => "DeathRun",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban #{id} {time} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "40",
		'game_code' => "cstrike",
		'name' => "JailBreak",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"},{\"desc\":\"\u041e\u0442\u043a\u043b\u044e\u0447\u0438\u0432\u0448\u0438\u0435\u0441\u044f \u0438\u0433\u0440\u043e\u043a\u0438\",\"rcon_command\":\"amx_last\"},{\"desc\":\"Amx who\",\"rcon_command\":\"amx_who\"},{\"desc\":\"Stats\",\"rcon_command\":\"stats\"}]",
		'aliases' => "[{\"alias\":\"default_map\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e\",\"only_admins\":false},{\"alias\":\"hl_exec\",\"desc\":\"\u0418\u0441\u043f\u043e\u043b\u043d\u044f\u0435\u043c\u044b\u0439 \u0444\u0430\u0439\u043b \u0438\u0433\u0440\u043e\u0432\u043e\u0433\u043e \u0441\u0435\u0440\u0432\u0435\u0440\u0430 (\u043e\u0431\u044b\u0447\u043d\u043e hlds_run \u0438\u043b\u0438 hlds.exe)\",\"only_admins\":true},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"only_admins\":true},{\"alias\":\"fps\",\"desc\":\"FPS\",\"only_admins\":true}]",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick #{id}",
		'ban_cmd' => "amx_ban #{id} {time} {reason}",
		'chname_cmd' => "amx_nick #{id} {name}",
		'srestart_cmd' => "restart",
		'chmap_cmd' => "changelevel {map}",
		'sendmsg_cmd' => "amx_say \"{msg}\"",
		'passwd_cmd' => "password {password}",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "42",
		'game_code' => "minecraft",
		'name' => "CraftBukkit 1.5.2",
		'fast_rcon' => "",
		'aliases' => "",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "reload",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say {msg}",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "43",
		'game_code' => "minecraft",
		'name' => "CraftBukkit 1.7.2",
		'fast_rcon' => "",
		'aliases' => "",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "reload",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say {msg}",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "44",
		'game_code' => "rust",
		'name' => "Standart",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "45",
		'game_code' => "rust",
		'name' => "Oxide",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"default_value\":\"\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "46",
		'game_code' => "rust",
		'name' => "Magma 1.1.5",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"default_value\":\"\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "47",
		'game_code' => "rust",
		'name' => "Magma 1.1.3",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"default_value\":\"\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "48",
		'game_code' => "rust",
		'name' => "Classic",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "49",
		'game_code' => "rust",
		'name' => "Rust Extended",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"default_value\":\"\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "50",
		'game_code' => "rust",
		'name' => "RustEssentials",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"maxplayers\",\"desc\":\"\u0421\u043b\u043e\u0442\u044b\",\"only_admins\":true}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "kick \"{id}\"",
		'ban_cmd' => "banid \"{id}\" \"{reason}\"",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say \"{msg}\"",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "51",
		'game_code' => "rust_exp",
		'name' => "Classic",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"rcon_password\",\"desc\":\"Rcon \u043f\u0430\u0440\u043e\u043b\u044c\",\"default_value\":\"\",\"only_admins\":false},{\"alias\":\"hostname\",\"desc\":\"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"default_value\":\"Empire-Host.org\",\"only_admins\":false},{\"alias\":\"saveinterval\",\"desc\":\"\u0418\u043d\u0442\u0435\u0440\u0432\u0430\u043b \u0441\u043e\u0445\u0440\u0430\u043d\u0435\u043d\u0438\u0439 (\u0441\u0435\u043a)\",\"default_value\":\"300\",\"only_admins\":false},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"default_value\":\"40\",\"only_admins\":true},{\"alias\":\"rcon_port\",\"desc\":\"Rcon \u043f\u043e\u0440\u0442\",\"default_value\":\"\",\"only_admins\":true},{\"alias\":\"identity\",\"desc\":\"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440\",\"default_value\":\"my_server_identity\",\"only_admins\":true},{\"alias\":\"worldsize\",\"desc\":\"\u0420\u0430\u0437\u043c\u0435\u0440 \u043c\u0438\u0440\u0430\",\"default_value\":\"4000\",\"only_admins\":false},{\"alias\":\"seed\",\"desc\":\"Seed\",\"default_value\":\"1234\",\"only_admins\":false},{\"alias\":\"level\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430\",\"default_value\":\"Procedural Map\",\"only_admins\":false}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "52",
		'game_code' => "rust_exp",
		'name' => "Pluton",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"rcon_password\",\"desc\":\"Rcon \u043f\u0430\u0440\u043e\u043b\u044c\",\"default_value\":\"\",\"only_admins\":false},{\"alias\":\"hostname\",\"desc\":\"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"default_value\":\"Empire-Host.org\",\"only_admins\":false},{\"alias\":\"saveinterval\",\"desc\":\"\u0418\u043d\u0442\u0435\u0440\u0432\u0430\u043b \u0441\u043e\u0445\u0440\u0430\u043d\u0435\u043d\u0438\u0439 (\u0441\u0435\u043a)\",\"default_value\":\"300\",\"only_admins\":false},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"default_value\":\"40\",\"only_admins\":true},{\"alias\":\"rcon_port\",\"desc\":\"Rcon \u043f\u043e\u0440\u0442\",\"default_value\":\"\",\"only_admins\":true},{\"alias\":\"identity\",\"desc\":\"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440\",\"default_value\":\"my_server_identity\",\"only_admins\":true},{\"alias\":\"worldsize\",\"desc\":\"\u0420\u0430\u0437\u043c\u0435\u0440 \u043c\u0438\u0440\u0430\",\"default_value\":\"4000\",\"only_admins\":false},{\"alias\":\"seed\",\"desc\":\"Seed\",\"default_value\":\"1234\",\"only_admins\":false},{\"alias\":\"level\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430\",\"default_value\":\"Procedural Map\",\"only_admins\":false}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "53",
		'game_code' => "rust_exp",
		'name' => "Oxide",
		'fast_rcon' => "",
		'aliases' => "[{\"alias\":\"rcon_password\",\"desc\":\"Rcon \u043f\u0430\u0440\u043e\u043b\u044c\",\"default_value\":\"\",\"only_admins\":false},{\"alias\":\"hostname\",\"desc\":\"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"default_value\":\"Empire-Host.org\",\"only_admins\":false},{\"alias\":\"saveinterval\",\"desc\":\"\u0418\u043d\u0442\u0435\u0440\u0432\u0430\u043b \u0441\u043e\u0445\u0440\u0430\u043d\u0435\u043d\u0438\u0439 (\u0441\u0435\u043a)\",\"default_value\":\"300\",\"only_admins\":false},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"default_value\":\"40\",\"only_admins\":true},{\"alias\":\"rcon_port\",\"desc\":\"Rcon \u043f\u043e\u0440\u0442\",\"default_value\":\"\",\"only_admins\":true},{\"alias\":\"identity\",\"desc\":\"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440\",\"default_value\":\"my_server_identity\",\"only_admins\":true},{\"alias\":\"worldsize\",\"desc\":\"\u0420\u0430\u0437\u043c\u0435\u0440 \u043c\u0438\u0440\u0430\",\"default_value\":\"4000\",\"only_admins\":false},{\"alias\":\"seed\",\"desc\":\"Seed\",\"default_value\":\"1234\",\"only_admins\":false},{\"alias\":\"secure\",\"desc\":\"\u0410\u043d\u0442\u0438\u0447\u0438\u0442 EAC\",\"default_value\":\"true\",\"only_admins\":false},{\"alias\":\"level\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430\",\"default_value\":\"Procedural Map\",\"only_admins\":false}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "54",
		'game_code' => "rust_exp",
		'name' => "RustEssentialsRedux",
		'fast_rcon' => "[{\"desc\":\"\u0421\u0442\u0430\u0442\u0443\u0441\",\"rcon_command\":\"status\"}]",
		'aliases' => "[{\"alias\":\"rcon_password\",\"desc\":\"Rcon \u043f\u0430\u0440\u043e\u043b\u044c\",\"default_value\":\"\",\"only_admins\":false},{\"alias\":\"hostname\",\"desc\":\"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435 \u0441\u0435\u0440\u0432\u0435\u0440\u0430\",\"default_value\":\"Empire-Host.org\",\"only_admins\":false},{\"alias\":\"saveinterval\",\"desc\":\"\u0418\u043d\u0442\u0435\u0440\u0432\u0430\u043b \u0441\u043e\u0445\u0440\u0430\u043d\u0435\u043d\u0438\u0439 (\u0441\u0435\u043a)\",\"default_value\":\"300\",\"only_admins\":false},{\"alias\":\"maxplayers\",\"desc\":\"\u041c\u0430\u043a\u0441\u0438\u043c\u0430\u043b\u044c\u043d\u043e\u0435 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e \u0438\u0433\u0440\u043e\u043a\u043e\u0432\",\"default_value\":\"40\",\"only_admins\":true},{\"alias\":\"rcon_port\",\"desc\":\"Rcon \u043f\u043e\u0440\u0442\",\"default_value\":\"\",\"only_admins\":true},{\"alias\":\"identity\",\"desc\":\"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440\",\"default_value\":\"my_server_identity\",\"only_admins\":true},{\"alias\":\"seed\",\"desc\":\"Seed\",\"default_value\":\"1234\",\"only_admins\":false},{\"alias\":\"worldsize\",\"desc\":\"\u0420\u0430\u0437\u043c\u0435\u0440 \u043c\u0438\u0440\u0430\",\"default_value\":\"4000\",\"only_admins\":false},{\"alias\":\"level\",\"desc\":\"\u041a\u0430\u0440\u0442\u0430\",\"default_value\":\"Procedural Map\",\"only_admins\":false}]",
		'disk_size' => "0",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
			
$data = array(
		'id' => "56",
		'game_code' => "minecraft",
		'name' => "CraftBukkit 1.8",
		'fast_rcon' => "",
		'aliases' => "",
		'disk_size' => "5000",
		'remote_repository' => "",
		'local_repository' => "",
		'kick_cmd' => "",
		'ban_cmd' => "",
		'chname_cmd' => "",
		'srestart_cmd' => "reload",
		'chmap_cmd' => "",
		'sendmsg_cmd' => "say {msg}",
		'passwd_cmd' => "",
		'game_types' => "",
);
$this->game_types->add_game_type($data);
