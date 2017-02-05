<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

use \Myth\Controllers\BaseController;

/**
 * Управление сервером
 *
 * Страница управления сервером, отображение основной
 * информации о сервере
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.1
 */
class Server_control extends BaseController {

	//Template
	var $tpl = array();

	var $user_data = array();
	var $server_data = array();

	// Количество игроков на сервере
	var $players = 0;

	private $_available_tasks = array(
		'server_start',
		'server_stop',
		'server_restart',
		'server_update',
		'server_rcon',
	);

	//--------------------------------------------------------------------------

	public function __construct()
    {
        parent::__construct();

		$this->load->database();
        $this->load->model('users');

        if ($this->users->check_user()) {

			$this->load->model('servers');
			$this->lang->load('server_control');
			$this->lang->load('server_command');
			$this->lang->load('web_ftp');
			$this->lang->load('servers_log');

			//Base Template
			$this->tpl['title'] 	= lang('server_control_title');
			$this->tpl['heading'] 	= lang('server_control_header');
			$this->tpl['content'] = '';
			$this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
			$this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);

        }else{
            redirect('auth');
        }
    }

	//--------------------------------------------------------------------------

	// Отображение информационного сообщения
    function _show_message($message = false, $link = false, $link_text = false)
    {

        if (!$message) {
			$message = lang('error');
		}

        if (!$link) {
			$link = 'javascript:history.back()';
		}

		if (!$link_text) {
			$link_text = lang('back');
		}

        $local_tpl['message'] = $message;
        $local_tpl['link'] = $link;
        $local_tpl['back_link_txt'] = $link_text;
        $this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }

    // --------------------------------------------------------------------------

    /**
     * Получение списка frcon команд
     */
    private function _get_frcon_list()
    {
		$tpl_list = array();

		$frcon_list = json_decode($this->servers->server_data['fast_rcon'], true);
		if ($frcon_list) {
			$tpl_list = $frcon_list;
			$i = 0;
			foreach($frcon_list as $array) {
				$tpl_list[$i]['id_fr'] = $i;
				$i ++;
			}
		}

		return $tpl_list;
	}

	// --------------------------------------------------------------------------

    /**
     * Получение списка игроков
     */
	private function _get_players_list()
	{
		$tpl_list = array();

		print_r('hello');

		if ($rcon_string) {
			$tpl_list = $this->rcon->get_players($rcon_string, $this->servers->server_data['engine']);
		}

		if (!empty($tpl_list)) {
			/* В качестве условия в шаблоне */
			$this->players = 1;
		}

		return $tpl_list;
	}

	// --------------------------------------------------------------------------

    /**
     * Получение списка карт для вставки в шаблон
     */
	private function _get_base_cvars()
	{
		$tpl = array();
		$server_id = $this->servers->server_data['id'];

		// Список базовых кваров
		$query['id'] 	= $server_id;
		$query['type'] 	= $this->servers->server_data['engine'];
		$query['host']	= $this->servers->server_data['server_ip'];
		$query['port']	= $this->servers->server_data['server_port'];
		$this->query->set_data($query);

		if ($base_cvars = $this->query->get_base_cvars()) {
			$base_cvars = $base_cvars[$server_id];

			$tpl[] = array('cvar_name' => lang('cvarname_hostname'), 'cvar_value' => $base_cvars['hostname']);
			$tpl[] = array('cvar_name' => lang('cvarname_map'), 'cvar_value' => $base_cvars['map']);
			$tpl[] = array('cvar_name' => lang('cvarname_players'),
													'cvar_value' => $base_cvars['players'] . '/' . $base_cvars['maxplayers']
													);

			$password_status = 	$base_cvars['password'] ? lang('set') : lang('no_set');
			$tpl[] = array('cvar_name' => lang('password'), 'cvar_value' => $password_status);

			if (isset($base_cvars['joinlink']) && $base_cvars['joinlink']) {
				$tpl[] = array('cvar_name' => lang('cvarname_joinlink'), 'cvar_value' => anchor($base_cvars['joinlink']));
			}
		}

		return $tpl;
	}

    // -----------------------------------------------------------------

    private function _get_modules_menu()
    {
        $this->load->driver('cache');
        $this->load->helper('directory');

        $modules_menu = array();

        if (!$menu = $this->_load_menu_from_cache()) {
            $menu = array();
            if ($map = directory_map(APPPATH . 'modules')) {
                foreach($map as $key => $value) {
                    if (!is_array($value)) {
                        /* Это файл */
                        continue;
                    }

                    if (!is_dir(APPPATH . 'modules/' . $key)) {
                        /* Это не директория */
                        continue;
                    }

                    if (file_exists(APPPATH . 'modules/' . $key . '/menu.json')) {
                        $menu[$key] = json_decode(file_get_contents(APPPATH . 'modules/' . $key . '/menu.json'), true);

                        if (!$menu[$key]) {
                            unset($menu[$key]);
                        }

                    }
                }
            }

            $this->_save_menu_to_cache($menu);
        }

        foreach ($menu as &$array) {
            if (isset($array['servers_control'])) {
                foreach ($array['servers_control'] as &$menu_item) {

                    if (isset($menu_item['games'])
                        && !empty($menu_item['games'])
                        && !in_array($this->servers->server_data['game'], $menu_item['games'])
                    ) {
                        continue;
                    }

                    if ($this->users->auth_data['group'] < $menu_item['group']) {
                        continue;
                    }

                    $modules_menu[] = [
                        'modules_menu_icon' => base_url($menu_item['icon']),
                        'modules_menu_link' => site_url($menu_item['link']),
                        'modules_menu_text' => $menu_item['text'],
                    ];
                }
            }
        }

        return $modules_menu;
    }

    //------------------------------------------------------------------

    private function _load_menu_from_cache()
    {
        return load_from_cache('servers_menu');
    }

    // -----------------------------------------------------------------

    private function _save_menu_to_cache($menu_list = array())
    {
        save_to_cache('servers_menu', $menu_list, 60);
    }

    //--------------------------------------------------------------------------

	/**
     * Главная страница управления сервером
     *
     * @param int - id сервера
     *
    */
    public function main($server_id = false)
    {
        $this->load->library('query');
        $this->load->driver('rcon');
        $this->load->helper('date');

		if(!$server_id) {
			$this->_show_message(lang('server_control_empty_server_id'));
			return false;
		}

		/* Получение данных сервера и привилегий на сервер */
		$this->servers->server_data = $this->servers->get_server_data($server_id);
		$this->users->get_server_privileges($server_id);

		if(!$this->servers->server_data) {
			$this->_show_message(lang('server_control_server_not_found'));
			return false;
		}

		if(!$this->users->auth_servers_privileges['VIEW']) {
			$this->_show_message(lang('server_control_server_not_found'));
			return false;
		}

        // Menu
        $local_tpl['modules_menu'] = $this->_get_modules_menu();

		$rcon_connect = false;

		if ($this->servers->server_status($this->servers->server_data['server_ip'], $this->servers->server_data['query_port'])) {
			$this->servers->server_data['server_status'] = 1;

			$this->rcon->set_variables(
				$this->servers->server_data['server_ip'],
				$this->servers->server_data['rcon_port'],
				$this->servers->server_data['rcon'],
				$this->servers->servers->server_data['engine'],
				$this->servers->servers->server_data['engine_version']
			);

			$rcon_connect = $this->rcon->connect();

		} else {
			$this->servers->server_data['server_status'] = 0;
		}

		if ($this->servers->server_data['server_status']) {

			$local_tpl['users_list'] 	= array();
			$local_tpl['players_list'] = array();

			if ($users_list = $this->rcon->get_players()){
				$local_tpl['users_list'] = $users_list;
				$local_tpl['players_list'] = $users_list;
			}

			if ($local_tpl['users_list']) {
				$this->players = 1;
			}

			// Костыль
			$local_tpl['users_list1'] =& $local_tpl['users_list'];
			$local_tpl['users_list2'] =& $local_tpl['users_list'];

			$local_tpl['frcon_list'] 	= $this->_get_frcon_list();
			$local_tpl['base_cvars'] 	= $this->_get_base_cvars();
			$this->rcon->disconnect();

		} else {
			// Ошибка соединения с сервером
			//~ $this->tpl['content'] .= lang('server_control_server_down');
		}

		/* Получение последних действий с сервером
		 *
		 * количество получаемых логов = 50
		 * количество отображаемых логов = 10
		 *
		 * Некоторые из получаемых логов могут не относиться к серверам, из-за этого
		 * таблица может быть пустой
		 *
		*/
		$where = array('server_id' => $server_id);
		$server_plogs = $this->panel_log->get_log($where, 50); // Логи сервера в админпанели

		$local_tpl['log_list'] = array();

		$log_num = 0;
		for ($i = 0; $i < count($server_plogs); $i++) {

			if ($log_num == 10) {
				break;
			}

			$local_tpl['log_list'][$i]['log_id'] = $server_plogs[$i]['id'];
			$local_tpl['log_list'][$i]['log_date'] = unix_to_human($server_plogs[$i]['date'], true, 'eu');
			$local_tpl['log_list'][$i]['log_server_id'] = $server_plogs[$i]['server_id'];
			$local_tpl['log_list'][$i]['log_user_name'] = $server_plogs[$i]['user_name'];
			$local_tpl['log_list'][$i]['log_command'] = $server_plogs[$i]['command'];

			/* Код действия на понятный язык */
			switch($server_plogs[$i]['type']) {
				case 'gdaemon_task_add':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_srv_task');

					switch ($server_plogs[$i]['command']) {
						case 'gsstart':
							$local_tpl['log_list'][$i]['log_command'] = lang('server_control_start');
							break;

						case 'gsstop':
							$local_tpl['log_list'][$i]['log_command'] = lang('server_control_stop');
							break;

						case 'gsrest':
							$local_tpl['log_list'][$i]['log_command'] = lang('server_control_restart');
							break;

						case 'gsinst':
						case 'gsupd':
							$local_tpl['log_list'][$i]['log_command'] = lang('server_control_update');
							break;
					}

					$log_num ++;
					break;

				case 'server_rcon':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_rcon_send');
					$log_num ++;
					break;

				case 'server_command':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_command');
					$log_num ++;
					break;

				case 'server_update':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_update');
					$log_num ++;
					break;

				case 'server_task':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_srv_task');
					$log_num ++;
					break;

				case 'server_settings':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_settings');
					$log_num ++;
					break;

				case 'server_files':
					$local_tpl['log_list'][$i]['log_type'] = lang('server_control_file_operation');
					$log_num ++;
					break;

				default:
					// Тип лога неизвестен, удаляем его из списка (не из базы)
					unset($local_tpl['log_list'][$i]);
					break;
			}
		}

		/* Крон задания */
		$local_tpl['task_list'] = array();

		if($this->users->auth_servers_privileges['TASK_MANAGE']) {

			$where = array('server_id' => $server_id);
			$query = $this->db->order_by('date_perform', 'asc');
			$query = $this->db->get_where('cron', $where);

			if($query->num_rows() > 0) {
				$task_list = $query->result_array();
			} else {
				$task_list = array();
			}

			$i = 0;
			$count_i = count($task_list);
			while($i < $count_i) {

				if (false == in_array($task_list[$i]['code'], $this->_available_tasks)) {
					$i ++;
					continue;
				}

				switch($task_list[$i]['code']) {
					case 'server_start':
						$local_tpl['task_list'][$i]['task_action'] = lang('server_control_start');
						break;

					case 'server_stop':
						$local_tpl['task_list'][$i]['task_action'] = lang('server_control_stop');
						break;

					case 'server_restart':
						$local_tpl['task_list'][$i]['task_action'] = lang('server_control_restart');
						break;

					case 'server_update':
						$local_tpl['task_list'][$i]['task_action'] = lang('server_control_update');
						break;

					case 'server_rcon':
						$local_tpl['task_list'][$i]['task_action'] = lang('server_control_rcon_send');
						break;

					default:
						break;
				}

				$local_tpl['task_list'][$i]['task_id'] = $task_list[$i]['id'];
				$local_tpl['task_list'][$i]['task_name'] = $task_list[$i]['name'];
				$local_tpl['task_list'][$i]['task_date'] = unix_to_human($task_list[$i]['date_perform'], true, 'eu');

				$i ++;
			}
		}

		$local_tpl['server_id'] = $server_id;
		$local_tpl['server_name'] = $this->servers->server_data['name'];

		$local_tpl['server_ip'] 			= $this->servers->server_data['server_ip'];
		$local_tpl['server_port'] 			= $this->servers->server_data['server_port'];
		$local_tpl['server_rcon_port'] 		= $this->servers->server_data['rcon_port'];
		$local_tpl['server_query_port'] 	= $this->servers->server_data['query_port'];

		$this->tpl['heading'] = lang('server_control_header') . ' "' . $this->servers->server_data['name'] . '"';

		if (file_exists(APPPATH . 'views/' . $this->config->config['template'] . '/server_control/' . $this->servers->server_data['game'] . '.html')) {
			$this->tpl['content'] .= $this->parser->parse('server_control/' . $this->servers->server_data['game'] . '.html', $local_tpl, true);
		} else {
			$this->tpl['content'] .= $this->parser->parse('server_control/default.html', $local_tpl, true);
		}

        $this->parser->parse('main.html', $this->tpl);
    }

    //-----------------------------------------------------------

	/**
     * Добавление нового задания для сервера
     *
     * @param int - id сервера
     *
    */
    function add_task($server_id)
    {
		$this->load->library('form_validation');
		$this->load->helper('date');

		$local_tpl = array();

		if(!$server_id) {
			$this->_show_message(lang('server_control_empty_server_id'));
			return false;
		} else {
			$server_id = (int)$server_id;
		}

		/* Получение данных сервера и привилегий на сервер */
		$this->servers->server_data = $this->servers->get_server_data($server_id);
		$this->users->get_server_privileges($server_id);

		/* Проверочки */
		if(!$this->users->auth_servers_privileges['VIEW']) {
			$this->_show_message(lang('server_control_server_not_found'));
			return false;
		} elseif(!$this->servers->server_data) {
			$this->_show_message(lang('server_control_server_not_found'));
			return false;
		} elseif(!$this->users->auth_servers_privileges['TASK_MANAGE']) {
			$this->_show_message(lang('server_control_no_task_privileges'));
			return false;
		}

		/* Правила для формы */
		$this->form_validation->set_rules('name', lang('title'), 'trim|max_length[64]');
		$this->form_validation->set_rules('code', lang('code'), 'trim|required|max_length[32]');
		$this->form_validation->set_rules('command', lang('server_control_param_for_command'), 'trim|max_length[128]');

		$this->form_validation->set_rules('date_perform', lang('server_control_execution_date'), 'trim|required|max_length[19]');
		$this->form_validation->set_rules('time_add', lang('server_control_repeat_period'), 'trim|required|integer|max_length[16]');

		$local_tpl['server_id'] 	= $server_id;
		$local_tpl['date_perform'] = unix_to_human(time()+86400, false, 'eu');

		if($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			$this->tpl['content'] .= $this->parser->parse('servers/task_add.html', $local_tpl, true);
		} else {

			$sql_data['server_id'] = $server_id;

			$sql_data['name'] 		= $this->input->post('name');
			$sql_data['code'] 		= $this->input->post('code');
			$sql_data['command'] 	= $this->input->post('command');
			$sql_data['time_add'] = $this->input->post('time_add');

			if(!$sql_data['date_perform'] = human_to_unix($this->input->post('date_perform'))) {
				$this->_show_message(lang('server_control_date_unavailable'));
				return false;
			}

			// Проверка корректности задания
			if (false == in_array($sql_data['code'], $this->_available_tasks)) {
				$this->_show_message('Task code unavailable');
				return false;
			}

			if ($sql_data['code'] == 'server_rcon' && empty($sql_data['command'])) {
				$this->_show_message(lang('server_control_empty_rcon_command'));
				return;
			}

			if ($sql_data['time_add'] > 0 && $sql_data['time_add'] < 21600) {
				$this->_show_message(lang('server_control_time_add_unavailable'));
				return false;
			}

			if (empty($sql_data['name'])) {
				$ex = explode('_', $sql_data['code']);
				$sql_data['name'] = lang($ex[1]);
			}

			if ($sql_data['code'] == 'server_update') {
				/*
				 * Если создать множество заданий cron с обновлением игровых серверов,
				 * то возможно замедлить работу выделенного сервера
				 * В этом случае нужно проверить, имеется ли задание обновления
				*/
				$where = array('code' => 'server_update', 'server_id' => $server_id);
				$query = $this->db->get_where('cron', $where, 1);

				if ($query->num_rows() > 0) {
					$this->_show_message(lang('server_command_update_task_exists'), site_url('admin/server_control/main/' . $server_id));
					return false;
				}
			} elseif ($sql_data['code'] == 'server_start' OR $sql_data['code'] == 'server_stop' OR $sql_data['code'] == 'server_restart') {

				$this->db->where(array('time_add' => $sql_data['code'], 'server_id' => $server_id));

				if ($this->db->count_all_results('cron') >= 3) {
					$this->_show_message(lang('server_command_max_tasks'), site_url('admin/server_control/main/' . $server_id));
					return false;
				}

				// Промежуток между заданиями запуска/остановки/перезапуска должен быть не менее 10 минут
				$this->db->where(array('date_perform >' => $sql_data['date_perform']-300, 'date_perform <' => $sql_data['date_perform']+300));

				if ($this->db->count_all_results('cron') > 0) {
					$this->_show_message(lang('server_control_interval_unavailable'), site_url('admin/server_control/main/' . $server_id));
					return false;
				}

			}

			$this->db->insert('cron', $sql_data);

			// Сохраняем логи
			$log_data['type'] 		= 'server_task';
			$log_data['command'] 	= 'add_task';
			$log_data['user_name'] 	= $this->users->auth_login;
			$log_data['server_id'] 	= $server_id;
			$log_data['msg'] 		= 'Add new task';
			$log_data['log_data'] 	= 'Name: ' . $sql_data['name'];
			$this->panel_log->save_log($log_data);

			$this->_show_message(lang('server_control_new_task_success'), site_url('admin/server_control/main/' . $server_id), lang('next'));
			return true;

		}

		$this->parser->parse('main.html', $this->tpl);
	}

	//-----------------------------------------------------------

	/**
     * Добавление нового задания для сервера
     *
     * @param int - id сервера
     *
    */
    function delete_task($task_id, $confirm = false)
    {
		if(!$task_id) {
				$this->_show_message(lang('server_control_empty_task_id'));
				return false;
		} else {
				$task_id = (int)$task_id;
		}

		$this->load->helper('date');

		$local_tpl = array();

		// Получение информации об удаляемом задании
		$where = array('id' => $task_id);
		$query = $this->db->get_where('cron', $where, 1);

		if($query->num_rows() > 0){
			$task_list = $query->result_array();
		} else {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		}

		/* Задание может не относится к серверу, такие нам не нужны */
		if(!$task_list[0]['server_id']) {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		}

		/* Получение данных сервера и привилегий на сервер */
		$this->servers->server_data = $this->servers->get_server_data($task_list[0]['server_id']);
		$this->users->get_server_privileges($task_list[0]['server_id']);

		/* Проверочки */
		if(!$this->users->auth_servers_privileges['VIEW']) {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		} elseif(!$this->servers->server_data) {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		} elseif(!$this->users->auth_servers_privileges['TASK_MANAGE']) {
			$this->_show_message(lang('server_control_no_task_privileges'));
			return false;
		}

		if($confirm != 'confirm') {

			/* Пользователь не подвердил намерения */
			$confirm_tpl['message'] = lang('server_control_task_delete_confirm');
			$confirm_tpl['confirmed_url'] = site_url('admin/server_control/delete_task/' . $task_id . '/confirm');
			$this->tpl['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);

		} else {
			$this->db->where('id', $task_id);
			$this->db->delete('cron');

			// Сохраняем логи
			$log_data['type'] = 'server_task';
			$log_data['command'] = 'delete_task';
			$log_data['user_name'] = $this->users->auth_login;
			$log_data['server_id'] = $task_list[0]['server_id'];
			$log_data['msg'] = 'Delete task';
			$log_data['log_data'] = '';
			$this->panel_log->save_log($log_data);

			$this->_show_message(lang('server_control_task_deleted'), site_url('/admin/server_control/main/' . $task_list[0]['server_id']), 'Далее');
			return true;
		}

		$this->parser->parse('main.html', $this->tpl);
	}

	//-----------------------------------------------------------

	/**
     * Добавление нового задания для сервера
     *
     * @param int - id сервера
     *
    */
    function edit_task($task_id)
    {

		if(!$task_id) {
			$this->_show_message(lang('server_control_empty_task_id'));
			return false;
		} else {
			$task_id = (int)$task_id;
		}

		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->helper('date');

		$local_tpl = array();

		// Получение информации о редактируемом задании
		$where = array('id' => $task_id);
		$query = $this->db->get_where('cron', $where, 1);

		if($query->num_rows() > 0){
			$task_list = $query->result_array();
		} else {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		}

		/* Задание может не относится к серверу, такие нам не нужны */
		if(!$task_list[0]['server_id']) {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		}

		/* Получение данных сервера и привилегий на сервер */
		$this->servers->server_data = $this->servers->get_server_data($task_list[0]['server_id']);
		$this->users->get_server_privileges($task_list[0]['server_id']);

		/* Проверочки */
		if(!$this->users->auth_servers_privileges['VIEW']) {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		} elseif(!$this->servers->server_data) {
			$this->_show_message(lang('server_control_task_not_found'));
			return false;
		} elseif(!$this->users->auth_servers_privileges['TASK_MANAGE']) {
			$this->_show_message(lang('server_control_no_task_privileges'));
			return false;
		}

		/* Правила для формы */
		$this->form_validation->set_rules('name', lang('title'), 'trim|max_length[64]');
		$this->form_validation->set_rules('command', lang('server_control_param_for_command'), 'trim|max_length[128]');

		$this->form_validation->set_rules('date_perform', lang('server_control_execution_date'), 'trim|required|max_length[19]');
		$this->form_validation->set_rules('time_add', lang('server_control_repeat_period'), 'trim|required|integer|max_length[16]');

		if($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			$options['code'] = array(
				'server_start' => 	lang('server_control_start'),
				'server_stop' => 	lang('server_control_stop'),
				'server_restart' =>	lang('server_control_restart'),
				'server_update' =>	lang('server_control_update'),
				'server_rcon' =>	lang('server_control_rcon_send')
			);

			$options['time_add'] = array(
				'0' => 		 lang('server_control_never'),
				'43200' =>	 lang('server_control_twelve_hours'),
				'86400' =>	 lang('server_control_day'),
				'172800' =>	 lang('server_control_two_day'),
				'604800' =>	 lang('server_control_week'),
				'2592000' => lang('server_control_month'),
			);

			$local_tpl['human_code'] = $options['code'][ $task_list[0]['code'] ];

			/* Создание форм */

			$local_tpl['input_time_add'] = form_dropdown('time_add', $options['time_add'], $task_list[0]['time_add']);

			$local_tpl['code'] = $task_list[0]['code'];
			$local_tpl['command'] = $task_list[0]['command'];
			$local_tpl['task_id'] = $task_list[0]['id'];
			$local_tpl['name'] = $task_list[0]['name'];
			$local_tpl['date_perform'] = unix_to_human($task_list[0]['date_perform'], false, 'eu');

			$this->tpl['content'] .= $this->parser->parse('servers/task_edit.html', $local_tpl, true);
		} else {
			$sql_data['name'] = $this->input->post('name') ? $this->input->post('name') : $task_list[0]['name'];

			// Код больше не редактируется
			//~ $sql_data['code'] = $this->input->post('code');

			$sql_data['command'] = $this->input->post('command');
			$sql_data['time_add'] = $this->input->post('time_add');

			if(!$sql_data['date_perform'] = human_to_unix($this->input->post('date_perform'))) {
				$this->_show_message(lang('server_control_date_unavailable'), 'javascript:history.back()');
				return false;
			}

			if ($task_list[0]['code'] == 'server_rcon' && empty($sql_data['command'])) {
				$this->_show_message(lang('server_control_empty_rcon_command'));
				return;
			}

			// Сбрасываем, если заданание уже выполнялось
			$sql_data['date_performed'] = '';

			//$sql_data['time_add'] = $this->input->post('time_add');

			$this->db->where('id', $task_id);
			$this->db->update('cron', $sql_data);

			// Сохраняем логи
			$log_data['type'] = 'server_task';
			$log_data['command'] = 'edit_task';
			$log_data['user_name'] = $this->users->auth_login;
			$log_data['server_id'] = $task_list[0]['server_id'];
			$log_data['msg'] = 'Edit task';
			$log_data['log_data'] = 'Name: ' . $sql_data['name'];
			$this->panel_log->save_log($log_data);

			$this->_show_message(lang('server_control_task_saved'), site_url('/admin/server_control/main/' . $task_list[0]['server_id']), 'Далее');
			return true;
		}

		$this->parser->parse('main.html', $this->tpl);
	}


}

/* End of file server_control.php */
/* Location: ./application/controllers/admin/server_control.php */
