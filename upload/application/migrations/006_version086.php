<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version086 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		/* Update for DEDICATED_SERVERS table */
		$fields = array(
					'ip' => array(
											'type' => 'TEXT',
										),
		);
		
		$this->dbforge->modify_column('dedicated_servers', $fields);
	}
	
	public function down() {
		$this->load->dbforge();

		$fields = array(
					'ip' => array(
											'type' => 'TINYTEXT',
										),
		);
		
		$this->dbforge->modify_column('dedicated_servers', $fields);
	}
	
}
