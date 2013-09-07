<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version08 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		$this->dbforge->drop_table('modules');
		
		/*----------------------------------*/
		/* 				modules				*/
		/*----------------------------------*/

		$fields = array(
					'short_name' => array(
										'type' => 'CHAR',
										'constraint' => 32, 
					),
					
					'name' => array(
										'type' => 'TINYTEXT',
					),
					
					'description' => array(
										'type' => 'TINYTEXT',
					),
					
					'version' => array(
										'type' => 'CHAR',
										'constraint' => 64, 
					),
					
					'show_in_menu' => array(
										'type' => 'INT',
										'constraint' => 1, 
					),
					
					'access' => array(
										'type' => 'TINYTEXT', 
					),

					'developer' => array(
										'type' => 'CHAR',
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

	}
	
	public function down() {
		
		$this->dbforge->drop_table('modules');

	}
	
}
