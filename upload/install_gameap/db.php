<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource	
 */

/**
 * Структура базы данных для мастера установки
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.7
 */

$this->load->dbforge();

// Удаление таблиц, если есть
$this->dbforge->drop_table('captcha');
$this->dbforge->drop_table('cron');
$this->dbforge->drop_table('dedicated_servers');
$this->dbforge->drop_table('games');
$this->dbforge->drop_table('game_types');
$this->dbforge->drop_table('logs');
$this->dbforge->drop_table('modules');
$this->dbforge->drop_table('servers');
$this->dbforge->drop_table('servers_privileges');
$this->dbforge->drop_table('settings');
$this->dbforge->drop_table('users');

/*----------------------------------*/
/* 				captcha				*/
/*----------------------------------*/
$fields = array(
		'captcha_id' => array(
							'type' => 'INT',
							'constraint' => 16, 
							'auto_increment' => TRUE
		),
		
		'captcha_time' => array(
							'type' => 'INT',
							'constraint' => 32, 
		),
		
		'ip_address' => array(
							'type' => 'VARCHAR',
							'constraint' => 64, 
		),
		
		'word' => array(
							'type' => 'VARCHAR',
							'constraint' => 64, 
		),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('captcha_id', TRUE);
$this->dbforge->create_table('captcha');

/*----------------------------------*/
/* 				cron				*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' => 'INT',
							'constraint' => 16, 
							'auto_increment' => TRUE
		),
		
		'name' => array(
							'type' => 'TINYTEXT',
		),
		
		'code' => array(
							'type' => 'CHAR',
							'constraint' => 32,
		),
		
		'command' => array(
							'type' => 'TINYTEXT',
		),
		
		'server_id' => array(
							'type' => 'INT',
							'constraint' => 16,
		),
		
		'user_id' => array(
							'type' => 'INT',
							'constraint' => 16,
		),
		
		'started' => array(
							'type' => 'INT',
							'constraint' => 1,
		),
		
		'date_perform' => array(
							'type' => 'INT',
							'constraint' => 32, 
		),
		
		'date_performed' => array(
							'type' => 'INT',
							'constraint' => 32, 
		),
		
		'time_add' => array(
							'type' => 'INT',
							'constraint' => 32, 
		),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('cron');

/*----------------------------------*/
/* 		dedicated_servers			*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' => 'INT',
							'constraint' => 16, 
							'auto_increment' => TRUE
		),
		
		'name' => array(
							'type' => 'TINYTEXT',
		),
		
		'os' => array(
							'type' => 'TINYTEXT',
		),
		
		'control_protocol' => array(
							'type' => 'CHAR',
							'constraint' => 8,
		),
		
		'location' => array(
							'type' => 'TINYTEXT',
		),
		
		'provider' => array(
							'type' => 'TINYTEXT',
		),
		
		'ip' => array(
							'type' => 'TINYTEXT',
		),
		
		'ram' => array(
							'type' => 'TINYTEXT',
		),
		
		'cpu' => array(
							'type' => 'TINYTEXT',
		),
		
		'steamcmd_path' => array(
							'type' => 'TINYTEXT',
		),
		
		'ssh_host' => array(
							'type' => 'TINYTEXT',
		),
		
		'ssh_login' => array(
							'type' => 'TINYTEXT',
		),
		
		'ssh_password' => array(
							'type' => 'TINYTEXT',
		),
		
		'ssh_path' => array(
							'type' => 'TINYTEXT',
		),
		
		'telnet_host' => array(
							'type' => 'TINYTEXT',
		),
		
		'telnet_login' => array(
							'type' => 'TINYTEXT',
		),
		
		'telnet_password' => array(
							'type' => 'TINYTEXT',
		),
		
		'telnet_path' => array(
							'type' => 'TINYTEXT',
		),
		
		'ftp_host' => array(
							'type' => 'TINYTEXT',
		),
		
		'ftp_login' => array(
							'type' => 'TINYTEXT',
		),
		
		'ftp_password' => array(
							'type' => 'TINYTEXT',
		),
		
		'ftp_path' => array(
							'type' => 'TINYTEXT',
		),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('dedicated_servers');

/*----------------------------------*/
/* 				games				*/
/*----------------------------------*/

$fields = array(
		'code' => array(
							'type' => 'CHAR',
							'constraint' => 16, 
		),
		
		'start_code' => array(
							'type' => 'CHAR',
							'constraint' => 16, 
		),

		'name' => array(
							'type' => 'TINYTEXT',
		),
		
		'engine' => array(
							'type' => 'TINYTEXT',
		),
		
		'engine_version' => array(
							'type' => 'CHAR',
							'constraint' => 32,
							'default' => '1',
		),
		
		'app_id' => array(
							'type' => 'INT',
							'constraint' => 16,
		),
		
		'app_set_config' => array(
							'type' => 'CHAR',
							'constraint' => 64,
							'default' => '',
		),
);

$this->dbforge->add_key('code', TRUE);
$this->dbforge->add_field($fields);
$this->dbforge->create_table('games');

/*----------------------------------*/
/* 				game_types			*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' => 'INT',
							'constraint' => 16, 
							'auto_increment' => TRUE
		),
		
		'game_code' => array(
							'type' => 'CHAR',
							'constraint' => 16, 
		),
		
		'name' => array(
							'type' => 'TINYTEXT',
		),
		
		'config_files' => array(
							'type' => 'TEXT',
		),
		
		'content_dirs' => array(
							'type' => 'TEXT',
		),
		
		'log_dirs' => array(
							'type' => 'TEXT',
		),
		
		'fast_rcon' => array(
							'type' => 'TEXT',
		),
		
		'aliases' => array(
							'type' => 'TEXT',
		),
		
		'disk_size' => array(
							'type' => 'INT',
							'constraint' => 16, 
		),
		
		'execfile_windows' => array(
							'type' => 'CHAR',
							'constraint' => 32,
		),
		
		'execfile_linux' => array(
							'type' => 'CHAR',
							'constraint' => 32,
		),
		
		'script_start' => array(
							'type' => 'TINYTEXT',
		),
		
		'script_stop' => array(
							'type' => 'TINYTEXT',
		),
		
		'script_restart' => array(
							'type' => 'TINYTEXT',
		),
		
		'script_status' => array(
							'type' => 'TINYTEXT',
		),
		
		'script_update' => array(
							'type' => 'TINYTEXT',
		),
		
		'script_get_console' => array(
							'type' => 'TINYTEXT',
		),
		
		'kick_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
						  
		'ban_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
						  
		'chname_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
		
		'srestart_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
						  
		'chmap_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
						 
		'sendmsg_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
						  
		'passwd_cmd' => array(
							'type' => 'VARCHAR',
							'constraint' => 64,
							'default' => '',
		),
		
		'game_types' => array(
							'type' => 'TINYTEXT',
		),

);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('game_types');

/*----------------------------------*/
/* 				logs				*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' => 'INT',
							'constraint' => 16, 
							'auto_increment' => TRUE
		),
		
		'date' => array(
							'type' => 'INT',
							'constraint' => 32, 
		),
		
		'type' => array(
							'type' => 'TINYTEXT',
		),
		
		'command' => array(
							'type' => 'CHAR',
							'constraint' => 32, 
		),
		
		'user_name' => array(
							'type' => 'TINYTEXT',
		),
		
		'server_id' => array(
							'type' => 'INT',
							'constraint' => 32, 
		),
		
		'ip' => array(
							'type' => 'TINYTEXT',
		),
		
		'msg' => array(
							'type' => 'TINYTEXT',
		),
		
		'log_data' => array(
							'type' => 'TEXT',
		),


);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('logs');

/*----------------------------------*/
/* 				modules				*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' => 'INT',
							'constraint' => 16, 
							'auto_increment' => TRUE
		),
		
		'name' => array(
							'type' => 'CHAR',
							'constraint' => 32, 
		),

		'file' => array(
							'type' => 'TINYTEXT',
		),
		
		'enabled' => array(
							'type' => 'INT',
							'constraint' => 1, 
							'default'	=> 1,
		),
		
		'version' => array(
							'type' => 'CHAR',
							'constraint' => 64, 
		),
		
		'developer' => array(
							'type' => 'CHAR',
							'constraint' => 64, 
		),
		
		'site' => array(
							'type' => 'TINYTEXT',
		),
		
		'information' => array(
							'type' => 'TINYTEXT',
		),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('modules');

/*----------------------------------*/
/* 				servers				*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16, 
							'auto_increment' => TRUE
		),
		
		'screen_name' => array(
							'type' 			=> 'CHAR',
							'constraint' 	=> 64,
							'default'		=> '',
		),
		
		'game' => array(
							'type' 			=> 'CHAR',
							'constraint' 	=> 16, 
		),
		
		'game_type' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16, 
		),
		
		'name' => array(
							'type' 			=> 'TINYTEXT',
		),
		
		'expires' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 32, 
		),
		
		'ds_id' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16, 
		),
		
		'enabled' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 1,
							'default'		=> 1,
		),
		
		'installed' => array(
							'type' => 'INT',
							'constraint' => 1,
		),
		
		'server_ip' => array(
							'type' => 'TINYTEXT',
		),
		
		'server_port' => array(
							'type' => 'INT',
							'constraint' => 5, 
		),
		
		'rcon' => array(
							'type' => 'TINYTEXT',
		),
		
		'maps_path' => array(
							'type' => 'TINYTEXT',
		),
		
		'maps_list' => array(
							'type' => 'TEXT',
		),
		
		'dir' => array(
							'type' 	=> 'TINYTEXT',
		),
		
		'su_user' => array(
							'type' 			=> 'CHAR',
							'constraint'	=> 32,
							'default'		=> '',
		),
		
		'script_start' => array(
							'type' => 'TINYTEXT',
		),
		
		'start_command' => array(
								 'type' => 'TEXT',
								 'default' => '',
						  ),
		
		'aliases' => array(
							'type' => 'TEXT',
		),


);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('servers');

/*----------------------------------*/
/* 		servers_privileges			*/
/*----------------------------------*/

$fields = array(
		'user_id' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16, 
		),
		
		'server_id' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16, 
		),
		
		'privilege_name' => array(
							'type' 			=> 'CHAR',
							'constraint' 	=> 32, 
		),
		
		'privilege_value' => array(
							'type' 			=> 'CHAR',
							'constraint' 	=> 3, 
		),
		

);

$this->dbforge->add_field($fields);
$this->dbforge->create_table('servers_privileges');

/*----------------------------------*/
/* 				settings			*/
/*----------------------------------*/

$fields = array(
		'sett_id' => array(
							'type' => 'CHAR',
							'constraint' => 32, 
		),
		
		'user_id' => array(
							'type' => 'INT',
							'constraint' => 16, 
		),
		
		'server_id' => array(
							'type' => 'INT',
							'constraint' => 16, 
		),
		
		'value' => array(
							'type' => 'CHAR',
							'constraint' => 64, 
		),
);

$this->dbforge->add_field($fields);
$this->dbforge->create_table('settings');

/*----------------------------------*/
/* 				users				*/
/*----------------------------------*/

$fields = array(
		'id' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16, 
							'auto_increment' => TRUE
		),
		
		'login' => array(
							'type' => 'TINYTEXT',
		),
		
		'password' => array(
							'type' => 'TEXT',
		),
		
		'hash' => array(
							'type' => 'TINYTEXT',
		),
		
		'is_admin' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 16,
							'default'		=> 0,
		),
		
		'recovery_code' => array(
							'type' => 'TINYTEXT',
		),
		
		'confirm_code' => array(
							'type' => 'TINYTEXT',
		),
		
		'action' => array(
							'type' => 'TINYTEXT',
		),
		
		'balance' => array(
							'type' => 'TINYTEXT',
		),
		
		'reg_date' => array(
							'type' 			=> 'CHAR',
							'constraint' 	=> 	32,
		),
		
		'last_auth' => array(
							'type' 			=> 'INT',
							'constraint' 	=> 32,
		),
		
		'name' => array(
							'type' => 'TINYTEXT',
		),
		
		'email' => array(
							'type' => 'TINYTEXT',
		),
		
		'privileges' => array(
							'type' => 'TEXT',
		),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('users');
