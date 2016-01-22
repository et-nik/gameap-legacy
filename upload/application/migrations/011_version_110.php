<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version_110 extends CI_Migration {
	// -----------------------------------------------------------------
	
	public function up() 
	{
		// Группы пользователей
		$fields = array();
		if (!$this->db->field_exists('group', 'users')) {
			$fields = array(
				'group' => array('type' => 'INT'),	
			);
			
			$this->dbforge->add_column('users', $fields, 'is_admin');
		}
		
		// Хуки GameAP
		$fields = array();
		if (!$this->db->table_exists('sessions')) {
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
		}
	}
	
	// -----------------------------------------------------------------
	
	public function down() 
	{
		$this->load->dbforge();
	}
}
