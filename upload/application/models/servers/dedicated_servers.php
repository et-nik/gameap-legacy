<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dedicated_servers extends CI_Model {
	
	var $ds_list = false;				// Список удаленных серверов
	
	var $commands			= array(); // Команды, которые отправлялись на сервер
    var $errors 			= ''; 	// Строка с ошибкой (если имеются)
	
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
			return true;
		} else {
			return false;
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
			return true;
		}else{
			return false;
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
    function get_ds_list($where = false, $limit = 1)
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
	
	// ----------------------------------------------------------------
    
    /**
     * Проверяет, существует ли выделенный сервер с данным id
     * 
     * @return bool
    */  
    function ds_live($id = false) {
		
		if (false == $id) {
			return false;
		}

        if ($this->db->count_all_results('dedicated_servers') > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    
    // ----------------------------------------------------------------
    
    /**
     * Получает данные выделенного сервера
     * 
     * @return bool
    */  
    function get_ds_data($id = false) {
		if (false == $id) {
			return false;
		}
		
		$where = array('id' => $id);
		$this->get_ds_list($where, 1);
		
		return $this->ds_list[0];
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
			return true;
		}else{
			return false;
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
		$this->db->where('ds_id', $ds_id);
		
		$this->db->where_in('server_port', $ports);
		$this->db->or_where_in('query_port', $ports);
		$this->db->or_where_in('rcon_port', $ports);
		
		$query = $this->db->get('servers');
		
		if($query->num_rows > 0) {
			return false;
		} else {
			return true;
		}
		
	}
	
	//-----------------------------------------------------------
    
    /*
     * Добавляет sudo к команде
     * 
     * 
    */
	private function _add_sudo($command, $server_data) {
		/* Если игровой сервер локальный, т.е. запущен на том же физическом сервере, что
		 * и админпанель, то запускаться он будет от пользователя www-data, который сделать 
		 * это не сможет, для решения проблемы необходимо чтобы скрипт запускался из-под рута
		*/
		
		if(is_array($server_data)) {
			$os = $server_data['os'];
		} else {
			$os = $server_data;
		}

		switch(strtolower($os)){
			case 'ubuntu':
				$command =  'sudo ' . $command;
				break;
				
			case 'debian':
				$command =  'sudo ' . $command;
				break;
				
			case 'linux':
				$command =  'sudo ' . $command;
				break;
				
			case 'centos':
				$command =  'sudo ' . $command;
				break;
				
			default:
				/* Для windows никаких sudo не требуется */
				//$command = $command;
				break;
		}
		
		return $command;
	}
	
	//-----------------------------------------------------------
	
	/*
	 * Функция отправляет команду на сервер
	*/
	function command($command, $server_data, $path = false)
    {

		/*
		 * 	Чтобы сервер успешно запустился через exec нужно:
		 *  выполнить: 
		 * 				sudo nano /etc/sudoers
		 *	добавить в конец: 
		 * 				www-data ALL = NOPASSWD: /путь/к/скрипту
		 * 
		 * Условие проверяет, является ли сервер локальным,
		 * если является то запускается простая команда exec
		 * если является удаленным, то идет обращение к функции ssh_command
		 * для отправки команды через ssh
		 * 
		*/


		/* -------------------
		 * 	Определяем путь 
		 * -------------------
		*/
		if(!$path) {
			$path = $server_data['script_path'];
		}

		/* Добавляем команду в зависимости от ОС */
		switch(strtolower($server_data['os'])) {
			case 'windows':
					/* Замена нормального линуксовского слеша
					 * на ненормальный виндовый \ */
					$path = str_replace('/', '\\', $path);
					$cd = "cd /D " . $path;
				break;
				
			default:
					$cd = "cd " . $path;
				break;
		}
		
		if($server_data['local_server']) {
			
			if (!$this->servers->_check_path($path)) {
				return $this->errors;
			}
			
			if(is_array($command)) {
				$result = '';

				foreach($command as $cmd_arr) {
					
					/* Проверка существования исполняемого файла и прав на выполнение */
					$script_file = explode(' ', $cmd_arr);
					$script_file = $script_file[0];
					$script_file = str_replace('./', '', $script_file);
					
					if (!$this->_check_file($path . '/' . $script_file)) {
						$result .= $this->errors;
					}

					if($result) { $result .= "\n---\n"; }
					$cmd_arr = $this->_add_sudo($cmd_arr, $server_data['os']);
					
					$result .= exec($cd . ' && ' . $cmd_arr);
					$this->commands[] = $cd . ' && ' . $cmd_arr;
				}
			} else {
				
				/* Проверка существования исполняемого файла и прав на выполнение */
				$script_file = explode(' ', $command);
				$script_file = $script_file[0];
				$script_file = str_replace('./', '', $script_file);
				
				/* 
				 * Проверяем, существует ли файл
				 * Проверяется файлы .sh, если это команда, например wget, то 
				 * проверки не будет 
				*/
				if (strpos($command, '.sh') !== false && !$this->servers->_check_file($path . '/' . $script_file)) {
					return $this->errors;
				}

				$command = $this->_add_sudo($command, $server_data['os']);
				
				$result = exec($cd . ' && ' . $command);
				$this->commands[] = $cd . ' && ' . $command;
			}
		} else {
			/* Удаленная машина */

			if (strtolower($server_data['control_protocol']) == 'telnet') { 
				
				/* Загрузка необходимой библиотеки */
				$this->load->library('telnet');
				
				//~ $result = $this->telnet_command($command, $server_data, $path);
				
				/* Получение данных для соединения */
				$telnet_data = explode(':', $server_data['telnet_host']);
				$telnet_ip = $telnet_data['0'];
				
				if(!isset($telnet_data['1'])){
					$telnet_port = 23; // Стандартный порт telnet
				}else{
					$telnet_port = $telnet_data['1'];
				}
				
				$this->telnet->connect($telnet_ip, $telnet_port);
				$this->telnet->auth($server_data['telnet_login'], $server_data['telnet_password']);
				
				$result = '';

				if(is_array($command)) {
					foreach($command as $cmd_arr) {
						//~ $this->telnet->write($cd . ' && ' . $cmd_arr  . "\r\n");
						$result .= $this->telnet->command($cd . ' && ' . $cmd_arr  . "\r\n");
						$result .= "\n/------------------------/\n\n";
						$this->commands[] = $cd . ' && ' . $cmd_arr  . "\r\n";
					}
				} else {
					//~ $this->telnet->write($cd . ' && ' . $command  . "\r\n");
					$result = $this->telnet->command($cd . ' && ' . $command  . "\r\n");
					$this->commands[] = $cd . ' && ' . $command  . "\r\n";
				}
				
			} else {
				
				/* Загрузка необходимой библиотеки */
				$this->load->library('ssh');
				
				$result = '';
				
				$ssh_data = explode(':', $server_data['ssh_host']);
		
				$ssh_ip = $ssh_data['0'];
				
				if(!isset($ssh_data['1'])){
					$ssh_port = 22;
				}else{
					$ssh_port = $ssh_data['1'];
				}
				
				$this->ssh->connect($ssh_ip, $ssh_port);
				
				if ($this->ssh->auth($server_data['ssh_login'], $server_data['ssh_password'])) {
					if(is_array($command)) {
						foreach($command as $cmd_arr) {
							//~ $stream[] = ssh2_exec($connection, $cd . ' && ' . $cmd_arr);
							$result .= $this->ssh->command($cd . ' && ' . $cmd_arr);
							$this->commands[] = $cd . ' && ' . $cmd_arr;
							$result = "\n/------------------------/\n\n";
							
						}
						
					} else {
							//~ $stream[] = ssh2_exec($connection, $cd . ' && ' . $command);
							$result = $this->ssh->command($cd . ' && ' . $command);
							$this->commands[] = $cd . ' && ' . $command;
					}
					
				}
			}
		}
		
		if ($result) {
			return $result;
		}else{
			return false;
		}
		
		return false;
	}

}
