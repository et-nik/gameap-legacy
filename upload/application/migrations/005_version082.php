<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version082 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		/* Update for DEDICATED_SERVERS table */
		if (!$this->db->field_exists('modules_data', 'dedicated_servers')) {
			$fields = array(
							'modules_data' => array(
													 'type' => 'TINYTEXT',
											  ),	
			);

			$this->dbforge->add_column('dedicated_servers', $fields, 'ftp_path');
		}
		

	}
	
	public function down() {
		$this->load->dbforge();

		$this->dbforge->drop_column('dedicated_servers', 'modules_data');
	}
	
}
