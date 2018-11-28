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
        'default' => '',
    ),

    'action' => array(
        'type' => 'VARCHAR',
        'constraint' => 64,
        'default' => '',
    ),

    'data' => array(
        'type' => 'MEDIUMTEXT',
        'null' => true,
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
        'default' => '',
    ),

    'word' => array(
        'type' => 'VARCHAR',
        'constraint' => 64,
        'default' => '',
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
        'null' => true,
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
        'null' => true,
    ),

    'provider' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ip' => array(
        'type' => 'TEXT',
    ),

    'ram' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'cpu' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'stats' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'steamcmd_path' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'gdaemon_host' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'gdaemon_key' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ssh_host' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ssh_login' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ssh_password' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ssh_path' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'telnet_host' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'telnet_login' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'telnet_password' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'telnet_path' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ftp_host' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ftp_login' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ftp_password' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'ftp_path' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'script_start' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'script_stop' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'script_restart' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'script_status' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'script_get_console' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'script_send_command' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'modules_data' => array(
        'type' => 'MEDIUMTEXT',
        'default' => '',
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
        'null' => true,
    ),

    'app_set_config' => array(
        'type' => 'VARCHAR',
        'constraint' => 64,
        'default' => '',
    ),

    'remote_repository' => array(
        'type' => 'TEXT',
        'null' => true,
    ),

    'local_repository' => array(
        'type' => 'TEXT',
        'null' => true,
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
        'default' => '',
    ),

    'aliases' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'disk_size' => array(
        'type' => 'INT',
        'constraint' => 16,
        'null' => true,
    ),

    'remote_repository' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'local_repository' => array(
        'type' => 'TEXT',
        'default' => '',
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
        'default' => '',
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
        'default' => '',
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
        'default' => '',
    ),

    'msg' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'log_data' => array(
        'type' => 'MEDIUMTEXT',
        'default' => '',
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
        'default' => '',
    ),

    'cron_script' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'version' => array(
        'type' => 'VARCHAR',
        'constraint' => 64,
        'default' => '',
    ),

    'update_info' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'show_in_menu' => array(
        'type' => 'INT',
        'constraint' => 1,
        'default' => 0,
    ),

    'access' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'developer' => array(
        'type' => 'VARCHAR',
        'constraint' => 64,
        'default' => '',
    ),

    'site' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'email' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'copyright' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'license' => array(
        'type' => 'TINYTEXT',
        'default' => '',
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
        'default'       => 0,
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
        'default' => 0,
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
        'default' => '',
    ),

    'maps_path' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'maps_list' => array(
        'type' => 'TEXT',
        'default' => '',
    ),

    'dir' => array(
        'type'  => 'TINYTEXT',
        'default' => '',
    ),

    'su_user' => array(
        'type'          => 'VARCHAR',
        'constraint'    => 32,
        'default'       => '',
    ),

    'cpu_limit' => array(
        'type' => 'INT',
        'default' => 0,
    ),

    'ram_limit' => array(
        'type' => 'INT',
        'default' => 0,
    ),

    'net_limit' => array(
        'type' => 'INT',
        'default' => 0,
    ),

    'status' => array(
        'type' => 'TEXT',
        'default' => 0,
    ),

    'script_start' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'start_command' => array(
        'type' => 'TEXT',
        'null' => true,
    ),

    'aliases' => array(
        'type' => 'TEXT',
        'null' => true,
    ),

    'modules_data' => array(
        'type' => 'MEDIUMTEXT',
        'null' => true,
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
        'default' => '',
    ),

    'is_admin' => array(
        'type'          => 'INT',
        'constraint'    => 16,
        'default'       => 0,
    ),

    'group' => array(
        'type' => 'INT',
        'null' => true,
    ),

    'recovery_code' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'confirm_code' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'action' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'balance' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'reg_date' => array(
        'type'          => 'VARCHAR',
        'constraint'    =>  32,
    ),

    'last_auth' => array(
        'type'          => 'INT',
        'constraint'    => 32,
        'null' => true,
    ),

    'name' => array(
        'type' => 'TINYTEXT',
        'null' => true,
    ),

    'email' => array(
        'type' => 'TINYTEXT',
    ),

    'privileges' => array(
        'type' => 'TEXT',
    ),

    'modules_data' => array(
        'type' => 'TINYTEXT',
        'default' => '',
    ),

    'filters' => array(
        'type' => 'MEDIUMTEXT',
        'default' => '',
    ),

    'notices' => array(
        'type' => 'MEDIUMTEXT',
        'default' => '',
    ),
);

$this->dbforge->add_field($fields);
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('users');
