<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version_100 extends CI_Migration {
	
	// -----------------------------------------------------------------
	
	public function up() 
	{
		$this->load->dbforge();
		
		$fields = array(
			'modules_data' => array('type' => 'MEDIUMTEXT',),	
		);
		
		$this->dbforge->modify_column('users', $fields);
		$this->dbforge->modify_column('servers', $fields);
		$this->dbforge->modify_column('dedicated_servers', $fields);

		// Лимиты серверов
		$fields = array();
		$this->db->field_exists('cpu_limit', 'servers') OR $fields['cpu_limit'] = array('type' => 'INT');
		$this->db->field_exists('ram_limit', 'servers') OR $fields['ram_limit'] = array('type' => 'INT');
		$this->db->field_exists('net_limit', 'servers') OR $fields['net_limit'] = array('type' => 'INT');
		$this->db->field_exists('status', 'servers') OR $fields['status'] = array('type' => 'TEXT');

		empty($fields) OR $this->dbforge->add_column('servers', $fields, 'su_user');
		
		// Выключение выделенного сервера
		if (!$this->db->field_exists('disabled', 'dedicated_servers')) {
			$fields = array(
				'disabled' => array('type' => 'INT', 'constraint' => 1),	
			);
			
			$this->dbforge->add_column('dedicated_servers', $fields, 'name');
		}
		
		// Параметры запуска в выделенных серверах
		$fields = array();
		$this->db->field_exists('script_start', 'dedicated_servers') 		OR $fields['script_start'] = array('type' => 'TEXT');
		$this->db->field_exists('script_stop', 'dedicated_servers') 		OR $fields['script_stop'] = array('type' => 'TEXT');
		$this->db->field_exists('script_restart', 'dedicated_servers') 		OR $fields['script_restart'] = array('type' => 'TEXT');
		$this->db->field_exists('script_status', 'dedicated_servers') 		OR $fields['script_status'] = array('type' => 'TEXT');
		$this->db->field_exists('script_get_console', 'dedicated_servers') 	OR $fields['script_get_console'] = array('type' => 'TEXT');
		$this->db->field_exists('script_send_command', 'dedicated_servers') OR $fields['script_send_command'] = array('type' => 'TEXT');
		
		empty($fields) OR $this->dbforge->add_column('dedicated_servers', $fields, 'ftp_path');
		
		// Удаление полей
		!$this->db->field_exists('script_start', 'game_types') 			OR $this->dbforge->drop_column('game_types', 'script_start');
		!$this->db->field_exists('script_stop', 'game_types') 			OR $this->dbforge->drop_column('game_types', 'script_stop');
		!$this->db->field_exists('script_restart', 'game_types') 		OR $this->dbforge->drop_column('game_types', 'script_restart');
		!$this->db->field_exists('script_status', 'game_types') 		OR $this->dbforge->drop_column('game_types', 'script_status');
		!$this->db->field_exists('script_update', 'game_types') 		OR $this->dbforge->drop_column('game_types', 'script_update');
		!$this->db->field_exists('script_get_console', 'game_types') 	OR $this->dbforge->drop_column('game_types', 'script_get_console');
		!$this->db->field_exists('script_send_command', 'game_types') 	OR $this->dbforge->drop_column('game_types', 'script_send_command');
		
		!$this->db->field_exists('execfile_windows', 'game_types') 		OR $this->dbforge->drop_column('game_types', 'execfile_windows');
		!$this->db->field_exists('execfile_linux', 'game_types') 		OR $this->dbforge->drop_column('game_types', 'execfile_linux');
		
		!$this->db->field_exists('config_files', 'game_types') 			OR $this->dbforge->drop_column('game_types', 'config_files');
		!$this->db->field_exists('content_dirs', 'game_types') 			OR $this->dbforge->drop_column('game_types', 'content_dirs');
		!$this->db->field_exists('log_dirs', 'game_types') 				OR $this->dbforge->drop_column('game_types', 'log_dirs');
		
		// Данные пользователей
		$fields = array();
		if (!$this->db->field_exists('notices', 'users')) {
			$fields = array(
				'notices' => array('type' => 'MEDIUMTEXT'),	
			);
			
			$this->dbforge->add_column('users', $fields, 'filters');
		}
		
		// Сессии
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
		
		// Подтверждение действий
		$fields = array();
		if (!$this->db->table_exists('actions')) {
			$fields = array(
				'id' => array(
								'type' => 'TINYTEXT',
				),
				
				'action' => array(
								'type' => 'VARCHAR',
								'constraint' => 64, 
				),
				
				'data' => array(
								'type' => 'MEDIUMTEXT',
				),
			);
			
			$this->dbforge->add_field($fields);
			$this->dbforge->create_table('actions');
		}
		
		// GameAP Daemon поля
		$fields = array();
		$this->db->field_exists('gdaemon_host', 'dedicated_servers') 	OR $fields['gdaemon_host'] = array('type' => 'TINYTEXT');
		$this->db->field_exists('gdaemon_key', 'dedicated_servers') 	OR $fields['gdaemon_key'] = array('type' => 'TINYTEXT');
		empty($fields) OR $this->dbforge->add_column('dedicated_servers', $fields, 'steamcmd_path');
		
	}
	
	// -----------------------------------------------------------------
	
	public function down() 
	{
		$this->load->dbforge();
	}
}
