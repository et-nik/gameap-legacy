<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Version090 extends CI_Migration {
	
	public function up() {

		$this->load->dbforge();
		
		if (!$this->db->field_exists('privileges', 'servers_privileges')) {
			$fields = array(
							'privileges' => array(
													 'type' => 'TEXT',
											  ),	
			);

			$this->dbforge->add_column('servers_privileges', $fields, 'server_id');
		}
		
		$query = $this->db->get('servers_privileges');
		
		/* Очистка таблицы и удаление полей */
		$this->db->truncate('servers_privileges');
		$this->dbforge->drop_column('servers_privileges', 'privilege_name');
		$this->dbforge->drop_column('servers_privileges', 'privilege_value');
		
		/* Конвертация данных */
		foreach ($query->result_array() as $row)
		{
			$new_privileges[ $row['user_id'] ][ $row['server_id'] ][ $row['privilege_name'] ] = $row['privilege_value'];
		}
		
		if (!empty($new_privileges)) {
			foreach($new_privileges as $uid => $server) {
				foreach($server as $sid => $privilege) {
					$sql_data[] = array('user_id' => $uid, 'server_id' => $sid, 'privileges' => json_encode($privilege));
				}
			}
			
			/* Вставка данных всем скопом */
			$this->db->insert_batch('servers_privileges', $sql_data);
		}
		
		// Конвертируем данные локального сервера
		$this->load->model('servers/dedicated_servers');
		
		$sql_data['name'] 				= 'Local server';
		$sql_data['os'] 				= $this->config->config['local_os'];
		$sql_data['control_protocol'] 	= 'local';
		$sql_data['location'] 			= 'Russia';
		$sql_data['ip'] 				= '["localhost"]';
		$sql_data['steamcmd_path'] 		= $this->config->config['local_steamcmd_path'];
		$sql_data['ssh_path'] 			= $this->config->config['local_script_path'];
		$sql_data['telnet_path'] 		= $this->config->config['local_script_path'];

		$this->dedicated_servers->add_dedicated_server($sql_data);
		$ds_id = $this->db->insert_id();
		
		$new_servers_data['ds_id'] = $ds_id;
		
		$this->db->where('ds_id', 0);
		$this->db->update('servers', $new_servers_data);
		
		// Пользовательские фильтры
		
		if (!$this->db->field_exists('filters', 'users')) {
			$fields = array(
							'filters' => array(
													 'type' => 'TINYTEXT',
											  ),	
			);

			$this->dbforge->add_column('users', $fields, 'modules_data');
		}

	}
	
	public function down() {
		/* Обратную конвертацию и откат делать лень, все равно никто не пользуется откатом =) */
	}
	
}
