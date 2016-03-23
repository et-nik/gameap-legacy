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
		if (!$this->db->table_exists('hooks')) {
			$fields = array(
                'id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                    
                'hook_id' => array(
                    'type' => 'INT',
                ),
                    
                'module' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 32, 
                ),
                    
                'callback' => array(
                    'type' => 'TINYTEXT',
                ),
                    
                'pre' => array(
                    'type' => 'INT',
                    'constraint' => 1,
                ),
            );

            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('hooks');
		}
	}
	
	// -----------------------------------------------------------------
	
	public function down() 
	{
		$this->load->dbforge();
	}
}
