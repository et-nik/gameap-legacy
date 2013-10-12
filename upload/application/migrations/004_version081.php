<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version081 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		/* Update for MODULES table */
		if (!$this->db->field_exists('cron_script', 'modules')) {
			$fields = array(
							'cron_script' => array(
													 'type' => 'TINYTEXT',
											  ),	
			);

			$this->dbforge->add_column('modules', $fields, 'description');
		}
		
		/* Update for USERS table */
		if (!$this->db->field_exists('modules_data', 'users')) {
			$fields = array(
							'modules_data' => array(
													 'type' => 'TINYTEXT',
											  ),	
			);

			$this->dbforge->add_column('users', $fields, 'privileges');
		}
		

	}
	
	public function down() {
		$this->load->dbforge();
		
		$this->dbforge->drop_column('modules', 'cron_script');
		$this->dbforge->drop_column('users', 'modules_data');
	}
	
}
