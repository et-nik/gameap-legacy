<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

// ------------------------------------------------------------------------

/**
 * CRON модуль
 *
 * Позволяет выполнять задания, выполнять автоматические действия.
 * Такие как установка сервера, обновление, запуск, перезапуск.
 * Имеет функции для обеспечения безопасности - автоматическая смена ркон
 * пароля, если он не совпадает в админпанели и на сервере.
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.5.6
 */
 
/*
 * Для работы этого скрипта необходимо добавить в крон следующее задание
 * php -f /path/to/adminpanel/index.php cron
 * 
 * Cron модуль необходим для работы многих функций АдминПанели.
 * Лучше всего поставить выполнение модуля каждые 5 минут, 
 * но не реже раза в 10 минут.
 * 
*/
class Cron extends MX_Controller {

	var $servers_data = array();
	
	var $_cron_result = '';
	private $_commands_result = array();
	private $_install_result = '';

	public function __construct()
    {
        parent::__construct();
        
        /* Скрипт можно запустить только из командной строки (через cron) */
        if(php_sapi_name() != 'cli'){
			show_404();
		}

		$this->load->model('servers');
		$this->load->model('servers/dedicated_servers');
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');
		
		$this->load->driver('control');
		$this->load->driver('files');
		$this->load->driver('rcon');
		$this->load->driver('installer');
		
		$this->load->helper('ds');
		$this->load->helper('date');
		//~ $this->load->library('ssh');
		//~ $this->load->library('telnet');
		$this->load->library('query');

		set_time_limit(0);
		$this->load->database();
    }
    
    // -----------------------------------------------------------------------
	
	/**
	 * По ответу на команду, отправленную на физ. сервер получает
	 * понятный пользователю ответ.
	 * 
	 * Можно было бы применить switch case, это было бы удобнее, 
	 * но ответ может состоять более чем из одной строки
	 * 
	 */
	private function _get_message($response = '', $server_id = '')
	{
		if (strpos($response, 'Server is already running') !== false) {
			/* Сервер запущен ранее */
			$message = lang('server_command_server_is_already_running', site_url('server_command/restart/'. $server_id), site_url('server_command/stop/' . $server_id));
			
		} elseif(strpos($response, 'Server started') !== false) {
			/* Сервер успешно запущен */
			$message = lang('server_command_started');
			
		} elseif(strpos($response, 'Server not started') !== false) {
			// Неудачный запуск
			$message = lang('server_command_start_failed');
			
		} elseif(strpos($response, 'Coulnd\'t find a running server') !== false) {
			// Не найден запущенный сервер
			$message = lang('server_command_running_server_not_found');
			
		} elseif(strpos($response, 'Server restarted') !== false) {
			// Сервер перезапущен
			$message = lang('server_command_restarted');
			
		} elseif(strpos($response, 'Server not restarted') !== false) {
			// Сервер не перезапущен
			$message = lang('server_command_restart_failed');
			
		} elseif(strpos($response, 'Server stopped') !== false) {
			// Сервер остановлен
			$message = lang('server_command_stopped');
			
		} elseif(strpos($response, 'Server not stopped') !== false) {
			// Сервер не остановлен
			$message = lang('server_command_stop_failed');

		} else {
			// Команда отправлена
			$message = lang('server_command_cmd_sended');
		}
		
		return $message;
	}
    
    // ----------------------------------------------------------------
    
    /**
     * Получение консоли сервера
    */
    private function _get_console($server_id)
    {
		/*
		 * Список расширений php
		 */
		$ext_list = get_loaded_extensions();
		
		/* 
		 * Заданы ли данные SSH у DS сервера 
		 * 
		 * Если сервер является удаленным, используется telnet
		 * и заданы хост, логин и пароль то все впорядке,
		 * иначе отправляем пользователю сообщение
		 * 
		*/
		if($this->servers_data[$server_id]['ds_id'] 
		&& $this->servers_data[$server_id]['control_protocol'] == 'ssh'
		&& (!$this->servers_data[$server_id]['ssh_host']
			OR !$this->servers_data[$server_id]['ssh_login']
			OR !$this->servers_data[$server_id]['ssh_password']
			)
		){
			return;	
		}
		
		/*
		 * Есть ли модуль SSH
		 */
		if($this->servers_data[$server_id]['ds_id'] 
		&& $this->servers_data[$server_id]['control_protocol'] == 'ssh'
		&& (!in_array('ssh2', $ext_list))
		){
			return;	
		}
		
		
		/* 
		 * Заданы ли данные TELNET у DS сервера 
		 * 
		 * Если сервер является удаленным, используется telnet
		 * и заданы хост, логин и пароль то все впорядке,
		 * иначе отправляем пользователю сообщение
		 * 
		*/
		
		if($this->servers_data[$server_id]['ds_id'] 
		&& $this->servers_data[$server_id]['control_protocol'] == 'telnet'
		&& (!$this->servers_data[$server_id]['telnet_host']
			OR !$this->servers_data[$server_id]['telnet_login']
			OR !$this->servers_data[$server_id]['telnet_password']
			)
		){
			return;	
		}
		
		if(!$this->servers_data[$server_id]['script_get_console']) {
			return;
		}
		
		/* Директория в которой располагается сервер */
		$dir = $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'];

		$command = $this->servers->command_generate($this->servers_data[$server_id], 'get_console');
		
		try {
			$console = send_command($command, $this->servers->server_data);
			return $console;
		} catch (Exception $e) {
			$message = $e->getMessage();
			$this->_cmd_output('Get console error. Message: ' . $message);
			return;
		}
	}
    
    // ----------------------------------------------------------------
    
    /**
     * Получает информацию о сервере информации о котором 
     * еще нет в массиве $this->server_data
    */
    private function _get_server_data($server_id)
    {
		if(!array_key_exists($server_id, $this->servers_data)) {
			$this->servers_data[$server_id] = $this->servers->get_server_data($server_id);
		}

		return $this->servers_data[$server_id];
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Создает директорию на выделенном сервере
     * 
     * @param string
     * @param integer
     * @param bool
     * @param string
     * @return bool
    */
	private function _mkdir($server_id)
	{
		$commands = array();
		
		switch(strtolower($this->servers_data[$server_id]['os'])) {
			case 'windows':
				$commands[] = 'mkdir ' . $this->servers_data[$server_id]['dir'];
				break;

			default:
				$commands[] = 'mkdir -p ' . $this->servers_data[$server_id]['dir'];
				$commands[] = 'chmod 755 ' . $this->servers_data[$server_id]['dir'];
				
				break;
		}
		
		return send_command($commands, $this->servers_data[$server_id]);
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Отправляет сообщение в командную строку
     * 
     * @param string
    */
	private function _cmd_output($msg = '')
	{
		$this->_cron_result .= $msg . PHP_EOL;
		echo $msg . PHP_EOL;
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Загружает файлы на удаленный сервер
     * 
     * @param string
     * @param integer
     * @param bool
     * @param string
     * @return bool
    */
	private function _wget_files($server_id, $link, $rep_type = 'local')
	{
		$commands = array();
		
		if ($rep_type == 'local') {
			/* Локальный репозиторий */
			
			if ($this->servers_data[$server_id]['control_protocol'] != 'local') {
				
				/* Установка на удаленный сервер из локального репозитория 
				 * Имеется 2 варианта:
				 * 1. Используется локальный репозиторий на удаленной машине
				 * 2. Используется локальный репозиторий на локальной машине, загрузка происходит по FTP
				 * 
				 * 2й пока недоступен и возможно не будет доступен никогда, т.к. вырезан
				 */
				 
				switch (strtolower($this->servers_data[$server_id]['os'])) {
					case 'windows':
						$commands[] = 'copy ' . $link . ' ' . $this->servers_data[$server_id]['script_path'] . '\\' .$this->servers_data[$server_id]['dir'];
						break;

					default:
						$commands[] = 'cp ' . $link . ' ' . $this->servers_data[$server_id]['script_path'] . '/' .$this->servers_data[$server_id]['dir'];
						break;
				}

				/* Загрузка файлов по FTP */
				//~ $connection = @ftp_connect($this->servers_data[$server_id]['ftp_host']);
				//~ 
				//~ if (!$connection) {
					//~ return false;
				//~ }
				//~ 
				//~ if (!ftp_login($connection, $this->servers_data[$server_id]['ftp_login'], $this->servers_data[$server_id]['ftp_passwd'])) {
					//~ return false;
				//~ }
				//~ 
				//~ /* Загружаем файл на удаленный сервер */
				//~ $ftp_put_result = ftp_put(
					//~ $connection, 
					//~ $this->servers_data[$server_id]['ftp_path'] . '/' . $this->servers_data[$server_id]['dir'] . '/' . basename($link), 
					//~ $link, 
					//~ FTP_BINARY
				//~ );
				//~ 
				//~ if (!$ftp_put_result) {
					//~ ftp_close($connection);
					//~ return false;
				//~ }
				//~ 
				//~ ftp_close($connection);
				
			} else {
				/* Установка на локальный сервер */
				$commands = array();
				
				switch (strtolower($this->servers_data[$server_id]['os'])) {
					case 'windows':
						$commands[] = 'copy ' . $link . ' ' . $this->config->config['local_script_path'] . '\\' .$this->servers_data[$server_id]['dir'];
						break;

					default:
						$commands[] = 'cp ' . $link . ' ' . $this->config->config['local_script_path'] . '/' .$this->servers_data[$server_id]['dir'];
						break;
				} 
			}

			$this->_install_result .= send_command(
							$commands,
							$this->servers_data[$server_id], 
							$this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir']
			);
			
			return true;
			
		} elseif ($rep_type == 'remote') {
			/* Удаленный репозиторий */
			
			switch (strtolower($this->servers_data[$server_id]['os'])) {
				case 'windows':
					$commands[] = 'wget -c ' . $link;
					break;

				default:
					$commands[] = 'wget -c ' . $link . ' -o /tmp/wget.log ';
					break;
			}
			
			$this->_install_result .= send_command(
							$commands,
							$this->servers_data[$server_id], 
							$this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir']
			);
			return true;
			
		} else {
			$this->_cmd_output('Unknown repository type: ' . $rep_type);
		}

	}
	
	// ----------------------------------------------------------------
    
    /**
     * Распаковка архивов на выделенном сервере
     * 
     * @param integer
     * @param string
     * @return bool
    */
	private function _unpack_files($server_id, $pack_file)
	{
		$pathinfo = pathinfo($pack_file);
		
		switch (strtolower($this->servers_data[$server_id]['os'])) {
			case 'windows':
				$commands[] = '"%PROGRAMFILES%/7-Zip/7z.exe" x ' . basename($pack_file) . ' -aoa -o' . $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'] . ' && del /F ' . basename($pack_file);
				break;

			default:
			
				switch (strtolower($pathinfo['extension'])) {
					case 'xz':
						$commands[] = 'tar -xpvJf ' . basename($pack_file) . ' && rm ' . basename($pack_file);;
						break;
						
					case 'gz':
						$commands[] = 'tar -xvf ' . basename($pack_file) . ' && rm ' . basename($pack_file);;
						break;
					
					case 'bz2':
						$commands[] = 'tar -xvf ' . basename($pack_file) . ' && rm ' . basename($pack_file);;
						break;
						
					case 'tar':
						$commands[] = 'tar -xvf ' . basename($pack_file) . ' && rm ' . basename($pack_file);;
						break;
						
					default:
						$commands[] = 'unzip -o ' . basename($pack_file) . ' && rm ' . basename($pack_file);
						break;
				}
			
				break;
		}
		
		$this->_install_result .= send_command(
			$commands,
			$this->servers_data[$server_id], 
			$this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir']
		);
			
		return true;

	}

	// ----------------------------------------------------------------
    
    /**
     * Установка игрового сервера с помощью SteamCMD
    */
	private function _install_from_steamcmd($server_id)
	{
		/* Установка через SteamCMD */
		if (!isset($this->servers_data[$server_id])) {
			$this->_get_server_data($server_id);
		}

		//steamcmd +login anonymous +force_install_dir ../czero +app_set_config 90 mod czero +app_update 90 validate +quit
		$cmd['app'] = '';

		if($this->servers_data[$server_id]['app_set_config']) {
			$cmd['app'] .= '+app_set_config ' . $this->servers_data[$server_id]['app_set_config'] . ' ';
		}

		$cmd['app'] .= '+app_update ' . $this->servers_data[$server_id]['app_id'];

		$cmd['login'] = '+login anonymous';
		$cmd['install_dir'] = '+force_install_dir ' . $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'];

		switch(strtolower($this->servers_data[$server_id]['os'])) {
			case 'windows':
				$command = 'steamcmd.exe ' . $cmd['login'] . ' ' . $cmd['install_dir'] . ' ' . $cmd['app'] . ' validate +quit';
				break;

			default:
				$command = './steamcmd.sh ' . $cmd['login'] . ' ' . $cmd['install_dir'] . ' ' . $cmd['app'] . ' validate +quit';
				break;
		}
		
		
		$result = send_command($command, $this->servers_data[$server_id], $this->servers_data[$server_id]['steamcmd_path']);
		$this->_install_result .= $result;

		if(strpos($result, 'Success! App \'' . $this->servers_data[$server_id]['app_id'] . '\' fully installed.') !== false
			OR strpos($result, 'Success! App \'' . $this->servers_data[$server_id]['app_id'] . '\' already up to date.') !== false
		) {
			$server_data = array('installed' => '1');
			$server_installed = true;

			$log_data['msg'] = 'Update server success';
			$this->_cmd_output("Install server success");
			
			return true;
			
		} elseif(strpos($result, 'Failed to request AppInfo update') !== false) {
			/* Сервер не установлен до конца */
			$server_data = array('installed' => '0');

			$log_data['msg'] = 'Update server failed';
			$this->_cmd_output("Install server failure");
			
			return false;
		} elseif(strpos($result, 'Error! App \'' . $this->games->games_list[0]['app_id'] . '\' state is') !== false) {
			/* Сервер не установлен до конца */
			$server_data = array('installed' => '0');

			$log_data['msg'] = 'Error. App state after update job';
			$this->_cmd_output("Install server failure");
			
			return false;
		} else {
			/* Неизвестная ошибка */
			$server_data = array('installed' => '1');

			$log_data['msg'] = 'Unknown error';
			$this->_cmd_output("Install server failure");
			
			return false;
		}
	}

	// ----------------------------------------------------------------
    
    /**
     * Обрабатывает полученные данные статистики
    */
	private function _stats_processing($ds)
	{
		$control_protocol =& $ds['control_protocol'];

		if (strtolower($ds['os']) == 'windows') {
			 /* Для Windows будут следующие команды:
			 * 		Загруженность процессора:	wmic cpu get LoadPercentage
			 * 		Получение свободной памяти: wmic os get FreePhysicalMemory
			 * 		Получение всей памяти:		wmic os get TotalVisibleMemorySize 
			*/
			
			$this->control->set_data(array('os' => 'windows'));
			$this->control->set_driver($control_protocol);
			
			try {
				$this->control->connect($ds['control_ip'], $ds['control_port']);
				$this->control->auth($ds['control_login'], $ds['control_password']);
				
				$stats_string['cpu_load'] 		= $this->control->command('wmic cpu get LoadPercentage');
				$stats_string['free_memory'] 	= $this->control->command('wmic os get FreePhysicalMemory');
				$stats_string['total_memory'] 	= $this->control->command('wmic os get TotalVisibleMemorySize');
				
			} catch (Exception $e) {
				$message = $e->getMessage();
				$this->_cmd_output('Getting stats failure. Message: ' . $message);
				return false;
			}

			/* Обработака загруженности процессора */

			/* 
			 * Пример значения $stats['cpu_load_string']:
				LoadPercentage  
				16              
				0 
				
			 * LoadPercentage - ненужная намс строка, ее убираем
			 * 16 - загруженность первого ядра (%)
			 * 0 - загруженность второго ядра (%)
			 * 
			 * Загруженность процессора = суммарная загруженность ядер / количество ядер
			 * В данном случае загруженность процессора = 8%
			*/

			$explode = explode("\n", $stats_string['cpu_load']);
			unset($explode[0]);

			$a = 0; 		// Количество строк (количество ядер процессора + 1)
			$value = 0; 	// Сумма значений в строках (суммарная загруженность каждого ядра)
			foreach($explode as &$arr) {
				if (trim($arr) == '') { continue;}
				
				$arr = trim($arr);
				$arr = (int)$arr;
				$value = $value + $arr;
				$a ++;
			}
			
			if (!$a) {
				return false;
			}

			$stats['cpu_usage'] = (int) round($value/$a);

			/* Свободно памяти */
			$explode = explode("\n", $stats_string['free_memory']);
			unset($explode[0]);
			$free_memory = (int)trim($explode[1]);

			/* Всего памяти */
			$explode = explode("\n", $stats_string['total_memory']);
			unset($explode[0]);
			$total_memory = (int)trim($explode[1]);

			$usage_memory = $total_memory - $free_memory;
			$stats['memory_usage'] = (int)round(($usage_memory/$total_memory) * 100);

			if($stats['cpu_usage'] > 100) {$stats['cpu_usage'] = 100;}
			if($stats['memory_usage'] > 100) {$stats['memory_usage'] = 100;}

			return $stats;

		} else {
			
			/* Для Linux выполняются две команды
			 * 	1. 'top -b -n 2 | grep Cpu'
			 * параметру -n присвоена 2, т.к. на тестовом сервере Debian
			 * первое значение всегда было одинаковым вне зависимости
			 * от реальной нагрузки, видимо где-то кешируется.
			 * 
			 * 2. free
			 * Информация о памяти
			 * Можно было бы использовать одну команду с top, и парсить значения,
			 *  но с давных времен используется две.
			*/
			$this->control->set_data(array('os' => 'linux'));
			$this->control->set_driver($control_protocol);
			
			try {
				$this->control->connect($ds['control_ip'], $ds['control_port']);
				$this->control->auth($ds['control_login'], $ds['control_password']);
				
				$stats_string['cpu_load'] 		= $this->control->command('top -b -n 2 | grep Cpu');
				$stats_string['memory_usage'] 	= $this->control->command('free');
			} catch (Exception $e) {
				$message = $e->getMessage();
				$this->_cmd_output('Getting stats failure. Message: ' . $message);
				return false;
			}

			/* Использование процессора 
			 * 
			 * Ubuntu/CentOS -- Cpu(s): 24.0%us, 10.2%sy,  0.0%ni, 61.9%id,  3.8%wa,  0.0%hi,  0.1%si,  0.0%st
			 * Debian --		%Cpu(s):  1.5 us,  0.0 sy,  0.0 ni, 95.7 id,  0.0 wa,  0.0 hi,  0.0 si,  2.7 st
			 */

			$stats_explode = preg_replace('| +|', ' ', array_pop(explode("\n", trim($stats_string['cpu_load']))));
			$stats_explode = preg_replace('/[^0-9\s\.]/i', '', end(explode(':', $stats_explode)));
			$stats_explode = explode(' ',  str_replace('  ', ' ', trim($stats_explode)));
			
			$stats['cpu_usage'] = isset($stats_explode[3]) 
										? 100-(int)$stats_explode[3] 
										: false;

			/* Использование памяти 
			 *               total       used       free     shared    buffers     cached
				Mem:       3960788    3292828     667960          0     121120    1186676
				-/+ buffers/cache:    1985032    1975756
				Swap:      2008060          0    2008060

			 */
			$stats_explode = preg_replace('| +|', ' ', $stats_string['memory_usage']);
			$stats_explode = explode("\n", trim($stats_explode));
			$stats_explode = explode(' ', $stats_explode[1]);
			
			$stats['memory_usage'] = (isset($stats_explode[1]) &&  isset($stats_explode[2]) && isset($stats_explode[5])) 
										? (int)round((($stats_explode[2]-$stats_explode[5])/$stats_explode[1])*100) 
										: false;
			
			$stats['cpu_usage'] 	= ($stats['cpu_usage'] > 100) ? 100 : $stats['cpu_usage'];
			$stats['memory_usage'] 	= ($stats['memory_usage'] > 100) ? 100 : $stats['memory_usage'];

			return $stats;
		}
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Выполняет cron скрипты модулей
    */
	private function _modules_cron()
	{
		/* Массив с именами cron скриптов 
		 * нужен для записи выполненных скриптов
		 * В случае одинаковых имен в разных модулях, второй и 
		 * последующие скрипты будут пропущены, иначе появится
		 * ошибка об одинаковых классах.
		*/
		$array_scripts = array();
		
		foreach($this->gameap_modules->modules_data as &$value) {

			if ($value['short_name'] == 'cron') {
				/* Пропускает самого себя */
				continue;
			}
			
			if (!$value['cron_script']) {
				/* Скрипт не задан */
				continue;
			}
			
			$value['cron_script'] = str_replace('.php', '', $value['cron_script']);
			$value['cron_script'] = str_replace('..', '', $value['cron_script']);
			$value['short_name'] = str_replace('..', '', $value['short_name']);
			
			if ($value['cron_script'] == 'cron') {
				/* Нельзя запускать скрипты с именем cron */
				$this->_cmd_output("Script {$value['cron_script']} on module {$value['short_name']} omitted");
				continue;
			}
			
			if (in_array($value['cron_script'], $array_scripts)) {
				/* Нельзя запускать скрипты с именем cron */
				$this->_cmd_output("Script {$value['cron_script']} on module {$value['short_name']} omitted");
				continue;
			}
			
			if (!file_exists(APPPATH . 'modules/' . $value['short_name'] . '/controllers/' . $value['cron_script'] . '.php')) {
				/* Скрипт отсутствует */
				$this->_cmd_output("Script not found on {$value['short_name']} module");
				continue;
			}
			
			$array_scripts[] = $value['cron_script'];
			
			/* Выполняем cron скрипт из модуля */
			$this->_cmd_output("Start {$value['short_name']}");
			$result = modules::run($value['short_name'] . '/' . $value['cron_script'] . '/index');
			//~ $this->_cmd_output($result);
		}
	}

    
    // ----------------------------------------------------------------
    
    /**
     * Функция, выполняющаяся при запуске cron
    */
    public function index()
    {
		$time = time();
		$cron_stats = array(
			'success' => 0,
			'failed' => 0,
			'skipped' => 0,
		);

		$log_data['user_name'] = 'System (cron)';

		/* Получение заданий из базы данных 
		 * Задания ограничиваются последним часом, если по какой либо 
		 * причине задания двухчасовой давносте не были выполнены они не 
		 * будут выполнены вновь
		 * */
		$where = array('date_perform >' => $time - 3600, 'date_perform <' => $time, 'started' => 0);
		$query = $this->db->get_where('cron', $where);

		$task_list = $query->result_array();

		/*==================================================*/
		/*     Выполняем задания                            */
		/*==================================================*/

		$this->_cmd_output("== Task manager ==");

		$i = 0;
		$a = 0;
		$count_i = count($task_list);
		while($i < $count_i) {

			// Если достигнут предел выполняемых задания, то оставляем оставшиеся на потом
			if($i >= 100) {
				$cron_stats['skipped'] += $count_i - $i;
				break;
			}

			$cron_success = false;

			/* Проверяем дату, чтобы выполнить то что нужно
			 * Возможно задание уже было выполнено ранее*/
			if($task_list[$i]['date_performed'] > $task_list[$i]['date_perform']){
				$cron_stats['skipped'] ++;
				$i ++;
				continue;
			}

			/*
			 * Получение данных сервера
			*/
			if(isset($task_list[$i]['server_id'])) {

				$server_id = $task_list[$i]['server_id'];

				// Получение данных сервера
				$this->_get_server_data($server_id);

			} else {
				$cron_stats['skipped'] ++;
				$i ++;
				continue;
			}

			/* 
			 * Отправляем данны о том, что задание начало выполняться 
			 * чтобы исключить повторное выполнение при следующем запуске cron скрипта, 
			 * в случаях когда задание не завершилось 
			*/
			$this->db->where('id', $task_list[$i]['id']);
			$this->db->update('cron', array('started' => '1')); 

			// Выполняем задание
			switch($task_list[$i]['code']) {
				case 'server_start':
				
					try {
						$response = $this->servers->start($this->servers_data[$server_id]);
						$message = $this->_get_message($response);
						
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cmd_output('Task: server #' . $server_id . '  start success');

						// Сохраняем логи
						$log_data['type'] 		= 'server_command';
						$log_data['command'] 	= 'start';
						$log_data['server_id'] 	= $server_id;
						$log_data['msg'] 		= $message;
						$log_data['log_data'] 	= $response;
						$this->panel_log->save_log($log_data);
						
					} catch (Exception $e) {
						$cron_stats['failed'] ++;
						$message = $e->getMessage();
						$this->_cmd_output('Task: server #' . $server_id . '  start failed. ' . $message);

						// Сохраняем логи
						$log_data['type'] 			= 'server_command';
						$log_data['command'] 		= 'start';
						$log_data['server_id'] 		= $server_id;
						$log_data['msg'] 			= 'Start server Error';
						$log_data['log_data'] 		= $message . "\n" . get_last_command();
						$this->panel_log->save_log($log_data);
					}

					break;
					
				case 'server_stop':
				
					try {
						$response = $this->servers->stop($this->servers_data[$server_id]);
						$message = $this->_get_message($response);
						
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cmd_output('Task: server #' . $server_id . '  stop success');

						// Сохраняем логи
						$log_data['type'] 		= 'server_command';
						$log_data['command'] 	= 'stop';
						$log_data['server_id'] 	= $server_id;
						$log_data['msg'] 		= $message;
						$log_data['log_data'] 	= $response;
						$this->panel_log->save_log($log_data);
						
					} catch (Exception $e) {
						$cron_stats['failed'] ++;
						$message = $e->getMessage();
						$this->_cmd_output('Task: server #' . $server_id . '  stop failed. ' . $message);

						// Сохраняем логи
						$log_data['type'] 			= 'server_command';
						$log_data['command'] 		= 'stop';
						$log_data['server_id'] 		= $server_id;
						$log_data['msg'] 			= 'Stop server Error';
						$log_data['log_data'] 		= $message . "\n" . get_last_command();
						$this->panel_log->save_log($log_data);
					}
					
					break;
					
				case 'server_restart':
					try {
						$response = $this->servers->restart($this->servers_data[$server_id]);
						$message = $this->_get_message($response);
						
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cmd_output('Task: server #' . $server_id . '  restart success');

						// Сохраняем логи
						$log_data['type'] 		= 'server_command';
						$log_data['command'] 	= 'restart';
						$log_data['server_id'] 	= $server_id;
						$log_data['msg'] 		= $message;
						$log_data['log_data'] 	= $response;
						$this->panel_log->save_log($log_data);
						
					} catch (Exception $e) {
						$cron_stats['failed'] ++;
						$message = $e->getMessage();
						$this->_cmd_output('Task: server #' . $server_id . '  restart failed. ' . $message);

						// Сохраняем логи
						$log_data['type'] 			= 'server_command';
						$log_data['command'] 		= 'restart';
						$log_data['server_id'] 		= $server_id;
						$log_data['msg'] 			= 'Restart server Error';
						$log_data['log_data'] 		= $message . "\n" . get_last_command();
						$this->panel_log->save_log($log_data);
					}
					
					break;
					
				case 'server_update':
					try {
						$response = $this->servers->update($this->servers_data[$server_id]);
						$message = $this->_get_message($response);
						
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cmd_output('Task: server #' . $server_id . '  update success');

						// Сохраняем логи
						$log_data['type'] 		= 'server_command';
						$log_data['command'] 	= 'update';
						$log_data['server_id'] 	= $server_id;
						$log_data['msg'] 		= $message;
						$log_data['log_data'] 	= $response;
						$this->panel_log->save_log($log_data);
						
					} catch (Exception $e) {
						$cron_stats['failed'] ++;
						$message = $e->getMessage();
						$this->_cmd_output('Task: server #' . $server_id . '  update failed. ' . $message);

						// Сохраняем логи
						$log_data['type'] 			= 'server_command';
						$log_data['command'] 		= 'update';
						$log_data['server_id'] 		= $server_id;
						$log_data['msg'] 			= 'Update server Error';
						$log_data['log_data'] 		= $message . "\n" . get_last_command();
						$this->panel_log->save_log($log_data);
					}
					break;
					
				case 'server_rcon':
					if($this->servers->server_status($this->servers_data[$server_id]['server_ip'], $this->servers_data[$server_id]['query_port'])) {

						$this->rcon->set_variables(
								$this->servers_data[$server_id]['server_ip'], 
								$this->servers_data[$server_id]['server_port'],
								$this->servers_data[$server_id]['rcon'],
								$this->servers_data[$server_id]['engine'],
								$this->servers_data[$server_id]['engine_version']
						);

						$rcon_connect = $this->rcon->connect();

						if($rcon_connect) {
							$rcon_string = $this->rcon->command($task_list[$i]['command']);

							$cron_success = true;
							$cron_stats['success'] ++;

							$this->_cmd_output('Task: server #' . $server_id . '  rcon send success');

							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['command'] = $task_list[$i]['command'];
							$log_data['server_id'] = $server_id;
							$log_data['msg'] = 'Rcon command';
							$log_data['log_data'] = 'Rcon string: ' . $rcon_string;
							$this->panel_log->save_log($log_data);
						} else {
							$cron_stats['failed'] ++;

							$this->_cmd_output('Task: server #' . $server_id . '  rcon send failed');

							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['command'] = $task_list[$i]['command'];
							$log_data['server_id'] = $server_id;
							$log_data['msg'] = 'Rcon connect error';
							$log_data['log_data'] = '';
							$this->panel_log->save_log($log_data);
						}
					} else {
						$cron_stats['failed'] ++;

						$this->_cmd_output('Task: server #' . $server_id . '  rcon send failed');

						// Сохраняем логи
						$log_data['type'] = 'server_rcon';
						$log_data['command'] = $task_list[$i]['command'];
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Server is down';
						$log_data['log_data'] = '';
						$this->panel_log->save_log($log_data);
					}

					break;
				
				case 'delete_files':
					
						// Команда удаления
						switch(strtolower($this->servers->server_data['os'])) {
							case 'windows':
								$command = 'rmdir /S ' . $this->servers->server_data['dir'];
								break;
							default:
								// Linux
								$command = 'rm -rf ' . $this->servers->server_data['dir'];
								break;
						}
						
						$log_data['type'] = 'server_command';
						$log_data['command'] = $task_list[$i]['command'];
						$log_data['server_id'] = $server_id;
						
						try {
							// Остановка сервера
							$response = $this->servers->stop($this->servers_data[$server_id]);
							
							// Отправка команды удаления
							$result = send_command($command, $this->servers_data[$server_id]);
							
							$this->_cmd_output('Task: server #' . $server_id . '  delete success');
							$cron_stats['success'] ++;
							$log_data['msg'] = 'Delete successful';
							
							// Удаление задания
							$this->db->delete('cron', array('id' => $task_list[$i]['id'])); 
							
						} catch (Exception $e) {
							$this->_cmd_output('Task: server #' . $server_id . '  delete failed');
							$cron_stats['failed'] ++;
							$log_data['msg'] = 'Delete failed';
							$log_data['log_data'] = $e->getMessage();
						}
						
						$this->panel_log->save_log($log_data);
						
					break;
					
				default:
					$i ++;
					continue;
					break;

			}

			/* Задание было выполнено */
			$sql_data[$a]['id'] = $task_list[$i]['id'];
			$sql_data[$a]['started'] = '0';

			// Устанавливаем дату выполнения
			$sql_data[$a]['date_performed'] = $time;

			// Устанавливаем дату следующего выполнения
			$sql_data[$a]['date_perform'] = $task_list[$i]['date_perform'] + $task_list[$i]['time_add'];

			$i ++;
			$a ++;

		}

		// Обновляем данные
		if(!empty($sql_data)) {
			$this->db->update_batch('cron', $sql_data, 'id');
		}

		// Отображаем статистику заданий
		$this->_cmd_output("Success: {$cron_stats['success']} Failed: {$cron_stats['failed']} Skipped: {$cron_stats['skipped']}");

		/*==================================================*/
		/*    				БЕГУН					        */
		/*    Пробегаем по каждому серверу			        */
		/*==================================================*/

		$this->_cmd_output("== Runner ==");

		$this->servers->get_server_list(false, false, array('enabled' => '1'));

		$i = 0;
		$count_i = count($this->servers->servers_list);
		while($i < $count_i) {
			$server_id = $this->servers->servers_list[$i]['id'];
			
			// Костыль
			$this->games->get_games_list(array('code' => $this->servers->servers_list[$i]['game']));

			// Получение данных сервера
			$this->_get_server_data($server_id);

			/*==================================================*/
			/*     Установка сервера					        */
			/*==================================================*/

			if ($this->servers_data[$server_id]['installed'] == '0'
				OR $this->servers_data[$server_id]['installed'] == '3'
			) {
				// Сервер не установлен
				$this->_cmd_output("Server #" . $server_id . " not installed");
				$server_installed = false;

				// Данные лога установки
				$log = '';
				$this->_install_result = '';
				$this->control->clear_commands();
				
				/* Получение данных об игровой модификации */
				$this->game_types->get_gametypes_list(array('id' => $this->servers_data[$server_id]['game_type']));

				/*
				 * Полю installed устанавливаем значение 2, что сервер начал устанавливаться
				*/

				$this->servers->edit_game_server($server_id, array('installed' => '2'));
				
				try {
					$this->_mkdir($server_id);
				} catch (Exception $e) {
					$this->_cmd_output('Mkdir failed: '. $e->getMessage());
					$server_installed = false;
				}
				
				if ($this->games->games_list[0]['local_repository']) {
					/* Установка из локального репозитория */
					
					$this->_cmd_output("Install from local repository");
					
					try {
						$this->_wget_files($server_id, $this->games->games_list[0]['local_repository'], 'local');
						$this->_unpack_files($server_id, $this->games->games_list[0]['local_repository']);
						$server_installed = true;
					} catch (Exception $e) {
						$this->_cmd_output("Install from local repository failed. Message: " . $e->getMessage());
						$server_installed = false;
					}
					
				} elseif ($this->games->games_list[0]['remote_repository']) {
					/* Установка из удаленного репозитория */
					
					$this->_cmd_output("Install from remote repository");
					
					try {
						$this->_wget_files($server_id, $this->games->games_list[0]['remote_repository'], 'remote');
						$this->_unpack_files($server_id, $this->games->games_list[0]['remote_repository']);
						$server_installed = true;
					} catch (Exception $e) {
						$this->_cmd_output("Install from remote repository failed. Message: " . $e->getMessage());
						$server_installed = false;
					}

				} elseif ($this->games->games_list[0]['app_id']) {
					/* Установка через SteamCMD */
					
					$this->_cmd_output("Install from SteamCMD");
					
					try {
						$server_installed = $this->_install_from_steamcmd($server_id);
					} catch (Exception $e) {
						$this->_cmd_output("Install from steamcmd failed. Message: " . $e->getMessage());
						$server_installed = false;
					}
					
				} else {
					/* 
					 * Не удалость выбрать тип установки 
					 * отсутствуют данные локального репозитория, удаленного репозитория и steamcmd
					 */
					$log .= "App_id and Repository data not specified \n";
					$this->_cmd_output("Server #" . $server_id . " install failed. App_id and Repository data not specified");
					$server_installed = false;
				}
				
				/* 
				 * Завершение установки.
				 * Установка прав на директории, задание ркон пароля
				*/
				if ($server_installed == true) {
					/* Загружаем дополнительный файлы игровой модификации */
					if (isset($this->game_types->game_types_list[0]['local_repository'])
						&& $this->game_types->game_types_list[0]['local_repository']
					) {
						
						try {
							$this->_wget_files($server_id, $this->game_types->game_types_list[0]['local_repository'], 'local');
							$this->_unpack_files($server_id, $this->game_types->game_types_list[0]['local_repository']);
						} catch (Exception $e) {
							$this->_cmd_output('Install modification from local repository failed. Message: ' . $e->getMessage());
						}
						
					} elseif (isset($this->game_types->game_types_list[0]['remote_repository'])
								&& $this->game_types->game_types_list[0]['remote_repository']
					) {
						
						try {
							$this->_wget_files($server_id, $this->game_types->game_types_list[0]['remote_repository'], 'remote');
							$this->_unpack_files($server_id, $this->game_types->game_types_list[0]['remote_repository']);
						} catch (Exception $e) {
							$this->_cmd_output('Install modification from remote repository failed. Message: ' . $e->getMessage());
						}
					}
					
					/* Устанавливаем 777 права на директории, в которые загружается контент (карты, модели и пр.)
					* и 666 на конфиг файлы, которые можно редактировать через админпанель */
					if(strtolower($this->servers_data[$server_id]['os']) != 'windows') {
						$config_files 	= json_decode($this->servers_data[$server_id]['config_files'], true);
						$content_dirs 	= json_decode($this->servers_data[$server_id]['content_dirs'], true);
						$log_dirs 		= json_decode($this->servers_data[$server_id]['log_dirs'], true);
						$command = array();

						if($config_files) {
							foreach($config_files as $file) {
								$command[] = 'chmod 666 ' . './' . $this->servers_data[$server_id]['dir'] . '/' . $file['file'];
								$log .= 'chmod 666 ' . './' . $this->servers_data[$server_id]['dir'] . '/' .  $file['file'] . "\n";
							}
						}
						
						if($content_dirs) {
							foreach($content_dirs as $dir) {
								$command[] = 'find ' . $this->servers_data[$server_id]['dir'] . '/' . $dir['path'] . ' -type d -exec chmod 777 {} \\;';
								$log .= 'find ' . $this->servers_data[$server_id]['dir'] . '/' . $dir['path'] . ' -type d -exec chmod 777 {} \\;';
							}
						}
						
						if($log_dirs) {
							foreach($log_dirs as $dir) {
								$command[] = 'find ' . $this->servers_data[$server_id]['dir'] . '/' . $dir['path'] . ' -type d -exec chmod 777 {} \\;';
								$log .= 'find ' . $this->servers_data[$server_id]['dir'] . '/' . $dir['path'] . ' -type d -exec chmod 777 {} \\;';
							}
						}
						
						if ($this->servers_data[$server_id]['su_user'] != '') {
							$command[] = 'chown -R ' . $this->servers_data[$server_id]['su_user'] . ' ' . $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'];
							$log .= 'chown -R ' . $this->servers_data[$server_id]['su_user'] . ' ' . $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'] . "\n";;
						}
						
						try {
							$log .= "\n---\nCHMOD\n" . $log . "\n" .  send_command($command, $this->servers_data[$server_id]);
						} catch (Exception $e) {
							$this->_cmd_output('CHMOD failed. Message: ' . $e->getMessage());
							$log .= $e->getMessage() . "\n";;
						}
					}


					/* Устанавливаем серверу rcon пароль */
					$this->load->helper('safety');
					$new_rcon = generate_code(8);
					
					try {
						$this->servers->change_rcon($new_rcon, $this->servers_data[$server_id]);
					} catch (Exception $e) {
						$this->_cmd_output('Rcon set failed. Message: ' . $e->getMessage());
					}
					
					/* Конфигурирование сервера */
					$this->installer->set_game_variables($this->servers_data[$server_id]['start_code'], 
													$this->servers_data[$server_id]['engine'],
													$this->servers_data[$server_id]['engine_version']
					);
					
					$this->installer->set_os($this->servers_data[$server_id]['os']);
					$this->installer->server_data = $this->servers_data[$server_id];
					
					// Правка конфигов
					try {
						$this->installer->change_config();
					} catch (Exception $e) {
						$this->_cmd_output('Change config failed. Message: ' . $e->getMessage());
					}
					
					$aliases_values = array();
					$aliases_values = json_decode($this->servers_data[$server_id]['aliases'], true);

					$server_data['installed'] 		= 1;
					$server_data['rcon']			= $new_rcon;
					$server_data['aliases'] 		= json_encode($this->installer->get_default_parameters($aliases_values));
					
					if (!$this->servers_data[$server_id]['start_command']) {
						$server_data['start_command'] 	= $this->installer->get_start_command();
					}
					
					// Путь к картам
					$server_data['maps_path'] = $this->installer->get_maps_path();
					
					// Список портов
					$ports = $this->installer->get_ports();
					
					$server_data['query_port'] = $ports[1];
					$server_data['rcon_port'] = $ports[2];
					unset($ports);
					
					$this->servers->edit_game_server($server_id, $server_data);
					
					$log_data['type'] = 'server_command';
					$log_data['command'] = 'install';
					$log_data['server_id'] = $server_id;
					$log_data['msg'] = 'Server install successful';
					$log_data['log_data'] = "Results:" . PHP_EOL . var_export($this->control->get_commands_result(), true) . PHP_EOL;
					$this->panel_log->save_log($log_data);
					
					$this->_cmd_output('Server install #' . $server_id . ' success');

				} else {
					
					$server_data = array('installed' => '0');
					$this->servers->edit_game_server($server_id, $server_data);

					$log_data['type'] = 'server_command';
					$log_data['command'] = 'install';
					$log_data['server_id'] = $server_id;
					$log_data['msg'] = 'Server install failed';
					$log_data['log_data'] = "Results:" . PHP_EOL . var_export($this->control->get_commands_result(), true) . PHP_EOL;
					$this->panel_log->save_log($log_data);
					
					$this->_cmd_output('Server install #' . $server_id . ' failed');
				}

				$i ++;
				continue;

			}

			$this->servers->get_server_settings($server_id);

			/*==================================================*/
			/*     Перезапуск сервера в случае падения          */
			/*==================================================*/

			/* В настройках указано, что сервер перезапускать не нужно */
			if($this->servers->server_settings['SERVER_AUTOSTART']) {

				/* Получение id игры в массиве */
				$a = 0;
				$count = count($this->games->games_list);
				while($a < $count) {
					if ($this->servers->servers_list[$i] == $this->games->games_list[$a]['code']) {
						$game_arr_id = $a;
						break;
					}
					$a++;
				}

				// Проверка статуса сервера
				$status = $this->servers->server_status($this->servers_data[$server_id]['server_ip'], $this->servers_data[$server_id]['query_port'], $this->servers_data[$server_id]['engine'], $this->servers_data[$server_id]['engine_version']);
				
				/* Повторная проверка, контрольная.
				 * Бывали случаи, что сервер перезагружался, даже если нормально работал */
				if (!$status) {
					sleep(3);
					$status = $this->servers->server_status($this->servers_data[$server_id]['server_ip'], $this->servers_data[$server_id]['query_port'], $this->servers_data[$server_id]['engine'], $this->servers_data[$server_id]['engine_version']);
				}
				
				if(!$status) {
					/* Смотрим данные предыдущих проверок, если сервер был в оффе, то запускаем его */

					/* Получение данных проверки крона из логов */
					$where = array('date >=' => $time - 780,  'type' => 'cron_check', 'command' => 'server_status', 'server_id' => $server_id, 'log_data' => 'Server is down');
					$logs = $this->panel_log->get_log($where); // Логи сервера в админпанели

					$response = false;

					if(count($logs) >= 1) {
						/* Перед запуском получаем консоль, чтобы знать от чего сервер упал */
						$console_data = $this->_get_console($server_id);
						
						/* При последней проверке сервер был оффлайн, запускаем его*/
						try {
							$response = $this->servers->start($this->servers_data[$server_id]);

							$log_data['command'] = 'start';
							$log_data['msg'] = 'Start server success';
						} catch (Exception $e) {
							$response = false;
							$this->_cmd_output('Start server #' . $server_id . ' failed. Message: ' . $e->getMessage());
							$log_data['command'] = 'start';
							$log_data['msg'] = 'Start server failed';
						}

						if(strpos($response, 'Server is already running') !== false) {
							/* Сервер запущен, видимо завис */
							try {
								$response = $this->servers->restart($this->servers_data[$server_id]);
								$log_data['command'] = 'restart';

								if(strpos($response, 'Server restarted') !== false) {
									$log_data['msg'] = 'Restart server success';	
								}
							} catch (Exception $e) {
								$response = false;
								$this->_cmd_output('Restart server #' . $server_id . ' failed. Message: ' . $e->getMessage());
							}

						}
					}

					if($response) {
						$response .= "\nConsole:\n" . $console_data;
						
						// Записываем лог запуска
						$log_data['type'] = 'server_command';
						$log_data['server_id'] = $server_id;
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
					}

					// Записываем лог проверки
					$log_data['type'] = 'cron_check';
					$log_data['command'] = 'server_status';
					$log_data['server_id'] = $server_id;
					$log_data['log_data'] = 'Server is down';
					$this->panel_log->save_log($log_data);
				}
			}

			/*==================================================*/
			/*     Смена rcon пароля					         */
			/*==================================================*/

			if($this->servers->server_settings['SERVER_RCON_AUTOCHANGE']) {

				if($this->servers->server_status($this->servers_data[$server_id]['server_ip'], $this->servers_data[$server_id]['server_port'], $this->servers_data[$server_id]['engine'], $this->servers_data[$server_id]['engine_version'])) {

					$this->rcon->set_variables(
							$this->servers_data[$server_id]['server_ip'], 
							$this->servers_data[$server_id]['server_port'],
							$this->servers_data[$server_id]['rcon'],
							$this->servers_data[$server_id]['engine'],
							$this->servers_data[$server_id]['engine_version']
					);

					$rcon_connect = $this->rcon->connect();

				} else {
					$rcon_connect = false;
				}

				if($rcon_connect) {
					$rcon_string = $this->rcon->command('status');

					$rcon_string = trim($rcon_string);

					if(strpos($rcon_string, 'Bad rcon_password.') !== false) {

						$this->load->helper('safety');

						$new_rcon = generate_code(8);
						
						$this->servers->server_data = $this->servers_data[$server_id];
						
						try {
							$this->servers->change_rcon($new_rcon, $this->servers_data[$server_id]);
							
							// Меняем пароль в базе
							$sql_data['rcon'] = $new_rcon;
							$this->servers->edit_game_server($server_id, $sql_data);
							
							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['command'] = 'rcon_password';
							$log_data['server_id'] = $server_id;
							$log_data['msg'] = 'Change rcon password';
							$log_data['log_data'] = 'Rcon command: rcon_password ' . $new_rcon . "\n";
							$this->panel_log->save_log($log_data);
							
						} catch (Exception $e) {
							$this->_cmd_output('Rcon change on server #' . $server_id . ' failed. Message: ' . $e->getMessage());
						}

						// Перезагружаем сервер
						try {
							$response = $this->servers->restart($this->servers_data[$server_id]);
							$log_data['msg'] = 'Restart server success';
							$log_data['log_data'] = $response;
						} catch (Exception $e) {
							$log_data['msg'] = 'Restart server failed';
							$this->_cmd_output('Restart server #' . $server_id . ' failed. Message: ' . $e->getMessage());
						}

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['server_id'] = $server_id;
						$this->panel_log->save_log($log_data);

						$cron_stats['success'] ++;	
					} else {
						$cron_stats['skipped'] ++;
					}


				} else {
					// Не удалось соединиться с сервером, либо он выключен
					$cron_stats['failed'] ++;
				}

			} else {
				$cron_stats['skipped'] ++;
			}

			$i ++;
		}

		/*==================================================*/
		/*    	СТАТИСТИКА ВЫДЕЛЕННОГО СЕРВЕРА  			*/
		/*==================================================*/

		$this->_cmd_output("== DS Stats ==");
		$this->dedicated_servers->get_ds_list();
		
		if (!empty($this->dedicated_servers->ds_list)) {
			foreach($this->dedicated_servers->ds_list as $ds) {

				if (!$stats = $this->_stats_processing($ds)) {
					$this->_cmd_output('Stats server #' . $ds['id'] . ' failed');
					continue;
				}
				
				if(isset($stats['cpu_usage']) && isset($stats['memory_usage'])) {
					$this->_cmd_output('Stats server #' . $ds['id'] . ' successful');
				} else {
					$this->_cmd_output('Stats server #' . $ds['id'] . ' failed');
					continue;
				}

				/* 
				 * Обновляем статистику
				 * Добавляем новое значение в существующий массив
				 * date - дата проверки (unix time)
				 * cpu_usage - использование cpu (%)
				 * memory_usage - использование памяти (%)
				*/

				$stats_array = json_decode($ds['stats'], true);
				$stats_array OR $stats_array = array();
				
				$stats_array = array_slice($stats_array, -40);

				$stats_array[] = array('date' => $time, 'cpu_usage' => $stats['cpu_usage'], 'memory_usage' => $stats['memory_usage']);
				$data['stats'] = json_encode($stats_array);
				$this->dedicated_servers->edit_dedicated_server($ds['id'], $data);
			}
		}
		
		/*==================================================*/
		/*    	ВЫПОЛНЕНИЕ CRON СКРИПТОВ ИЗ МОДУЛЕЙ			*/
		/*==================================================*/
		
		$this->_cmd_output("== Modules cron ==");
		
		/* Чтобы данные выполнения пользовательского крона выводились правильно
		 * и на своем месте, то записываем весь вывод предыдущих задач 
		 * а после этого запускаем пользовательский крон
		*/
		$this->_modules_cron();
		
		// Очистка старых cron логов (старше 3 дня)
		$this->db->delete('logs', array('date <' => now() - 86400*3, 'type' => 'cron'));
		
		$this->_cmd_output("Cron end");

		$log_data['type'] = 'cron';
		$log_data['command'] = 'cron work';
		$log_data['msg'] = 'Cron end working';
		$log_data['log_data'] = $this->_cron_result;
		$this->panel_log->save_log($log_data);

	}

}
