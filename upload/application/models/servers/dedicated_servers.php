<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dedicated_servers extends Servers {
	
	var $ds_list = FALSE;				// Список удаленных серверов
	
	
	//-----------------------------------------------------------
	
	/**
     * Шифровка паролей
     * 
     * @param array
     * @return bool
     *
    */
	function _encrypt_passwords($data) {
		
		$this->load->library('encrypt');
		
		if (isset($data['ssh_login'])) {
			$data['ssh_login']	= $this->encrypt->encode($data['ssh_login']);
			if ($data['ssh_password'] == '') {
				unset($data['ssh_password']);
			} else {
				$data['ssh_password']	= $this->encrypt->encode($data['ssh_password']);
			}
		}

		if (isset($data['telnet_login'])) {
			$data['telnet_login']	= $this->encrypt->encode($data['telnet_login']);
			if ($data['telnet_password'] == '') {
				unset($data['telnet_password']);
			} else {
				$data['telnet_password']	= $this->encrypt->encode($data['telnet_password']);
			}
		}
		
		if (isset($data['ftp_login'])) {
			$data['ftp_login']	= $this->encrypt->encode($data['ftp_login']);
			if ($data['ftp_password'] == '') {
				unset($data['ftp_password']);
			} else {
				$data['ftp_password']	= $this->encrypt->encode($data['ftp_password']);
			}
		}
		
		return $data;
	}
	
	//-----------------------------------------------------------
	
	/**
     * Добавление выделенного сервера
     * 
     * @param array
     * @return bool
     *
    */
	function add_dedicated_server($data)
	{
		$data = $this->_encrypt_passwords($data);
		
		if ($this->db->insert('dedicated_servers', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	//-----------------------------------------------------------
	
	/**
     * Удаление выделенного сервера
     * 
     * @param array
     * @return bool
     *
    */
	function del_dedicated_server($id)
	{
		if($this->db->delete('dedicated_servers', array('id' => $id))){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------
	
	/**
     * Получение списка удаленных сервров (машин)
     * 
     * @param array - условия для выборки
     * @param int
     * 
     * @return array
     *
    */
    function get_ds_list($where = FALSE, $limit = 1)
    {
		$this->load->library('encrypt');

		/*
		 * В массиве $where храняться данные для выборки.
		 * Например:
		 * 		$where = array('id' => 1);
		 * в этом случае будет выбран сервер id которого = 1
		 * 
		*/

		if(is_array($where)){
			$query = $this->db->get_where('dedicated_servers', $where, $limit);
		}else{
			$query = $this->db->get('dedicated_servers');
		}

		if($query->num_rows > 0) {
			
			$this->ds_list = $query->result_array();
			
			/* Расшифровываем пароли */
			$i = 0;
			$count_ds_list = count($this->ds_list);
			while($i < $count_ds_list) {
				
				$this->ds_list[$i]['ssh_login']			= $this->encrypt->decode($this->ds_list[$i]['ssh_login']);
				$this->ds_list[$i]['ssh_password'] 		= $this->encrypt->decode($this->ds_list[$i]['ssh_password']);
				
				$this->ds_list[$i]['telnet_login']		= $this->encrypt->decode($this->ds_list[$i]['telnet_login']);
				$this->ds_list[$i]['telnet_password']	= $this->encrypt->decode($this->ds_list[$i]['telnet_password']);
				
				$this->ds_list[$i]['ftp_login']			= $this->encrypt->decode($this->ds_list[$i]['ftp_login']);
				$this->ds_list[$i]['ftp_password']		= $this->encrypt->decode($this->ds_list[$i]['ftp_password']);
				
				$i ++;
			}
			
			return $this->ds_list;
			
		}else{
			return array();
		}
	}
	
	//-----------------------------------------------------------
	
	/**
     * Редактирование выделенного сервера
     * 
     * @param id - id сервера
     * @param array - новые данные
     * @return bool
     *
    */
	function edit_dedicated_server($id, $data)
	{
		$data = $this->_encrypt_passwords($data);
		
		$this->db->where('id', $id);

		if($this->db->update('dedicated_servers', $data)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------
	
	/**
     * Получение данных выделенного сервера для шаблона
     * (вырезаны ненужные данные - пароли и пр.)
     * 
     *
    */
	function tpl_data_ds()
    {
		$num = -1;
		
		if(!$this->ds_list){
			$this->get_ds_list();
		}
		
		if($this->ds_list){
		
			foreach ($this->ds_list as $dedicated_servers) {
				$num++;
				
				$tpl_data[$num]['ds_name'] = $dedicated_servers['name'];
				$tpl_data[$num]['ds_location'] = $dedicated_servers['location'];
				$tpl_data[$num]['ds_provider'] = $dedicated_servers['provider'];
				$tpl_data[$num]['ds_ip'] = $dedicated_servers['ip'];
				$tpl_data[$num]['ds_os'] = $dedicated_servers['os'];
				$tpl_data[$num]['ds_ram'] = $dedicated_servers['ram'];
				$tpl_data[$num]['ds_cpu'] = $dedicated_servers['cpu'];
				$tpl_data[$num]['ds_id'] = $dedicated_servers['id'];
				
				/* Количество игровых серверов */
				$this->db->count_all();
				
				$this->db->where('id', $dedicated_servers['id']);
				$this->db->from('servers');
				$tpl_data[$num]['servers_count'] = $this->db->count_all_results();
				
			}
			
			return $tpl_data;
			
		}else{
			return array();
		}
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Проверка занятости портов 
	 * 
	 * @param str, array
	*/
	function check_ports($ds_id, $ports)
	{
		$this->db->or_where('ds_id', $ds_id);
		
		if (is_array($ports)) {
			foreach($ports as $port) {
				if (!is_int($port) OR !$port) {
					continue;
				}
				
				$this->db->or_where('server_port', $port);
				$this->db->or_where('query_port', $port);
				$this->db->or_where('rcon_port', $port);
			}
		} else {
			$this->db->or_where('server_port', $port);
			$this->db->or_where('query_port', $port);
			$this->db->or_where('rcon_port', $port);
		}
		
		$query = $this->db->get('servers');
		
		print_r($query);
		
		if($query->num_rows > 0) {
			return FALSE;
		} else {
			return TRUE;
		}
		
	}


}
