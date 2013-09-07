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

	public function __construct()
    {
        parent::__construct();
        
        /* Скрипт можно запустить только из командной строки (через cron)*/
        if(php_sapi_name() != 'cli'){
			exit('Access Denied');
		}
		
		set_time_limit(0);
		$this->load->database();
    }
    
    public function index()
    {
		$this->load->model('servers');
		$this->load->model('valve_rcon');
		
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
		$where = array('date_perform >' => $time - 3600, 'date_perform <' => $time);
		$query = $this->db->get_where('cron', $where);
		
		$task_list = $query->result_array();
		
		/*==================================================*/
		/*     Выполняем задания                            */
		/*==================================================*/
		
		echo "== Task manager ==\n";
		
		$i = 0;
		$a = 0;
		$count_i = count($task_list);
		while($i < $count_i) {
			
			// Если достигнут предел выполняемых задания, то оставляем оставшиеся на потом
			if($i >= 100) {
				$cron_stats['skipped'] += $count_i - $i;
				break;
			}
			
			$cron_success = FALSE;
			
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
				
				if(!array_key_exists($server_id , $this->servers_data)) {
					$this->servers_data[$server_id ] = $this->servers->get_server_data($server_id);
				}
				
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
						$cron_success = TRUE;
						$cron_stats['success'] ++;
						
						echo 'Task: server #' . $server_id . '  start success' . "\n";
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'start';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Start server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
						
					}else{
						
						$cron_stats['failed'] ++;
						echo 'Task: server #' . $server_id . '  start failed' . "\n";
						
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
						$cron_success = TRUE;
						$cron_stats['success'] ++;
						
						echo 'Task: server #' . $server_id . '  stop success' . "\n";
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'stop';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Stop server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
						
					}else{
						$cron_stats['failed'] ++;
						
						echo 'Task: server #' . $server_id . '  stop failed' . "\n";

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
						$cron_success = TRUE;
						$cron_stats['success'] ++;
						
						echo 'Task: server #' . $server_id . '  restart success' . "\n";
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Restart server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
						
					}else{
						$cron_stats['failed'] ++;
						
						echo 'Task: server #' . $server_id . '  restart failed' . "\n";
						
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
						$cron_success = TRUE;
						$cron_stats['success'] ++;
						
						echo 'Task: server #' . $server_id . '  update success' . "\n";
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'update';
						$log_data['server_id'] = $server_id;
						$log_data['msg'] = 'Update server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
						
					} else {
						$cron_stats['failed'] ++;
						
						echo 'Task: server #' . $server_id . '  update failed' . "\n";
						
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
					if($this->servers->server_status($this->servers_data[$server_id]['server_ip'], $this->servers_data[$server_id]['server_port'])) {
						$rcon_connect = $this->valve_rcon->connect(
								$this->servers_data[$server_id]['server_ip'], 
								$this->servers_data[$server_id]['server_port'],
								$this->servers_data[$server_id]['rcon'],
								$this->servers_data[$server_id]['engine']
						);
						
						if($rcon_connect) {
							$rcon_string = $this->valve_rcon->command($task_list[$i]['command']);
							
							$cron_success = TRUE;
							$cron_stats['success'] ++;
							
							echo 'Task: server #' . $server_id . '  rcon send success' . "\n";
							
							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['command'] = $task_list[$i]['command'];
							$log_data['server_id'] = $server_id;
							$log_data['msg'] = 'Rcon command';
							$log_data['log_data'] = 'Rcon string: ' . $rcon_string;
							$this->panel_log->save_log($log_data);
						} else {
							$cron_stats['failed'] ++;
							
							echo 'Task: server #' . $server_id . '  rcon send failed' . "\n";
							
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
						
						echo 'Task: server #' . $server_id . '  rcon send failed' . "\n";
							
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
		echo "Success: $cron_stats[success] Failed: $cron_stats[failed] Skipped: $cron_stats[skipped]\n";
		
		
		/*==================================================*/
		/*    				БЕГУН					        */
		/*    Пробегаем по каждому серверу			        */
		/*==================================================*/
		
		echo "== Runner ==\n";

		$this->servers->get_server_list(FALSE, FALSE, array('enabled' => '1'));
		
		$i = 0;
		$count_i = count($this->servers->servers_list);
		while($i < $count_i) {
			$server_id = $this->servers->servers_list[$i]['id'];

			if(!array_key_exists($server_id, $this->servers_data)) {
				$this->servers_data[$server_id] = $this->servers->get_server_data($server_id);
			}
			
			//print_r($this->servers_data[$server_id]);
			//~ $cfg_files = json_decode($this->servers_data[$server_id]['config_files'], TRUE);
			//~ 
			//~ foreach($cfg_files as $file) {
				//~ echo $this->servers_data[$server_id]['dir'] . $file['file'] . ' ' ;
			//~ }
			
			/*==================================================*/
			/*     Установка сервера					        */
			/*==================================================*/
			
			if($this->servers_data[$server_id]['installed'] == '0'
				OR $this->servers_data[$server_id]['installed'] == '3'
			) {
				// Сервер не установлен
				echo "Server #" . $server_id . " not installed\n";
				
				/*
				 * Полю installed устанавливаем значение 2, что сервер начал устанавливаться
				*/
				
				$this->servers->edit_game_server($server_id, array('installed' => '2'));
				
				if($this->games->games_list[0]['app_id']) {
					//steamcmd +login anonymous +force_install_dir ../czero +app_set_config 90 mod czero +app_update 90 validate +quit
					$cmd['app'] = '';
					
					if($this->games->games_list[0]['app_set_config']) {
						$cmd['app'] .= '+app_set_config ' . $this->games->games_list[0]['app_set_config'] . ' ';
					}
					
					$cmd['app'] .= '+app_update ' . $this->games->games_list[0]['app_id'];
					
					$cmd['login'] = '+login anonymous';
					$cmd['install_dir'] = '+force_install_dir ' . $this->servers_data[$server_id]['script_path'] . '/' . $this->servers_data[$server_id]['dir'];
					
					switch(strtolower($this->servers_data[$server_id]['os'])) {
						case 'windows':
							$command = 'steamcmd.exe ' . $cmd['login'] . ' ' . $cmd['install_dir'] . ' ' . $cmd['app'] . ' validate +quit';
							$result = $this->servers->command_windows($command, $this->servers_data[$server_id], $this->servers_data[$server_id]['steamcmd_path']);
							break;
						default:
							$command = './steamcmd.sh ' . $cmd['login'] . ' ' . $cmd['install_dir'] . ' ' . $cmd['app'] . ' validate +quit';
							$result = $this->servers->command($command, $this->servers_data[$server_id], $this->servers_data[$server_id]['steamcmd_path']);
							break;
					}
					
					$exec_command = array_pop($this->servers->commands);

					if(strpos($result, 'Success! App \'' . $this->games->games_list[0]['app_id'] . '\' fully installed.') !== FALSE
						OR strpos($result, 'Success! App \'' . $this->games->games_list[0]['app_id'] . '\' already up to date.') !== FALSE
					) {
						$server_data = array('installed' => '1');
						
						/* Устанавливаем 777 права на директории, в которые загружается контент (карты, модели и пр.)
						* и 666 на конфиг файлы, которые можно редактировать через админпанель */
						if($this->servers_data[$server_id]['os'] != 'Windows') {
							$config_files 	= json_decode($this->servers_data[$server_id]['config_files'], TRUE);
							$content_dirs 	= json_decode($this->servers_data[$server_id]['content_dirs'], TRUE);
							$log_dirs 		= json_decode($this->servers_data[$server_id]['log_dirs'], TRUE);
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

							$result .= "\n---\nCHMOD\n" . $log . "\n" .  $this->servers->command($command, $this->servers_data[$server_id]);
							
						}
						
						
						/* Устанавливаем серверу rcon пароль */
						$this->load->helper('safety');
						$this->servers->change_rcon(generate_code(8), $this->servers_data[$server_id]);

						$server_data['rcon'] = $this->encrypt->encode($new_rcon);
						
						$log_data['msg'] = 'Update server success';
						echo "Install server success\n";
					} elseif(strpos($result, 'Failed to request AppInfo update') !== FALSE) {
						/* Сервер не установлен до конца */
						$server_data = array('installed' => '0');
						
						$log_data['msg'] = 'Update server failed';
						echo "Install server failure\n";
					} elseif(strpos($result, 'Error! App \'' . $this->games->games_list[0]['app_id'] . '\' state is') !== FALSE) {
						/* Сервер не установлен до конца */
						$server_data = array('installed' => '0');
						
						$log_data['msg'] = 'Error. App state after update job';
						echo "Install server failure\n";
					} else {
						/* Неизвестная ошибка */
						$server_data = array('installed' => '1');
						
						$log_data['msg'] = 'Unknown error';
						$command = array_pop($this->servers->commands);
						echo "Install server failure\n";
					}
					
					$this->servers->edit_game_server($server_id, $server_data);
					
					$log_data['type'] = 'server_command';
					$log_data['command'] = 'install';
					$log_data['server_id'] = $server_id;
					$log_data['log_data'] = $result . "Command: ". $exec_command;
					$this->panel_log->save_log($log_data);
					
				} else {
					/*
					 * Для игры не задан или не существует парамера app_update для SteamCMD
					*/
					echo "Server #" . $server_id . " install failed. App_id not specified\n";
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

				// Проверка статуса сервера
				$status = $this->servers->server_status($this->servers->servers_list[$i]['server_ip'], $this->servers->servers_list[$i]['server_port']);

				if(!$status) {
					/* Смотрим данные предыдущих проверок, если сервер был в оффе, то запускаем его */
					
					/* Получение данных проверки крона из логов */
					$where = array('date >=' => $time - 780,  'type' => 'cron_check', 'command' => 'server_status', 'server_id' => $server_id, 'log_data' => 'Server is down');
					$logs = $this->panel_log->get_log($where); // Логи сервера в админпанели
					
					$response = FALSE;

					if(count($logs) >= 1) {
						/* При последней проверке сервер был оффлайн, запускаем его*/
						$response = $this->servers->start($this->servers_data[$server_id]);
						
						$log_data['command'] = 'start';
						$log_data['msg'] = 'Start server success';
						
						if(strpos($response, 'Server is already running') !== FALSE) {
							/* Сервер запущен, видимо завис */
							$response = $this->servers->restart($this->servers_data[$server_id]);
							$log_data['command'] = 'restart';
							
							if(strpos($response, 'Server restarted') !== FALSE) {
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
				
				if($this->servers->server_status($this->servers_data[$server_id]['server_ip'], $this->servers_data[$server_id]['server_port'])) {
				
					$rcon_connect = $this->valve_rcon->connect(
						$this->servers_data[$server_id]['server_ip'], 
						$this->servers_data[$server_id]['server_port'],
						$this->servers_data[$server_id]['rcon'],
						$this->servers_data[$server_id]['engine']
					);
					
				} else {
					$rcon_connect = FALSE;
				}
						
				if($rcon_connect) {
					$rcon_string = $this->valve_rcon->command('status');
					
					$rcon_string = trim($rcon_string);
					
					if(strpos($rcon_string, 'Bad rcon_password.') !== FALSE) {

						$this->load->helper('safety');
						
						$new_rcon = generate_code(8);
						
						$this->servers->server_data = $this->servers_data[$server_id];
						$this->servers->change_rcon($new_rcon);
						
						// Меняем пароль в базе
						$sql_data['rcon'] = $this->encrypt->encode($new_rcon);
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
		
		echo "Cron end\n";

	}

}
