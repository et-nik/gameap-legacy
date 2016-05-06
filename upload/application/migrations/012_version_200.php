<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version_200 extends CI_Migration {
    public function up() 
	{
        $this->load->dbforge();

        if ($this->db->field_exists('control_protocol', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'control_protocol');
        
        if ($this->db->field_exists('ssh_host', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ssh_host');
        if ($this->db->field_exists('ssh_login', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ssh_login');
        if ($this->db->field_exists('ssh_password', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ssh_password');
        if ($this->db->field_exists('ssh_path', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ssh_path');
        
        if ($this->db->field_exists('telnet_host', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'telnet_host');
        if ($this->db->field_exists('telnet_login', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'telnet_login');
        if ($this->db->field_exists('telnet_password', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'telnet_password');
        if ($this->db->field_exists('telnet_path', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'telnet_path');
        
        if ($this->db->field_exists('ftp_host', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ftp_host');
        if ($this->db->field_exists('ftp_login', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ftp_login');
        if ($this->db->field_exists('ftp_password', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ftp_password');
        if ($this->db->field_exists('ftp_path', 'dedicated_servers')) $this->dbforge->drop_column('dedicated_servers', 'ftp_path');

        if (!$this->db->table_exists('gdaemon_tasks')) {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'auto_increment' => true
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
        }

        if (!$this->db->table_exists('ds_users')) {
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
        }

        if ($this->db->field_exists('disk_size', 'game_types')) $this->dbforge->drop_column('game_types', 'disk_size');
        if ($this->db->field_exists('game_types', 'game_types')) $this->dbforge->drop_column('game_types', 'game_types');

        $this->dbforge->drop_table('captcha');

        $fields = array();
		if (!$this->db->field_exists('gdaemon_login', 'dedicated_servers')) {
			$fields = array(
				'gdaemon_login' => array('type' => 'TEXT')
			);
			
			$this->dbforge->add_column('dedicated_servers', $fields, 'gdaemon_key');
		}

        $fields = array();
		if (!$this->db->field_exists('gdaemon_password', 'dedicated_servers')) {
			$fields = array(
				'gdaemon_password' => array('type' => 'TEXT')
			);
			
			$this->dbforge->add_column('dedicated_servers', $fields, 'gdaemon_login');
		}
    }

    public function down() 
	{
		$this->load->dbforge();
	}
}
