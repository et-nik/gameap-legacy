<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (GameAP)
 *
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (NiK)
 * @copyright	Copyright (c) 2014-2016
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
*/

use \Myth\Controllers\BaseController;

class Server_command extends BaseController {

	//Template
	var $tpl = array();
	var $user_data = array();

	var $ext_list;
	var $errors = '';

	public function __construct()
    {
        parent::__construct();

		$this->load->database();
        $this->load->model('users');
        $this->lang->load('server_command');

        if (!$this->users->check_user()) {
            redirect('auth');
        }

        //Base Template
        $this->tpl['title'] 	= lang('server_command_title_index');
        $this->tpl['heading'] 	= lang('server_command_header_index');
        $this->tpl['content'] = '';
        $this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
        $this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);

        $this->load->model('servers');

        $this->ext_list = get_loaded_extensions();
    }

	// -----------------------------------------------------------------

	/**
	 * Получение ника игрока по id
	 */
	private function _get_player($player_id)
	{
		$this->load->driver('rcon');

		if (!$this->servers->server_status($this->servers->server_data['server_ip'], $this->servers->server_data['query_port'])) {
			throw new Exception(lang('server_command_server_down'));
		}

		$this->rcon->set_variables(
				$this->servers->server_data['server_ip'],
				$this->servers->server_data['rcon_port'],
				$this->servers->server_data['rcon'],
				$this->servers->servers->server_data['engine'],
				$this->servers->servers->server_data['engine_version']
		);

		$rcon_connect = $this->rcon->connect();
		$players_list = $this->rcon->get_players();

		foreach ($players_list as &$player) {
			if ($player['user_id'] == $player_id) {
				$this->rcon->disconnect();
				return $player;
			}
		}

		throw new Exception(lang('server_command_player_not_found'));
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
	 * Главная страница
	 *
	 * Используется как заглушка,
	 * если посетитель не указал параметры server_command
	 *
	*/
	public function index()
    {
		redirect('admin');
	}

    // -----------------------------------------------------------------

	function rcon($command = false, $server_id = false, $custom_id = false, $confirm = false)
	{
		$this->servers->get_server_data($server_id);
		$this->users->get_server_privileges($server_id);

		if($this->servers->server_data){

			$template_file = null;
			$no_submit_name = false;

			// Получение прав на сервер
			$this->users->get_server_privileges($this->servers->server_data['id']);

			$local_tpl['server_id'] = $server_id;
			$local_tpl['custom_id'] = $custom_id;

			/**
			 * Проверки на права
			 *
			 * $this->users->user_privileges 	- общие привилегии
			 * $this->users->user_servers 		- cписок серверов пользователя
			 * $this->servers->server_data				- данные редактируемого сервера
			 * $this->users->servers_privileges - массив с привилегиями на сервер
			*/
			switch($command){
				case 'pl_ban':
					$privileges = (bool)($this->users->auth_servers_privileges['PLAYERS_BAN']); 	// Привилегии
					$isset = (bool)$this->servers->server_data['ban_cmd'];		// Задана ли команда

					$submit_name = 'submit_ban';
					$template_file = 'player_ban.html';

					break;

				case 'pl_kick':
					$privileges = (bool)($this->users->auth_servers_privileges['PLAYERS_KICK']);
					$isset = (bool)$this->servers->server_data['kick_cmd'];		// Задана ли команда

					$submit_name = 'submit_kick';
					$template_file = 'player_kick.html';
					break;

				case 'pl_changename':
					$privileges = (bool)($this->users->auth_servers_privileges['PLAYERS_CH_NAME']);
					$isset = (bool)$this->servers->server_data['chname_cmd'];

					try {
						$player_data = $this->_get_player($custom_id);
					} catch (Exception $e) {
						$this->_show_message($e->getMessage());
						return false;
					}

					$local_tpl['nickname'] 	= $player_data['user_name'];
					$local_tpl['steam_id'] 	= $player_data['steam_id'];
					$local_tpl['user_ip'] 		= $player_data['user_ip'];

					$submit_name = 'submit_changename';
					$template_file = 'player_changename.html';
					break;

				case 'send_msg';
					$privileges = (bool)($this->users->auth_servers_privileges['SERVER_CHAT_MSG']);
					$isset = (bool)$this->servers->server_data['sendmsg_cmd'];

					$submit_name = 'submit_sendmsg';
					$template_file = 'send_msg.html';
					break;

				case 'changemap';
					$privileges = (bool)($this->users->auth_servers_privileges['CHANGE_MAP']);
					$isset = (bool)$this->servers->server_data['chmap_cmd'];

					$submit_name = 'submit_changemap';
					//$template_file = 'send_msg.html';
					break;

				case 'restart':
					$privileges = (bool)($this->users->auth_servers_privileges['SERVER_SOFT_RESTART']);
					$isset = (bool)$this->servers->server_data['srestart_cmd'];

					$submit_name = 'submit_restart';
					$no_submit_name = true;
					break;

				case 'set_password':
					$privileges = (bool)($this->users->auth_servers_privileges['SERVER_SET_PASSWORD']);
					$isset = (bool)$this->servers->server_data['passwd_cmd'];

					$submit_name = 'submit_set_password';
					break;

				case 'fast':
					/* Fast RCON */
					$privileges = (bool)$this->users->auth_servers_privileges['FAST_RCON'];
					$isset = true;
					//$submit_name = 'submit_set_password';
					$no_submit_name = true;
					break;

				case 'rcon_command':
					$privileges = (bool)($this->users->auth_servers_privileges['RCON_SEND']);
					$isset = true;

					$submit_name = 'submit_rcon';
					//$template_file = 'player_changename.html';
					break;
			}

			if($privileges) {
				/* Пользователь прошел проверку на привилегии */

				if (!$isset) {
					$this->_show_message(lang('server_control_command_not_set'));
					return false;
				}

				if(!$no_submit_name && !$this->input->post($submit_name)) {

					if($template_file){
						$this->tpl['content'] .= $this->parser->parse($template_file, $local_tpl, true);
					}

				} else {

					$this->load->library('form_validation');

					$no_form_vallidation = false;

					/* Правила проверки для форм
					 *
					 * если формы нет, то нужно задать переменной
					 * $no_form_vallidation значение true
					*/
					switch($command) {
						case 'pl_ban':
							$this->form_validation->set_rules('reason', 'причина', 'trim|required|max_length[32]|min_length[3]');
							$this->form_validation->set_rules('time', 'время', 'trim|max_length[2]|integer');
							$this->form_validation->set_rules('time_multiply', 'отсчет времени', 'trim|max_length[6]|integer');
							break;

						case 'pl_kick':
							$no_form_vallidation = true;
							break;

						case 'pl_changename':
							$this->form_validation->set_rules('new_name', 'новое имя', 'trim|required|max_length[32]|min_length[1]');
							break;

						case 'send_msg';
							$this->form_validation->set_rules('msg_text', 'текст', 'trim|required|max_length[64]|min_length[1]');
							break;

						case 'changemap';
							$this->form_validation->set_rules('map', 'карта', 'trim|required|max_length[64]|min_length[1]');
							break;

						case 'restart':
							$no_form_vallidation = true;
							break;

						case 'set_password':
							$this->form_validation->set_rules('password', 'пароль', 'trim|max_length[32]|min_length[1]');
							break;

						case 'fast':
							$no_form_vallidation = true;
							break;

						case 'rcon_command';
							$this->form_validation->set_rules('rcon_command', 'команда', 'required');
							break;
					}

					if (!$no_form_vallidation) {
						$form_validate = $this->form_validation->run();
					} else {

						if($confirm == $this->security->get_csrf_hash()) {
							$form_validate = true;
						} elseif($custom_id == $this->security->get_csrf_hash()) {
							/* В некоторых случаях $custom_id можно использовать как $confirm */
							$form_validate = true;
						} else {
							$form_validate = false;
						}
					}

					// Проверяем заполненные поля
					if (!$form_validate) {
						$this->_show_message(lang('server_command_form_unavailable'), site_url('admin/server_control/main/' . $server_id));
						return false;
					} else {

						$this->load->helper('translit');

						/* Получение данных полей */
						switch($command) {
							case 'pl_ban':
								$pl_ban_reason = $this->input->post('reason', true);
								$pl_ban_time = $this->input->post('time', true) * $this->input->post('time_multiply', true);
								break;

							case 'pl_kick':
								// empty
								break;

							case 'pl_changename':
								$pl_newname = $this->input->post('new_name', true);
								break;

							case 'send_msg';
								$msg_text = $this->input->post('msg_text', true);
								break;

							case 'changemap';
								$map = $this->input->post('map', true);
								break;

							case 'restart';
								// empty
								break;

							case 'set_password':
								$password = $this->input->post('password', true);
								break;

							case 'fast':
								$fast_rcon = json_decode($this->servers->server_data['fast_rcon'], true);

								// Существует ли команда
								if(!$fast_rcon OR !array_key_exists($custom_id, $fast_rcon)){
									$this->_show_message(lang('server_command_rcon_command_not_found'), site_url('admin/server_control/main/' . $server_id));
									return false;
								}

								$rcon_command = $fast_rcon[$custom_id]['rcon_command'];

								break;

							case 'rcon_command';
								$rcon_command = $this->input->post('rcon_command', true);

								// Объединение массива с частями в одну команду
								if (is_array($rcon_command)) {
									$rcon_command = implode('', $rcon_command);
								}

								if(!$this->_check_rcon_command($rcon_command)) {
									$this->_show_message(lang('server_command_rcon_command_access_denied'), site_url('admin/server_control/main/' . $server_id));
									return false;
								}

								break;
						}

						if(!$this->servers->server_status($this->servers->server_data['server_ip'], $this->servers->server_data['query_port'])) {
							$this->_show_message(lang('server_command_server_down'), site_url('admin/server_control/main/' . $server_id));
							return false;
						}

						$this->load->driver('rcon');

						$this->rcon->set_variables(
												$this->servers->server_data['server_ip'],
												$this->servers->server_data['rcon_port'],
												$this->servers->server_data['rcon'],
												$this->servers->servers->server_data['engine'],
												$this->servers->servers->server_data['engine_version']
						);

						$rcon_connect = $this->rcon->connect();

						if(@$rcon_connect) {
							$player_id = $custom_id;

							switch($command){
								case 'pl_ban':
									/*
									 * Параметры команды amx_ban могут быть разными,
									 * в зависимости от версии плагина на сервере
									 *
									 * Шаблоны команд хранятся в игровых модификациях
									 *
									 * Usage:  amx_ban <time in mins> <steamID or nickname or #authid or IP> <reason>
									 *
									*/
									$rcon_command = $this->servers->server_data['ban_cmd'];
									$rcon_command = str_replace('{id}', $player_id, $rcon_command);
									$rcon_command = str_replace('{time}', $pl_ban_time, $rcon_command);
									$rcon_command = str_replace('{reason}', $pl_ban_reason, $rcon_command);

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_ban_command_sended');
									break;

								case 'pl_kick':
									$rcon_command = $this->servers->server_data['kick_cmd'];
									$rcon_command = str_replace('{id}', $player_id, $rcon_command);

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_kick_command_sended');
									break;

								case 'pl_changename':
									$rcon_command = $this->servers->server_data['chname_cmd'];
									$rcon_command = str_replace('{id}', $player_id, $rcon_command);
									$rcon_command = str_replace('{name}', $pl_newname, $rcon_command);

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_nickchange_command_sended');
									break;

								case 'send_msg':
									$rcon_command = $this->servers->server_data['sendmsg_cmd'];
									$rcon_command = str_replace('{msg}', $msg_text, $rcon_command);

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_msg_command_sended');
									break;

								case 'changemap':
									$rcon_command = $this->servers->server_data['chmap_cmd'];
									$rcon_command = str_replace('{map}', $map, $rcon_command);

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_mapchange_command_sended', $map);
									break;

								case 'restart':
									$rcon_command = $this->servers->server_data['srestart_cmd'];

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_restart_cmd_sended');
									break;

								case 'set_password':
									$rcon_command = $this->servers->server_data['passwd_cmd'];
									$rcon_command = str_replace('{password}', $password, $rcon_command);
									$rcon_command = str_replace('{pass}', $password, $rcon_command);

									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_password_set');
									break;

								case 'fast':
									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_cmd_sended');
									break;

								case 'rcon_command':
									$rcon_string = $this->rcon->command($rcon_command);
									$message = lang('server_command_cmd_sended');
									break;
							}

							$this->rcon->disconnect();

							if(trim($rcon_string) != '') {
								$rcon_string = str_replace("\n", "<br />", $rcon_string);
								$rcon_string = str_replace("\r", "<br />", $rcon_string);

								//$rcon_string = iconv('windows-1251', 'UTF-8', $rcon_string);

								$message .= '<p align="left"><strong>' . lang('server_command_answer') . ':</strong> <code>' . $rcon_string . '</code></p>';
							}

							// Получаем команду без параметров, для логов
							$rcommand = explode(' ', $rcon_command);
							$log_data['command'] = $rcommand[0];

							// Сохраняем логи
							$log_data['type'] = 'server_rcon';
							$log_data['user_name'] = $this->users->auth_login;
							$log_data['server_id'] = $this->servers->server_data['id'];
							$log_data['msg'] = 'Rcon command';
							$log_data['log_data'] = 'Rcon command: ' . $rcon_command . "\n" . 'Rcon string: ' . $rcon_string;
							$this->panel_log->save_log($log_data);

						} else {
							$message = 'Error';
						}

						$this->_show_message($message, site_url('admin/server_control/main/' . $server_id), lang('next'));
						return true;
					}


				}

			} else {
				$this->_show_message(lang('server_command_no_players_privileges'), site_url('admin'), lang('next'));
				return false;
			}

		}

		$this->parser->parse('main.html', $this->tpl);
	}

	/**
	 *
	 * Проверка rcon команд, некоторые команды могут требовать
	 * дополнительных действий, либо быть запрещены
	 *
	*/
	private function _check_rcon_command($rcon_command)
	{
		/* Получаем ркон команду */
		$rcon_command = explode(' ', $rcon_command);
		$rcon_command['0'] = strtolower($rcon_command['0']);

		/* Пользователь, у которого нет прав на смену ркон пароля не имеет права отправлять rcon_password */
		if(!$this->users->auth_servers_privileges['CHANGE_RCON'] && in_array('rcon_password', $rcon_command)) {
			return false;
		}

		/* Пользователь, у которого нет прав на выставление пароля на сервер */
		if(!$this->users->auth_servers_privileges['SERVER_SET_PASSWORD'] && in_array('sv_password', $rcon_command)) {
			return false;
		}

		switch ($rcon_command['0']) {
			case 'rcon_password':
				// Смена rcon пароля, правка конфиг файлов и тп.
				if(isset($this->servers->server_data['id']) && isset($rcon_command['1'])) {
					$this->servers->change_rcon($rcon_command['1'], $this->servers->server_data, true);
				}

				break;
		}


		return true;
	}

	// -----------------------------------------------------------------

	/**
	 * Console view
	*/
	public function console_view($server_id = 0)
    {
		$this->tpl['title'] 	= lang('server_command_title_console_view');
		$this->tpl['heading'] 	= lang('server_command_header_console_view');

		/* Получены ли необходимые данные о сервере */
		if (!$this->servers->get_server_data($server_id)) {
            $this->_show_message(lang('server_command_server_not_found'), site_url('admin'), lang('next'));
			return;
        }

        $local_tpl['server_id'] = $server_id;

        // Получение прав на сервер
        $this->users->get_server_privileges($this->servers->server_data['id']);

        if(!$this->users->auth_servers_privileges['CONSOLE_VIEW']) {
            $this->_show_message(lang('server_command_no_console_privileges'), site_url('admin/server_control/main/' . $server_id));
            return;
        }

        $this->tpl['content'] = $this->parser->parse('servers/console_view.html', $local_tpl, true);
		$this->parser->parse('main.html', $this->tpl);
	}
}

/* End of file server_command.php */
/* Location: ./application/controllers/server_command.php */
