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

	}
	
	public function down() {
		/* Обратную конвертацию и откат делать лень, все равно никто не пользуется откатом =) */
	}
	
}
