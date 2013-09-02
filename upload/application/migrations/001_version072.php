<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version072 extends CI_Migration {

	public function up() {

		$this->load->dbforge();
		
		// Servers
		
		$this->dbforge->drop_column('servers', 'script_start');
		$this->dbforge->drop_column('servers', 'script_stop');
		$this->dbforge->drop_column('servers', 'script_restart');
		$this->dbforge->drop_column('servers', 'script_status');
		$this->dbforge->drop_column('servers', 'script_update');
		$this->dbforge->drop_column('servers', 'script_get_console');
		
		$fields = array(
						'start_command' => array(
												 'type' => 'TEXT',
												 'default' => '',
										  ),
		);
		
		$this->dbforge->add_column('servers', $fields, 'su_user');
		
		// Game Types
		$fields = array(
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
		);
		
		$this->dbforge->add_column('game_types', $fields, 'script_get_console');
	

	}
	
	public function down() {

	}
	
}
