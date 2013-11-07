<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/
class Servers extends CI_Model {

    var $server_id   = '';				// ИД сервера
    var $servers_list = array();		// Список игровых серверов
    var $server_data = array();		// Данные сервера
    var $server_ds_data = array();		// Данные DS игрового сервера
    var $server_game_data = array();	// Данные игры к которой принадлежит сервер
    
    var $all_settings = array(
		'SERVER_AUTOSTART'			=> 'Автостарт сервера в случае его падения (через cron)',
		'SERVER_RCON_AUTOCHANGE' 	=> 'Автоматическая смена rcon пароля, в случае если в админпанели и на сервере он не совпадает',
    );
    
    var $server_settings 	= array();
    var $commands			= array(); // Команды, которые отправлялись на сервер
    var $errors 			= ''; 	// Строка с ошибкой (если имеются)

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
        $this->load->helper('safety');
        $this->load->library('encrypt');
    }

    function _strip_quotes($string) {
		$string = str_replace('"', '', $string);
		$string = str_replace('\'', '', $string);
		
		return $string;
	}
	
	//-----------------------------------------------------------
    
    /*
     * Проверяет директорию на необходимые права
     * 
     * 
    */
	function _check_path($path) {
		
		if (!is_dir($path)) {
			/* Это не директория */
			$this->errors = "Dir " . $path . " not found";
			return false;
		}

		return true;
		
	}
	
	//-----------------------------------------------------------
    
    /*
     * Проверяет файл на необходимые права
     * 
     * @param str - путь к локальному файлу
     * @return bool
     * 
    */
	function _check_file($file) {
		if (!file_exists($file)) {
			$this->errors = 'Error: ' . $file . ' file not found';
			return false;
		}
		
		if (!is_executable($file)) {
			$this->errors = 'Error: ' . $file . ' file not executable';
			return false;
		}
		
		
		
		return true;
	}
	
	//-----------------------------------------------------------
	
	/*
	 * Замена шоткодов в команде
	*/
	private function _replace_shotcodes_in_string($command, $server_data)
	{
		/*-------------------*/
		/* Шаблонная замена */
		/*-------------------*/
		
		/* В случае использования Windows значение может быть пустым
		 * и параметры собьются */
		if(empty($server_data['screen_name'])) {
			$server_data['screen_name'] = 'null';
		}
		
		$command = str_replace('{command}', 	$this->_strip_quotes($server_data['start_command']) , $command);							// Команда запуска игрового сервера (напр. "hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire")
		$command = str_replace('{id}', 			$this->_strip_quotes($server_data['id']) 			, $command);							// ID сервера
		$command = str_replace('{script_path}', $this->_strip_quotes($server_data['script_path']) 	, $command);
		$command = str_replace('{game_dir}', 	$this->_strip_quotes($server_data['dir'])  			, $command);							// Директория с игрой
		$command = str_replace('{dir}', 		$this->_strip_quotes($server_data['script_path'] . '/' . $server_data['dir'])  , $command);	// Корневая директория (где скрипт запуска)
		$command = str_replace('{name}', 		$this->_strip_quotes($server_data['screen_name']) 	, $command);							// Имя скрина
		$command = str_replace('{ip}', 			$this->_strip_quotes($server_data['server_ip']) 	, $command);							// IP сервера для коннекта (может не совпадать с ip дедика)
		$command = str_replace('{port}', 		$this->_strip_quotes($server_data['server_port']) 	, $command);							// Порт сервера для коннекта
		$command = str_replace('{game}', 		$this->_strip_quotes($server_data['start_code']) 	, $command);							// Игра
		$command = str_replace('{user}', 		$this->_strip_quotes($server_data['su_user']) 		, $command);							// Пользователь

		/*-------------------*/
		/* Замена по алиасам */
		/*-------------------*/
		
		/* Допустимые алиасы */
		$allowable_aliases = json_decode($server_data['aliases_list'], true);
		/* Значения алиасов на сервере */
		$server_aliases = json_decode($server_data['aliases'], true);
		
		/* Прогон по алиасам */
		if($allowable_aliases && !empty($allowable_aliases)){
			foreach ($allowable_aliases as $alias) {
				if(isset($server_aliases[$alias['alias']]) && !empty($server_aliases[$alias['alias']])) {
					$command = str_replace('{' . $alias['alias'] . '}', $server_aliases[$alias['alias']] , $command);	
				}
			}
		}
		
		return $command;
	}

	//-----------------------------------------------------------
	
	/*
	 * Замена шоткодов в команде
	*/
	private function _replace_shotcodes_in_array($command, $server_data)
	{
		if (is_array($command)) {
			foreach($command as &$str) { 
				$str = $this->_replace_shotcodes_in_string($str, $server_data); 
			}
		} else {
			$command = $this->_replace_shotcodes_in_string($command, $server_data);
		}
		
		return $command;
	}
	
	//-----------------------------------------------------------
    
    /*
     * Генерирует команду для отправки на сервер
     * 
     * 
    */
    function command_generate($server_data, $type = 'start')
    {
		
		/* Получение команд из данных сервера */
		switch($type){
			case 'start':
				$command = $server_data['script_start'];
				break;
			case 'stop':
				$command = $server_data['script_stop'];
				break;
			case 'restart':
				$command = $server_data['script_restart'];
				break;
			case 'update':
				$command = $server_data['script_update'];
				break;
			case 'get_console':
				$command = $server_data['script_get_console'];
				break;
			default:
				return false;
		}

		return $command;
	}

	//-----------------------------------------------------------
	
	/*
	 * Функция отправляет команду на выделенный сервер
	*/
	function command($command, $server_data, $path = false)
    {
		$this->load->model('servers/dedicated_servers');
		
		$command = $this->_replace_shotcodes_in_array($command, $server_data);
		
		$result = $this->dedicated_servers->command($command, $server_data, $path);

		$this->commands = $this->dedicated_servers->commands;
		$this->errors = $this->dedicated_servers->commands;
		
		return $result;
	}
    
    
    /*
     * Запуск сервера
     * 
     * @param array - данные сервера или его id
     *
    */
    function start($server_data)
    {
		if(!is_array($server_data)) {
			// был передан id, получаем данные сервера
			$server_data = $this->get_server_data($server_data, false, true, true);
		}
		
		$command = $this->command_generate($server_data, 'start');
		$result = $this->command($command, $server_data);

		return $result;
	}
	
	
	//-----------------------------------------------------------	
	/*
     * Остановка сервера
     * 
     * @param array - данные сервера
     *
    */
    function stop($server_data)
    {
		if(!is_array($server_data)) {
			// был передан id, получаем данные сервера
			$server_data = $this->get_server_data($server_data, false, true, true);
		}
		
		$command = $this->command_generate($server_data, 'stop');
		$result = $this->command($command, $server_data);
		
		return $result;
	}
	
	
	//-----------------------------------------------------------	
	/*
     * Перезапуск сервера
     * 
     * @param array - данные сервера
     *
    */
    function restart($server_data)
    {
		if(!is_array($server_data)) {
			// был передан id, получаем данные сервера
			$server_data = $this->get_server_data($server_data, false, true, true);
		}
		
		$command = $this->command_generate($server_data, 'restart');
		$result = $this->command($command, $server_data);
		
		return $result;
	}
	
	//-----------------------------------------------------------	
	/*
     * Перезапуск сервера
     * 
     * @param array - данные сервера
     *
    */
    function update($server_data)
    {
		if(!is_array($server_data)) {
			// был передан id, получаем данные сервера
			$server_data = $this->get_server_data($server_data);
		}
		
		$command = $this->command_generate($server_data, 'update');
		
		/* Определение пути до steamcmd */
		if ($server_data['steamcmd_path']) {
			$steamcmd_path = $server_data['steamcmd_path'];
		} else {
			$steamcmd_path = false;
		}
		
		$result = $this->command($command, $server_data, $steamcmd_path);
		
		return $result;
	}
	
	//-----------------------------------------------------------	
	/*
     * Добавление нового сервера
     * 
     *
    */
    function add_game_server($data)
    {
		$this->load->helper('string');
		$this->load->model('games');
		
		if (isset($data['rcon'])) {
			$data['rcon'] = $this->encrypt->encode($data['rcon']);
		}
		
		/* Присваиваем имя scren  */
		$data['screen_name'] = (!isset($data['screen_name'])) ? $data['game'] . '_' . random_string('alnum', 6) . '_' . $data['server_port'] : $data['screen_name'];
		
		if ($this->db->insert('servers', $data)) {
			return true;
		} else {
			return false;
		}
	}

	//-----------------------------------------------------------	
	/*
     * Редактирование сервера
     * 
     *
    */
    function edit_game_server($id, $data)
    {
		$this->db->where('id', $id);
		
		if (isset($data['rcon'])) {
			$data['rcon'] = $this->encrypt->encode($data['rcon']);
		}
		
		if($this->db->update('servers', $data)){
			return true;
		}else{
			return false;
		}
	}

	//-----------------------------------------------------------	
	/*
     * Удаление сервера
     * 
     *
    */
    function delete_game_server($id)
    {
		if ($this->db->delete('servers', array('id' => $id))) {
			
			$this->db->delete('servers_privileges', array('server_id' => $id));
			$this->db->delete('logs', array('server_id' => $id));
			
			return true;
		} else {
			return false;
		}
	}
	
	//-----------------------------------------------------------
	
	/**
     * Получение списка серверов
     * 
     * @param int - id пользователя для которого получаем серверы
     * @param str - привилегия пользователя
     * @param array - where для запроса sql
     *
    */
	function get_server_list($user_id = false, $privilege_name = 'VIEW', $where = array('enabled' => '1', 'installed' => '1', ))
	{
		return $this->get_servers_list($user_id, $privilege_name, $where);
	}

	//-----------------------------------------------------------
	
	/**
     * Получение списка серверов
     * 
     * @param int - id пользователя для которого получаем серверы
     * @param str - привилегия пользователя
     * @param array - where для запроса sql
     *
    */
    function get_servers_list($user_id = false, $privilege_name = 'VIEW', $where = array('enabled' => '1', 'installed' => '1'), $limit = 99999, $offset = 0, $engine = false, $engine_version = false)
    {
		/* Если задан движок, то получаем список игр на этом движке,
		 * а после выбираем серверы только этих игр 
		 */
		$games = array();
		if ($engine) {
			$this->db->where('engine', $engine);
			if ($engine_version) {$this->db->where('engine_version', $engine_version);}
			
			$this->db->select('code');
			$query = $this->db->get('games');
			
			$games_list = $query->result_array();
			
			foreach($games_list as &$game_data) {
				$games[] = $game_data['code'];
			}
		}
		
		/* 
		 * Если user_id не задан, то получаем все серверы
		 * Если задан, то получаем лишь серверы владельцем
		 * которых является user_id
		*/
		if (!$user_id) {
			$this->db->where_in('game', $games);
			$this->db->where($where);
			$query = $this->db->get('servers');
		} else {
			
				/*
				 * Выбираются данные из таблицы servers_privileges 
				 * для пользователя $user_id со следующими привилегиями:
				 * privilege_name = $privilege_name
				 * privilege_value = 1	(разрешено)
				*/
				$query = $this->db->get_where('servers_privileges', array('user_id' => $user_id, 'privilege_name' => $privilege_name, 'privilege_value' => '1'));
				
				if($query->num_rows > 0){

					$this->db->where($where);
					foreach ($query->result_array() as $privileges){
						$servers[] = $privileges['server_id'];
					}
					
					$this->db->where_in('id', $servers);
					if (!empty($games)) { $this->db->where_in('game', $games); }
					
				} else {
					/* Количество серверов = 0 */
					
					/* 
					 * Чтобы избавиться от некоторых уязвимостей, связанных с бесправными пользователями
					 * у которых нет серверов, но при этом они отображаются в списке
					*/
					$this->servers_list = array();
					return NULL;
				}

				$query = $this->db->get('servers');
			}
			
			if ($query->num_rows > 0) {

				$a = 0;
				foreach ($query->result_array() as $server_data) {
					
					$server_list[$a] = $server_data;
					
					if (!$server_data['query_port']) {
						$server_list[$a]['query_port'] = $server_data['server_port'];
					}
					
					if (!$server_data['rcon_port']) {
						$server_list[$a]['rcon_port'] = $server_data['server_port'];
					}
					
					$a++;
				}
				
				$this->servers_list = $server_list;
				return $this->servers_list;
				
			} else {
				/* Количество серверов = 0 */
				
				/* 
				 * Чтобы избавиться от некоторых уязвимостей, связанных с бесправными пользователями
				 * у которых нет серверов, но при этом они отображаются в списке
				*/
				$this->servers_list = array();
				return NULL;
			}
	}

	
	//-----------------------------------------------------------
	
	/**
     * Получение данных сервера
     * 
     * @param int - id сервера
     * @param bool - если true, то данные выделенного сервера получены не будут
     * @param bool - если true, то данные игры получены не будут
     * @param bool - если true, то данные типа игры получены не будут
     * 
     * @return array
     *
    */
    function get_server_data($server_id, $no_get_ds = false, $no_get_game = false, $no_get_gt = false)
    {
		// Загрузка необходимых моделей
		$this->load->model('servers/dedicated_servers');
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');
		
		$this->load->library('encrypt');
		
		$return_data = array(); // Возвращаемые данные о сервере
		
		$query = $this->db->get_where('servers', array('id' => $server_id), 1);
		
		if($query->num_rows > 0) {
			$this->server_data = $query->row_array();
			
			/* Записываем переменную в список */
			$this->servers_list['0'] = $this->server_data;
			
			/* Расшифровываем RCON пароль */
			$this->server_data['rcon'] = $this->encrypt->decode($this->server_data['rcon']);
			
			/* Получаем query и rcon порты */
			if (!$this->server_data['query_port']) {
				$this->server_data['query_port'] = $this->server_data['server_port'];
			}
			
			if (!$this->server_data['rcon_port']) {
				$this->server_data['rcon_port'] = $this->server_data['server_port'];
			}
			
			/* 
			 * Получение информации об удаленном сервере
			 * 
			 * Необходимо, чтобы был указан ds_id (ID выделенного сервере)
			 * если он будет равен 0 или не будет указан, то сервер
			 * принимается за локальный
			 * 
			*/
			if (!$no_get_ds && $this->server_data['ds_id']) {

				$where = array('id' => $this->server_data['ds_id']);
				$this->dedicated_servers->get_ds_list($where, 1);
				
				$this->server_ds_data = $this->dedicated_servers->ds_list['0'];
				
				// Данные для игрового сервера из машины
				$this->server_data['os'] = strtolower($this->server_ds_data['os']);
				
				$this->server_data['local_server'] = 0;
				
				switch (strtolower($this->server_ds_data['control_protocol'])) {
					case 'ssh':
						$this->server_data['control_protocol'] = 'ssh';
						break;
					
					case 'telnet':
						$this->server_data['control_protocol'] = 'telnet';
						break;
					
					default:
						if ($this->server_data['os'] == 'windows') {
							$this->server_data['control_protocol'] = 'telnet';
						} else {
							$this->server_data['control_protocol'] = 'ssh';
						}
						
						break;
				}

				$this->server_data['ssh_host'] = $this->server_ds_data['ssh_host'];
				$this->server_data['ssh_login'] = $this->server_ds_data['ssh_login'];
				$this->server_data['ssh_password'] = $this->server_ds_data['ssh_password'];
				$this->server_data['ssh_passwd'] = $this->server_ds_data['ssh_password'];
				$this->server_data['ssh_path'] = $this->server_ds_data['ssh_path'];
				
				$this->server_data['telnet_host'] = $this->server_ds_data['telnet_host'];
				$this->server_data['telnet_login'] = $this->server_ds_data['telnet_login'];
				$this->server_data['telnet_password'] = $this->server_ds_data['telnet_password'];
				$this->server_data['telnet_path'] = $this->server_ds_data['telnet_path'];
				
				$this->server_data['ftp_host'] = $this->server_ds_data['ftp_host'];
				$this->server_data['ftp_login'] = $this->server_ds_data['ftp_login'];
				$this->server_data['ftp_password'] = $this->server_ds_data['ftp_password'];
				$this->server_data['ftp_passwd'] = $this->server_ds_data['ftp_password'];
				$this->server_data['ftp_path'] = $this->server_ds_data['ftp_path'];
				
				/* Определение пути до скрипта и до steamcmd */
				switch ($this->server_data['control_protocol']) {
					case 'local':
						$this->server_data['script_path'] 	= $this->config->config['local_script_path'];
						$this->server_data['steamcmd_path'] = ($this->config->config['local_steamcmd_path']) ? $this->config->config['local_steamcmd_path'] : $this->config->config['local_script_path'];
						break;
						
					case 'telnet':
						$this->server_data['script_path'] 	= $this->server_ds_data['telnet_path'];
						$this->server_data['steamcmd_path'] = ($this->server_ds_data['steamcmd_path']) ? $this->server_ds_data['steamcmd_path'] : $this->server_ds_data['telnet_path'];
						break;

					default:
						// По умолчанию SSH
						$this->server_data['script_path'] 	= $this->server_ds_data['ssh_path'];
						$this->server_data['steamcmd_path'] = ($this->server_ds_data['steamcmd_path']) ? $this->server_ds_data['steamcmd_path'] : $this->server_ds_data['ssh_path'];
						break;
				}
				
				//$this->server_data['control_protocol'] = $this->server_ds_data['control_protocol'];
			} else {
				$this->server_data['os'] 			= $this->config->config['local_os'];
				$this->server_data['control_protocol'] = 'local';
				$this->server_data['script_path'] 	= $this->config->config['local_script_path'];
				$this->server_data['local_path'] 	= $this->config->config['local_script_path'];
				$this->server_data['steamcmd_path'] = ($this->config->config['local_steamcmd_path']) ? $this->config->config['local_steamcmd_path'] : $this->config->config['local_script_path'];
				$this->server_data['local_server'] 	= 1;
			}
			
			if (!$no_get_game && $this->server_data['game']) {
				$where = array('code' => $this->server_data['game']);
				$this->games->get_games_list($where, 1);
				
				$this->server_game_data = $this->games->games_list['0'];
				$this->server_data['start_code'] = $this->server_game_data['start_code'];
				$this->server_data['game_name'] = $this->server_game_data['name'];
				$this->server_data['engine'] = $this->server_game_data['engine'];
				$this->server_data['engine_version'] = $this->server_game_data['engine_version'];
				
			} else {
				/* Информация об игре не найдена */
				
			}
			
			if (!$no_get_gt && $this->server_data['game_type']) {
				$where = array('id' => $this->server_data['game_type']);
				$this->game_types->get_gametypes_list($where, 1);

				$this->server_data['mod_name'] 		= $this->game_types->game_types_list['0']['name'];
				$this->server_data['config_files'] 	= $this->game_types->game_types_list['0']['config_files'];
				$this->server_data['content_dirs'] 	= $this->game_types->game_types_list['0']['content_dirs'];
				$this->server_data['log_dirs'] 		= $this->game_types->game_types_list['0']['log_dirs'];
				$this->server_data['fast_rcon']		= $this->game_types->game_types_list['0']['fast_rcon'];
				$this->server_data['aliases_list'] 	= $this->game_types->game_types_list['0']['aliases'];
				$this->server_data['disk_size'] 	= $this->game_types->game_types_list['0']['disk_size'];
				
				if(strtolower($this->server_data['os']) == 'windows') {
					$execfile_upd = 'steamcmd.exe';
					$execfile = $this->game_types->game_types_list['0']['execfile_windows'];
				} else {
					$execfile_upd = './steamcmd.sh';
					$execfile = $this->game_types->game_types_list['0']['execfile_linux'];
					
					/* Добавляем к линуксу ./ в случае необходимости */
					if (stripos($execfile, './') === false) {
						$execfile = './' . $execfile;
					}
				}
				
				$this->server_data['script_start'] 		= $execfile . ' ' . $this->game_types->game_types_list['0']['script_start'];
				$this->server_data['script_stop'] 		= $execfile . ' ' . $this->game_types->game_types_list['0']['script_stop'];
				$this->server_data['script_restart'] 	= $execfile . ' ' . $this->game_types->game_types_list['0']['script_restart'];
				$this->server_data['script_status'] 	= $execfile . ' ' . $this->game_types->game_types_list['0']['script_status'];
				$this->server_data['script_update'] 	= $execfile_upd . ' ' . $this->game_types->game_types_list['0']['script_update'];
				$this->server_data['script_get_console'] = $execfile . ' ' . $this->game_types->game_types_list['0']['script_get_console'];
				
				$this->server_data['kick_cmd'] 		= $this->game_types->game_types_list['0']['kick_cmd'];
				$this->server_data['ban_cmd'] 		= $this->game_types->game_types_list['0']['ban_cmd'];
				$this->server_data['chname_cmd'] 	= $this->game_types->game_types_list['0']['chname_cmd'];
				$this->server_data['srestart_cmd'] 	= $this->game_types->game_types_list['0']['srestart_cmd'];
				$this->server_data['chmap_cmd'] 	= $this->game_types->game_types_list['0']['chmap_cmd'];
				$this->server_data['sendmsg_cmd'] 	= $this->game_types->game_types_list['0']['sendmsg_cmd'];
				$this->server_data['passwd_cmd'] 	= $this->game_types->game_types_list['0']['passwd_cmd'];

				
			} else {
				/* Информация о модификации игры не найдена */
				
			}

			
		} else {
			return false;
		}

		return $this->server_data;
	}
	
	//-----------------------------------------------------------
	
	/**
     * Получение данных сервера для шаблона
     * (вырезаны ненужные данные - пароли и пр.)
     * 
     *
    */
	function tpl_data()
    {
		$num = -1;
		
		$tpl_data = array();
		
		if (!isset($this->servers_list)) {
			$this->get_game_servers_list();
		}
		
		foreach ($this->servers_list as $server_data){
			$num++;
			
			$tpl_data[$num]['server_id'] 	= $server_data['id'];
			$tpl_data[$num]['server_game'] 	= $server_data['game'];
			$tpl_data[$num]['server_name'] 	= $server_data['name'];
			$tpl_data[$num]['server_ip'] 	= $server_data['server_ip'];
			$tpl_data[$num]['server_port'] 	= $server_data['server_port'];
		}
		
		return $tpl_data;
	}

	// ----------------------------------------------------------------
    
    /**
     * Проверяет, существует ли сервер с данным id
     * 
     * @return bool
    */	
	function server_live($server_id)
	{
		$this->db->where('id', $server_id);
		if($this->db->count_all_results('servers') > 0){
			return true;
		}else{
			return false;
		}
		
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Проверяет, существует ли выделенный сервер с данным id
     * 
     * @return bool
    */	
	function ds_server_live($server_id){
		
		$this->db->where('id', $server_id);
		if($this->db->count_all_results('dedicated_servers') > 0){
			return true;
		}else{
			return false;
		}
		
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Проверяет статус сервера
     * 
     * @return bool
    */	
	function server_status($host = false, $port = false, $engine = false, $engine_version = false)
	{
		$this->load->driver('query');
		
		if (!$host) {
			$host = $this->server_data['server_ip'];
		}
		
		if (!$port) {
			$port = $this->server_data['query_port'];
		}

		if (!$engine or !$engine_version) {

			if (!isset($this->games->games_list)) {
				$this->load->models('servers/games');
				$this->games->get_games_list();
			}
			
			/* Получение id игры в массиве */
			$i = 0;
			$count = count($this->games->games_list);
			while($i < $count) {
				if ($this->server_data['game'] == $this->games->games_list[$i]['code']) {
					$game_arr_id = $i;
					break;
				}
				$i++;
			}
		}
		
		if (!$engine) {
			$engine = $this->games->games_list[$game_arr_id]['engine'];
		}
		
		if (!$engine_version) {
			$engine_version = $this->games->games_list[$game_arr_id]['engine_version'];
		}

		$this->query->set_engine($engine, $engine_version);

		if ($this->query->get_status($host, $port)) {
			return true;
		}else{
			return false;
		}
		
	}

	//-----------------------------------------------------------
	
	/**
     * Получение списка игровых серверов
     * Функция аналогична get_servers_list за исключением того, что
     * ей можно задать любое условие, а не только id пользователей, 
     * которым принадлежит игровой сервер.
     * 
     * @param array 	условие для выборки
     * @param integer	лимит
     * @param integer 	offset
     * @param string	движок
     * @param integer	версия движка
     * 
     * @return array	
     * 
    */
    function get_game_servers_list($where = false, $limit = 10000, $offset = 0, $engine = false, $engine_version = false)
    {
		/* Если задан движок, то получаем список игр на этом движке,
		 * а после выбираем серверы только этих игр 
		 */
		if ($engine) {
			$this->db->where('engine', $engine);
			if ($engine_version) {$this->db->where('engine_version', $engine_version);}
			
			$this->db->select('code');
			$query = $this->db->get('games');
			
			$games_list = $query->result_array();
			
			foreach($games_list as &$game_data) {
				$games[] = $game_data['code'];
			}
			
			if(!empty($games)) {
				$this->db->where_in('game', $games);
			}
		}
		
		if (is_array($where)) {
			$this->db->where($where);
		}
		
		$this->db->limit($limit, $offset);
		$query = $this->db->get('servers');
		
		if ($query->num_rows > 0) {
			$this->servers_list = $query->result_array();
			return $this->servers_list;
			
		} else{ 
			return NULL;
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Получение количества игровых серверов в зависимости от условия
	 * 
	 * @param array 	условие
	 * @return integer
	 */
	function get_servers_count($where = array())
	{
		
		$this->db->where($where);
		return $this->db->count_all('servers');
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Получение списка файлов на сервере (удаленном или локальном)
	*/
	public function get_files_list($server_data = false, $dir = '', $file_time = false, $file_size = false)
	{
		$server_data = empty($server_data) ? $this->server_data : $server_data;
		
		if ($server_data['ds_id'] == 0) {
			$dir = $server_data['script_path'] . '/' . $server_data['dir'] . '/' . $dir;
			return $this->get_local_files($server_data, $dir, $file_time, $file_size);
		} else {
			$dir = $server_data['ftp_path'] . '/' . $server_data['dir'] . '/' . $dir;
			return $this->get_remote_files($server_data, $dir, $file_time, $file_size);
		}
	}

	// ----------------------------------------------------------------
    
    /**
     * Получает список файлов на локальном сервере
     * 
     * @param array - данные сервера
     * @param string - каталог на сервере
     * @param bool - получать время о последней модификации файла
     * @param bool - получать размер файла
     * 
     * @return array - возвращает список файлов с полным путем к файлу
     * 
    */
	function get_local_files($server_data, $dir, $file_time = false, $file_size = false)
	{
		if($dir) {
			$files_list = glob($dir);
			
			$num = -1;
			$files = array();
			
			/* Перебор файлов */	
			if(!empty($files_list)) {
				foreach ($files_list as $file) {
					$num ++;	
					$files[$num]['file_name'] = $file;
					
					if(($file_time OR $file_size) && $file_stat = stat($file)) {
					
						if($file_time){
							$files[$num]['file_time'] = $file_stat['mtime'];
						}
						
						if($file_size){
							$files[$num]['file_size'] = $file_stat['size'];
						}
					}
				}
			}
			
			return $files;
		}else{
			return false;
		}
	}
	
	
	// ----------------------------------------------------------------
    
    /**
     * Получает список файлов на удаленном сервере в указанной директории
     * 
     * @param array - данные сервера
     * @param string - каталог на сервере
     * @param bool - получать время о последней модификации файла
     * @param bool - получать размер файла
     * 
     * @return array - возвращает список файлов с полным путем к файлу
     * 
    */
	function get_remote_files($server_data, $dir, $file_time = false, $file_size = false)
	{
		
		$connection = ftp_connect($server_data['ftp_host']);
		
		if(ftp_login($connection, $server_data['ftp_login'], $server_data['ftp_passwd'])){
			
			$files_list = ftp_nlist($connection, $dir);
			
			$num = -1;
			$maps = array();
			
			
			/* Перебор файлов, и удаление расширения файла */	
			if($files_list){
				foreach ($files_list as $file) {
					$num++;	
					$files[$num]['file_name'] = $file;
					
					if($file_time){
						$files[$num]['file_time'] = ftp_mdtm($connection, $file);
					}
					
					if($file_size){
						$files[$num]['file_size'] = ftp_size($connection, $file);
					}
				}
			}else{
				return false;
			}
			
			return $files;

		}else{
			return false;
		}

	}


	// ----------------------------------------------------------------
	
    /**
     * Получает список карт
     * 
    */
	function get_server_maps()
    {
		$this->load->helper('path');
		$time = time();

		/* Получаем список карт из базы (своеобразный кеш)*/
		$maps_cache = json_decode($this->server_data['maps_list'], true);
		
		/* Если списку не более суток */
		if($maps_cache && $maps_cache['time'] > $time - 86400){
			unset ($maps_cache['time']); // Удаляем time элемент
			return $maps_cache;
		}
		
		/* Определение, является сервер локальным или удаленным */
		if($this->server_data['local_server']){
			// Сервер локальный, получаем данные для него
			$dir = set_realpath($this->server_data['local_path'] . '/' . $this->server_data['dir'] . '/' . $this->server_data['maps_path']);
			$files_list = $this->get_local_files($this->server_data, $dir . "*.bsp");
		}else{
			// Сервер удаленный
			$dir = set_realpath($this->server_data['ftp_path'] . '/' . $this->server_data['dir'] . '/' . $this->server_data['maps_path']);
			$files_list = $this->get_remote_files($this->server_data, $dir . "*.bsp", true);
		}
		
		/* Сортировка массива с файлами по возрастанию
		 * 
		 * Применена пользовательская сортировка по функции uasort_asc
		 * которая чуть ниже. Сортировка происходит по file_name 
		 * в массиве
		*/
		
		if($files_list){
			uasort($files_list, array('Servers','uasort_asc'));
			
			$num = -1;
			$maps = array();
			
			/* Перебор карт, и удаление расширения файла */	
			foreach ($files_list as $file) {
				$num++;	
				$maps[$num]['map_name'] = str_replace('.bsp', '', basename($file['file_name']));
			}
			
			/* 
			 * Т.к получение карт процесс долгий, а в некоторых случаях
			 * (когда количество карт на сервере очень большое),
			 * то в этом случае список карт лучше отправлять в данные к серверу,
			 * что ниже и происходит.
			*/
			
			$time_array = array('time' => $time);
			$server_data['maps_list'] = json_encode($maps + $time_array);
			$this->edit_game_server($this->server_data['id'], $server_data);
			
			
			return $maps;
			
		}else{
			return NULL;
		}

		
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Сортировка массива по возрастанию
    */
	function uasort_asc($a, $b) 
	{
		if ($a['file_name'] === $b['file_name']) return 0;
		return $a['file_name'] > $b['file_name'] ? 1 : -1;
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Сортировка массива по убыванию
    */
	function uasort_desc($a, $b) 
	{
		if ($a['file_name'] === $b['file_name']) return 0;
		return $a['file_name'] < $b['file_name'] ? 1 : -1;
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Читает содержимое файла с локального сервера
     * 
     * @param str
     * @return str
    */
	function read_local_file($file)
	{
		
		if(file_exists($file)) {
			if(is_readable($file)) {
				$file_contents = file_get_contents($file);
			} else {
				$this->errors = 'Отсутствуют права на чтение файла';
				return false;
			}
			
		} else {
			$this->errors = 'Файл не найден';
			return false;
		}
		
		return $file_contents;
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Читает содержимое файла
     * 
     * @param string 	$file расположение файла без script_path и dir
     * @param array		$server_data массив с данными сервера
     * @return string
    */
	public function read_file($file, $server_data = array()) 
	{
		$server_data = empty($server_data) ? $this->server_data : $server_data;
		$file_contents = '';
		
		if ($server_data['ds_id'] == 0) {
			$file = $server_data['script_path'] . '/' . $server_data['dir'] . '/' . $file;
			$file_contents = $this->servers->read_local_file($file);
		} else {
			$file = $server_data['ftp_path'] . '/' . $server_data['dir'] . '/' . $file;
			$file_contents = $this->servers->read_remote_file($file, $server_data);
		}
		
		return $file_contents;
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Записывает содержимое файла
     * 
     * @param string 	$file расположение файла без script_path и dir
     * @param array		$server_data массив с данными сервера
     * @return bool
    */
	public function write_file($file, $file_contents = '', $server_data = array()) 
	{
		$server_data = empty($server_data) ? $this->server_data : $server_data;
		
		if ($server_data['ds_id'] == 0) {
			$file = $server_data['script_path'] . '/' . $server_data['dir'] . '/' . $file;
			return $this->servers->write_local_file($file, $file_contents);
		} else {
			$file = $server_data['ftp_path'] . '/' . $server_data['dir'] . '/' . $file;
			return $this->servers->write_remote_file($file, $file_contents, $server_data);
		}
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Читает содержимое файла с удаленного сервера
     * 
     * @param str
     * @return str
     * 
    */
	function read_remote_file($file, $server_data = array())
	{
		$server_data = empty($server_data) ? $this->server_data : $server_data;
		
		$connection = ftp_connect($server_data['ftp_host']);
		
		if (ftp_login($connection, $server_data['ftp_login'], $server_data['ftp_passwd'])) {
			
			// Определяем временный файл
			$temp_file = tempnam(sys_get_temp_dir(), basename($file));
			
			$handle = fopen($temp_file, 'r+');
			
			// Производим скачивание файла
			if (@!ftp_fget($connection, $handle, $file, FTP_ASCII, 0)) {
				$this->errors = 'Файл не найден';
				return false;
			}

			$file_contents = file_get_contents($temp_file);
			
			fclose($handle);
			unlink($temp_file);
			
			return $file_contents;
		} else {
			return false;
		}
	}
	
	
	// ----------------------------------------------------------------
	 
    /**
     * Записывает локальный файл
     * файл должен существовать
     * 
     * @param str
     * @return str
    */
	function write_local_file($file, $data = false) {
		
		if(!$data) {
			return false;
		}
		
		if(file_exists($file)) {
			
			if(is_writable($file)) {
				
				if(file_put_contents($file, $data)) {
					return true;
				}else{
					$this->errors = 'Неизвестная ошибка';
					return false;
				}
				
			} else {
				$this->errors = 'Отсутствуют права на запись файла';
				return false;
			}

		} else {
			$this->errors = 'Файл не найден';
			return false;
		}

	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Загружает файл на удаленный сервер
     * 
     * @param str
     * @param str
     * @return bool
     * 
    */
	function upload_remote_file($file, $remote_file, $mode = 0666)
	{
		
		$server_data = $this->server_data;
		
		$connection = ftp_connect($server_data['ftp_host']);
		
		if(!$connection) {
			$this->errors = 'Ошибка соединения с ftp сервером';
			return false;
		}
		
		if(ftp_login($connection, $server_data['ftp_login'], $server_data['ftp_passwd'])) {
			
			if (!ftp_put($connection, $remote_file, $file, FTP_BINARY)) {
				$this->errors = 'Ошибка записи файла';
				ftp_close($connection);
				return false;
			} else {
				@ftp_chmod($connection, $mode, $remote_file);
				ftp_close($connection);
				return true;
			}
		
		} else {
			$this->errors = 'Ошибка авторизации FTP';
			return false;
		}
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Записывает данные в удаленный файл
     * 
     * @param str
     * @return str
     * 
    */
	function write_remote_file($file, $data, $server_data = array()) 
	{
		$server_data = empty($server_data) ? $this->server_data : $server_data;
		
		// Определяем временный файл
		$temp_file = tempnam(sys_get_temp_dir(), basename($file));

		if (file_put_contents($temp_file, $data) === false) {
			$this->errors = 'Ошибка записи временного файла';
			return false;
		}
		
		if($this->upload_remote_file($temp_file, $file)){
			return true;
		}else{
			return false;
		}
	}
	
	// ----------------------------------------------------------------
	
    /**
     * Смена rcon пароля серверу
     * 
     * @param string	$new_rcon новый RCON пароль
     * @pararm array	$server_data данные сервера
     * @param bool		$update_db обновлять данные БД
     * 
     * @return bool
    */
    function change_rcon($new_rcon, $server_data = false, $update_db = false) 
    {
		$this->load->helper('patterns_helper');
		$this->load->driver('rcon');
		
		if($server_data) {
			$this->server_data = $server_data;
		}
		
		$this->rcon->set_variables(
								$this->server_data['server_ip'],
								$this->server_data['server_port'],
								$this->server_data['rcon'], 
								$this->servers->server_data['engine'],
								$this->servers->server_data['engine_version']
		);
		
		$this->rcon->change_rcon($new_rcon);

		if ($update_db) {
			$sql_data = array('rcon' => $new_rcon);
			$this->edit_game_server($this->server_data['id'], $sql_data);
		}
		
		return true;
	}
	
	// ----------------------------------------------------------------
	
    /**
     * Получение настроек сервера
     * 
     * @param int - id сервера
     * @param int - id пользователя
     * 
     * @return array
    */
    function get_server_settings($server_id, $user_id = false)
    {
		if(!$user_id) {
            $where = array('server_id' => $server_id);
        } else {
            $where = array('server_id' => $server_id, 'user_id' => $user_id);
        }
        
        $query = $this->db->get_where('settings', $where);
        $server_settings = array();
        foreach ($query->result_array() as $settings) {
            if(array_key_exists($settings['sett_id'], $this->all_settings)) {
                $server_settings[$settings['sett_id']] = $settings['value'];
            }
        }
        
        // Заполнение пустых значений
        foreach ($this->all_settings as $key => $value) {
            if(!array_key_exists($key, $server_settings)) {
                $server_settings[$key] = 0;
            }
        }
        
        $this->server_settings = $server_settings;
        
        return $server_settings;
	}
	
	// ----------------------------------------------------------------

    /**
     * Запись настроек
     * 
     * @return bool
    */
    function set_server_settings($sett_id, $value, $server_id, $user_id = false)
    {
        $where = array('sett_id' => $sett_id,
						'server_id' => $server_id);
						
		if($user_id) {
			$where['user_id'] = $user_id;
		}
        
        $query = $this->db->get_where('settings', $where);
        
        $data = array(
            'sett_id' 		=> $sett_id,
            'server_id' 	=> $server_id,
            'value' 		=> $value
        );
        
        if($user_id) {
			$data['user_id'] = $user_id;
		}
        
        $this->db->where('sett_id', $sett_id);
        $this->db->where('server_id', $server_id);
            
        if($query->num_rows > 0){
           /* Если привилегия уже есть в базе данных, то обновляем */
           if($this->db->update('settings', $data)){
                return true;
            }else{
                return false;
            }
            
        }else{
			/* Привилегии нет в базе данных, создаем новую строку */
			if($this->db->insert('settings', $data)){
                return true;
            }else{
                return false;
            }
		}

            
    }
}


/* End of file servers.php */
/* Location: ./application/models/servers.php */
