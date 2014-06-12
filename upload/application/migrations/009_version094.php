<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version094 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		if (!$this->db->field_exists('update_info', 'modules')) {
			$fields = array(
							'update_info' => array(
									'type' => 'TINYTEXT',
							),	
			);

			$this->dbforge->add_column('modules', $fields, 'version');
			
			$this->db->where('short_name', 'commercial');
			$this->db->update('modules', array('update_info' => 'http://www.gameap.ru/updates/commercial.txt'));
			
			$this->db->where('short_name', 'amxx_plugins_control');
			$this->db->update('modules', array('update_info' => 'http://www.gameap.ru/updates/amxx_plugins_control.txt'));
			
			$this->db->where('short_name', 'chat');
			$this->db->update('modules', array('update_info' => 'http://www.gameap.ru/updates/chat.txt'));
			
			$this->db->where('short_name', 'subnetban');
			$this->db->update('modules', array('update_info' => 'http://www.gameap.ru/updates/subnetban.txt'));
		}
		
		if (!$this->db->field_exists('modules_data', 'servers')) {
			$fields = array(
							'modules_data' => array(
													 'type' => 'TINYTEXT',
							),	
			);

			$this->dbforge->add_column('servers', $fields, 'aliases');
		}

	}
	
	public function down() {
		$this->dbforge->drop_column('update_info', 'modules');
	}
	
}
