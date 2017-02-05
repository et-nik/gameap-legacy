<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (GameAP)
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014-2016, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
*/

use \Myth\Controllers\BaseController;
use GameQ\GameQ;

/**
 * Управление серверами
 *
 * Контроллер управляет выделенными серверами, игровыми серверами,
 * играми и игровыми модификациями.
 * Позволяет производить следующие действия: добавление, редактирование,
 * удаление, дублирование игровой модификации.
 *
 * Установку игровых серверов производит модуль cron, adm_servers лишь
 * делает запись о том, что сервер нужно установить.
 *
 * Переустановка игровых серверов делается заданием значения 0 поля
 * installed таблицы servers.
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 */

class Adm_servers extends BaseController {

	var $available_control_protocols = array('gdaemon');

	public function __construct()
    {
        parent::__construct();

		$this->load->database();
        $this->load->model('users');
        $this->lang->load('adm_servers');
        $this->lang->load('server_control');
        $this->lang->load('main');

        $this->load->model('servers');
        $this->load->model('servers/dedicated_servers');
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');

		$this->load->helper('string');
		$this->load->helper('ds');

        if (!$this->users->check_user()) {
            redirect('auth');
        }

        //Base Template
        $this->tpl['title'] 	= lang('adm_servers_title_index');
        $this->tpl['heading'] 	= lang('adm_servers_heading_index');
        $this->tpl['content'] 	= '';

        /* Есть ли у пользователя права */
        if(!$this->users->auth_privileges['srv_global']) {
            redirect('admin');
        }

        $this->load->model('gdaemon_tasks');

        $this->load->model('servers');
        $this->load->library('form_validation');
        $this->load->helper('form');

        $this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
        $this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
    }

    // -----------------------------------------------------------------

    /**
     * Show info message
     *
     * @param string    $message
     * @param string    $link
     * @param string    $link_test
    */
    private function _show_message($message = false, $link = false, $link_text = false)
    {
        $message 	OR $message = lang('error');
		$link 		OR $link = 'javascript:history.back()';
		$link_text 	OR $link_text = lang('back');

        $local_tpl['message'] = $message;
        $local_tpl['link'] = $link;
        $local_tpl['back_link_txt'] = $link_text;

        $this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }

	// -----------------------------------------------------------------

	/**
	 * Проверка GameAP Daemon
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @return bool
	*/
	private function _check_gdaemon($host, $privkey, $keypass, $login, $password)
	{
		return true;
	}

	// -----------------------------------------------------------------

	/**
	 * Данные по умолчанию для игрового сервера
	 *
	 * @param array - некоторые данные о сервере (такие как ОС, движок и др.)
	 * @return array
	 *
	*/
	function _gs_default_data($data)
	{
		$this->load->driver('installer');

		if (!$this->dedicated_servers->ds_list) {
			$where = array('id' => $data['ds_id']);
			$this->dedicated_servers->get_ds_list($where, 1);
		}

		if (!$this->games->games_list) {
			$where = array('code' => $data['game']);
			$this->games->get_games_list($where, 1);
		}

		foreach ($this->dedicated_servers->ds_list as &$ds) {
			if ($ds['id'] == $data['ds_id']) {
				$os = strtolower($ds['os']);
				break;
			} else {
				$os = 'linux';
			}
		}

		$this->installer->set_game_variables($this->games->games_list[0]['start_code'],
												$this->games->games_list[0]['engine'],
												$this->games->games_list[0]['engine_version']
		);

		$this->installer->set_os($os);
		$this->installer->server_data = $data;

		// Список портов
		$ports = $this->installer->get_ports();
		$data['query_port'] = $ports[1];
		$data['rcon_port'] 	= $ports[2];

		$data['aliases'] = json_encode($this->installer->get_default_parameters());
		$data['start_command'] 	= $this->installer->get_start_command();

		/* Присваиваем значения пути к картам и имя screen  */
		$data['screen_name'] = $data['game'] . '_' . random_string('alnum', 6) . '_' . $data['server_port'];

		// Прочие данные
		$this->installer->change_server_data($data);

		return $data;
	}

	// -----------------------------------------------------------------

	/**
	 * Получение данных фильтра для вставки в шаблон
	 */
	private function _get_gservers_tpl_filter($filter = false)
	{
		$this->load->model('servers');

		if (!$filter) {
			$filter = $this->users->get_filter('servers_list');
		}

		$this->servers->select_fields('game, server_ip');

		$games_array 	= array();
		$ip_array		= array();

		if ($servers_list = $this->servers->get_list()) {
			foreach($this->servers->get_list() as $server) {
				if (!in_array($server['game'], $games_array)) {
					$games_array[] 	= $server['game'];
				}

				if (!in_array($server['server_ip'], $ip_array)) {
					$ip_array[ $server['server_ip'] ]		= $server['server_ip'];
				}
			}
		}

		if (empty($this->games->games_list)) {
			$this->games->get_active_games_list();
		}

		foreach($this->games->games_list as &$game) {
			$games_option[ $game['code'] ] = $game['name'];
		}

		$tpl['filter_name']			= isset($filter['name']) ? $filter['name'] : '';
		$tpl['filter_ip']				= isset($filter['ip']) ? $filter['ip'] : '';

		$tpl['filter_ip_dropdown']		= form_multiselect('filter_ip[]', $ip_array, $tpl['filter_ip']);

		$default = isset($filter['game']) ? $filter['game'] : null;
		$tpl['filter_games_dropdown'] 	= form_multiselect('filter_game[]', $games_option, $default);

		return $tpl;
	}

	// -----------------------------------------------------------------

	/**
	 *
	 * Функция получает IP адрес для игрового сервера, если он не указан
	*/
	private function _get_default_ip($ds_id = false)
	{
		if($ds_id) {
			foreach($this->dedicated_servers->ds_list as $array) {
				if($ds_id == $array['id']) {
					/* Первый IP из списка */
					return $array['ip'][0];
				}
			}
		} else {
			return '127.0.0.1';
		}
	}

	// -----------------------------------------------------------------

    //Главная
    public function index()
    {
		$this->parser->parse('main.html', $this->tpl);
	}

	// -----------------------------------------------------------------

	/**
	 * Просмотр списка
	 * 	выделенных серверов
	 * 	игровых серверов
	 * 	игр
	 * 	типов игр
	 *
	 * @param string - тип
	 * 			dedicated_servers - выделенные серверы
	 * 			game_servers - игровые серверы
	 * 			games - игры
	 * 			type_games - типы игр
	 *
	 *
	*/
	public function view($type = 'dedicated_servers', $id = false)
	{
		if($this->users->auth_id){
			// Пользователь авторизован

			$local_tpl = array();
			$error_msg = false;

			switch ($type) {
				case 'dedicated_servers':
					$this->load->model('servers/dedicated_servers');

					$this->tpl['title'] 	= lang('adm_servers_title_ds');
					$this->tpl['heading'] 	= lang('adm_servers_heading_ds');

					$parse_list_file = 'adm_servers/dedicated_servers_list.html';	// Шаблон списка
					$local_tpl['ds_list'] = $this->dedicated_servers->tpl_data_ds();

					break;

				case 'game_servers':
					$this->load->helper('games');

					$this->tpl['title'] 	= lang('adm_servers_title_gs');
					$this->tpl['heading'] 	= lang('adm_servers_heading_gs');

					$parse_list_file = 'adm_servers/game_servers_list.html';	// Шаблон списка

					$filter = $this->users->get_filter('servers_list');
					$local_tpl = $this->_get_gservers_tpl_filter();

					$this->servers->set_filter($filter);
					$this->servers->get_server_list(false, false, array());

					$local_tpl['games_list'] = servers_list_to_games_list($this->servers->servers_list);

					//~ $local_tpl['servers_list'] = $this->servers->tpl_data();

					break;

				case 'games':
					$this->load->model('servers/game_types');
					$this->load->model('servers/games');

					$this->tpl['title'] 	= lang('adm_servers_title_gt');
					$this->tpl['heading'] 	= lang('adm_servers_heading_gt');

					$parse_list_file = 'adm_servers/games_list.html';	// Шаблон списка

					if (!$error_msg) {
						/* Получение игр */
						if($games_list = $this->games->get_games_list()){
							$num = 0;
							$tpl = array();
							foreach ($games_list as $games) {

									//~ /* Если у игры нет модификаций, то не отображаем ее */
									//~ if(!$this->game_types->get_gametypes_list($where)){
										//~ continue;
									//~ }

									$tpl[$num]['gt_list'] = $this->game_types->tpl_data_game_types(array('game_code' => $games['code']));

									$tpl[$num]['game_name'] = $games['name'];
									$tpl[$num]['game_code'] = $games['code'];
									$tpl[$num]['game_start_code'] = $games['start_code'];
									$tpl[$num]['game_engine'] = $games['engine'];
									$tpl[$num]['game_engine_version'] = $games['engine_version'];

									$num++;
								}

							$local_tpl['games_list'] = $tpl;

						} else {
							$error_msg .= '<p>' . lang('adm_servers_games_unavailable') . '</p>';
						}
					}

					break;

				case 'game_types':
					redirect('/adm_servers/view/games');
					break;

				default:
					redirect('/adm_servers/view/dedicated_servers');
					break;
			}

			// Верхняя оболочка, в качестве меню
			if(isset($parse_file)){
				$this->tpl['content'] .= $this->parser->parse($parse_file, $local_tpl, true);
			}

			/* Если ошибок никаких, то отображаем список */
			if(!$error_msg){
				$this->tpl['content'] .= $this->parser->parse($parse_list_file, $local_tpl, true);
			}else{
				$this->tpl['content'] .= $error_msg;
			}

		}else{
			redirect();
		}

		$this->parser->parse('main.html', $this->tpl);

	}

	// -----------------------------------------------------------------

	/**
	 * Добавление
	 * 	выделенных серверов
	 * 	игровых серверов
	 * 	игр
	 * 	типов игр
	 *
	 * @param string - тип
	 * 			dedicated_servers - выделенные серверы
	 * 			game_servers - игровые серверы
	 * 			games - игры
	 * 			type_games - типы игр
	 *
	 * @param string - служит для передачи дополнительных параметров
	 * @param string
	 *
	 *
	*/
	public function add($type = 'dedicated_servers', $param_1 = false, $param_2 = false)
	{
		if($this->users->auth_id) {
			// Пользователь авторизован

			$local_tpl = array();
			$error_msg = false;

			/* Параметры для форм, задание правил проверки
			 * title страниц, файлы шаблонов
			*/
			switch ($type) {
				case 'dedicated_servers':

					/* --------------------------------------------	*/
					/* 				Выделенные серверы 				*/
					/* --------------------------------------------	*/

					$this->load->model('servers/dedicated_servers');

					// Добавление выделенного сервера
					$this->tpl['title'] 	= lang('adm_servers_title_add_ds');
					$this->tpl['heading'] 	= lang('adm_servers_heading_add_ds');

					$tpl_file_add = 'adm_servers/dedicated_servers_add.html';

					/* Проверка формы */
					$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]');
					$this->form_validation->set_rules('os', lang('os'), 'trim|required|max_length[64]|min_length[3]');
					$this->form_validation->set_rules('location', lang('adm_servers_location'), 'trim|required|max_length[64]|min_length[3]');
					$this->form_validation->set_rules('provider', lang('adm_servers_provider'), 'trim|max_length[64]|min_length[3]');
					$this->form_validation->set_rules('ip', lang('ip'), 'trim|required');
					$this->form_validation->set_rules('ram', lang('adm_servers_ram'), 'trim|max_length[64]');
					$this->form_validation->set_rules('cpu', lang('adm_servers_cpu'), 'trim|max_length[64]');
					$this->form_validation->set_rules('steamcmd_path', lang('adm_servers_steamcmd_path'), 'trim|max_length[256]');
					$this->form_validation->set_rules('script_path', lang('adm_servers_script_path'), 'trim|max_length[256]');

					$this->form_validation->set_rules('gdaemon_host', lang('adm_servers_gdaemon_host'), 'trim|max_length[64]');
                    $this->form_validation->set_rules('gdaemon_privkey', lang('adm_servers_gdaemon_privkey'), 'trim|max_length[64]');
                    $this->form_validation->set_rules('gdaemon_pubkey', lang('adm_servers_gdaemon_pubkey'), 'trim|max_length[64]');
                    $this->form_validation->set_rules('gdaemon_keypass', lang('adm_servers_gdaemon_keypass'), 'trim|max_length[64]');
                    $this->form_validation->set_rules('gdaemon_login', lang('adm_servers_gdaemon_login'), 'trim|max_length[64]');
                    $this->form_validation->set_rules('gdaemon_password', lang('adm_servers_gdaemon_password'), 'trim|max_length[64]');

					break;

				case 'game_servers':
					redirect('adm_servers/install_game_server');
					break;

				case 'games':

					/* --------------------------------------------	*/
					/* 				Игры			 				*/
					/* --------------------------------------------	*/

					$this->load->model('servers/games');

					$this->tpl['title'] 	= lang('adm_servers_title_add_game');
					$this->tpl['heading'] 	= lang('adm_servers_heading_add_game');

					$tpl_file_add = 'adm_servers/games_add.html';

					$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[32]|min_length[3]');
					$this->form_validation->set_rules('code', lang('adm_servers_game_code'), 'trim|required|is_unique[games.code]|max_length[64]|min_length[2]');
					$this->form_validation->set_rules('start_code', lang('adm_servers_game_start_code'), 'trim|required|max_length[32]|min_length[2]');
					$this->form_validation->set_rules('engine', lang('adm_servers_engine'), 'trim|required|max_length[64]|min_length[3]');
					$this->form_validation->set_rules('engine_version', lang('adm_servers_engine_version'), 'trim|max_length[11]');

					$this->form_validation->set_rules('app_id', 'app_id', 'trim|integer|max_length[32]');
					$this->form_validation->set_rules('app_set_config', 'app_set_config', 'trim|max_length[32]');

					$this->form_validation->set_rules('local_repository', lang('adm_servers_local_repository'), 'trim');
					$this->form_validation->set_rules('remote_repository', lang('adm_servers_remote_repository'), 'trim');

					break;

				case 'game_types':

					/* --------------------------------------------	*/
					/* 				Игровые моды					*/
					/* --------------------------------------------	*/

					$this->load->model('servers/game_types');
					$this->load->model('servers/games');

					$this->tpl['title'] 	= lang('adm_servers_title_add_game_type');
					$this->tpl['heading'] 	= lang('adm_servers_heading_add_game_type');

					$this->form_validation->set_rules('code', 'код игры', 'trim|required|max_length[64]|min_length[2]');
					$this->form_validation->set_rules('name', 'название игры', 'trim|required|max_length[32]|min_length[2]');

					if($tpl_list = $this->games->tpl_data_games()) {
						$local_tpl['games_list'] = $tpl_list;
					}

					if(empty($this->games->games_list)) {
						$this->_show_message(lang('adm_servers_empty_games_list', base_url() . 'adm_servers/add/games'));
						return false;
					}

					$tpl_file_add = 'adm_servers/game_types_add.html';
					break;
				default:
					redirect('');
					break;
			}

			/* Проверяем форму */
			if ($this->form_validation->run() == false) {

				if (validation_errors()) {
					$this->_show_message(validation_errors());
					return false;
				}

				if (!isset($tpl_file_add)) {
					$this->_show_message('', $link = 'javascript:history.back()');
					return false;
				} else {
					$local_tpl['message'] = '';
					$local_tpl['back_link_txt'] = lang('back');
					$local_tpl['link'] = 'javascript:history.back()';
					$this->tpl['content'] .= $this->parser->parse($tpl_file_add, $local_tpl, true);
				}


			} else {

				/*
				 * Проверка пройдена
				 * Подготовка данных и отправка их в базу
				 *
				 * Все данные проходят XSS фильтрацию, это указывается добавлением параметра
				 * xss_clean при задании правил, либо через заданием true:
				 * $this->input->post('name', true);
				 *
				*/

				$local_tpl = array();

				switch($type){
					case 'dedicated_servers':

						/* --------------------------------------------	*/
						/* 				Выделенные серверы 				*/
						/* --------------------------------------------	*/

						$sql_data['name'] 				= $this->input->post('name');
						$sql_data['os'] 				= strtolower($this->input->post('os'));
						$sql_data['location'] 			= $this->input->post('location');
						$sql_data['provider'] 			= $this->input->post('provider');

						/* Обработка списка IP адресов */
						$ip_list = explode(',', str_replace(' ', '', $this->input->post('ip')));
						$sql_data['ip'] = json_encode($ip_list);

						$sql_data['ram'] = (int)$this->input->post('ram');
						$sql_data['cpu'] = (int)$this->input->post('cpu');

						$sql_data['steamcmd_path'] = $this->input->post('steamcmd_path');

                        $sql_data['gdaemon_host'] 	    = $this->input->post('gdaemon_host');
                        $sql_data['gdaemon_privkey'] 	= $this->input->post('gdaemon_privkey');
                        $sql_data['gdaemon_pubkey'] 	= $this->input->post('gdaemon_pubkey');
                        $sql_data['gdaemon_keypass'] 	= $this->input->post('gdaemon_keypass');
                        $sql_data['gdaemon_login'] 		= $this->input->post('gdaemon_login');
                        $sql_data['gdaemon_password'] 	= $this->input->post('gdaemon_password');

						/*
						 * Проверка указандых данных gdaemon, ssh, telnet, ftp
						 * чтобы пароль подходил
						*/

						// GDaemon
						if (!empty($sql_data['gdaemon_host'])) {
                            if (false == $this->_check_gdaemon(
                                $sql_data['gdaemon_host'],
                                $sql_data['gdaemon_privkey'],
                                $sql_data['gdaemon_keypass'],
                                $sql_data['gdaemon_login'],
                                $sql_data['gdaemon_password'])
                            ) {
								$this->_show_message(lang('adm_servers_gdaemon_data_unavailable'), 'javascript:history.back()');
								return false;
							}
						}

						// Добавление сервера
						if ($this->dedicated_servers->add_dedicated_server($sql_data)) {
							$local_tpl['message'] = lang('adm_servers_add_server_successful');
						} else {
							$local_tpl['message'] = lang('adm_servers_add_server_failed');
						}

						// Записываем логи
						$log_data['type'] 			= 'adm_servers';
						$log_data['command'] 		= 'add_ds';
						$log_data['server_id'] 		= 0;
						$log_data['user_name'] 		= $this->users->auth_login;
						$log_data['msg'] 			= $local_tpl['message'];
						$log_data['log_data'] 		= '';
						$this->panel_log->save_log($log_data);

						$local_tpl['link'] = site_url('adm_servers/view/dedicated_servers');
						$local_tpl['back_link_txt'] = lang('adm_servers_back_to_servers');

						break;

					case 'game_servers':
						redirect('adm_servers/install_game_server');
						break;

					case 'games':

						/* --------------------------------------------	*/
						/* 				Игры							*/
						/* --------------------------------------------	*/

						$sql_data['code'] 			= $this->input->post('code');
						$sql_data['start_code'] 	= $this->input->post('start_code');
						$sql_data['name'] 			= $this->input->post('name');
						$sql_data['engine'] 		= $this->input->post('engine');
						$sql_data['engine_version'] = $this->input->post('engine_version');

						$sql_data['app_id'] 		= $this->input->post('app_id');
						$sql_data['app_set_config'] = $this->input->post('app_set_config');

						$sql_data['local_repository'] 	= $this->input->post('local_repository');
						$sql_data['remote_repository'] 	= $this->input->post('remote_repository');

                        // Проверка наличия файла в удалённом репозитории
                        if ($sql_data['remote_repository'] != "" && !remote_file_exists($sql_data['remote_repository'])) {
                            $this->_show_message('adm_servers_rep_file_not_exists');
                            return false;
                        }

						// Проверяем наличие Query класса
						if (!file_exists(FCPATH . 'vendor/knik/gameq/src/GameQ/Protocols/' . ucfirst(strtolower($sql_data['engine'])) . '.php')) {
							$this->_show_message('adm_servers_unknown_engine');
							return false;
						}

						/* Убираем кавычки из app_set_config */
						$sql_data['app_set_config'] = str_replace('\'', '', $sql_data['app_set_config']);
						$sql_data['app_set_config'] = str_replace('"', '', $sql_data['app_set_config']);
						$sql_data['app_set_config'] = str_replace('	', '', $sql_data['app_set_config']);

						if($this->games->add_game($sql_data)){
							$local_tpl['message'] = lang('adm_servers_add_game_successful');
						}else{
							$local_tpl['message'] = lang('adm_servers_add_game_failed');
						}

						// Записываем логи
						$log_data['type'] 			= 'adm_servers';
						$log_data['command'] 		= 'add_game';
						$log_data['server_id'] 		= 0;
						$log_data['user_name'] 		= $this->users->auth_login;
						$log_data['msg'] 			= $local_tpl['message'];
						$log_data['log_data'] 		= '';
						$this->panel_log->save_log($log_data);

						$local_tpl['link'] = site_url('adm_servers/view/games');
						$local_tpl['back_link_txt'] = lang('adm_servers_back_to_games');


						break;

					case 'game_types':

						/* --------------------------------------------	*/
						/* 				Игровые модификации				*/
						/* --------------------------------------------	*/

						$sql_data['game_code'] = $this->input->post('code');
						$sql_data['name'] = $this->input->post('name');

						if($this->game_types->add_game_type($sql_data)) {
							$local_tpl['message'] = lang('adm_servers_add_game_type_successful');
						} else {
							$local_tpl['message'] = lang('adm_servers_add_game_type_failed');
						}

						// Записываем логи
						$log_data['type'] 			= 'adm_servers';
						$log_data['command'] 		= 'add_game_type';
						$log_data['server_id'] 		= 0;
						$log_data['user_name'] 		= $this->users->auth_login;
						$log_data['msg'] 			= $local_tpl['message'];
						$log_data['log_data'] 		= '';
						$this->panel_log->save_log($log_data);

						$local_tpl['link'] = site_url('adm_servers/edit/game_types/' . $this->db->insert_id());
						$local_tpl['back_link_txt'] = 'Далее';

						break;

				}

				$this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);

			}

		} else {
			redirect('');
		}

		$this->parser->parse('main.html', $this->tpl);

	}

	// -----------------------------------------------------------------

	/**
	 * Удаление
	 * 	выделенных серверов
	 * 	игровых серверов
	 * 	игр
	 * 	типов игр
	 *
	 * @param string - тип
	 * 			dedicated_servers - выделенные серверы
	 * 			game_servers - игровые серверы
	 * 			games - игры
	 * 			type_games - типы игр
	 *
	 * @param string - служит для передачи дополнительных параметров
	 * @param string
	 *
	 *
	*/
	public function delete($type = 'dedicated_servers', $id = false, $confirm = false)
	{
		$this->load->model('gdaemon_tasks');

        if (!$this->users->auth_id) {
            redirect('auth/in');
        }

        $local_tpl = array();
        $error_msg = false;

        if ($confirm == $this->security->get_csrf_hash()) {

            switch($type) {
                case 'dedicated_servers':

                    /* --------------------------------------------	*/
                    /* 				Выделенные серверы 				*/
                    /* --------------------------------------------	*/

                    $this->load->model('servers/dedicated_servers');

                    if (!$this->dedicated_servers->get_ds_list(array('id' => $id))) {
                        $this->_show_message(lang('adm_servers_selected_ds_unavailable'), site_url('adm_servers/view/dedicated_servers'));
                        return false;
                    }

                    if ($this->servers->get_server_list(false, false, array('ds_id' => $id))) {
                        $this->_show_message(lang('adm_servers_ds_contains_game_servers'), site_url('adm_servers/view/dedicated_servers'));
                        return false;
                    }

                    if ($this->dedicated_servers->del_dedicated_server($id)) {
                        $local_tpl['message'] = lang('adm_servers_delete_server_successful');
                    } else {
                        $local_tpl['message'] = lang('adm_servers_delete_server_failed');
                    }

                    // Записываем логи
                    $log_data['type'] 			= 'adm_servers';
                    $log_data['command'] 		= 'delete_ds';
                    $log_data['server_id'] 		= 0;
                    $log_data['user_name'] 		= $this->users->auth_login;
                    $log_data['msg'] 			= $local_tpl['message'];
                    $log_data['log_data'] 		= 'ID: ' . $id;
                    $this->panel_log->save_log($log_data);

                    $local_tpl['link'] 			= site_url('adm_servers/view/dedicated_servers');
                    $local_tpl['back_link_txt'] 	= lang('adm_servers_back_to_servers');

                    break;

                case 'game_servers':
                    /* --------------------------------------------	*/
                    /* 				Игровые серверы 				*/
                    /* --------------------------------------------	*/

                    $server_id = &$id;

                    if(!$this->servers->get_server_data($server_id)) {
                        $this->_show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/game_servers'));
                        return false;
                    }

                    // Для логов
                    $files_deleted = 'false';

                    if ($this->input->post('delete_files') && $this->servers->server_data['installed']) {
                        $task_id = $this->gdaemon_tasks->add(array(
                            'ds_id'         => $this->servers->server_data['ds_id'],
                            'server_id'     => $server_id,
                            'time_create'   => now(),
                            'time_stchange' => now(),
                            'task'          => 'gsdel',
                            'status'        => 'waiting',
                        ));

                        $this->_show_message(lang('adm_servers_delete_task_added'), site_url('adm_servers/view/game_servers'), lang('adm_servers_back_to_servers'));
                        return;
                    }

                    if ($this->servers->delete_game_server($server_id)) {
                        $local_tpl['message'] = lang('adm_servers_delete_server_successful');
                    } else {
                        $local_tpl['message'] = lang('adm_servers_delete_server_failed');
                    }

                    // Записываем логи
                    $log_data['type'] 			= 'adm_servers';
                    $log_data['command'] 		= 'delete_game_server';
                    $log_data['server_id'] 		= $server_id;
                    $log_data['user_name'] 		= $this->users->auth_login;
                    $log_data['msg'] 			= $local_tpl['message'];
                    $log_data['log_data'] 		= 'ID: ' . $server_id . ' Files deleted: ' . $files_deleted;
                    $this->panel_log->save_log($log_data);

                    $local_tpl['link'] = site_url('adm_servers/view/game_servers');
                    $local_tpl['back_link_txt'] = lang('adm_servers_back_to_servers');

                    break;

                case 'games':

                    /* --------------------------------------------	*/
                    /* 				Игры			 				*/
                    /* --------------------------------------------	*/

                    $this->load->model('servers/games');

                    if(!$this->games->get_games_list(array('code' => $id))) {
                        $this->_show_message(lang('adm_servers_game_not_found'), site_url('adm_servers/view/games'));
                        return false;
                    }

                    if($this->servers->get_server_list(false, false, array('game' => $id))) {
                        $this->_show_message(lang('adm_servers_game_contains_game_servers'), site_url('adm_servers/view/games'));
                        return false;
                    }

                    if($this->games->delete_game($id)){
                        $local_tpl['message'] = lang('adm_servers_delete_game_successful');
                    }else{
                        $local_tpl['message'] = lang('adm_servers_delete_game_failed');
                    }

                    // Записываем логи
                    $log_data['type'] 			= 'adm_servers';
                    $log_data['command'] 		= 'delete_game';
                    $log_data['server_id'] 		= 0;
                    $log_data['user_name'] 		= $this->users->auth_login;
                    $log_data['msg'] 			= $local_tpl['message'];
                    $log_data['log_data'] 		= 'ID: ' . $id;
                    $this->panel_log->save_log($log_data);

                    $local_tpl['link'] 			= site_url('adm_servers/view/games');
                    $local_tpl['back_link_txt'] 	= lang('adm_servers_back_to_games');

                    break;

                case 'game_types':

                    /* --------------------------------------------	*/
                    /* 				Игровые модификации				*/
                    /* --------------------------------------------	*/

                    $this->load->model('servers/game_types');

                    if(!$this->game_types->get_gametypes_list(array('id' => $id))) {
                        $this->_show_message(lang('adm_servers_game_type_not_found'), site_url('adm_servers/view/game_types'));
                        return false;
                    }

                    if($this->servers->get_server_list(false, false, array('game_type' => $id))) {
                        $this->_show_message(lang('adm_servers_game_type_contains_game_servers'), site_url('adm_servers/view/game_types'));
                        return false;
                    }

                    /* Удаление модификации */
                    if($this->game_types->delete_game_type($id)){
                        $local_tpl['message'] = lang('adm_servers_delete_game_type_successful');
                    }else{
                        $local_tpl['message'] = lang('adm_servers_delete_game_type_failed');
                    }

                    // Записываем логи
                    $log_data['type'] 			= 'adm_servers';
                    $log_data['command'] 		= 'delete_game_type';
                    $log_data['server_id'] 		= 0;
                    $log_data['user_name'] 		= $this->users->auth_login;
                    $log_data['msg'] 			= $local_tpl['message'];
                    $log_data['log_data'] 		= 'ID: ' . $id;
                    $this->panel_log->save_log($log_data);

                    $local_tpl['link'] 			= site_url('adm_servers/view/game_types');
                    $local_tpl['back_link_txt'] 	= lang('adm_servers_back_to_game_types');

                    break;
                default:
                    $local_tpl['message'] 			= lang('adm_servers_unknown_page');
                    $local_tpl['link'] 			= site_url('/adm_servers/view/game_types');
                    $local_tpl['back_link_txt'] 	= lang('adm_servers_back_to_game_types');
                    break;
            }

            // Отображаем инфо сообщение
            $this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);

        } else {

            /* Пользователь не подвердил */

            switch($type){
                case 'dedicated_servers':
                    $confirm_tpl['message'] = lang('adm_servers_delete_ds_confirm');
                    $confirm_tpl['confirmed_url'] = site_url('adm_servers/delete/dedicated_servers/'. $id . '/' . $this->security->get_csrf_hash());
                    break;

                case 'game_servers':
                    $confirm_tpl['extra_checkbox'] = form_checkbox('delete_files', 'accept', true, 'id="extra"');
                    $confirm_tpl['extra_text']		= lang('adm_servers_delete_files');
                    $confirm_tpl['message'] = lang('adm_servers_delete_gs_confirm');
                    $confirm_tpl['confirmed_url'] = site_url('adm_servers/delete/game_servers/'. $id . '/' . $this->security->get_csrf_hash());
                    break;

                case 'games':
                    $confirm_tpl['message'] = lang('adm_servers_delete_game_confirm');
                    $confirm_tpl['confirmed_url'] = site_url('adm_servers/delete/games/'. $id . '/' . $this->security->get_csrf_hash());
                    break;

                case 'game_types':
                    $confirm_tpl['message'] = lang('adm_servers_delete_game_type_confirm');
                    $confirm_tpl['confirmed_url'] = site_url('adm_servers/delete/game_types/'. $id . '/' . $this->security->get_csrf_hash());
                    break;
            }

            $this->tpl['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
        }

		$this->parser->parse('main.html', $this->tpl);

	}


	// -----------------------------------------------------------

	/**
	 * Редактирование
	 * 	выделенных серверов
	 * 	игровых серверов
	 * 	игр
	 * 	типов игр
	 *
	 * @param string - тип
	 * 			dedicated_servers - выделенные серверы
	 * 			game_servers - игровые серверы
	 * 			games - игры
	 * 			type_games - типы игр
	 *
	 * @param string - служит для передачи дополнительных параметров
	 * @param string
	 *
	 *
	*/
	public function edit($type = 'dedicated_servers', $id = false, $param_2 = false)
	{
		$local_tpl = array();
		$error_msg = false;

		switch($type) {
			case 'dedicated_servers':

				/* --------------------------------------------	*/
				/* 				Выделенные серверы 				*/
				/* --------------------------------------------	*/

				$this->load->model('servers/dedicated_servers');

				if (!$this->dedicated_servers->get_ds_list(array('id' => $id), 1)) {
					$this->_show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/dedicated_servers'));
					return false;
				}

				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/dedicated_servers_control.html';

				$tpl_list = $this->dedicated_servers->tpl_data_ds();
				$local_tpl = $tpl_list[0];

				// Скрипты
				$local_tpl['script_start'] 			= quotes_to_entities($this->dedicated_servers->ds_list['0']['script_start']);
				$local_tpl['script_stop'] 			= quotes_to_entities($this->dedicated_servers->ds_list['0']['script_stop']);
				$local_tpl['script_restart'] 		= quotes_to_entities($this->dedicated_servers->ds_list['0']['script_restart']);
				$local_tpl['script_status'] 		= quotes_to_entities($this->dedicated_servers->ds_list['0']['script_status']);
				$local_tpl['script_get_console'] 	= quotes_to_entities($this->dedicated_servers->ds_list['0']['script_get_console']);
				$local_tpl['script_send_command'] 	= quotes_to_entities($this->dedicated_servers->ds_list['0']['script_send_command']);

				$local_tpl['work_path'] 		= $this->dedicated_servers->ds_list['0']['work_path'];
				$local_tpl['steamcmd_path'] 	= $this->dedicated_servers->ds_list['0']['steamcmd_path'];

				$local_tpl['gdaemon_host'] 		= $this->dedicated_servers->ds_list['0']['gdaemon_host'];
				$local_tpl['gdaemon_privkey'] 	= $this->dedicated_servers->ds_list['0']['gdaemon_privkey'];
				$local_tpl['gdaemon_pubkey'] 	= $this->dedicated_servers->ds_list['0']['gdaemon_privkey'];
				$local_tpl['gdaemon_keypass'] 	= $this->dedicated_servers->ds_list['0']['gdaemon_keypass'];
				$local_tpl['gdaemon_login'] 	= $this->dedicated_servers->ds_list['0']['gdaemon_login'];
				$local_tpl['gdaemon_password']  = $this->dedicated_servers->ds_list['0']['gdaemon_password'];

				$local_tpl['disabled_checkbox'] = form_checkbox('disabled', 'accept', $this->dedicated_servers->ds_list['0']['disabled']);

				// Получаем список серверов на DS
				$gs = $this->servers->get_game_servers_list(array('ds_id' => $id));

				$local_tpl['servers_list'] = $this->servers->tpl_data();

				/*
				 * Правила для формы
				 *
				 * Документация:
				 * http://cidocs.ru/213/libraries/form_validation.html
				 *
				*/
				$this->form_validation->set_rules('name', lang('title'), 'trim|required|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('os', lang('operationg_system'), 'trim|required|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('location', lang('adm_servers_location'), 'trim|required|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('provider', 'adm_servers_provider', 'trim|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('ip', 'IP', 'trim|required');
				$this->form_validation->set_rules('ram', 'RAM', 'trim|max_length[64]');
				$this->form_validation->set_rules('cpu', 'CPU', 'trim|max_length[64]');

				// Скрипты
				$this->form_validation->set_rules('script_start', lang('adm_servers_command_start'), 'trim|max_length[512]');
				$this->form_validation->set_rules('script_stop', lang('adm_servers_command_stop'), 'trim|max_length[512]');
				$this->form_validation->set_rules('script_restart', lang('adm_servers_command_restart'), 'trim|max_length[512]');
				$this->form_validation->set_rules('script_status', lang('adm_servers_command_status'), 'trim|max_length[512]');
				$this->form_validation->set_rules('script_get_console', lang('adm_servers_command_get_console'), 'trim|max_length[512]');
				$this->form_validation->set_rules('script_send_command', lang('adm_servers_send_command'), 'trim|max_length[512]');

				// Редактирование данных доступа к серверу (пароли ftp, ssh)
				$this->form_validation->set_rules('steamcmd_path', lang('adm_servers_steamcmd_path'), 'trim|max_length[256]');
				$this->form_validation->set_rules('work_path', lang('adm_servers_work_path'), 'trim|max_length[256]');

				$this->form_validation->set_rules('gdaemon_host', lang('adm_servers_gdaemon_host'), 'trim|max_length[64]');
				$this->form_validation->set_rules('gdaemon_privkey', lang('adm_servers_gdaemon_privkey'), 'trim|max_length[64]');
				$this->form_validation->set_rules('gdaemon_pubkey', lang('adm_servers_gdaemon_pubkey'), 'trim|max_length[64]');
				$this->form_validation->set_rules('gdaemon_keypass', lang('adm_servers_gdaemon_keypass'), 'trim|max_length[64]');
				$this->form_validation->set_rules('gdaemon_login', lang('adm_servers_gdaemon_login'), 'trim|max_length[64]');
				$this->form_validation->set_rules('gdaemon_password', lang('adm_servers_gdaemon_password'), 'trim|max_length[64]');

				break;

			case 'game_servers':

				/* --------------------------------------------	*/
				/* 				Игровые серверы					*/
				/* --------------------------------------------	*/

				$this->load->model('servers/dedicated_servers');
				$this->load->model('servers/games');
				$this->load->model('servers/game_types');
				$this->load->helper('form');

				//if(!$game_servers_list = $this->servers->get_game_servers_list(array('id' => $id), 1)){
				//	$this->_show_message('Сервера с таким ID не существует', '/adm_servers/view/game_servers');
				//	return false;
				//}

				if(!$this->servers->get_server_data($id)){
					$this->_show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/game_servers'));
					return false;
				}

				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/game_servers_control.html';

				$servers_list = $this->servers->tpl_data();

				$local_tpl = $servers_list[0];
				$local_tpl['information'] = array();

				// Для tpl
				$local_tpl['screen_name'] 			= $this->servers->server_data['screen_name'];
				$local_tpl['su_user'] 				= $this->servers->server_data['su_user'];
				$local_tpl['server_dir'] 			= $this->servers->server_data['dir'];
				$local_tpl['game_type_id']			= $this->servers->server_data['game_type'];
				$local_tpl['server_start_code']		= $this->servers->server_data['start_code'];

				$local_tpl['start_command'] 		= $this->servers->server_data['start_command'];

				// Замена фигурных скобок, чтобы в параметрах запуска шоткоды команды не считались за шоткоды шаблона
				$local_tpl['start_command'] 		= str_replace('{', '&#123;', $local_tpl['start_command']);
				$local_tpl['start_command'] 		= str_replace('}', '&#125;', $local_tpl['start_command']);

				$local_tpl['query_port'] 			= $this->servers->server_data['query_port'];
				$local_tpl['rcon_port'] 			= $this->servers->server_data['rcon_port'];

				$local_tpl['cpu_limit'] 			= $this->servers->server_data['cpu_limit'];
				$local_tpl['ram_limit'] 			= $this->servers->server_data['ram_limit'];
				$local_tpl['net_limit'] 			= $this->servers->server_data['net_limit'];

				/* Получаем абсолютный путь к корневой директории с сервером и к исполняемым файлам */
				$local_tpl['full_server_path']      = $this->servers->server_data['work_path'] . '/' . $this->servers->server_data['dir'];
				$local_tpl['work_path']             = $this->servers->server_data['work_path'];

				// Модификация
				$where = array('game_code' => $this->servers->server_data['game']);
				$gametypes_list = $this->game_types->get_gametypes_list($where);

				$gtypes_options = array();
				$i = 0;
				foreach($gametypes_list as $list) {
					$gtypes_options[$list['id']] = $list['name'];

					/* Узнаем ключ в массиве модификации которой принадлежит этот сервер */
					if ($list['id'] == $this->servers->server_data['game_type']) {
						$gt_key = $i;
					}

					$i ++;
				}

				$local_tpl['game_type_dropdown'] = array();
				$local_tpl['aliases_list'] = array();

				$server_aliases = $this->servers->server_data['aliases'];

				$local_tpl['game_type_dropdown'] 		= form_dropdown('game_type', $gtypes_options, $this->servers->server_data['game_type']);
				$local_tpl['server_enabled_checkbox'] 	= form_checkbox('enabled', 'accept', $this->servers->server_data['enabled']);

                $ds_list = $this->dedicated_servers->get_ds_list();

                $ds_options = array();
				foreach($ds_list as &$dsarr) {
					$ds_options[$dsarr['id']] = $dsarr['name'];
				}
                $local_tpl['ds_list_dropdown'] = form_dropdown('ds_id', $ds_options, $this->servers->server_data['ds_id']);

				// Заменяем двойные кавычки на html символы
				$local_tpl['start_command'] 	= str_replace('"', '&quot;', $local_tpl['start_command'] );

				/* Информация о DS */
				if ($this->servers->server_data['ds_id']) {

					$local_tpl['ds_name'] 		= $this->dedicated_servers->ds_list[0]['name'];
					$local_tpl['ds_id'] 		= $this->dedicated_servers->ds_list[0]['id'];
					$local_tpl['ds_location'] 	= $this->dedicated_servers->ds_list[0]['location'];
					$local_tpl['ds_provider'] 	= $this->dedicated_servers->ds_list[0]['provider'];
				} else {
					// Сервер локальный
					$local_tpl['ds_name'] 	= lang('adm_servers_local_server');
					$local_tpl['ds_id'] 	= 0;
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
				$where = array('server_id' => $id);
				$server_plogs = $this->panel_log->get_log($where, 100); // Логи сервера в админпанели

				$local_tpl['log_list'] = array();

				$log_num = 0;
				$i = 0;
				$count_i = count($server_plogs);
				while($i < $count_i){

					if($log_num == 15) {
						break;
					}

					$local_tpl['log_list'][$i]['log_id'] = $server_plogs[$i]['id'];
					$local_tpl['log_list'][$i]['log_date'] = unix_to_human($server_plogs[$i]['date'], true, 'eu');
					$local_tpl['log_list'][$i]['log_server_id'] = $server_plogs[$i]['server_id'];
					$local_tpl['log_list'][$i]['log_user_name'] = $server_plogs[$i]['user_name'];
					$local_tpl['log_list'][$i]['log_command'] = $server_plogs[$i]['command'];


					/* Код действия на понятный язык */
					switch($server_plogs[$i]['type']){
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

					$i ++;
				}

				/* ------------------------------ */
				/* Различная информация о сервере */
				/* ------------------------------ */


				if ($this->servers->server_data['installed'] == '0') {
					$local_tpl['information'][]['text'] = lang('adm_servers_serv_not_installed') . '<br />';
				} elseif ($this->servers->server_data['installed'] == '1') {
					$local_tpl['information'][]['text'] = lang('adm_servers_serv_installed') . '<br />';
				} elseif ($this->servers->server_data['installed'] == '2') {
					$local_tpl['information'][]['text'] = lang('adm_servers_serv_installed_proccess') . '<br />';
				}

				/*
				 * --------------------------------------------
				 * Проверка, имеются ли параметры в настройках
				 * --------------------------------------------
				*/

				/* Допустимые алиасы */
				$allowable_aliases = isset($this->servers->server_data['aliases_list'])
										? json_decode($this->servers->server_data['aliases_list'], true)
										: false;

				/* Значения алиасов на сервере */
				$server_aliases = $this->servers->server_data['aliases'];

				/* Прогон по алиасам */

				$empty_alias = '';
				if ($allowable_aliases && !empty($allowable_aliases)) {

					/* Если параметр пуст, то выводим сообщение с предупреждением */
					$i = 0;
					foreach ($allowable_aliases as $alias) {
						$local_tpl['aliases_list'][$i]['alias'] 		= $alias['alias'];
						$local_tpl['aliases_list'][$i]['desc'] 			= $alias['desc'];

						if(!isset($server_aliases[$alias['alias']]) OR empty($server_aliases[$alias['alias']])) {
							$empty_alias .= '"' . $alias['desc'] . '", ';
							$local_tpl['aliases_list'][$i]['alias_value'] 	= '<' . lang('value_not_set') . '>';
						} else {
							$local_tpl['aliases_list'][$i]['alias_value'] 	= htmlspecialchars($server_aliases[$alias['alias']]);
						}
						$i ++;
					}
				}


				if ($empty_alias != '') {
					$local_tpl['information'][]['text'] = lang('adm_servers_gs_empty_settings') . ': ' . $empty_alias;
				}

				/*
				 * --------------------------------------------
				 * Правила для формы
				 * --------------------------------------------
				*/
				$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]');

				$this->form_validation->set_rules('server_ip', lang('ip'), 'trim|max_length[64]|min_length[4]');
				$this->form_validation->set_rules('server_port', lang('port'), 'trim|required|integer|max_length[6]|min_length[2]');
				$this->form_validation->set_rules('query_port', lang('adm_servers_query_port'), 'trim|integer|max_length[6]|min_length[2]');
				$this->form_validation->set_rules('rcon_port', lang('adm_servers_rcon_port'), 'trim|integer|max_length[6]|min_length[2]');

				$this->form_validation->set_rules('rcon', 'RCON password', 'trim|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('game_type', lang('adm_servers_game_type'), 'trim|required|integer');
				$this->form_validation->set_rules('dir', lang('adm_servers_server_dir'), 'trim|required|max_length[64]');

				$this->form_validation->set_rules('screen_name', lang('adm_servers_screen_name'), 'trim|max_length[64]');
				$this->form_validation->set_rules('su_user', lang('adm_servers_user_start'), 'trim|max_length[64]');
				$this->form_validation->set_rules('start_command', lang('adm_servers_command_start'), 'trim|max_length[1024]');

				$this->form_validation->set_rules('cpu_limit', lang('adm_servers_cpu_limit'), 'trim|integer|less_than[100]');
				$this->form_validation->set_rules('ram_limit', lang('adm_servers_ram_limit'), 'trim|integer');
				$this->form_validation->set_rules('net_limit', lang('adm_servers_net_limit'), 'trim|integer');

				break;

			case 'games':

				/* --------------------------------------------	*/
				/* 				Игры							*/
				/* --------------------------------------------	*/

				$this->load->model('servers/games');
				$this->load->model('servers/game_types');

				if(!$this->games->get_games_list(array('code' => $id), 1)){
					$this->_show_message(lang('adm_servers_game_not_found'), site_url('adm_servers/view/games'));
					return false;
				}

				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/games_control.html';

				$tpl_list = $this->games->tpl_data_games();
				$local_tpl = $tpl_list[0];

				// Список модификаций
				$local_tpl['gt_list'] = $this->game_types->tpl_data_game_types(array('game_code' => $id));

				/* Правила для проверки формы */
				$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('code', lang('adm_servers_game_code'), 'trim|required|max_length[32]|min_length[2]');
				$this->form_validation->set_rules('start_code', lang('adm_servers_game_start_code'), 'trim|required|max_length[64]|min_length[2]');

				$this->form_validation->set_rules('engine', lang('adm_servers_engine'), 'trim|required|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('engine_version', lang('adm_servers_engine_version'), 'trim|max_length[64]');

				$this->form_validation->set_rules('app_id', 'app_id', 'trim|integer|max_length[32]');
				$this->form_validation->set_rules('app_set_config', 'app_set_config', 'trim|max_length[32]');

				$this->form_validation->set_rules('local_repository', lang('adm_servers_local_repository'), 'trim');
				$this->form_validation->set_rules('remote_repository', lang('adm_servers_remote_repository'), 'trim');

				break;

			case 'game_types':

				/* --------------------------------------------	*/
				/* 				Игровые модификации				*/
				/* --------------------------------------------	*/

				$this->load->model('servers/game_types');
				$this->load->model('servers/games');

				if(!$gt_list = $this->game_types->get_gametypes_list(array('id' => $id))){
					$this->_show_message(lang('adm_servers_game_type_not_found'), site_url('adm_servers/view/game_types'));
					return false;
				}

				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/game_types_control.html';

				$tpl_list = $this->game_types->tpl_data_game_types();
				$local_tpl = $tpl_list[0];
				$local_tpl['game_code'] = $gt_list[0]['game_code'];

				/* Делаем список с играми */
				$games_list = $this->games->get_games_list();

				foreach($games_list as $list) {
					$options[$list['code']] = $list['name'];
				}

				$local_tpl['gt_code'] = form_dropdown('game_code', $options, $gt_list[0]['game_code']);

				$local_tpl['frcon_list'] 	= array();
				$local_tpl['aliases_list'] 	= array();

				$local_tpl['frcon_count']		= 0;
				$local_tpl['aliases_count']		= 0;

				if($json_decode = json_decode($gt_list[0]['fast_rcon'], true)) {

					$i = 0;
					foreach($json_decode as $array) {
						$local_tpl['frcon_list'][$i]['id'] 			= $i;
						$local_tpl['frcon_list'][$i]['desc'] 			= form_input('frcon_desc[]', 	$array['desc']);
						$local_tpl['frcon_list'][$i]['rcon_command'] 	= form_input('frcon_command[]', $array['rcon_command']);
						$i ++;
					}

					$local_tpl['frcon_count'] = $i;
				}

				if($json_decode = json_decode($gt_list[0]['aliases'], true)) {

					$i = 0;
					foreach($json_decode as $array) {

						isset($array['default_value']) OR $array['default_value'] = '';

						$local_tpl['aliases_list'][$i]['id'] 			= $i;
						$local_tpl['aliases_list'][$i]['alias'] 		= form_input('alias_name[]', $array['alias']);
						$local_tpl['aliases_list'][$i]['desc'] 			= form_input('alias_desc[]', $array['desc']);
						$local_tpl['aliases_list'][$i]['default_value'] = form_input('default_value[]', $array['default_value']);
						$local_tpl['aliases_list'][$i]['only_admins'] 	= form_checkbox('alias_only_admins[' . $i . ']', 'accept', $array['only_admins']);
						$i ++;
					}

					$local_tpl['aliases_count'] = $i;

				}

				/*
				 * Данные для проверки формы
				*/

				$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]');
				$this->form_validation->set_rules('game_code', lang('adm_servers_game_code'), 'trim|required|max_length[32]|min_length[2]');

				/* Сведения о fast rcon командах */
				$this->form_validation->set_rules('frcon_desc[]', 'описание fast rcon команды', 'trim|max_length[64]');
				$this->form_validation->set_rules('frcon_command[]', 'fast rcon команда', 'trim|max_length[64]');

				/* Сведения об алиасах */
				$this->form_validation->set_rules('alias_name[]', 'имя алиаса', 'trim|max_length[64]');
				$this->form_validation->set_rules('alias_desc[]', 'описание алиаса', 'trim|max_length[64]');
				$this->form_validation->set_rules('alias_only_admins[]', 'только для администраторов', 'trim');

				/* Сведения для управления игроками */
				$this->form_validation->set_rules('kick_cmd', 		lang('adm_servers_kick_cmd'), 'trim|max_length[64]');
				$this->form_validation->set_rules('ban_cmd', 		lang('adm_servers_ban_cmd'), 'trim|max_length[64]');
				$this->form_validation->set_rules('chname_cmd', 	lang('adm_servers_chname_cmd'), 'trim|max_length[64]');
				$this->form_validation->set_rules('srestart_cmd', 	lang('adm_servers_srestart_cmd'), 'trim|max_length[64]');
				$this->form_validation->set_rules('chmap_cmd', 		lang('adm_servers_chmap_cmd'), 'trim|max_length[64]');
				$this->form_validation->set_rules('sendmsg_cmd', 	lang('adm_servers_sendmsg_cmd'), 'trim|max_length[64]');
				$this->form_validation->set_rules('passwd_cmd', 	lang('adm_servers_passwd_cmd'), 'trim|max_length[64]');

				/* Репозитории */
				$this->form_validation->set_rules('local_repository', lang('adm_servers_local_repository'), 'trim');
				$this->form_validation->set_rules('remote_repository', lang('adm_servers_remote_repository'), 'trim');

				break;
			default:
				redirect('');
				break;
		}
		/*
		 * Проверка заполненной формы, если все в порядке,
		 * то добавляем данные в базу.
		 * Если не в порядке, то отображаем форму
		 */
		if ($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			$this->tpl['content'] .= $this->parser->parse($tpl_file_edit, $local_tpl, true);
		} else {

			// Форма проверена, все впорядке

			switch($type){
				case 'dedicated_servers':

					/* --------------------------------------------	*/
					/* 				Выделенные серверы 				*/
					/* --------------------------------------------	*/

					// Форма проверена, все хорошо, добавляем сервер
					$sql_data['name'] = $this->input->post('name');
					$sql_data['os'] = $this->input->post('os');
					$sql_data['location'] = $this->input->post('location');
					$sql_data['provider'] = $this->input->post('provider');
					$sql_data['ram'] = (int)$this->input->post('ram');
					$sql_data['cpu'] = (int)$this->input->post('cpu');
					$sql_data['disabled'] = (int)(bool)$this->input->post('disabled');

					/* Обработка списка IP адресов */
					$ip_list = explode(',', str_replace(' ', '', $this->input->post('ip')));
					$sql_data['ip'] = json_encode($ip_list);

					// Скрипты
					$sql_data['script_start'] 			= $this->input->post('script_start');
					$sql_data['script_stop'] 			= $this->input->post('script_stop');
					$sql_data['script_restart'] 		= $this->input->post('script_restart');
					$sql_data['script_status'] 			= $this->input->post('script_status');
					$sql_data['script_get_console'] 	= $this->input->post('script_get_console');
					$sql_data['script_send_command'] 	= $this->input->post('script_send_command');

					// Редактирование данных доступа к серверу (пароли ftp, ssh)
					$sql_data['steamcmd_path'] 		= $this->input->post('steamcmd_path');
					$sql_data['work_path'] 		    = $this->input->post('work_path');

					$sql_data['gdaemon_host'] 		= $this->input->post('gdaemon_host');
					$sql_data['gdaemon_privkey'] 	= $this->input->post('gdaemon_privkey');
					$sql_data['gdaemon_pubkey'] 	= $this->input->post('gdaemon_pubkey');
					$sql_data['gdaemon_keypass'] 	= $this->input->post('gdaemon_keypass');
					$sql_data['gdaemon_login'] 		= $this->input->post('gdaemon_login');
					$sql_data['gdaemon_password'] 	= $this->input->post('gdaemon_password');

					// GDaemon check
                    $gdaemon_password   = empty($sql_data['gdaemon_password'])
                        ? $this->dedicated_servers->ds_list['0']['gdaemon_password']
                        : $sql_data['gdaemon_password'];

                    $gdaemon_keypass    = empty($sql_data['gdaemon_keypass'])
                        ? $this->dedicated_servers->ds_list['0']['gdaemon_keypass']
                        : $sql_data['gdaemon_keypass'];

                    if (false == $this->_check_gdaemon(
                        $sql_data['gdaemon_host'],
                        $sql_data['gdaemon_privkey'],
                        $gdaemon_keypass,
                        $sql_data['gdaemon_login'],
                        $gdaemon_password)
                    ) {
                        $this->_show_message(lang('adm_servers_gdaemon_data_unavailable'), 'javascript:history.back()');
                        return false;
                    }

					if ($this->dedicated_servers->edit_dedicated_server($id, $sql_data)){
						$local_tpl['message'] = lang('adm_servers_server_data_changed');
					}else{
						$local_tpl['message'] = lang('adm_servers_error_server_edit');
					}

					// Записываем логи
					$log_data['type'] 			= 'adm_servers';
					$log_data['command'] 		= 'edit_ds';
					$log_data['server_id'] 		= 0;
					$log_data['user_name'] 		= $this->users->auth_login;
					$log_data['msg'] 			= $local_tpl['message'];
					$log_data['log_data'] 		= 'ID: ' . $id;
					$this->panel_log->save_log($log_data);

					$local_tpl['link'] = site_url('adm_servers/view/dedicated_servers');
					$local_tpl['back_link_txt'] = lang('adm_servers_back_to_servers');


					break;

				case 'game_servers':

					/* --------------------------------------------	*/
					/* 				Игровые серверы					*/
					/* --------------------------------------------	*/

					$sql_data['name'] = $this->input->post('name');

					$sql_data['server_ip'] = $this->input->post('server_ip');
					$sql_data['server_port'] = $this->input->post('server_port');
					$sql_data['query_port'] 	= $this->input->post('query_port');
					$sql_data['rcon_port'] 		= $this->input->post('rcon_port');

					//$sql_data['game'] = $this->input->post('code');
					$sql_data['dir'] = $this->input->post('dir');
					$sql_data['game_type'] = $this->input->post('game_type');
					$sql_data['enabled'] = (int)(bool)$this->input->post('enabled');
					$sql_data['ds_id'] = $this->input->post('ds_id');

					$sql_data['screen_name'] = $this->input->post('screen_name');
					$sql_data['su_user'] = $this->input->post('su_user');
					$sql_data['start_command'] = $this->input->post('start_command');

					$sql_data['cpu_limit'] = $this->input->post('cpu_limit');
					$sql_data['ram_limit'] = $this->input->post('ram_limit');
					$sql_data['net_limit'] = $this->input->post('net_limit');

					/* Чтобы ид модификации был правильный и подходил для выбранной игры */
					$where = array('id' => $sql_data['game_type'], 'game_code' => $this->servers->server_data['game']);
					if(!$this->game_types->get_gametypes_list($where, 1)) {
						$this->_show_message(lang('adm_servers_game_type_select_wrong'));
						return false;
					}

					/* RCON */
					if($this->input->post('rcon') != '') {
						/* Собственно смена rcon пароля */
						$this->servers->change_rcon($this->input->post('rcon'), null, false);
						$sql_data['rcon'] = $this->input->post('rcon');
					}

					if($this->servers->edit_game_server($id, $sql_data)){
						$local_tpl['message'] = lang('adm_servers_server_data_changed');
					}else{
						$local_tpl['message'] = lang('adm_servers_error_server_edit');
					}

					// Записываем логи
					$log_data['type'] 			= 'adm_servers';
					$log_data['command'] 		= 'edit_game_server';
					$log_data['server_id'] 		= $id;
					$log_data['user_name'] 		= $this->users->auth_login;
					$log_data['msg'] 			= $local_tpl['message'];
					$log_data['log_data'] 		= 'ID: ' . $id;
					$this->panel_log->save_log($log_data);

					$local_tpl['link'] = site_url('adm_servers/view/game_servers');
					$local_tpl['back_link_txt'] = lang('adm_servers_back_to_servers');


					break;
				case 'games':

					/* --------------------------------------------	*/
					/* 				Игры			 				*/
					/* --------------------------------------------	*/

					$sql_data['name'] 			= $this->input->post('name');
					$sql_data['code'] 			= $this->input->post('code');
					$sql_data['start_code'] 	= $this->input->post('start_code');
					$sql_data['engine'] 		= $this->input->post('engine');
					$sql_data['engine_version'] = $this->input->post('engine_version');

					$sql_data['app_id'] 		= $this->input->post('app_id');
					$sql_data['app_set_config'] = $this->input->post('app_set_config');

					$sql_data['local_repository'] 	= $this->input->post('local_repository');
					$sql_data['remote_repository'] 	= $this->input->post('remote_repository');

					// Проверка наличия файла в удалённом репозитории
					if ($sql_data['remote_repository'] != "" && !remote_file_exists($sql_data['remote_repository'])) {
						$this->_show_message('adm_servers_rep_file_not_exists');
						return false;
					}

                    // Проверяем наличие Query класса
                    if (!file_exists(FCPATH . 'vendor/knik/gameq/src/GameQ/Protocols/' . ucfirst(strtolower($sql_data['engine'])) . '.php')) {
                        $this->_show_message('adm_servers_unknown_engine');
                        return false;
                    }

                    /* Убираем кавычки из app_set_config */
					$sql_data['app_set_config'] = str_replace('\'', '', $sql_data['app_set_config']);
					$sql_data['app_set_config'] = str_replace('"', '', $sql_data['app_set_config']);
					$sql_data['app_set_config'] = str_replace('	', '', $sql_data['app_set_config']);

					if($this->games->edit_game($id, $sql_data)){
						$local_tpl['message'] = lang('adm_servers_game_data_changed');
					}else{
						$local_tpl['message'] = lang('adm_servers_error_game_edit');
					}

					// Записываем логи
					$log_data['type'] 			= 'adm_servers';
					$log_data['command'] 		= 'edit_game';
					$log_data['server_id'] 		= 0;
					$log_data['user_name'] 		= $this->users->auth_login;
					$log_data['msg'] 			= $local_tpl['message'];
					$log_data['log_data'] 		= 'ID: ' . $id;
					$this->panel_log->save_log($log_data);

					$local_tpl['link'] 			= site_url('adm_servers/view/games');
					$local_tpl['back_link_txt'] 	= lang('adm_servers_back_to_games');

					break;

				case 'game_types':

					/* --------------------------------------------	*/
					/* 				Игровые модификации				*/
					/* --------------------------------------------	*/

					$sql_data['name'] 				= $this->input->post('name');
					$sql_data['game_code'] 			= $this->input->post('game_code');

					$sql_data['kick_cmd'] 			= $this->input->post('kick_cmd');
					$sql_data['ban_cmd'] 			= $this->input->post('ban_cmd');
					$sql_data['chname_cmd'] 		= $this->input->post('chname_cmd');
					$sql_data['srestart_cmd'] 		= $this->input->post('srestart_cmd');
					$sql_data['chmap_cmd'] 			= $this->input->post('chmap_cmd');
					$sql_data['sendmsg_cmd'] 		= $this->input->post('sendmsg_cmd');
					$sql_data['passwd_cmd'] 		= $this->input->post('passwd_cmd');

					$sql_data['local_repository'] 	= $this->input->post('local_repository');
					$sql_data['remote_repository'] 	= $this->input->post('remote_repository');

					// Проверка наличия файла в удалённом репозитории
					if ($sql_data['remote_repository'] != "" && !remote_file_exists($sql_data['remote_repository'])) {
						$this->_show_message('adm_servers_rep_file_not_exists');
						return false;
					}

					/*
					 * ----------------------------
					 * 	Перебор frcon комманд
					 * ----------------------------
					*/
					$frcon_list['desc'] 		= $this->input->post('frcon_desc');
					$frcon_list['command'] 		= $this->input->post('frcon_command');
					$frcon_list['delete'] 		= $this->input->post('frcon_delete');

					if(!empty($frcon_list['command'])) {
						$i = -1;
						foreach($frcon_list['command'] as $command) {
							$i ++;

							/* Пустые значения выкидываем */
							if($command == '') {
								continue;
							}

							/* Пустые значения выкидываем */
							if($frcon_list['desc'][$i] == '') {
								continue;
							}

							/* Значение должно быть удалено */
							if(isset($frcon_list['delete'][$i])) {
								continue;
							}

							$fast_rcon[$i]['desc'] 			= $frcon_list['desc'][$i];
							$fast_rcon[$i]['rcon_command'] 	= $command;
						}

						if(isset($fast_rcon)) {
							$sql_data['fast_rcon'] = json_encode(array_values($fast_rcon));
						}
					}

					/*
					 * ----------------------------
					 * 	Перебор алиасов
					 * ----------------------------
					*/
					$aliases_list['alias'] 				= $this->input->post('alias_name');
					$aliases_list['desc'] 				= $this->input->post('alias_desc');
					$aliases_list['default_value'] 		= $this->input->post('default_value');
					$aliases_list['only_admins'] 		= $this->input->post('alias_only_admins');
					$aliases_list['delete'] 			= $this->input->post('alias_delete');

					/* Массив с системными алиасами. Их использовать нельзя */
					$sys_aliases = array('id', 'script_path', 'command', 'game_dir', 'dir', 'name', 'ip', 'port', 'game', 'user');

					if(!empty($aliases_list['alias'])) {

						$i = -1;
						foreach($aliases_list['alias'] as $alias) {
							$i ++;

							/* Пустые значения выкидываем */
							if($alias == '') {
								continue;
							}

							/* Пустые значения выкидываем */
							if($aliases_list['desc'][$i] == '') {
								continue;
							}

							/* Пустые значения выкидываем */
							if(empty($aliases_list['only_admins'][$i])) {
								$aliases_list['only_admins'][$i] = false;
							}

							/* Значение должно быть удалено */
							if(isset($aliases_list['delete'][$i])) {
								continue;
							}

							/* Алиас не должен быть системным */
							if(in_array($alias, $sys_aliases)) {
								continue;
							}

							$aliases[$i]['alias'] 			= $alias;
							$aliases[$i]['desc'] 			= $aliases_list['desc'][$i];
							$aliases[$i]['default_value'] 	= $aliases_list['default_value'][$i];
							$aliases[$i]['only_admins'] 	= (bool)$aliases_list['only_admins'][$i];
						}

						if(isset($aliases)) {
							$sql_data['aliases'] = json_encode(array_values($aliases));
						}
					}

					if($this->game_types->edit_game_type($id, $sql_data)){
						$local_tpl['message'] = lang('adm_servers_game_type_data_changed');
					}else{
						$local_tpl['message'] = lang('adm_servers_error_game_type_edit');
					}

					// Записываем логи
					$log_data['type'] 			= 'adm_servers';
					$log_data['command'] 		= 'edit_game_type';
					$log_data['server_id'] 		= 0;
					$log_data['user_name'] 		= $this->users->auth_login;
					$log_data['msg'] 			= $local_tpl['message'];
					$log_data['log_data'] 		= 'ID: ' . $id;
					$this->panel_log->save_log($log_data);

					$local_tpl['link'] = site_url('adm_servers/edit/game_types/' . $id);
					$local_tpl['back_link_txt'] = lang('adm_servers_back_to_game_types');
					break;
			}

			$this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
		}

		$this->parser->parse('main.html', $this->tpl);

	}

	// -----------------------------------------------------------------

	/**
	 * Установка выделенного сервера
	 *
	 *
	*/
	function install_game_server()
	{
		$this->load->model('servers/dedicated_servers');
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');

		/* Загрузка языка */
		$this->lang->load('server_command');

		/* Хелпер safety обычно уже загружен в auth */
		$this->load->helper('safety');

		/* Хелпер работы со строками, нужен для генерации случайной строки */
		$this->load->helper('string');

		$local_tpl = array();

		if(false == $this->dedicated_servers->get_ds_list()) {
			$this->_show_message(lang('adm_servers_empty_ds_list', site_url('adm_servers/add/dedicated_servers')));
			return false;
		}

		// Получаем данные игр для шаблона
		$local_tpl['games_list'] = $this->games->tpl_data_games();

		if(empty($this->games->games_list)) {
			$this->_show_message(lang('adm_servers_empty_games_list', site_url('adm_servers/add/games')));
			return false;
		}

		$this->tpl['title'] 	= lang('adm_servers_title_install_game_server');
		$this->tpl['heading'] 	= lang('adm_servers_heading_install_game_server');

		$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]');
		$this->form_validation->set_rules('server_ip', lang('ip'), 'trim|max_length[64]|min_length[3]');
		$this->form_validation->set_rules('server_port', lang('port'), 'trim|required|max_length[64]|min_length[3]');
		//$this->form_validation->set_rules('rcon', 'RCON пароль', 'trim|max_length[64]|min_length[3]');
		$this->form_validation->set_rules('code', 'игра', 'trim|required|max_length[64]|min_length[3]');
		$this->form_validation->set_rules('game_type', 'модификация', 'trim|required|integer');
		$this->form_validation->set_rules('ds_id', 'выделенный сервер', 'trim|integer|max_length[16]');
		$this->form_validation->set_rules('dir', 'директория', 'trim|required|max_length[64]|min_length[3]');
		//$this->form_validation->set_rules('screen_name', 'имя screen', 'trim|required|max_length[64]|min_length[3]');

		if ($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			// Получаем данные DS для шаблона
			$local_tpl['ds_list'] = $this->dedicated_servers->tpl_data_ds();

			$this->tpl['content'] = $this->parser->parse('adm_servers/install_game_server.html', $local_tpl, true);
		} else {

			$new_gs['name'] 		= $this->input->post('name');
			$new_gs['server_ip'] 	= $this->input->post('server_ip');
			$new_gs['server_port'] 	= $this->input->post('server_port');
			//$new_gs['rcon'] 		= $this->input->post('rcon'); 		// Ркон задается случайной строкой после установки
			$new_gs['game'] 		= $this->input->post('code');
			$new_gs['game_type'] 	= $this->input->post('game_type');
			$new_gs['ds_id'] 		= $this->input->post('ds_id');
			$new_gs['dir'] 			= $this->input->post('dir');
			$new_gs['enabled']		= '1';

			// Если отмечен флаг "Установить сервер", то сервер считается еще не установленным и будет установлен
			$new_gs['installed']	= $this->input->post('install_server') ? 0 : 1;


			/* Не занят ли порт на выделенном сервере*/
			//~ if (!$this->dedicated_servers->check_ports($sql_data['ds_id'], array($sql_data['server_port'], $sql_data['rcon_port'], $sql_data['query_port']))) {
				//~ $this->_show_message(lang('adm_servers_port_exists'));
				//~ return false;
			//~ }

			if (!$new_gs['server_ip']) {
				$new_gs['server_ip'] = $this->_get_default_ip($new_gs['ds_id']);
			}

			if(!$new_gs['server_ip'] && $new_gs['ds_id'] !== '0') {
				$this->_show_message(lang('adm_servers_selected_ds_unavailable'));
				return false;
			}

			$game_data = $this->games->get_games_list(array('code' => $new_gs['game']), 1);

			/* Чтобы выделенный сервер существовал */
			if (!$this->dedicated_servers->ds_live($new_gs['ds_id'])) {
				$this->_show_message(lang('adm_servers_selected_ds_unavailable'));
				return false;
			}

			/* Получение стандартных данных */
			$new_gs = $this->_gs_default_data($new_gs);

			/* Чтобы ид модификации был правильный и подходил для выбранной игры */
			$where = array('id' => $new_gs['game_type'], 'game_code' => $new_gs['game']);
			if(!$this->game_types->get_gametypes_list($where, 1)) {
				$this->_show_message(lang('adm_servers_game_type_select_wrong'));
				return false;
			}

			if ($this->games->get_games_list(array('code'=> $new_gs['game']), 1)) {

				if(!$new_gs['installed']
					&& !$this->games->games_list[0]['app_id']
					&& !$this->games->games_list[0]['local_repository']
					&& !$this->games->games_list[0]['remote_repository']
				) {
					/*
					 * Для игры не задан или не существует парамера app_update для SteamCMD,
					 * нет ссылок на локальный и удаленные репозитории
					*/
					$this->_show_message(lang('adm_servers_no_steamcmd_data'));
					return false;
				}


			} else {
				// Игры не существует
				$this->_show_message(lang('adm_servers_base_not_contains_game'));
				return false;
			}

			// Добавление сервера
			if($this->servers->add_game_server($new_gs)) {
				$new_server_id = $this->db->insert_id();

                if (!$new_gs['installed']) {
                    // New task to install server
                    $this->gdaemon_tasks->add(array(
                        'ds_id'     => $new_gs['ds_id'],
                        'server_id' => $new_server_id,
                        'task' => 'gsinst',
                    ));
                }

				$succes_mesage = $new_gs['installed'] ? lang('adm_servers_add_server_successful') : lang('adm_servers_server_to_be_installed');
				$this->_show_message($succes_mesage, site_url('adm_servers/edit/game_servers/' . $new_server_id), lang('adm_servers_go_to_settings'));
				$log_data['msg'] = $succes_mesage;
			} else {
				$this->_show_message(lang('adm_servers_add_game_failed'));
				$log_data['msg'] = lang('adm_servers_add_game_failed');
			}

			// Записываем логи
			$log_data['type'] 			= 'adm_servers';
			$log_data['command'] 		= 'add_game_server';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;
			$log_data['log_data'] 		= '';
			$this->panel_log->save_log($log_data);

			return;

		}

		$this->parser->parse('main.html', $this->tpl);
	}

	// -----------------------------------------------------------------

	/**
	 * Переустановка игрового сервера
	 *
	 *
	*/
	function reinstall_game_server($server_id, $confirm = false)
	{
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');

		$local_tpl['content'] = '';

		if(!$this->servers->get_server_data($server_id)){
			$this->_show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/game_servers'));
			return false;
		}

		if ($confirm == $this->security->get_csrf_hash()) {

			$sql_data['installed'] = 0;

			if ($this->servers->edit_game_server($server_id, $sql_data)) {

                $task_del_id = $this->gdaemon_tasks->add(array(
                    'ds_id'     => $this->servers->server_data['ds_id'],
                    'server_id' => $this->servers->server_data['id'],
                    'task'      => 'gsdel',
                ));

                if ($task_del_id == 0) {
                    $this->_show_message(lang('adm_servers_task_add_error'), site_url('adm_servers/edit/game_servers/' . $server_id), lang('next'));
                    $log_data['msg'] = lang('adm_servers_task_add_error');
                    return;
                } else {
                    // New task to install server
                    $this->gdaemon_tasks->add(array(
                        'run_aft_id' => $task_del_id,
                        'ds_id'     => $this->servers->server_data['ds_id'],
                        'server_id' => $this->servers->server_data['id'],
                        'task' => 'gsinst',
                    ));

                    $this->_show_message(lang('adm_servers_server_will_be_reinstalled'), site_url('adm_servers/edit/game_servers/' . $server_id), lang('next'));
                    $log_data['msg'] = lang('adm_servers_server_will_be_reinstalled');
                }
            } else {
				$this->_show_message(lang('adm_servers_error_server_edit'), site_url('adm_servers/edit/game_servers/' . $server_id), lang('next'));
				$log_data['msg'] = lang('adm_servers_error_server_edit');
			}

			// Записываем логи
			$log_data['type'] 			= 'adm_servers';
			$log_data['command'] 		= 'edit_ds';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;
			$log_data['log_data'] 		= 'ID: ' . $server_id;
			$this->panel_log->save_log($log_data);

			return;

		} else {

			$confirm_tpl['message'] = lang('adm_servers_reinstall_gs_confirm');
			$confirm_tpl['confirmed_url'] = site_url('adm_servers/reinstall_game_server/'. $server_id . '/' . $this->security->get_csrf_hash());

			$this->tpl['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
		}

		$this->parser->parse('main.html', $this->tpl);
	}

	// -----------------------------------------------------------------

	/**
	 * Быстрое примерение фильтров списка серверов на машине.
	 *
	 * @param int
	 */
	function filter_ds_servers($ds_id = 0)
	{
		if (!$ds_id) {
			redirect('admin');
		}

		if (!$this->dedicated_servers->get_ds_list(array('id' => $ds_id), 1)) {
			redirect('admin');
		}

		$this->servers->select_fields('id, server_ip');
		$game_servers = $this->servers->get_game_servers_list(array('ds_id' => $ds_id));

		$filter = array('ip' => array());
		foreach ($game_servers as &$gserv) {
			$filter['ip'][] = $gserv['server_ip'];
		}

		$this->users->update_filter('servers_list', $filter);
		redirect('admin');
	}

	// -----------------------------------------------------------------

	/**
	 * Создание дубликата игровой модификации
	 *
	 *
	*/
	function dublicate_game_type($id, $confirm = false)
	{
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');

		$local_tpl['content'] = '';
		$local_tpl['gt_id'] = (int)$id;

		// Получаем данные игр для шаблона
		$local_tpl['games_list'] = $this->games->tpl_data_games();

		if(empty($this->games->games_list)) {
			$this->_show_message(lang('adm_servers_empty_games_list', base_url() . 'adm_servers/add/games'));
			return false;
		}

		/* Существует ли модификация */
		if(!$gt_list = $this->game_types->get_gametypes_list(array('id' => $id))){
			$this->_show_message(lang('adm_servers_game_type_not_found'), site_url('adm_servers/view/game_types'));
			return false;
		}

		$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]');
		$this->form_validation->set_rules('code', lang('game'), 'trim|required|max_length[64]');

		if ($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			/* Если были ошибки проверки формы, то отображаем ошибки, если нет, то отображаем форму */
			if ($validation_errors = validation_errors()) {
				$this->_show_message();
				return false;
			} else {
				$this->tpl['content'] = $this->parser->parse('adm_servers/dublicate_game_type.html', $local_tpl, true);
			}

		} else {
			$sql_data = $gt_list[0];
			unset($sql_data['id']);
			$sql_data['game_code'] = $this->input->post('code');
			$sql_data['name'] = $this->input->post('name');

			if($this->game_types->add_game_type($sql_data)) {
				$local_tpl['message'] = lang('adm_servers_add_game_type_successful');
			} else {
				$local_tpl['message'] = lang('adm_servers_add_game_type_failed');
			}

			$new_game_type_id = $this->db->insert_id();

			// Записываем логи
			$log_data['type'] 			= 'adm_servers';
			$log_data['command'] 		= 'clone_game_type';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;
			$log_data['msg'] 			= $local_tpl['message'];
			$log_data['log_data'] 		= 'ID: ' . $id . ' CloneID: ' . $this->db->insert_id();
			$this->panel_log->save_log($log_data);

			$this->_show_message($local_tpl['message'], site_url('adm_servers/edit/game_types/' . $new_game_type_id), lang('next'));
			return true;
		}

		$this->parser->parse('main.html', $this->tpl);
	}
}
/* End of file adm_servers.php */
/* Location: ./application/controllers/adm_servers.php */
