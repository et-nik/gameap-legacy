<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version093 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		if (!$this->db->field_exists('script_send_command', 'game_types')) {
			$fields = array(
							'script_send_command' => array(
													 'type' => 'TINYTEXT',
							),	
			);

			$this->dbforge->add_column('game_types', $fields, 'script_get_console');
		}
		
		$this->db->update('game_types', array('script_send_command' => 'send_command {dir} {name} {ip} {port} "{command}" {user}'));
		
		$fields = array(
						'modules_data' => array(
												 'type' => 'TINYTEXT',
						),	
		);

		$this->dbforge->add_column('servers', $fields, 'aliases');

	}
	
	public function down() {
		$this->dbforge->drop_column('game_types', 'script_send_command');
	}
	
}
