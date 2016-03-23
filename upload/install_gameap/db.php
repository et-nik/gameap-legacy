<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package     Game AdminPanel
 * @author      Nikita Kuznetsov (ET-NiK)
 * @copyright   Copyright (c) 2013-2016, Nikita Kuznetsov (http://hldm.org)
 * @license     http://gameap.ru/license.html
 * @link        http://gameap.ru
 * @filesource  
 */

/**
 * Структура базы данных для мастера установки
 *
 * @package     Game AdminPanel
 * @category    Controllers
 * @category    Controllers
 * @author      Nikita Kuznetsov (ET-NiK)
 * @sinse       0.7
 */

$this->load->dbforge();

// Удаление таблиц, если есть
$this->dbforge->drop_table('actions');
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
$this->dbforge->drop_table('sessions');
$this->dbforge->drop_table('users');

/*----------------------------------*/
/*              actions             */
/*----------------------------------*/

$fields = array(
    'id' => array(
        'type' => 'TINYTEXT',
    ),
    
    'action' => array(
        'type' => 'VARCHAR',
        'constraint' => 64, 
    ),
    
    'data' => array(
        'type' => 'MEDIUMTEXT',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->create_table('actions');

/*----------------------------------*/
/*              captcha             */
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
/*              cron                */
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
        'type' => 'VARCHAR',
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
/*      dedicated_servers           */
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
    
    'disabled' => array(
        'type' => 'INT', 
        'constraint' => 1,
    ),
    
    'os' => array(
        'type' => 'TINYTEXT',
    ),
    
    'control_protocol' => array(
        'type' => 'VARCHAR',
        'constraint' => 8,
    ),
    
    'location' => array(
        'type' => 'TINYTEXT',
    ),
    
    'provider' => array(
        'type' => 'TINYTEXT',
    ),
    
    'ip' => array(
        'type' => 'TEXT',
    ),
    
    'ram' => array(
        'type' => 'TINYTEXT',
    ),
    
    'cpu' => array(
        'type' => 'TINYTEXT',
    ),
    
    'stats' => array(
        'type' => 'TEXT',
    ),
    
    'steamcmd_path' => array(
        'type' => 'TINYTEXT',
    ),
    
    'gdaemon_host' => array(
        'type' => 'TINYTEXT',
    ),
    
    'gdaemon_key' => array(
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
    
    'script_start' => array(
        'type' => 'TEXT',
    ),
    
    'script_stop' => array(
        'type' => 'TEXT',
    ),
    
    'script_restart' => array(
        'type' => 'TEXT',
    ),
    
    'script_status' => array(
        'type' => 'TEXT',
    ),

    'script_get_console' => array(
        'type' => 'TEXT',
    ),
    
    'script_send_command' => array(
        'type' => 'TEXT',
    ),  
    
    'modules_data' => array(
        'type' => 'MEDIUMTEXT',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('dedicated_servers');

/*----------------------------------*/
/*              games               */
/*----------------------------------*/

$fields = array(
    'code' => array(
        'type' => 'VARCHAR',
        'constraint' => 16, 
    ),

    'start_code' => array(
        'type' => 'VARCHAR',
        'constraint' => 16, 
    ),

    'name' => array(
        'type' => 'TINYTEXT',
    ),

    'engine' => array(
        'type' => 'TINYTEXT',
    ),

    'engine_version' => array(
        'type' => 'VARCHAR',
        'constraint' => 32,
        'default' => '1',
    ),

    'app_id' => array(
        'type' => 'INT',
        'constraint' => 16,
    ),

    'app_set_config' => array(
        'type' => 'VARCHAR',
        'constraint' => 64,
        'default' => '',
    ),

    'remote_repository' => array(
        'type' => 'TEXT',
    ),

    'local_repository' => array(
        'type' => 'TEXT',
    ),
);

$this->dbforge->add_key('code', TRUE);
$this->dbforge->add_field($fields);
$this->dbforge->create_table('games');

/*----------------------------------*/
/*              game_types          */
/*----------------------------------*/

$fields = array(
    'id' => array(
        'type' => 'INT',
        'constraint' => 16, 
        'auto_increment' => TRUE
    ),

    'game_code' => array(
        'type' => 'VARCHAR',
        'constraint' => 16, 
    ),

    'name' => array(
        'type' => 'TINYTEXT',
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

    'remote_repository' => array(
        'type' => 'TEXT',
    ),

    'local_repository' => array(
        'type' => 'TEXT',
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
/*              logs                */
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
        'type' => 'VARCHAR',
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
        'type' => 'MEDIUMTEXT',
    ),
    

);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('logs');

/*----------------------------------*/
/*              modules             */
/*----------------------------------*/

$fields = array(
    'short_name' => array(
        'type' => 'VARCHAR',
        'constraint' => 32, 
    ),
    
    'name' => array(
        'type' => 'TINYTEXT',
    ),
    
    'description' => array(
        'type' => 'TINYTEXT',
    ),
    
    'cron_script' => array(
        'type' => 'TINYTEXT',
    ),
    
    'version' => array(
        'type' => 'VARCHAR',
        'constraint' => 64, 
    ),
    
    'update_info' => array(
        'type' => 'TINYTEXT',
    ),  
    
    'show_in_menu' => array(
        'type' => 'INT',
        'constraint' => 1, 
    ),
    
    'access' => array(
        'type' => 'TINYTEXT', 
    ),
    
    'developer' => array(
        'type' => 'VARCHAR',
        'constraint' => 64, 
    ),
    
    'site' => array(
        'type' => 'TINYTEXT',
    ),
    
    'email' => array(
        'type' => 'TINYTEXT',
    ),
    
    'copyright' => array(
        'type' => 'TINYTEXT',
    ),
    
    'license' => array(
        'type' => 'TINYTEXT',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('short_name', TRUE);
$this->dbforge->create_table('modules');

/*----------------------------------*/
/*              servers             */
/*----------------------------------*/

$fields = array(
    'id' => array(
        'type'          => 'INT',
        'constraint'    => 16, 
        'auto_increment' => TRUE
    ),
    
    'screen_name' => array(
        'type'          => 'VARCHAR',
        'constraint'    => 64,
        'default'       => '',
    ),
    
    'game' => array(
        'type'          => 'VARCHAR',
        'constraint'    => 16, 
    ),
    
    'game_type' => array(
        'type'          => 'INT',
        'constraint'    => 16, 
    ),
    
    'name' => array(
        'type'          => 'TINYTEXT',
    ),
    
    'expires' => array(
        'type'          => 'INT',
        'constraint'    => 32, 
    ),
    
    'ds_id' => array(
        'type'          => 'INT',
        'constraint'    => 16, 
    ),
    
    'enabled' => array(
        'type'          => 'INT',
        'constraint'    => 1,
        'default'       => 1,
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
    
    'query_port' => array(
        'type' => 'INT',
        'constraint' => 5,
    ),
    
    'rcon_port' => array(
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
        'type'  => 'TINYTEXT',
    ),
    
    'su_user' => array(
        'type'          => 'VARCHAR',
        'constraint'    => 32,
        'default'       => '',
    ),
    
    'cpu_limit' => array(
        'type' => 'INT'
    ),
    
    'ram_limit' => array(
        'type' => 'INT'
    ),
    
    'net_limit' => array(
        'type' => 'INT'
    ),
    
    'status' => array(
        'type' => 'TEXT'
    ),
    
    'script_start' => array(
        'type' => 'TINYTEXT',
    ),
    
    'start_command' => array(
        'type' => 'TEXT',
    ),
    
    'aliases' => array(
        'type' => 'TEXT',
    ),
    
    'modules_data' => array(
        'type' => 'MEDIUMTEXT',
    ),  
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('servers');

/*----------------------------------*/
/*      servers_privileges          */
/*----------------------------------*/

$fields = array(
    'user_id' => array(
        'type'          => 'INT',
        'constraint'    => 16, 
    ),
    
    'server_id' => array(
        'type'          => 'INT',
        'constraint'    => 16, 
    ),
    
    'privileges' => array(
        'type' => 'TEXT',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->create_table('servers_privileges');

/*----------------------------------*/
/*              settings            */
/*----------------------------------*/

$fields = array(
    'sett_id' => array(
        'type' => 'VARCHAR',
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
        'type' => 'VARCHAR',
        'constraint' => 64, 
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->create_table('settings');

/*----------------------------------*/
/*              sessoins            */
/*----------------------------------*/

$fields = array(
    'user_id' => array(
        'type' => 'INT',
    ),
    
    'hash' => array(
        'type' => 'TINYTEXT',
    ),
    
    'ip_address' => array(
        'type' => 'VARCHAR',
        'constraint' => 64, 
    ),
    
    'user_agent' => array(
        'type' => 'TINYTEXT',
    ),
    
    'expires' => array(
        'type' => 'INT',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->create_table('sessions');
            
/*----------------------------------*/
/*              users               */
/*----------------------------------*/

$fields = array(
    'id' => array(
        'type'          => 'INT',
        'constraint'    => 16, 
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
        'type'          => 'INT',
        'constraint'    => 16,
        'default'       => 0,
    ),

    'group' => array(
        'type' => 'INT'
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
        'type'          => 'VARCHAR',
        'constraint'    =>  32,
    ),

    'last_auth' => array(
        'type'          => 'INT',
        'constraint'    => 32,
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

    'modules_data' => array(
        'type' => 'TINYTEXT',
    ),

    'filters' => array(
        'type' => 'MEDIUMTEXT',
    ),

    'notices' => array(
        'type' => 'MEDIUMTEXT',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('users');
