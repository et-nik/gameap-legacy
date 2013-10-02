<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version08release extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		/* Update for SERVERS table */
		$fields = array(
						'rcon_port' => array(
												 'type' => 'INT',
												 'constraint' => 5,
										  ),
										  
						'query_port' => array(
												 'type' => 'INT',
												 'constraint' => 5, 
										  ),			
		);
		
		$this->dbforge->add_column('servers', $fields, 'server_port');
		
		/* DEDICATED SERVERS */
		
		$fields = array(
						'stats' => array(
												 'type' => 'TEXT',
												 'default' => '',
										  ),
										  		
		);
		
		$this->dbforge->add_column('dedicated_servers', $fields, 'cpu');
		
		/* Repositories */
		$fields = array(
						'remote_repository' => array(
												 'type' => 'TEXT',
												 'default' => '',
										  ),
										  
						'local_repository' => array(
												 'type' => 'TEXT',
												 'default' => '',
										  ),
										  		
		);
		
		$this->dbforge->add_column('games', $fields, 'app_set_config');
		$this->dbforge->add_column('game_types', $fields, 'disk_size');
		

	}
	
	public function down() {
		$this->load->dbforge();
		
		$this->dbforge->drop_column('servers', 'query_port');
		$this->dbforge->drop_column('servers', 'rcon_port');
		
		$this->dbforge->drop_column('dedicated_servers', 'stats');
		
		$this->dbforge->drop_column('game_types', 'remote_repository');
		$this->dbforge->drop_column('game_types', 'local_repository');
		
		$this->dbforge->drop_column('games', 'remote_repository');
		$this->dbforge->drop_column('games', 'local_repository');
	}
	
}
