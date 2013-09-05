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
    var $errors 			= FALSE; 	// Строка с ошибкой (если имеются)

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        
        $this->load->helper('safety');
    }

    /* hlftp j8p9B7ctU65APuEy */
	//-----------------------------------------------------------
    function get_chat($ftp_hostname, $ftp_username, $ftp_password, $ftp_path, $ftp_id)
    {
        $this->load->library('ftp');
        $this->load->helper('file');

        $ftp['hostname'] = $ftp_hostname;
        $ftp['username'] = $ftp_username;
        $ftp['password'] = $ftp_password;
        $ftp['debug']	= TRUE;

        $this->ftp->connect($ftp);

        $list = $this->ftp->list_files($ftp_path);
		
        asort($list);
        reset($list);

        // Задаем переменную чтобы небыло ошибки
		$chat_messages = "";
		
		// Загружаем файл с информацией об уже обработанных логах
		$load_logs = array();
		
		@$load_logs = file ('./db/'.$ftp_id.'_load_logs.txt', FILE_IGNORE_NEW_LINES);
		
		if($load_logs == false)
				$load_logs = array();
				
		$num = 0;

        while (list($key, $val) = each($list))
        {
            /* $val - имена файлов логов */
			
			
			/* Последний лог не загружаем
				потому что на сервере он не завершен
			*/
			$num++;
			if($num == count($list))
			break;
			
			
            $file_name = basename($val);
			
			if(in_array ($file_name, $load_logs))
			continue;
			
			/* Загружаем лог для его обработки */
            $this->ftp->download($val, "./temp/$file_name");

            $string = read_file('./temp/'.$file_name);
            $str_exp = explode("\n", $string);
			
			/* Записываем имя обработанного лога*/
			write_file('./db/'.$ftp_id.'_load_logs.txt', $file_name."\n", 'a+');

            for ($a = 0; $a < count($str_exp); $a++)
            {
                $matches = null;
				
                /*
				Пример строки
				L 02/25/2012 - 21:36:05: "[UMI7EPATOP] Devastator<7><STEAM_0:0:474356155><robo>" say "чтобы самому симаол не ставить чтобы русс буквы прошли"';
				*/

                $pattern = '!L (\d\d)/(\d\d)/(\d\d\d\d) - (\d\d):(\d\d):(\d\d): "(.*?)<(\d*)><([a-zA-Z0-9\_\:]*?)><(.*?)>" say \"(.*?)\"!si';
                $preg_match = preg_match($pattern, $str_exp[$a], $matches);

                //print_r ($matches);
				
                if($preg_match)
                {
					$matches[7] = htmlspecialchars($matches[7]);
                    $matches[11] = htmlspecialchars($matches[11]);
					
					//@$matches[11] = iconv('UTF-8','CP1251', $matches[11]);
					
					/*
					Array ( 
					[0] => L 02/25/2012 - 21:30:59: "[UMI7EPATOP] Devastator<2>" say "Bах :-)" 
					[1] => 02 
					[2] => 25 
					[3] => 2012 
					[4] => 21 
					[5] => 30 
					[6] => 59 
					[7] => [UMI7EPATOP] Devastator 
					[8] => 2 
					[9] => STEAM_0:0:474356155 
					[10] => helmet 
					[11] => Bах :-) )
					
					*/
					
					$this->load->helper('date');
					
					/* 
					 * unix_to_human()
					 * Принимает метку Unix на вход и возвращает удобный для человека формат
					 * 
					 * unix_to_human($now, TRUE, 'us'); // американское время с секундами
					 * unix_to_human($now, TRUE, 'eu'); // европейское время с секундами
					 * 
					 * human_to_unix()
					 * Противоположность
					 * 
					 * YYYY-MM-DD HH:MM:SS AM/PM
					 * 
					*/
					
					//$human = unix_to_human($now, TRUE, 'eu');
					
					$unix_time = human_to_unix("$matches[3]-$matches[1]-$matches[2] $matches[4]:$matches[5]:$matches[6]");
					$message = "$matches[2]/$matches[1]/$matches[3] - $matches[4]:$matches[5]:$matches[6]    |   $matches[7]: <b>$matches[11]</b><br>"."\n";
					
					//write_file('./db/'.$server_id.'/chat.txt', $message, 'a+');
                    
					$data = array(
								   'server_id' => $ftp_id,
								   'date' => $unix_time,
								   'type' => 'chat_msg',
								   'steam_id' => $matches[9],
								   'name' => $matches[7],
								   'ip' => '',
								   'msg' => $matches[11],
								   //'param_1' => $matches[10],
								   'log_name' => $file_name,
								);

					$this->db->insert('logs', $data);
                }
            }
			
			/* Уничтожаем загруженный файл */
			unlink('./temp/'.$file_name);

        }
        $this->ftp->close();

        return TRUE;
    }
    
    function _strip_quotes($string) {
		$string = str_replace('"', '', $string);
		$string = str_replace('\'', '', $string);
		
		return $string;
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
				return FALSE;
		}
		
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
		$allowable_aliases = json_decode($server_data['aliases_list'], TRUE);
		/* Значения алиасов на сервере */
		$server_aliases = json_decode($server_data['aliases'], TRUE);
		
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
     * Определяет относительный или абсолютный путь
     * 
     * 
    */
	public function path_detect($path){
		
	}
	
	//-----------------------------------------------------------
    
    /*
     * Добавляет sudo к команде
     * 
     * 
    */
	public function add_sudo($command, $server_data) {
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
	 * Отправка команды на сервер через telnet
	 * 
	 * @param string - команда на сервер
	 * @param array - массив с данными сервера
	*/
	private function telnet_command($command, $server_data, $path = FALSE)
    {
		// Загрузка необходимой модели
		$this->load->model('telnet');
		
		/* -------------------
		 * 	Определяем путь 
		 * -------------------
		*/
		if(!$path) {
			$cd = $server_data['telnet_path'];
		}
		
		/* Добавляем команду в зависимости от ОС */
		switch(strtolower($server_data['os'])) {
			case 'windows':
					$cd = "cd /D " . $path;
				break;
				
			default:
					$cd = "cd " . $path;
				break;
		}
		
		/* Получение данных для соединения */
		$telnet_data = explode(':', $server_data['telnet_host']);
		$telnet_ip = $telnet_data['0'];
		
		if(!isset($telnet_data['1'])){
			$telnet_port = 23; // Стандартный порт telnet
		}else{
			$telnet_port = $telnet_data['1'];
		}
		
		$this->telnet->connect($telnet_ip, $telnet_port);
		
		/*
		 * Все что ниже работает исключительно на магии, лучше не трогать.
		*/
		$this->telnet->read_till("ogin: ");
		$this->telnet->write( $server_data['telnet_login'] . "\r\n");
		$this->telnet->read_till("word: ");
		$this->telnet->write( $server_data['telnet_password'] . "\r\n");
		$this->telnet->read_till(":> ");
		
		$this->telnet->write("\r\n");
		$this->telnet->read_till(":> ");
		
		if(is_array($command)) {
			foreach($command as $cmd_arr) {
				$this->telnet->write($cd . ' && ' . $cmd_arr  . "\r\n");
				$this->commands[] = $cd . ' && ' . $cmd_arr  . "\r\n";
			}
		} else {
			$this->telnet->write($cd . ' && ' . $command  . "\r\n");
			$this->commands[] = $cd . ' && ' . $command  . "\r\n";
		}

		$return = $this->telnet->read_till(":> ");
		$this->telnet->close();
		
		/*
		 * Здесь лучше написать
		 * return хрен знает;
		 * =)
		*/
		return $return;
	
	}
	
	
	
	
	//-----------------------------------------------------------
	
	/* 
	 * Отправка команды на сервер через ssh
	 * 
	 * @param string - команда на сервер
	 * @param array - массив с данными сервера
	*/
	private function ssh_command($command, $server_data, $path = FALSE)
    {
		/* -------------------
		 * 	Определяем путь 
		 * -------------------
		*/
		if(!$path) {
			$path = $server_data['ssh_path'];
		}
		
		/* Добавляем команду в зависимости от ОС */
		switch(strtolower($server_data['os'])) {
			case 'windows':
					$cd = "cd /D " . $path;
				break;
				
			default:
					$cd = "cd " . $path;
				break;
		}
		
		$ssh_data = explode(':', $server_data['ssh_host']);
		
		$ssh_ip = $ssh_data['0'];
		
		if(!isset($ssh_data['1'])){
			$ssh_port = 22;
		}else{
			$ssh_port = $ssh_data['1'];
		}

		// Соединение с сервером
		$connection = ssh2_connect($ssh_ip, $ssh_port);
		$stream = array();
		
		if (ssh2_auth_password($connection, $server_data['ssh_login'], $server_data['ssh_password'])) {

			if(is_array($command)) {
				foreach($command as $cmd_arr) {
					$stream[] = ssh2_exec($connection, $cd . ' && ' . $cmd_arr);
					$this->commands[] = $cd . ' && ' . $cmd_arr;
					
				}
				
			} else {
					$stream[] = ssh2_exec($connection, $cd . ' && ' . $command);
					$this->commands[] = $cd . ' && ' . $command;
			}
			
			$data = "";
			
			foreach($stream as $st) {
				if($data) { $data .= "\n---\n"; }
				
				stream_set_blocking($st, true);
				$data .= stream_get_contents($st);	
			}

			return $data;
			
		} else {
			// Ошибка аутентификации
			//$this->commands[] = 'SSH auth error';
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------
	
	/*
	 * Функция отправляет команду на сервер
	*/
	function command_windows($command, $server_data, $path = FALSE)
    {
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
					$cd = "cd /D " . $path;
				break;
				
			default:
					$cd = "cd " . $path;
				break;
		}
		
		if ($server_data['local_server']) {
			if(is_array($command)) {
				$result = '';
				
				foreach($command as $cmd_arr) {
					/* Проверка существования исполняемого файла */
					$script_file = explode(' ', $cmd_arr);
					$script_file = $script_file[0];
					
					if (!file_exists($path . '/' . $script_file)) {
						$result .= 'Error: ' . $script_file . ' file not found';
						continue;
					}
					
					if($result) { $result .= "\n---\n"; }
					
					$result .= exec($cd . ' && ' . $cmd_arr);
					$this->commands[] = $cd .  ' && ' . $cmd_arr;
				}
			} else {
				/* Проверка существования исполняемого файла */
				$script_file = explode(' ', $command);
				$script_file = $script_file[0];
				
				if (!file_exists($path . '/' . $script_file)) {
					$result = 'Error: ' . $script_file . ' file not found';
					return $result;
				}
				
				$result = exec($cd . ' && ' . $command);
				$this->commands[] = $cd . ' && ' . $command;
			}
		} else {
			if (strtolower($server_data['control_protocol']) == 'telnet') { 
				$result = $this->telnet_command($command, $server_data, $path);
			} else {
				$result = $this->ssh_command($command, $server_data, $path);
			}
		}
		
		//$result = $this->ssh_command($command, $server_data);
		//echo 'Команда: '  . "<strong>cd " . $server_data['ssh_path'] . $command . '' . '</strong><br />Ответ сервера:<code>';
		//print_r($result);
		//echo '</code>';
		
		if($result){
			return $result;
		}else{
			return FALSE;
		}
		
		return FALSE;
	}
	
	//-----------------------------------------------------------
	/*
	 * Функция отправляет команду на сервер
	*/
	function command($command, $server_data, $path = FALSE)
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
		
		if(strtolower($server_data['os']) == 'windows') {
			return $this->command_windows($command, $server_data);
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
					$cd = "cd /D " . $path;
				break;
				
			default:
					$cd = "cd " . $path;
				break;
		}
		
		if($server_data['local_server']) {
			if(is_array($command)) {
				$result = '';
				
				foreach($command as $cmd_arr) {
					
					/* Проверка существования исполняемого файла и прав на выполнение */
					$script_file = explode(' ', $cmd_arr);
					$script_file = $script_file[0];
					
					if (!file_exists($path . '/' . $script_file)) {
						$result .= 'Error: ' . $script_file . ' file not found';
						continue;
					}
					
					if (!is_executable($path . '/' . $script_file)) {
						$result .= 'Error: ' . $script_file . ' file not executable';
						continue;
					}
					
					if($result) { $result .= "\n---\n"; }
					$cmd_arr = $this->add_sudo($cmd_arr, $server_data['os']);
					
					$result .= exec($cd . ' && ' . $cmd_arr);
					$this->commands[] = $cd . ' && ' . $cmd_arr;
				}
			} else {
				
				/* Проверка существования исполняемого файла и прав на выполнение */
				$script_file = explode(' ', $command);
				$script_file = $script_file[0];
				
				if (!file_exists($path . '/' . $script_file)) {
					$result = 'Error: ' . $script_file . ' file not found';
					return $result;
				}
				
				if (!is_executable($path . '/' . $script_file)) {
					$result = 'Error: ' . $script_file . ' file not executable';
					return $result;
				}
				
				$command = $this->add_sudo($command, $server_data['os']);
				
				$result = exec($cd . ' && ' . $command);
				$this->commands[] = $cd . ' && ' . $command;
			}
		} else {
			if (strtolower($server_data['control_protocol']) == 'telnet') { 
				$result = $this->telnet_command($command, $server_data, $path);
			} else {
				$result = $this->ssh_command($command, $server_data, $path);
			}
		}
		
		if($result){
			return $result;
		}else{
			return FALSE;
		}
		
		return FALSE;
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
			$server_data = $this->get_server_data($server_data, FALSE, TRUE, TRUE);
		}
		
		$command = $this->command_generate($server_data, 'start');
		
		switch($server_data['os']){
			case 'Windows':
				$result = $this->command_windows($command, $server_data);
				break;
			default:
				$result = $this->command($command, $server_data);
				break;
		}

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
			$server_data = $this->get_server_data($server_data, FALSE, TRUE, TRUE);
		}
		
		$command = $this->command_generate($server_data, 'stop');
		
		switch($server_data['os']){
			case 'Windows':
				$result = $this->command_windows($command, $server_data);
				break;
			default:
				$result = $this->command($command, $server_data);
				break;
		}
		
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
			$server_data = $this->get_server_data($server_data, FALSE, TRUE, TRUE);
		}
		
		$command = $this->command_generate($server_data, 'restart');
		
		switch($server_data['os']){
			case 'Windows':
				$result = $this->command_windows($command, $server_data);
				break;
			default:
				$result = $this->command($command, $server_data);
				break;
		}
		
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
			$server_data = $this->get_server_data($server_data, FALSE, TRUE, TRUE);
		}
		
		$command = $this->command_generate($server_data, 'update');
		
		switch($server_data['os']){
			case 'Windows':
				$result = $this->command_windows($command, $server_data);
				break;
			default:
				$result = $this->command($command, $server_data);
				break;
		}
		
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
		if($this->db->insert('servers', $data)){
			return TRUE;
		}else{
			return FALSE;
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
		
		if($this->db->update('servers', $data)){
			return TRUE;
		}else{
			return FALSE;
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
		if($this->db->delete('servers', array('id' => $id))){
			return TRUE;
		}else{
			return FALSE;
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
    function get_server_list($user_id = FALSE, $privilege_name = 'VIEW', $where = array('enabled' => '1', 'installed' => '1', ))
    {
		/* 
		 * Если user_id не задан, то получаем все серверы
		 * Если задан, то получаем лишь серверы владельцем
		 * которых является user_id
		*/

		if(!$user_id) {
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
			
			if($query->num_rows > 0){

				foreach ($query->result_array() as $server_data){
					
					$server_list[] = $server_data;
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
     * @param bool - если TRUE, то данные выделенного сервера получены не будут
     * @param bool - если TRUE, то данные игры получены не будут
     * @param bool - если TRUE, то данные типа игры получены не будут
     * 
     * @return array
     *
    */
    function get_server_data($server_id, $no_get_ds = FALSE, $no_get_game = FALSE, $no_get_gt = FALSE)
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
				$this->server_data['os'] = $this->server_ds_data['os'];
				
				$this->server_data['local_server'] = 0;
				
				$this->server_data['control_protocol'] = $this->server_ds_data['control_protocol'];

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
						$this->server_data['steamcmd_path'] = $this->config->config['local_steamcmd_path'];
						break;
						
					case 'telnet':
						$this->server_data['script_path'] 	= $this->server_ds_data['telnet_path'];
						$this->server_data['steamcmd_path'] = $this->server_ds_data['steamcmd_path'];
						break;

					default:
						// По умолчанию SSH
						$this->server_data['script_path'] 	= $this->server_ds_data['ssh_path'];
						$this->server_data['steamcmd_path'] = $this->server_ds_data['steamcmd_path'];
						break;
				}
				
				//$this->server_data['control_protocol'] = $this->server_ds_data['control_protocol'];
			} else {
				$this->server_data['os'] 			= $this->config->config['local_os'];
				$this->server_data['script_path'] 	= $this->config->config['local_script_path'];
				$this->server_data['local_path'] 	= $this->config->config['local_script_path'];
				$this->server_data['steamcmd_path'] = $this->config->config['local_steamcmd_path'];
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
					$execfile = $this->game_types->game_types_list['0']['execfile_windows'];
				} else {
					$execfile = $this->game_types->game_types_list['0']['execfile_linux'];
					
					/* Добавляем к линуксу ./ в случае необходимости */
					if (stripos($execfile, './') === FALSE) {
						$execfile = './' . $execfile;
					}
					
				}
				
				$this->server_data['script_start'] 		= $execfile . ' ' . $this->game_types->game_types_list['0']['script_start'];
				$this->server_data['script_stop'] 		= $execfile . ' ' . $this->game_types->game_types_list['0']['script_stop'];
				$this->server_data['script_restart'] 	= $execfile . ' ' . $this->game_types->game_types_list['0']['script_restart'];
				$this->server_data['script_status'] 	= $execfile . ' ' . $this->game_types->game_types_list['0']['script_status'];
				$this->server_data['script_update'] 	= $execfile . ' ' . $this->game_types->game_types_list['0']['script_update'];
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
			return FALSE;
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
	function server_live($server_id){
		
		if($this->db->count_all_results('servers', array('id' => $server_id)) > 0){
			return TRUE;
		}else{
			return FALSE;
		}
		
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Проверяет, существует ли выделенный сервер с данным id
     * 
     * @return bool
    */	
	function ds_server_live($server_id){
		
		if($this->db->count_all_results('dedicated_servers', array('id' => $server_id)) > 0){
			return TRUE;
		}else{
			return FALSE;
		}
		
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Проверяет статус сервера
     * 
     * @return bool
    */	
	function server_status($ip = FALSE, $port = FALSE){
		
		if(!$ip) {
			$ip = $this->server_data['server_ip'];
		}
		
		if(!$port) {
			$port = $this->server_data['server_port'];
		}
		
		/* Загрузка хелпера */
		$this->load->helper('serverinfo');

		if(A2S_INFO($ip, $port)){
			return TRUE;
		}else{
			return FALSE;
		}
		
	}

	//-----------------------------------------------------------
	/**
     * Получение списка игровых серверов
     * Функция аналогична get_servers_list за исключением того, что
     * ей можно задать любое условие, а не только id пользователей, 
     * которым принадлежит игровой сервер.
     * 
     *
    */
    function get_game_servers_list($where = FALSE, $limit = 10000)
    {
		if(is_array($where)){
			$query = $this->db->get_where('servers', $where, $limit);
		}else{
			$query = $this->db->get('servers');
		}

		if($query->num_rows > 0){
			
			$this->servers_list = $query->result_array();
			return $this->servers_list;
			
		}else{
			return NULL;
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
	function get_local_files($server_data, $dir, $file_time = FALSE, $file_size = FALSE)
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
			return FALSE;
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
	function get_remote_files($server_data, $dir, $file_time = FALSE, $file_size = FALSE)
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
				return FALSE;
			}
			
			return $files;

		}else{
			return FALSE;
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
		$maps_cache = json_decode($this->server_data['maps_list'], TRUE);
		
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
			$files_list = $this->get_remote_files($this->server_data, $dir . "*.bsp", TRUE);
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
	function read_local_file($file){
		
		if(file_exists($file)) {
			if(is_readable($file)) {
				$file_contents = file_get_contents($file);
			} else {
				$this->errors = 'Отсутствуют права на чтение файла';
				return FALSE;
			}
			
		} else {
			$this->errors = 'Файл не найден';
			return FALSE;
		}
		
		return $file_contents;
	}
	
	// ----------------------------------------------------------------
	 
    /**
     * Читает содержимое файла с удаленного сервера
     * 
     * @param str
     * @return str
     * 
    */
	function read_remote_file($file){
		
		$server_data = $this->server_data;
		
		$connection = ftp_connect($server_data['ftp_host']);
		
		if (ftp_login($connection, $server_data['ftp_login'], $server_data['ftp_passwd'])) {
			
			// Определяем временный файл
			$temp_file = tempnam(sys_get_temp_dir(), basename($file));
			
			$handle = fopen($temp_file, 'r+');
			
			// Производим скачивание файла
			if (@!ftp_fget($connection, $handle, $file, FTP_ASCII, 0)) {
				$this->errors = 'Файл не найден';
				return FALSE;
			}

			$file_contents = file_get_contents($temp_file);
			
			fclose($handle);
			unlink($temp_file);
			
			return $file_contents;
		} else {
			return FALSE;
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
	function write_local_file($file, $data = FALSE) {
		
		if(!$data) {
			return FALSE;
		}
		
		if(file_exists($file)) {
			
			if(is_writable($file)) {
				
				if(file_put_contents($file, $data)) {
					return TRUE;
				}else{
					$this->errors = 'Неизвестная ошибка';
					return FALSE;
				}
				
			} else {
				$this->errors = 'Отсутствуют права на запись файла';
				return FALSE;
			}

		} else {
			$this->errors = 'Файл не найден';
			return FALSE;
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
	function upload_remote_file($file, $remote_file, $mode = 0666){
		
		$server_data = $this->server_data;
		
		$connection = ftp_connect($server_data['ftp_host']);
		
		if(!$connection) {
			$this->errors = 'Ошибка соединения с ftp сервером';
			return FALSE;
		}
		
		if(ftp_login($connection, $server_data['ftp_login'], $server_data['ftp_passwd'])) {
			
			if (!ftp_put($connection, $remote_file, $file, FTP_BINARY)) {
				$this->errors = 'Ошибка записи файла';
				return FALSE;
			} else {
				@ftp_chmod($connection, $mode, $remote_file);
				return TRUE;
			}
		
		} else {
			$this->errors = 'Ошибка авторизации FTP';
			return FALSE;
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
	function write_remote_file($file, $data){
		
		$server_data = $this->server_data;
		
		// Определяем временный файл
		$temp_file = tempnam(sys_get_temp_dir(), basename($file));

		if (file_put_contents($temp_file, $data) === FALSE) {
			$this->errors = 'Ошибка записи временного файла';
			return FALSE;
		}
		
		if($this->upload_remote_file($temp_file, $file)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	// ----------------------------------------------------------------
	
    /**
     * Смена rcon пароля серверу
     * 
     * @param str - новый RCON пароль
     * @pararm arr -
     * 
     * @return bool
    */
    function change_rcon($new_rcon, $server_data = FALSE) 
    {
		$this->load->helper('serverinfo');
		
		if($server_data) {
			$this->server_data = $server_data;
		}
		
		/* Редактируем rcon в server.cfg */
		switch ($this->servers->server_data['engine']) {
			case 'GoldSource':
				$servercfg_file = 'server.cfg';
				break;
			case 'Source':
				$servercfg_file = 'cfg/server.cfg';
				break;
			default:
				return FALSE;
				break;
		}
		
		/* Получаем содержимое конфигурационного файла */
		if(!$this->servers->server_data['ds_id']){
			$file_contents = $this->servers->read_local_file($this->servers->server_data['local_path'] . '/' . 
					$this->servers->server_data['dir'] . '/' . 
					$this->servers->server_data['start_code'] . '/' . 
					$servercfg_file
			);
		}else{
			$file_contents = $this->servers->read_remote_file($this->servers->server_data['ftp_path'] . '/' . 
					$this->servers->server_data['dir'] . '/' . 
					$this->servers->server_data['start_code'] . '/' . 
					$servercfg_file
			);
		}
		
		/* Ошибка чтения, либо файл не найден */
		if(!$file_contents) {
			return FALSE;
		}
		
		$file_strings = explode("\n", $file_contents);
		
		$string_found = FALSE;
		$new_cfg_data = '';
		$i = 0;
		$count_i = count($file_strings);
		while($i < $count_i) {
			preg_match('/([\s]*)rcon_password([\s]*)([\"]?)(.*)(\"?)(\\\\?)(.*)/si', $file_strings[$i], $matches);
			
			/* Найдены совпадения, поэтому меняем ркон */
			if(!empty($matches)){
				$file_strings[$i] = 'rcon_password "' . $new_rcon . '"';
				$string_found = TRUE; // Строка rcon_password найдена
			}
			
			/* Записываем данные в переменную, которую потом запишем как новый конфиг */
			$new_cfg_data .= $file_strings[$i] . "\n";
			$i++;
		}
		
		if(!$string_found) {
			/* Строка rcon_password в конфиге не найдена,
			 * поэтому добавляем ее */
			 $new_cfg_data .= 'rcon_password "' . $new_rcon . "\"\n";
		}
		
		/* Записываем содержимое конфигурационного файла */
		if(!$this->servers->server_data['ds_id']) {
			$dir = $this->servers->server_data['local_path'] . '/' . $this->servers->server_data['dir'];
			$this->servers->write_local_file($this->servers->server_data['local_path'] . '/' . 
					$this->servers->server_data['dir'] . '/' . 
					$this->servers->server_data['start_code'] . '/' . 
					$servercfg_file,
					$new_cfg_data
			);
		} else {
			$dir = $this->servers->server_data['ftp_path'] . '/' . $this->servers->server_data['dir'];
			$this->servers->write_remote_file($this->servers->server_data['ftp_path'] . '/' . 
					$this->servers->server_data['dir'] . '/' . 
					$this->servers->server_data['start_code'] . '/' . 
					$servercfg_file, 
					$new_cfg_data
			);
		}
				
		/* Отправляем новый rcon пароль в консоль сервера*/
		if($this->servers->server_status($this->server_data['server_ip'], $this->server_data['server_port'])) {
			
			$this->load->model('valve_rcon');
			
			$rcon_connect = $this->valve_rcon->connect(
				$this->server_data['server_ip'], 
				$this->server_data['server_port'],
				$this->server_data['rcon'],
				$this->server_data['engine']
			);

			$this->valve_rcon->command('rcon_password ' . $new_rcon);
			
			
		}
		
		$this->server_data['rcon'] = $new_rcon;
		
		return TRUE;

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
    function get_server_settings($server_id, $user_id = FALSE)
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
    function set_server_settings($sett_id, $value, $server_id, $user_id = FALSE)
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
                return TRUE;
            }else{
                return FALSE;
            }
            
        }else{
			/* Привилегии нет в базе данных, создаем новую строку */
			if($this->db->insert('settings', $data)){
                return TRUE;
            }else{
                return FALSE;
            }
		}

            
    }
}
