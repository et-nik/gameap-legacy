<?php

class Migration_New_version extends CI_Migration {

  public function up() {
    
	$this->load->dbforge();
	
    /*----------------------------------*/
	/* 				modules				*/
	/*----------------------------------*/

	$fields = array(
			'id' => array(
								'type' => 'INT',
								'constraint' => 16, 
								'auto_increment' => TRUE
			),
			
			'name' => array(
								'type' => 'CHAR',
								'constraint' => 32, 
			),

			'file' => array(
								'type' => 'TINYTEXT',
			),
			
			'enabled' => array(
								'type' => 'INT',
								'constraint' => 1, 
								'default'	=> 1,
			),
			
			'version' => array(
								'type' => 'CHAR',
								'constraint' => 64, 
			),
			
			'developer' => array(
								'type' => 'CHAR',
								'constraint' => 64, 
			),
			
			'site' => array(
								'type' => 'TINYTEXT',
			),
			
			'information' => array(
								'type' => 'TINYTEXT',
			),
	);
	
	$this->dbforge->add_key('id', TRUE);
	$this->dbforge->add_field($fields);
	$this->dbforge->create_table('modules3');
	
  }

  public function down() {
    $this->dbforge->drop_table('modules');
  }

}
