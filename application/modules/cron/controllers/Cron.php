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

use \Myth\Controllers\CLIController;
use \Myth\Modules;

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
 * @sinse		1.0
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
class Cron extends CLIController {

	var $servers_data = array();

	var $_cron_result = [];
	private $_commands_result = array();
	private $_install_result = '';

	public function __construct()
	{
		parent::__construct();

		/* Скрипт можно запустить только из командной строки (через cron) */
		if(php_sapi_name() != 'cli'){
			show_404();
		}

		$this->load->model('gdaemon_tasks');

		$this->load->model('servers');
		$this->load->model('servers/dedicated_servers');

		$this->load->driver('rcon');
		$this->load->driver('installer');

		$this->load->library('query');

		$this->load->helper('ds');
		$this->load->helper('date');

		// Максимальное время выполнени 1 час
		set_time_limit(1*3600);

		// Загрузка базы данных
		$this->load->database();
	}

	// ----------------------------------------------------------------

	/**
	 * Отправляет сообщение в командную строку
	 *
	 * @param string
	*/
	private function _cmd_output($msg = '')
	{
		$this->_cron_result[] = $msg;
		echo end($this->_cron_result) . PHP_EOL;
	}

	// -----------------------------------------------------------------

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
	 * Получение консоли сервера
	*/
	private function _get_console($server_id)
	{

	}

	// -----------------------------------------------------------------------

	/**
	 * Линуксовые слеши в виндовые
	 */
	private function _linux_to_windows_path($path = '')
	{
		$path = str_replace('/', '\\', $path . '\\');
		$path = preg_replace('/\\\\{2,}/si', '\\', $path);
		$path = substr($path, 0, strlen($path)-1);
		return $path;
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
				$this->_cmd_output("--Script {$value['cron_script']} on module {$value['short_name']} omitted");
				continue;
			}

			if (in_array($value['cron_script'], $array_scripts)) {
				/* Нельзя запускать скрипты с именем cron */
				$this->_cmd_output("--Script {$value['cron_script']} on module {$value['short_name']} omitted");
				continue;
			}

			if (!file_exists(APPPATH . 'modules/' . $value['short_name'] . '/controllers/' . ucfirst($value['cron_script']) . '.php')) {
				/* Скрипт отсутствует */
				$this->_cmd_output("--Script not found on {$value['short_name']} module");
				continue;
			}

			$array_scripts[] = $value['cron_script'];

			/* Выполняем cron скрипт из модуля */
			$this->_cmd_output("--Start {$value['short_name']}");

            $this->load->add_package_path(Modules::path($value['short_name']));
            $classname = ucfirst($value['cron_script']);
            Modules::load_file($classname, Modules::path($value['short_name']) . 'controllers/');

            (new $classname())->index();
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Выполнение заданий
	 *
	 * @param array  список с id серверов
	 */
	private function _tasks($servers_id_list = array())
	{
		$log_data['user_name'] = 'System (cron)';

		if (empty($servers_id_list)) {
			return;
		}

		$this->_cmd_output("--Task manager");

		$cron_stats = array(
			'success' => 0,
			'failed' => 0,
			'skipped' => 0,
		);

		/* Получение заданий из базы данных
		 * Задания ограничиваются последним часом, если по какой либо
		 * причине задания двухчасовой давносте не были выполнены они не
		 * будут выполнены вновь
		 * */
		$where = array('date_perform >' => now() - 3600, 'date_perform <' => now(), 'started' => 0);
		$this->db->where_in('server_id', $servers_id_list);
		$query = $this->db->get_where('cron', $where);

		$task_list = $query->result_array();

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
				//~ $this->_get_server_data($server_id);

			} else {
				$cron_stats['skipped'] ++;
				$i ++;
				continue;
			}

			// Получение данных сервера
			$this->_get_server_data($server_id);

			/*
			 * Отправляем данны о том, что задание начало выполняться
			 * чтобы исключить повторное выполнение при следующем запуске cron скрипта,
			 * в случаях когда задание не завершилось
			*/
			$this->db->where('id', $task_list[$i]['id']);
			$this->db->update('cron', array('started' => '1'));

			// Выполняем задание
			switch($task_list[$i]['code']) {

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

							$this->_cmd_output('---Task: server #' . $server_id . '  rcon send success');

							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['command'] = $task_list[$i]['command'];
							$log_data['server_id'] = $server_id;
							$log_data['msg'] = 'Rcon command';
							$log_data['log_data'] = 'Rcon string: ' . $rcon_string;
							$this->panel_log->save_log($log_data);
						} else {
							$cron_stats['failed'] ++;

							$this->_cmd_output('---Task: server #' . $server_id . '  rcon send failed');

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

						$this->_cmd_output('---Task: server #' . $server_id . '  rcon send failed');

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

			/* Задание было выполнено */
			$sql_data[$a]['id'] = $task_list[$i]['id'];
			$sql_data[$a]['started'] = '0';

			// Устанавливаем дату выполнения
			$sql_data[$a]['date_performed'] = now();

			if ($task_list[$i]['time_add']) {
				// Устанавливаем дату следующего выполнения
				$sql_data[$a]['date_perform'] = $task_list[$i]['date_perform'] + $task_list[$i]['time_add'];
			}
			else {
				// Удаление задания, т.к. даты следующего выполнения нет
				$this->db->where('id', $task_list[$i]['id']);
				$this->db->delete('cron');
			}

			$i ++;
			$a ++;
		}

		// Обновляем данные
		if(isset($sql_data) && is_array($sql_data) && !empty($sql_data)) {
			$this->db->update_batch('cron', $sql_data, 'id');
		}

		// Отображаем статистику заданий
		//~ $this->_cmd_output("---Success: {$cron_stats['success']} Failed: {$cron_stats['failed']} Skipped: {$cron_stats['skipped']}");
		$this->_cmd_output('-- End Task manager');
	}

	// -----------------------------------------------------------------

	/**
	 * Функция, выполняющаяся при запуске cron
	*/
	public function index()
	{
		$start_microtime = microtime(true);
		$log_data['user_name'] = 'System (cron)';

		$this->_cmd_output('Cron started');

        // Find and fix servers errors

        // Getting a list of servers that installation process
        $this->servers->select_fields('id');
        $this->servers->get_list(false, '', array('installed' => 2));

        $install_process_servers = array();
        foreach ($this->servers->servers_list as &$server) {
            $install_process_servers[] = $server['id'];
        }

        $this->gdaemon_tasks->set_filter('server_id', $install_process_servers);
        $this->gdaemon_tasks->set_filter('task', array('gsinst', 'gsupd'));
        $this->gdaemon_tasks->get_list();

        $inst_progress_approved = array();
        foreach ($this->gdaemon_tasks->tasks_list as &$task) {
            $inst_progress_approved[] = $task['server_id'];
        }

        $not_approved = array_diff($install_process_servers, $inst_progress_approved);

		// Repeat install
		foreach ($not_approved as &$server_id) {
			$server_data = $this->_get_server_data($server_id);

			$this->gdaemon_tasks->add([
				'server_id' => $server_id,
				'ds_id' 	=> $server_data['ds_id'],
				'task' 		=> 'gsinst',
			]);
		}

		/*==================================================*/
		/*    	ВЫПОЛНЕНИЕ CRON СКРИПТОВ ИЗ МОДУЛЕЙ			*/
		/*==================================================*/
		$this->_cmd_output("-Modules cron");

		/* Чтобы данные выполнения пользовательского крона выводились правильно
		 * и на своем месте, то записываем весь вывод предыдущих задач
		 * а после этого запускаем пользовательский крон
		*/
		$this->_modules_cron();

		// Статистика
		$this->_cmd_output("Cron stats");
		$end_mircotime = microtime(true);
		$this->_cmd_output('-Time elapsed: ' . round($end_mircotime - $start_microtime, 4) . ' seconds');
		$this->_cmd_output('-Memory peak usage: ' . round(memory_get_peak_usage()/1024, 2) . ' Kb');

		// Удаление логов за два месяца
		$this->db->delete('logs', array('date <' => now()-(604800*8)));

		$this->_cmd_output("Cron end");

		$log_data['server_id'] = 0;
		$log_data['type'] = 'cron';
		$log_data['command'] = 'cron work';
		$log_data['msg'] = 'Cron end working';
		$log_data['log_data'] = implode("\n", $this->_cron_result);
		$this->panel_log->save_log($log_data);
	}

}
