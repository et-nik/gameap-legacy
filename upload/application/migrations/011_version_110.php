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
	}
	
	// -----------------------------------------------------------------
	
	public function down() 
	{
		$this->load->dbforge();
	}
}
