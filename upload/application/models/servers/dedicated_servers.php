<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dedicated_servers extends CI_Model {
	
	var $ds_list = array();				// Список удаленных серверов
	
	//~ var $commands; 							// Команды, которые отправлялись на сервер
    var $errors; 							// Строка с ошибкой (если имеются)
    
    private $_commands = array();
    private $_errors	= false;
    
    //-----------------------------------------------------------

    public function __construct()
	{
		parent::__construct();
	}
	
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
     * Получение списка отправленных команд
     * 
     * @param bool Если $last_command TRUE, то будет отправлена лишь последняя команда
     * @return array
     *
    */
	function get_sended_commands($last_command = false)
	{
		if (count($this->_commands) <= 0) {
			return;
		}
		
		if(false == $last_command) {
			return $this->_commands;
		} else {
			return $this->_commands[count($this->_commands)-1];
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
			
			/* Выполняем необходимые действия с данными
			 * Расшифровываем пароли, преобразуем списки из json в понятный массив */
			$i = 0;
			$count_ds_list = count($this->ds_list);
			while($i < $count_ds_list) {
				
				$ds_ip = $this->ds_list[$i]['ip'];
				if (!$this->ds_list[$i]['ip'] = json_decode($ds_ip, true)) {
					/* Строка с данными не является json, в этом случае присваиваем первому
					 * массиву значение этой строки
					 * Сделано для совместимости со старыми версиями после обновления
					*/
					$this->ds_list[$i]['ip'] = array();
					$this->ds_list[$i]['ip'][] = $ds_ip;
				}
				unset($ds_ip);
				
				$this->ds_list[$i]['ssh_login']			= $this->encrypt->decode($this->ds_list[$i]['ssh_login']);
				$this->ds_list[$i]['ssh_password'] 		= $this->encrypt->decode($this->ds_list[$i]['ssh_password']);
				
				$this->ds_list[$i]['telnet_login']		= $this->encrypt->decode($this->ds_list[$i]['telnet_login']);
				$this->ds_list[$i]['telnet_password']	= $this->encrypt->decode($this->ds_list[$i]['telnet_password']);
				
				$this->ds_list[$i]['ftp_login']			= $this->encrypt->decode($this->ds_list[$i]['ftp_login']);
				$this->ds_list[$i]['ftp_password']		= $this->encrypt->decode($this->ds_list[$i]['ftp_password']);
				
				switch(strtolower($this->ds_list[$i]['control_protocol'])) {
					case 'ssh':
						$this->ds_list[$i]['script_path'] = $this->ds_list[$i]['ssh_path'];
						break;
						
					case 'telnet':
						$this->ds_list[$i]['script_path'] = $this->ds_list[$i]['telnet_path'];
						break;
					
					default:
						$this->ds_list[$i]['script_path'] = $this->ds_list[$i]['ssh_path'];
						break;
				}
				
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
    function get_ds_data($id = false) 
    {
		
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
     * Обновляет поле с данными для модулей
     * 
     * @param id 	 	id сервера
     * @param array 	новые данные
     * @param string	имя модуля
     * @return bool
     *
    */
	function update_modules_data($id, $data, $module_name)
	{
		$ds_data = $this->get_ds_data($id);
		
		$modules_data_array = json_decode($ds_data['modules_data'], true);
		$modules_data_array[$module_name] = $data;
		$modules_data_json = json_encode($modules_data_array);
		
		$sql_data['modules_data'] = $modules_data_json;
		
		if ($this->edit_dedicated_server($id, $sql_data)) {
			return true;
		} else {
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
		
		if ($this->ds_list) {
		
			foreach ($this->ds_list as $dedicated_servers) {
				$num++;
				
				$tpl_data[$num]['ds_name'] = $dedicated_servers['name'];
				$tpl_data[$num]['ds_location'] = $dedicated_servers['location'];
				$tpl_data[$num]['ds_provider'] = $dedicated_servers['provider'];
				$tpl_data[$num]['ds_os'] = $dedicated_servers['os'];
				$tpl_data[$num]['ds_ram'] = $dedicated_servers['ram'];
				$tpl_data[$num]['ds_cpu'] = $dedicated_servers['cpu'];
				$tpl_data[$num]['ds_id'] = $dedicated_servers['id'];
				
				/* Список IP адресов */
				$tpl_data[$num]['ds_ip'] = implode(', ', $dedicated_servers['ip']);
				
				/* Количество игровых серверов */
				$this->db->count_all();
				
				$this->db->where('ds_id', $dedicated_servers['id']);
				$this->db->from('servers');
				$tpl_data[$num]['servers_count'] = $this->db->count_all_results();
				
			}
			
			return $tpl_data;
			
		} else {
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
	function command($command, $server_data = false, $path = false)
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
		
		if (!$server_data) {
			$server_data = &$this->ds_list[0];
		}
		
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
		
		if ($server_data['local_server']) {
			
			if (!$this->servers->_check_path($path)) {
				$this->errors = 'Path ' . $path . " not found\n";
				return false;
			}
			
			if(is_array($command)) {
				$result = '';

				foreach($command as $cmd_arr) {
					
					/* Проверка существования исполняемого файла и прав на выполнение */
					$script_file = explode(' ', $cmd_arr);
					$script_file = $script_file[0];
					$script_file = str_replace('./', '', $script_file);
					
					/* 
					 * Проверяем, существует ли файл
					 * Проверяется файлы .sh, если это команда, например wget, то 
					 * проверки не будет 
					*/
					if (strpos($cmd_arr, '.sh') !== false && strpos($cmd_arr, '.exe') !== false && !$this->servers->_check_file($path . '/' . $script_file)) {
						return $this->errors;
					}

					if($result) { $result .= "\n---\n"; }
					
					$command = $this->_add_sudo($command, $server_data['os']);
					exec($cd . ' && ' . $cmd_arr, $output);
					$result .= implode("\n", $output);
					$result .= "\n/------------------------/\n\n";

					$this->_commands[] = $cd . ' && ' . $cmd_arr;
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
				if (strpos($command, '.sh') !== false && strpos($command, '.exe') !== false && !$this->servers->_check_file($path . '/' . $script_file)) {
					return $this->errors;
				}

				$command = $this->_add_sudo($command, $server_data['os']);
				exec($cd . ' && ' . $command, $output);
				$result = implode("\n", $output);

				$this->_commands[] = $cd . ' && ' . $command;
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
						$result .= $this->telnet->command($cd . ' && ' . $cmd_arr  . "\r\n");
						$result .= "\n/------------------------/\n\n";
						
						$this->_commands[] = $cd . ' && ' . $cmd_arr  . "\r\n";
					}
				} else {
					$result = $this->telnet->command($cd . ' && ' . $command  . "\r\n");
					
					$this->_commands[] = $cd . ' && ' . $command  . "\r\n";
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
							$result .= $this->ssh->command($cd . ' && ' . $cmd_arr);
							$result = "\n/------------------------/\n\n";
							
							$this->_commands[] = $cd . ' && ' . $cmd_arr;		
						}
						
					} else {
							//~ $stream[] = ssh2_exec($connection, $cd . ' && ' . $command);
							$result = $this->ssh->command($cd . ' && ' . $command);
							
							$this->_commands[] = $cd . ' && ' . $command;
					}
					
				}
			}
		}
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
		
		return false;
	}

}
