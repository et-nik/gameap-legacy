<?php

class Migration_install extends CI_Migration {

    public function up ()
    {
        $this->load->dbforge();

        // actions
        $this->dbforge->add_field([
            'id' => [
                'type' => 'TINYTEXT',
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'hash' => [
                'type' => 'VARCHAR',
                'constraint'  => 64,
                'default' => '',
            ],
            'data' => [
                'type' => 'MEDIUMTEXT',
                'default'   => '',
            ],
        ]);
        $this->dbforge->create_table('actions');

        // cron
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 16,
                'auto_increment' => TRUE
            ],
            'name' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => '',
            ],
            'command' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'server_id' => [
                'type' => 'INT',
            ],
            'user_id' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'started' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0,
            ],
            'date_perform' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'date_performed' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'time_add' => [
                'type' => 'INT',
                'default' => 0,
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('cron');

        // dedicated_servers
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => TRUE
            ],
            'name' => [
                'type' => 'TINYTEXT',
            ],
            'disabled' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0,
            ],
            'os' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'location' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'provider' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'ip' => [
                'type' => 'TEXT',
                'default' => '',
            ],
            'ram' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'cpu' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'work_path' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
                'default' => '',
            ],
            'steamcmd_path' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'gdaemon_host' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'gdaemon_login' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
                'default' => '',
            ],
            'gdaemon_password' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'gdaemon_privkey' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
                'default' => '',
            ],
            'gdaemon_pubkey' => [
                'type' => 'VARCHAR',
                'constraint' => 256,
                'default' => '',
            ],
            'gdaemon_keypass' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'script_start' => [
                'type'      => 'TEXT',
                'default'   => '',
            ],
            'script_stop' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'script_restart' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'script_status' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'script_get_console' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'script_send_command' => [
                'type' => 'TEXT',
                'default'   => '',
            ],
            'modules_data' => [
                'type' => 'MEDIUMTEXT',
                'default'   => '',
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('dedicated_servers');

        // ds_stats
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'ds_id' => [
                'type' => 'INT'
            ],
            'time' => [
                'type' => 'INT'
            ],
            'loa' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'ram' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'cpu' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'ifstat' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'ping' => [
                'type' => 'INT',
                'constraint' => 4
            ],
            'drvspace' => [
                'type' => 'TINYTEXT'
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('ds_stats');

        // ds_users
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'ds_id' => [
                'type' => 'INT'
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 32
            ],
            'uid' => [
                'type' => 'INT'
            ],
            'gid' => [
                'type' => 'INT'
            ],
            'password' => [
                'type' => 'TEXT'
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('ds_users');

        // games
        $this->dbforge->add_field([
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],
            'start_code' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
                'default' => '',
            ],
            'name' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'engine' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'engine_version' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => '1',
            ],
            'app_id' => [
                'type' => 'INT',
            ],
            'app_set_config' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'remote_repository' => [
                'type' => 'TEXT',
            ],
            'local_repository' => [
                'type' => 'TEXT',
            ],
        ]);
        $this->dbforge->add_key('code', TRUE);
        $this->dbforge->create_table('games');

        // game_types
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => TRUE
            ],
            'game_code' => [
                'type' => 'VARCHAR',
                'constraint' => 16,
            ],
            'name' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'fast_rcon' => [
                'type' => 'TEXT',
            ],
            'aliases' => [
                'type' => 'TEXT',
            ],
            'remote_repository' => [
                'type' => 'TEXT',
            ],
            'local_repository' => [
                'type' => 'TEXT',
            ],
            'kick_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'ban_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'chname_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'srestart_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'chmap_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'sendmsg_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'passwd_cmd' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('game_types');

        // gameap_tasks
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'run_aft_id' => [
                'type' => 'INT'
            ],
            'time_create' => [
                'type' => 'INT'
            ],
            'time_stchange' => [
                'type' => 'INT'
            ],
            'ds_id' => [
                'type' => 'INT'
            ],
            'server_id' => [
                'type' => 'INT'
            ],
            'task' => [
                'type' => 'VARCHAR',
                'constraint' => 8,
                'default' => '',
            ],
            'data' => [
                'type' => 'MEDIUMTEXT'
            ],
            'cmd' => [
                'type' => 'TEXT'
            ],
            'output' => [
                'type' => 'MEDIUMTEXT'
            ],
            'status' => [
                'type' => 'ENUM("waiting", "working", "error", "success")',
                'default' => 'waiting',
                'null' => false,
            ]
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('gdaemon_tasks');

        // logs
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => TRUE
            ],
            'date' => [
                'type' => 'INT',
            ],
            'type' => [
                'type' => 'TINYTEXT',
            ],
            'command' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => '',
            ],
            'user_name' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'server_id' => [
                'type' => 'INT',
            ],
            'ip' => [
                'type' => 'TINYTEXT',
            ],
            'msg' => [
                'type' => 'TINYTEXT',
            ],
            'log_data' => [
                'type' => 'MEDIUMTEXT',
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('logs');

        // modules
        $this->dbforge->add_field([
            'short_name' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'name' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'description' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'cron_script' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'version' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'update_info' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'show_in_menu' => [
                'type' => 'INT',
                'constraint' => 1,
            ],
            'access' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'developer' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
            'site' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'email' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'copyright' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'license' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
        ]);
        $this->dbforge->add_key('short_name', TRUE);
        $this->dbforge->create_table('modules');

        // servers
        $this->dbforge->add_field([
            'id' => [
                'type'          => 'INT',
                'auto_increment' => TRUE
            ],
            'screen_name' => [
                'type'          => 'VARCHAR',
                'constraint'    => 64,
                'default'       => '',
            ],
            'game' => [
                'type'          => 'VARCHAR',
                'constraint'    => 16,
                'default'       => '',
            ],
            'game_type' => [
                'type'          => 'INT',
            ],
            'name' => [
                'type'          => 'TINYTEXT',
                'default'       => '',
            ],
            'expires' => [
                'type'          => 'INT',
            ],
            'ds_id' => [
                'type'          => 'INT',
            ],
            'enabled' => [
                'type'          => 'INT',
                'constraint'    => 1,
                'default'       => 1,
            ],
            'installed' => [
                'type' => 'INT',
                'constraint' => 1,
            ],
            'server_ip' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'server_port' => [
                'type' => 'INT',
                'constraint' => 5,
            ],
            'query_port' => [
                'type' => 'INT',
                'constraint' => 5,
            ],
            'rcon_port' => [
                'type' => 'INT',
                'constraint' => 5,
            ],
            'rcon' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'maps_path' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'maps_list' => [
                'type' => 'TEXT',
            ],
            'dir' => [
                'type'  => 'TINYTEXT',
                'default' => '',
            ],
            'su_user' => [
                'type'          => 'VARCHAR',
                'constraint'    => 32,
                'default'       => '',
            ],
            'cpu_limit' => [
                'type' => 'INT'
            ],
            'ram_limit' => [
                'type' => 'INT'
            ],
            'net_limit' => [
                'type' => 'INT'
            ],
            'status' => [
                'type' => 'TEXT'
            ],
            'script_start' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'start_command' => [
                'type' => 'TEXT',
            ],
            'start_after_crash' => [
                'type' => 'INT',
                'constraint' => 1
            ],
            'process_active' => [
                'type' => 'INT',
                'constraint' => 1
            ],
            'last_process_check' => [
                'type' => 'INT'
            ],
            'aliases' => [
                'type' => 'TEXT',
            ],
            'modules_data' => [
                'type' => 'MEDIUMTEXT',
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('servers');

        // server_stats
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'server_id' => [
                'type' => 'INT'
            ],
            'time' => [
                'type' => 'INT'
            ],
            'ram' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'cpu' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'netstat' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'drvspace' => [
                'type' => 'TINYTEXT'
            ],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('servers_stats');

        // servers_privileges
        $this->dbforge->add_field([
            'user_id' => [
                'type'          => 'INT',
            ],
            'server_id' => [
                'type'          => 'INT',
            ],
            'privileges' => [
                'type' => 'TEXT',
            ],
        ]);
        $this->dbforge->create_table('servers_privileges');

        // settings
        $this->dbforge->add_field([
            'sett_id' => [
                'type' => 'VARCHAR',
                'constraint' => 32
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'server_id' => [
                'type' => 'INT',
            ],
            'value' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default' => '',
            ],
        ]);
        $this->dbforge->create_table('settings');

        // sessions
        $this->dbforge->add_field([
            'user_id' => [
                'type' => 'INT',
            ],
            'hash' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'default'   => '',
            ],
            'user_agent' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'expires' => [
                'type' => 'INT',
            ],
        ]);
        $this->dbforge->create_table('sessions');

        // users
        $this->dbforge->add_field([
            'id' => [
                'type'          => 'INT',
                'auto_increment' => TRUE
            ],
            'login' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'password' => [
                'type' => 'TEXT',
            ],
            'hash' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'is_admin' => [
                'type'          => 'INT',
                'default'       => 0,
            ],
            'group' => [
                'type' => 'INT'
            ],
            'recovery_code' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'confirm_code' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'action' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'balance' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'reg_date' => [
                'type'          => 'INT'
            ],
            'last_auth' => [
                'type'          => 'INT'
            ],
            'name' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'email' => [
                'type' => 'TINYTEXT',
            ],
            'privileges' => [
                'type' => 'TEXT',
            ],
            'modules_data' => [
                'type' => 'TINYTEXT',
                'default' => '',
            ],
            'filters' => [
                'type' => 'MEDIUMTEXT',
            ],
            'notices' => [
                'type' => 'MEDIUMTEXT',
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users');
    }

    //--------------------------------------------------------------------

    public function down ()
    {
        $this->dbforge->drop_table('actions', true);
        $this->dbforge->drop_table('cron', true);
        $this->dbforge->drop_table('dedicated_servers', true);
        $this->dbforge->drop_table('ds_stats', true);
        $this->dbforge->drop_table('ds_users', true);
        $this->dbforge->drop_table('games', true);
        $this->dbforge->drop_table('game_types', true);
        $this->dbforge->drop_table('gdaemon_tasks', true);
        $this->dbforge->drop_table('logs', true);
        $this->dbforge->drop_table('modules', true);
        $this->dbforge->drop_table('servers', true);
        $this->dbforge->drop_table('servers_stats', true);
        $this->dbforge->drop_table('servers_privileges', true);
        $this->dbforge->drop_table('settings', true);
        $this->dbforge->drop_table('sessions', true);
        $this->dbforge->drop_table('users', true);
    }
}