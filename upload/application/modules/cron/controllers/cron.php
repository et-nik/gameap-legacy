<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.5.6
 */
 
/*
 * Для работы этого скрипта необходимо добавить в крон следующее задание
 * php -f /path/to/adminpanel/index.php cron
 * 
 * Cron модуль необходим для работы многих функций АдминПанели.
 * Лучше всего поставить выполнение модуля каждый 5 минут, 
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
        
        /* Скрипт можно запустить только из командной строки (через cron)*/
        if(php_sapi_name() != 'cli'){
			show_404();
		}

		$this->load->model('servers');
		$this->load->model('servers/dedicated_servers');
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');
		$this->load->driver('rcon');
		$this->load->library('ssh');
		$this->load->library('telnet');

		set_time_limit(0);
		$this->load->database();
    }
    
    // ----------------------------------------------------------------
    
    /**
     * Получает информацию о сервере информации о котором 
     * еще нет в массиве $this->server_data
    */
    function _get_server_data($server_id)
    {
		if(!array_key_exists($server_id, $this->servers_data)) {
			$this->servers_data[$server_id] = $this->servers->get_server_data($server_id);

			$this->servers_data[$server_id]['app_id'] = $this->games->games_list[0]['app_id'];
			$this->servers_data[$server_id]['app_set_config'] = $this->games->games_list[0]['app_set_config'];
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
	function _mkdir($server_id)
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
			
		if ($this->servers->command($commands, $this->servers_data[$server_id])) {
			return true;
		} else {
			return false;
		}
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
	function _wget_files($server_id, $link, $rep_type = 'local')
	{
		$commands = array();

		if ($rep_type == 'local') {
			
			/* Загружаем данные на сервер по FTP */
			if ($this->servers_data[$server_id]['control_protocol'] != 'local') {
				$connection = @ftp_connect($this->servers_data[$server_id]['ftp_host']);
				
				if (!$connection) {
					return false;
				}
				
				if (!ftp_login($connection, $this->servers_data[$server_id]['ftp_login'], $this->servers_data[$server_id]['ftp_passwd'])) {
					return false;
				}
				
				/* Загружаем файл на удаленный сервер */
				$ftp_put_result = ftp_put(
					$connection, 
					$this->servers_data[$server_id]['ftp_path'] . '/' . $this->servers_data[$server_id]['dir'] . '/' . basename($link), 
					$link, 
					FTP_BINARY
				);
				
				if (!$ftp_put_result) {
					ftp_close($connection);
					return false;
				}
				
				ftp_close($connection);
				
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
			
			$this->_install_result .= $this->servers->command($commands, $this->servers_data[$server_id], $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir']);
			
			return true;

		} elseif ($rep_type == 'remote') {
			
			switch (strtolower($this->servers_data[$server_id]['os'])) {
				case 'windows':
					$command = 'wget ' . $link;
					break;

				default:
					$command = 'wget ' . $link;
					break;
			}
			
			$this->_install_result .= $this->servers->command(
										$command,
										$this->servers_data[$server_id], 
										$this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir']
			);
			
			//~ if ($result) {
				//~ return true;
			//~ }
			return true;

		} else {
			return false;
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
	function _unpack_files($server_id, $pack_file)
	{
		switch (strtolower($this->servers_data[$server_id]['os'])) {
			case 'windows':
				$commands[] = '"%PROGRAMFILES%/7-Zip/7z.exe" x ' . basename($pack_file) . ' -o' . $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'] . ' && del /F ' . basename($pack_file);
				break;

			default:
				$commands[] = 'unzip -o ' . basename($pack_file) . ' && rm ' . basename($pack_file);
				break;
		}

		$result .= $this->servers->command(
									$commands,
									$this->servers_data[$server_id], 
									$this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir']
		);
		
		if ($result) {
			$this->_install_result .= $result;
			return true;
		}
	}

	// ----------------------------------------------------------------
    
    /**
     * Установка игрового сервера с помощью SteamCMD
    */
	function _install_from_steamcmd($server_id)
	{
		/* Установка через SteamCMD */

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
		
		$result = $this->servers->command($command, $this->servers_data[$server_id], $this->servers_data[$server_id]['steamcmd_path']);
		
		$this->_install_result .= $result;

		if(strpos($result, 'Success! App \'' . $this->servers_data[$server_id]['app_id'] . '\' fully installed.') !== false
			OR strpos($result, 'Success! App \'' . $this->servers_data[$server_id]['app_id'] . '\' already up to date.') !== false
		) {
			$server_data = array('installed' => '1');
			$server_installed = true;

			$log_data['msg'] = 'Update server success';
			$this->_cron_result .= "Install server success\n";
			
			return true;
			
		} elseif(strpos($result, 'Failed to request AppInfo update') !== false) {
			/* Сервер не установлен до конца */
			$server_data = array('installed' => '0');

			$log_data['msg'] = 'Update server failed';
			$this->_cron_result .= "Install server failure\n";
			
			return false;
		} elseif(strpos($result, 'Error! App \'' . $this->games->games_list[0]['app_id'] . '\' state is') !== false) {
			/* Сервер не установлен до конца */
			$server_data = array('installed' => '0');

			$log_data['msg'] = 'Error. App state after update job';
			$this->_cron_result .= "Install server failure\n";
			
			return false;
		} else {
			/* Неизвестная ошибка */
			$server_data = array('installed' => '1');

			$log_data['msg'] = 'Unknown error';
			$command = array_pop($this->servers->commands);
			$this->_cron_result .= "Install server failure\n";
			
			return false;
		}
	}

	// ----------------------------------------------------------------
    
    /**
     * Обрабатывает полученные данные статистики
    */
	function _stats_processing($ds)
	{
		// Определение протокола управления
		switch (strtolower($ds['control_protocol'])) {
			case 'ssh':
				$control_protocol = 'ssh';
				break;

			case 'telnet':
				$control_protocol = 'telnet';
				break;

			case 'local':
				$control_protocol = 'local';
				break;

			default:
				if ($ds['os'] == 'windows') {
					$control_protocol = 'telnet';
				} else {
					$control_protocol = 'ssh';
				}
				break;

		}

		/* 
		 * Для Windows будут следующие команды:
		 * 		Загруженность процессора:	wmic cpu get LoadPercentage
		 * 		Получение свободной памяти: wmic os get FreePhysicalMemory
		 * 		Получение всей памяти:		wmic os get TotalVisibleMemorySize 
		 * 
		 * Для Linux команды будут следующими:
		 * 									vmstats
		*/
		if (strtolower($ds['os']) == 'windows') {

			if ($control_protocol == 'telnet') {
				$telnet_data = explode(':', $ds['telnet_host']);

				$telnet_ip = $telnet_data['0'];
				$telnet_port = 23;

				if (isset($telnet_data['1'])) {
					$telnet_port = $telnet_data['1'];
				}

				$this->telnet->connect($telnet_ip, $telnet_port);
				$this->telnet->auth($ds['telnet_login'], $ds['telnet_password']);

				$stats_string['cpu_load'] = $this->telnet->command('wmic cpu get LoadPercentage');
				$stats_string['free_memory'] = $this->telnet->command('wmic os get FreePhysicalMemory');
				$stats_string['total_memory'] = $this->telnet->command('wmic os get TotalVisibleMemorySize');
			} elseif ($control_protocol == 'local') {
				$stats_string['cpu_load'] = shell_exec('wmic cpu get LoadPercentage');
				$stats_string['free_memory'] = shell_exec('wmic os get FreePhysicalMemory');
				$stats_string['total_memory'] = shell_exec('wmic os get TotalVisibleMemorySize');
			} else {
				$ssh_data = explode(':', $ds['ssh_host']);

				$ssh_ip = $ssh_data['0'];
				$ssh_port = 22;

				if (isset($ssh_data['1'])) {
					$ssh_port = $ssh_data['1'];
				}

				$this->ssh->connect($ssh_ip, $ssh_port);
				$this->ssh->auth($ds['ssh_login'], $ds['ssh_password']);

				$stats_string['cpu_load'] = $this->ssh->command('wmic cpu get LoadPercentage');
				$stats_string['free_memory'] = $this->ssh->command('wmic os get FreePhysicalMemory');
				$stats_string['total_memory'] = $this->ssh->command('wmic os get TotalVisibleMemorySize');
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

			$stats['cpu_usage'] = (int)round($value/$a);

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
			if ($control_protocol == 'telnet') {
				$telnet_data = explode(':', $ds['telnet_host']);

				$telnet_ip = $telnet_data['0'];
				$telnet_port = 23;

				if (isset($telnet_data['1'])) {
					$telnet_port = $telnet_data['1'];
				}

				$this->telnet->connect($telnet_ip, $telnet_port);
				$this->telnet->auth($ds['telnet_login'], $ds['telnet_password']);

				$stats_string['cpu_load'] = $this->telnet->command('vmstat');
				$stats_string['memory_usage'] = $this->telnet->command('free');
			} elseif ($control_protocol == 'local') {
				$stats_string['cpu_load'] = shell_exec('vmstat');
				$stats_string['memory_usage'] = shell_exec('free');
			} else {
				$ssh_data = explode(':', $ds['ssh_host']);

				$ssh_ip = $ssh_data['0'];
				$ssh_port = 22;

				if (isset($ssh_data['1'])) {
					$ssh_port = $ssh_data['1'];
				}

				$this->ssh->connect($ssh_ip, $ssh_port);
				$this->ssh->auth($ds['ssh_login'], $ds['ssh_password']);

				$stats_string['cpu_load'] = $this->ssh->command('vmstat');
				$stats_string['memory_usage'] = $this->ssh->command('free');
			}

			/* Использование процессора */
			$stats_explode = preg_replace('| +|', ' ', array_pop(explode("\n", trim($stats_string['cpu_load']))));
			$stats_explode = explode(' ', trim($stats_explode));
			$stats['cpu_usage'] = (int)$stats_explode[12] + $stats_explode[13];

			/* Использование памяти */
			$stats_explode = preg_replace('| +|', ' ', $stats_string['memory_usage']);
			$stats_explode = explode("\n", trim($stats_explode));
			$stats_explode = explode(' ', $stats_explode[1]);
			$stats['memory_usage'] = (int)round(($stats_explode[2]/$stats_explode[1])*100);

			if($stats['cpu_usage'] > 100) {$stats['cpu_usage'] = 100;}
			if($stats['memory_usage'] > 100) {$stats['memory_usage'] = 100;}

			return $stats;
		}
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Выполняет cron скрипты модулей
    */
	function _modules_cron()
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
				$this->_cron_result .= "Script {$value['cron_script']} on module {$value['short_name']} omitted\n";
				continue;
			}
			
			if (in_array($value['cron_script'], $array_scripts)) {
				/* Нельзя запускать скрипты с именем cron */
				$this->_cron_result .= "Script {$value['cron_script']} on module {$value['short_name']} omitted\n";
				continue;
			}
			
			if (!file_exists(APPPATH . 'modules/' . $value['short_name'] . '/controllers/' . $value['cron_script'] . '.php')) {
				/* Скрипт отсутствует */
				$this->_cron_result .= "Script not found on {$value['short_name']} module\n";
				continue;
			}
			
			$array_scripts[] = $value['cron_script'];
			
			//~ $this->_cron_result .= $value['short_name'] . " cron started\n";
			
			/* Выполняем cron скрипт из модуля */
			$this->_cron_result .= modules::run($value['short_name'] . '/' . $value['cron_script'] . '/index');
		}
	}

    
    // ----------------------------------------------------------------
    
    /**
     * Функция, выполняющаяся при запуске cron
    */
    public function index()
    {
		$time = time();
		$this->_cron_result = '';
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
		$where = array('date_perform >' => $time - 3600, 'date_perform <' => $time);
		$query = $this->db->get_where('cron', $where);

		$task_list = $query->result_array();

		/*==================================================*/
		/*     Выполняем задания                            */
		/*==================================================*/

		$this->_cron_result .= "== Task manager ==\n";

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

					if($response = $this->servers->start($this->servers_data[$server_id])){
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  start success' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'start';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Start server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);

					}else{

						$cron_stats['failed'] ++;
						$this->_cron_result .= 'Task: server #' . $server_id . '  start failed' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'start';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Start server Error';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
					}
					break;
				case 'server_stop':
					if($response = $this->servers->stop($this->servers_data[$server_id])){
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  stop success' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'stop';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Stop server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);

					}else{
						$cron_stats['failed'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  stop failed' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'stop';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Sop server Error';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
					}
					break;
				case 'server_restart':
					if($response = $this->servers->restart($this->servers_data[$server_id])){
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  restart success' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Restart server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);

					}else{
						$cron_stats['failed'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  restart failed' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Restart server Error';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
					}
					break;
				case 'server_update':
					if($response = $this->servers->update($this->servers_data[$server_id])) {
						$cron_success = true;
						$cron_stats['success'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  update success' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'update';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Update server success';
						$log_data['log_data'] = 'Command: ' . array_pop($this->servers->commands) . "\nResponse: \n" . $response;
						$this->panel_log->save_log($log_data);

					} else {
						$cron_stats['failed'] ++;

						$this->_cron_result .= 'Task: server #' . $server_id . '  update failed' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'update';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Update server Error';
						$log_data['log_data'] = $response;
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

							$this->_cron_result .= 'Task: server #' . $server_id . '  rcon send success' . "\n";

							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['command'] = $task_list[$i]['command'];
							$log_data['server_id'] = $server_id;
							$log_data['msg'] = 'Rcon command';
							$log_data['log_data'] = 'Rcon string: ' . $rcon_string;
							$this->panel_log->save_log($log_data);
						} else {
							$cron_stats['failed'] ++;

							$this->_cron_result .= 'Task: server #' . $server_id . '  rcon send failed' . "\n";

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

						$this->_cron_result .= 'Task: server #' . $server_id . '  rcon send failed' . "\n";

						// Сохраняем логи
						$log_data['type'] = 'server_rcon';
						$log_data['command'] = $task_list[$i]['command'];
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Server is down';
						$log_data['log_data'] = '';
						$this->panel_log->save_log($log_data);
					}

					break;
				default:
					$i ++;
					continue;
					break;

			}

			/* Задание было успешно выполнено */
			if($cron_success) {
				$sql_data[$a]['id'] = $task_list[$i]['id'];
				$sql_data[$a]['started'] = '0';

				// Устанавливаем дату выполнения
				$sql_data[$a]['date_performed'] = $time;

				// Устанавливаем дату следующего выполнения
				$sql_data[$a]['date_perform'] = $task_list[$i]['date_perform'] + $task_list[$i]['time_add'];
			}

			$i ++;
			$a ++;

		}

		// Обновляем данные
		if(!empty($sql_data)) {
			$this->db->update_batch('cron', $sql_data, 'id');
		}

		// Отображаем статистику заданий
		$this->_cron_result .= "Success: $cron_stats[success] Failed: $cron_stats[failed] Skipped: $cron_stats[skipped]\n";


		/*==================================================*/
		/*    				БЕГУН					        */
		/*    Пробегаем по каждому серверу			        */
		/*==================================================*/

		$this->_cron_result .= "== Runner ==\n";

		$this->servers->get_server_list(false, false, array('enabled' => '1'));
		//~ $this->games->get_game_list();

		$i = 0;
		$count_i = count($this->servers->servers_list);
		while($i < $count_i) {
			$server_id = $this->servers->servers_list[$i]['id'];

			// Получение данных сервера
			$this->_get_server_data($server_id);

			/*==================================================*/
			/*     Установка сервера					        */
			/*==================================================*/

			if ($this->servers_data[$server_id]['installed'] == '0'
				OR $this->servers_data[$server_id]['installed'] == '3'
			) {
				// Сервер не установлен
				$this->_cron_result .= "Server #" . $server_id . " not installed\n";
				$server_installed = false;

				/* Получение данных об игровой модификации */
				$this->game_types->get_gametypes_list(array('id' => $this->servers_data[$server_id]['dir']));

				/*
				 * Полю installed устанавливаем значение 2, что сервер начал устанавливаться
				*/

				$this->servers->edit_game_server($server_id, array('installed' => '2'));
				
				$this->_mkdir($server_id);
				
				if ($this->games->games_list[0]['local_repository']) {
					/* Установка из локального репозитория */
					if ($this->_wget_files($server_id, $this->games->games_list[0]['local_repository'], 'local')) {
						$this->_unpack_files($server_id, $this->games->games_list[0]['local_repository']);
						$server_installed = true;
					}
					
				} elseif ($this->games->games_list[0]['remote_repository']) {
					/* Установка из удаленного репозитория */
					if ($this->_wget_files($server_id, $this->games->games_list[0]['remote_repository'], 'remote')) {
						$this->_unpack_files($server_id, $this->games->games_list[0]['remote_repository']);
						$server_installed = true;
					}
				} elseif ($this->games->games_list[0]['app_id']) {
					/* Установка через SteamCMD */
					if ($this->_install_from_steamcmd($server_id)) {
						$server_installed = true;
					}
					
				} else {
					/* 
					 * Не удалость выбрать тип установки 
					 * отсутствуют данные локального репозитория, удаленного репозитория и steamcmd
					 */
					$this->_cron_result .= "Server #" . $server_id . " install failed. App_id and Repository data not specified\n";
					$server_installed = false;
				}

				/* 
				 * Завершение установки.
				 * Установка прав на директории, задание ркон пароля
				*/
				if ($server_installed == true) {
					/* Загружаем дополнительный файлы игровой модификации */
					if ($this->game_types->game_types_list[0]['local_repository']) {
						if ($this->_wget_files($server_id, $this->game_types->game_types_list[0]['local_repository'], 'local')) {
							$this->_unpack_files($server_id, $this->game_types->game_types_list[0]['local_repository']);
						}
					} elseif ($this->game_types->game_types_list[0]['remote_repository']) {
						if ($this->_wget_files($server_id, $this->game_types->game_types_list[0]['remote_repository'], 'remote')) {
							$this->_unpack_files($server_id, $this->game_types->game_types_list[0]['remote_repository']);
						}
					}
					
					/* Устанавливаем 777 права на директории, в которые загружается контент (карты, модели и пр.)
					* и 666 на конфиг файлы, которые можно редактировать через админпанель */
					if(strtolower($this->servers_data[$server_id]['os']) != 'windows') {
						$config_files 	= json_decode($this->servers_data[$server_id]['config_files'], true);
						$content_dirs 	= json_decode($this->servers_data[$server_id]['content_dirs'], true);
						$log_dirs 		= json_decode($this->servers_data[$server_id]['log_dirs'], true);
						$command = array();
						$log = '';

						foreach($config_files as $file) {
							$command[] = 'chmod 666 ' . './' . $this->servers_data[$server_id]['dir'] . '/' . $file['file'];
							$log .= 'chmod 666 ' . './' . $this->servers_data[$server_id]['dir'] . '/' .  $file['file'] . "\n";
						}

						foreach($content_dirs as $dir) {
							$command[] = 'chmod 777 ' . './' . $this->servers_data[$server_id]['dir']. '/' .  $dir['path'];
							$log .= 'chmod 777 ' . './' . $this->servers_data[$server_id]['dir'] . '/'. $dir['path'] . "\n";
						}

						foreach($log_dirs as $dir) {
							$command[] = 'chmod 777 ' . './' . $this->servers_data[$server_id]['dir'] . '/' . $dir['path'];
							$log .= 'chmod 777 ' . './' . $this->servers_data[$server_id]['dir'] . '/' . $dir['path'] . "\n";
						}

						$log .= "\n---\nCHMOD\n" . $log . "\n" .  $this->servers->command($command, $this->servers_data[$server_id]);
					}


					/* Устанавливаем серверу rcon пароль */
					$this->load->helper('safety');
					$new_rcon = generate_code(8);
					$this->servers->change_rcon($new_rcon, $this->servers_data[$server_id]);

					$server_data = array('installed' => '1', 'rcon' => $new_rcon);
					$this->servers->edit_game_server($server_id, $server_data);

					$log_data['type'] = 'server_command';
					$log_data['command'] = 'install';
					$log_data['server_id'] = $server_id;
					$log_data['msg'] = 'Server install successful';
					$log_data['log_data'] = 'Commands: ' . var_export($this->dedicated_servers->commands, true) . "\n\nResults: " . $this->_install_result . "\n";
					$this->panel_log->save_log($log_data);

				} else {
					
					$server_data = array('installed' => '0');
					$this->servers->edit_game_server($server_id, $server_data);

					$log_data['type'] = 'server_command';
					$log_data['command'] = 'install';
					$log_data['server_id'] = $server_id;
					$log_data['msg'] = 'Server install failed';
					$log_data['log_data'] = 'Commands: ' . var_export($this->servers->commands, true);
					$this->panel_log->save_log($log_data);
					
					$this->_cron_result .= 'Server install #' . $server_id . ' failed';
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

				if(!$status) {
					/* Смотрим данные предыдущих проверок, если сервер был в оффе, то запускаем его */

					/* Получение данных проверки крона из логов */
					$where = array('date >=' => $time - 780,  'type' => 'cron_check', 'command' => 'server_status', 'server_id' => $server_id, 'log_data' => 'Server is down');
					$logs = $this->panel_log->get_log($where); // Логи сервера в админпанели

					$response = false;

					if(count($logs) >= 1) {
						/* При последней проверке сервер был оффлайн, запускаем его*/
						$response = $this->servers->start($this->servers_data[$server_id]);

						$log_data['command'] = 'start';
						$log_data['msg'] = 'Start server success';

						if(strpos($response, 'Server is already running') !== false) {
							/* Сервер запущен, видимо завис */
							$response = $this->servers->restart($this->servers_data[$server_id]);
							$log_data['command'] = 'restart';

							if(strpos($response, 'Server restarted') !== false) {
								$log_data['msg'] = 'Restart server success';	
							}

						}
					}


					if($response) {
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
						$this->servers->change_rcon($new_rcon);

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

						// Перезагружаем сервер
						$response = $this->servers->restart($this->servers_data[$server_id]);

						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Restart server success';
						$log_data['log_data'] = $response;
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

		$this->_cron_result .= "== DS Stats ==\n";
		$this->dedicated_servers->get_ds_list();
		
		if (!empty($this->dedicated_servers->ds_list)) {
			foreach($this->dedicated_servers->ds_list as $ds) {
				
				if (!$ds['ssh_host'] && !$ds['telnet_host']) {
					continue;
				}
				
				$stats = $this->_stats_processing($ds);

				if(isset($stats['cpu_usage']) && isset($stats['cpu_usage'])) {
					$this->_cron_result .= 'Stats server #' . $ds['id'] . ' successful' . "\n";
				} else {
					$this->_cron_result .= 'Stats server #' . $ds['id'] . ' failed'. "\n";
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

				$stats_array[] = array('date' => $time, 'cpu_usage' => $stats['cpu_usage'], 'memory_usage' => $stats['memory_usage']);
				$data['stats'] = json_encode($stats_array);
				$this->dedicated_servers->edit_dedicated_server($ds['id'], $data);
			}
		}

		// Статистика для локального сервера
		$ds = array('os' => $this->config->config['local_os'], 'control_protocol' => 'local'); 
		$stats = $this->_stats_processing($ds);

		if(isset($stats['cpu_usage']) && isset($stats['cpu_usage'])) {

			$stats_array = json_decode(@file_get_contents(APPPATH . 'cache/local_server_stats.json', true));
			$stats_array[] = array('date' => $time, 'cpu_usage' => $stats['cpu_usage'], 'memory_usage' => $stats['memory_usage']);
			$data['stats'] = json_encode($stats_array);
			file_put_contents(APPPATH . 'cache/local_server_stats.json', $data['stats']);

			$this->_cron_result .= 'Local server stats successful' . "\n";

		} else {
			$this->_cron_result .= 'Local server stats failed'. "\n";
		}

		/*==================================================*/
		/*    	ВЫПОЛНЕНИЕ CRON СКРИПТОВ ИЗ МОДУЛЕЙ			*/
		/*==================================================*/
		
		$this->_cron_result .= "== Modules cron ==\n";
		
		/* Чтобы данные выполнения пользовательского крона выводились правильно
		 * и на своем месте, то записываем весь вывод предыдущих задач 
		 * а после этого запускаем пользовательский крон
		*/
		$this->output->append_output($this->_cron_result);
		$this->_cron_result = '';
		$this->_modules_cron();
		
		$this->_cron_result .= "Cron end\n";
		$this->output->append_output($this->_cron_result);

		$log_data['type'] = 'cron';
		$log_data['command'] = 'cron work';
		$log_data['msg'] = 'Cron end working';
		$log_data['log_data'] = $this->output->get_output();
		$this->panel_log->save_log($log_data);

	}

}
