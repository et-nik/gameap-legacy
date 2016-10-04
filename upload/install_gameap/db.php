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

// Delete tables, if exists
$this->dbforge->drop_table('actions', true);
$this->dbforge->drop_table('cron' true);
$this->dbforge->drop_table('dedicated_servers' true);
$this->dbforge->drop_table('ds_stats' true);
$this->dbforge->drop_table('ds_users' true);
$this->dbforge->drop_table('games' true);
$this->dbforge->drop_table('game_types' true);
$this->dbforge->drop_table('gdaemon_tasks' true);
$this->dbforge->drop_table('logs' true);
$this->dbforge->drop_table('modules' true);
$this->dbforge->drop_table('servers' true);
$this->dbforge->drop_table('servers_privileges' true);
$this->dbforge->drop_table('settings' true);
$this->dbforge->drop_table('sessions' true);
$this->dbforge->drop_table('users' true);

/*----------------------------------*/
/*              actions             */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->create_table('actions');

/*----------------------------------*/
/*              cron                */
/*----------------------------------*/

$this->dbforge->add_field($fields = array(
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
););
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('cron');

/*----------------------------------*/
/*      dedicated_servers           */
/*----------------------------------*/

$this->dbforge->add_field(array(
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

    'work_path' => array(
        'type' => 'VARCHAR',
        'constraint' => 256
    ),
    
    'steamcmd_path' => array(
        'type' => 'TINYTEXT',
    ),

    'gdaemon_host' => array(
        'type' => 'TINYTEXT',
    ),

    'gdaemon_login' => array(
        'type' => 'VARCHAR',
        'constraint' => 128
    ),

    'gdaemon_password' => array(
        'type' => 'TEXT'
    ),

    'gdaemon_privkey' => array(
        'type' => 'VARCHAR',
        'constraint' => 256
    ),

    'gdaemon_pubkey' => array(
        'type' => 'VARCHAR',
        'constraint' => 256
    ),

    'gdaemon_keypass' => array(
        'type' => 'TEXT'
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
));
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('dedicated_servers');

/*----------------------------------*/
/*              ds_stats            */
/*----------------------------------*/

$this->dbforge->add_field(array(
    'id' => array(
        'type' => 'INT',
        'auto_increment' => true
    ),
    'ds_id' => array(
        'type' => 'INT'
    ),
    'time' => array(
        'type' => 'INT'
    ),
    'loa' => array(
        'type' => 'TINYTEXT'
    ),
    'ram' => array(
        'type' => 'TINYTEXT'
    ),
    'cpu' => array(
        'type' => 'TINYTEXT'
    ),
    'ifstat' => array(
        'type' => 'TINYTEXT'
    ),
    'ping' => array(
        'type' => 'INT',
        'constraint' => 4
    ),
    'drvspace' => array(
        'type' => 'TINYTEXT'
    ),
));

$this->dbforge->add_key('id', true);
$this->dbforge->create_table('ds_stats');
            
/*----------------------------------*/
/*              ds_users            */
/*----------------------------------*/

$this->dbforge->add_field(array(
    'id' => array(
        'type' => 'INT',
        'auto_increment' => true
    ),
    'ds_id' => array(
        'type' => 'INT'
    ),
    'username' => array(
        'type' => 'VARCHAR',
        'constraint' => 32
    ),
    'uid' => array(
        'type' => 'INT'
    ),
    'gid' => array(
        'type' => 'INT'
    ),
    'password' => array(
        'type' => 'TEXT'
    ),
));

$this->dbforge->add_key('id', true);
$this->dbforge->create_table('ds_users');
            
/*----------------------------------*/
/*              games               */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->add_key('code', TRUE);
$this->dbforge->create_table('games');

/*----------------------------------*/
/*              game_types          */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
    )
));
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('game_types');

/*----------------------------------*/
/*       gdaemon_tasks              */
/*----------------------------------*/

$this->dbforge->add_field(array(
    'id' => array(
        'type' => 'INT',
        'auto_increment' => true
    ),

    'run_aft_id' => array(
        'type' => 'INT'
    ),
    
    'time_create' => array(
        'type' => 'INT'
    ),
    
    'time_stchange' => array(
        'type' => 'INT'
    ),
    
    'ds_id' => array(
        'type' => 'INT'
    ),
    
    'server_id' => array(
        'type' => 'INT'
    ),

    'task' => array(
        'type' => 'VARCHAR',
        'constraint' => 8
    ),

    'data' => array(
        'type' => 'MEDIUMTEXT'
    ),
    
    'cmd' => array(
        'type' => 'TEXT'
    ),
    
    'output' => array(
        'type' => 'MEDIUMTEXT'
    ),
    
    'status' => array(
        'type' => 'ENUM("waiting", "working", "error", "success")',
        'default' => 'waiting',
        'null' => false,
    )
));
$this->dbforge->add_key('id', true);
$this->dbforge->create_table('gdaemon_tasks');

/*----------------------------------*/
/*              logs                */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
    

));
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('logs');

/*----------------------------------*/
/*              modules             */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->add_key('short_name', TRUE);
$this->dbforge->create_table('modules');

/*----------------------------------*/
/*              servers             */
/*----------------------------------*/

$this->dbforge->add_field(array(
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

    'start_after_crash' => array(
        'type' => 'INT',
        'constraint' => 1
    ),

    'process_active' => array(
        'type' => 'INT',
        'constraint' => 1
    ),
    
    'last_process_check' => array(
        'type' => 'INT'
    ),
    
    'aliases' => array(
        'type' => 'TEXT',
    ),
    
    'modules_data' => array(
        'type' => 'MEDIUMTEXT',
    ),  
));
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('servers');

/*----------------------------------*/
/*      servers_privileges          */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->create_table('servers_privileges');

/*----------------------------------*/
/*              settings            */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->create_table('settings');

/*----------------------------------*/
/*              sessoins            */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->create_table('sessions');
            
/*----------------------------------*/
/*              users               */
/*----------------------------------*/

$this->dbforge->add_field(array(
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
));
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('users');
