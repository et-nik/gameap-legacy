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
class Server_command extends CI_Controller {
	
	//Template
	var $tpl_data = array();
	var $user_data = array();
	
	var $ext_list;
	var $errors = '';

	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('server_command');
        
        if ($this->users->check_user()) {
			//Base Template
			$this->tpl_data['title'] 	= lang('server_command_title_index');
			$this->tpl_data['heading'] 	= lang('server_command_header_index');
			$this->tpl_data['content'] = '';
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, true);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
			
			$this->load->model('servers');
			
			$this->ext_list = get_loaded_extensions();
        } else {
            redirect('auth');
        }
    }
    
    // -----------------------------------
    
	// Отображение информационного сообщения
	function _show_message($message = false, $link = false, $link_text = false)
	{
		
		if (!$message) {
			$message = ($this->errors OR lang('error'));
		}
		
		if (!$link) {
			$link = site_url('admin');
		}
		
		if (!$link_text) {
			$link_text = lang('back');
		}
	
		$local_tpl_data['message'] = $message;
		$local_tpl_data['link'] = $link;
		$local_tpl_data['back_link_txt'] = $link_text;
		$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, true);
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// -----------------------------------------------------------------------
	
	/**
	 * Проверка SSH данных
	*/
	function _check_ssh() {

		/* 
		 * Заданы ли данные SSH у DS сервера 
		 * 
		 * Если сервер является удаленным, используется telnet
		 * и заданы хост, логин и пароль то все впорядке,
		 * иначе отправляем пользователю сообщение
		 * 
		*/
		if ($this->servers->server_data['ds_id']
		&& $this->servers->server_data['control_protocol'] == 'ssh'
		&& (!$this->servers->server_data['ssh_host']
			OR !$this->servers->server_data['ssh_login']
			OR !$this->servers->server_data['ssh_password']
			)
		) {
			$this->errors = lang('server_command_ssh_not_set');
			return false;	
		}
		
		/*
		 * Есть ли модуль SSH
		 */
		if ($this->servers->server_data['ds_id'] 
		&& $this->servers->server_data['control_protocol'] == 'ssh'
		&& (!in_array('ssh2', $this->ext_list))
		) {
			$this->errors = lang('server_command_ssh_not_module');
			return false;	
		}
		
		return true;
	}
	
	// -----------------------------------------------------------------------
	
	/**
	 * Проверка данных Telnet
	*/
	function _check_telnet() {
		
		/* 
		 * Заданы ли данные TELNET у DS сервера 
		 * 
		 * Если сервер является удаленным, используется telnet
		 * и заданы хост, логин и пароль то все впорядке,
		 * иначе отправляем пользователю сообщение
		 * 
		*/
		
		if($this->servers->server_data['ds_id']
		&& $this->servers->server_data['control_protocol'] == 'telnet'
		&& (!$this->servers->server_data['telnet_host']
			OR !$this->servers->server_data['telnet_login']
			OR !$this->servers->server_data['telnet_password']
			)
		){
			$this->errors = lang('server_command_telnet_not_set');
			return false;	
		}
		
		return true;
	}

	/*
	 * 
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
	
	
	function rcon($command = false, $server_id = false, $id = false, $confirm = false)
	{
		if(!$this->check_errors($server_id)){
			$this->servers->get_server_data($server_id);
		}
			
		$this->users->get_server_privileges($server_id);
		
		if($this->servers->server_data){
				
			$template_file = null;
			$no_submit_name = false;
				
			// Получение прав на сервер
			$this->users->get_server_privileges($this->servers->server_data['id']);
				
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
						$this->tpl_data['content'] .= $this->parser->parse($template_file, $this->tpl_data, true);
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
							$this->form_validation->set_rules('reason', 'причина', 'trim|required|max_length[32]|min_length[3]|xss_clean');
							$this->form_validation->set_rules('time', 'время', 'trim|max_length[2]|integer');
							$this->form_validation->set_rules('time_multiply', 'отсчет времени', 'trim|max_length[6]|integer');
							break;
							
						case 'pl_kick':
							$no_form_vallidation = true;
							break;
							
						case 'pl_changename':
							$this->form_validation->set_rules('new_name', 'новое имя', 'trim|required|max_length[32]|min_length[1]|xss_clean');
							break;
							
						case 'send_msg';
							$this->form_validation->set_rules('msg_text', 'текст', 'trim|required|max_length[64]|min_length[1]|xss_clean');
							break;
							
						case 'changemap';
							$this->form_validation->set_rules('map', 'карта', 'trim|required|max_length[64]|min_length[1]|xss_clean');
							break;
							
						case 'restart':
							$no_form_vallidation = true;
							break;
							
						case 'set_password':
							$this->form_validation->set_rules('password', 'пароль', 'trim|max_length[32]|min_length[1]|xss_clean');
							break;
							
						case 'fast':
							$no_form_vallidation = true;
							break;
							
						case 'rcon_command';
							$this->form_validation->set_rules('rcon_command', 'команда', 'trim|required|max_length[64]|min_length[1]|xss_clean');
							break;
					}
						
					if (!$no_form_vallidation) {
						$form_validate = $this->form_validation->run();
					} else {
						
						if($confirm == $this->security->get_csrf_hash()) {
							$form_validate = true;
						} elseif($id == $this->security->get_csrf_hash()) {
							/* В некоторых случаях $id можно использовать как $confirm */
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
								$pl_ban_reason = translit($this->input->post('reason', true));
								$pl_ban_time = $this->input->post('time', true) * $this->input->post('time_multiply', true);
								break;
								
							case 'pl_kick':
								// empty
								break;
								
							case 'pl_changename':
								$pl_newname = translit($this->input->post('new_name', true));
								break;
								
							case 'send_msg';
								$msg_text = translit($this->input->post('msg_text', true));
								break;
								
							case 'changemap';
								$map = translit($this->input->post('map', true));
								break;
								
							case 'restart';
								// empty
								break;
								
							case 'set_password':
								$password = translit($this->input->post('password', true));
								break;
								
							case 'fast':
								$fast_rcon = json_decode($this->servers->server_data['fast_rcon'], true);
									
								// Существует ли команда
								if(!$fast_rcon OR !array_key_exists($id, $fast_rcon)){
									$this->_show_message(lang('server_command_rcon_command_not_found'), site_url('admin/server_control/main/' . $server_id));
									return false;
								}
									
								$rcon_command = $fast_rcon[$id]['rcon_command'];
									
								break;
								
							case 'rcon_command';
								$rcon_command = translit($this->input->post('rcon_command', true));
									
								if(!$this->check_rcon_command($rcon_command)) {
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
							$player_id = $id;
								
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
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	
	/**
	 * 
	 * Проверка на ошибки
	 * 
	*/
	private function check_errors($id)
	{
		$error_desc = false;
		
		if(!$id){
			$error_desc .= 'не указан параметр server_command <br />';
		}
		
		return $error_desc;
	}
	
	
	/**
	 * 
	 * Проверка rcon команд, некоторые команды могут требовать
	 * дополнительных действий, либо быть запрещены
	 * 
	*/
	private function check_rcon_command($rcon_command) 
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
					$this->servers->change_rcon($rcon_command['1']);
					$sql_data['rcon'] = $rcon_command['1'];
					$this->servers->edit_game_server($this->servers->server_data['id'], $sql_data);
				}
				
				break;
		}
	
		
		return true;
	}
	
	
	/**
	 * 
	 * Запуск сервера
	 * 
	*/
	public function console_view($id)
    {
		
		$this->tpl_data['title'] 	= lang('server_command_title_console_view');
		$this->tpl_data['heading'] 	= lang('server_command_header_console_view');
			
		/* Получены ли необходимые данные о сервере */
		if($this->servers->get_server_data($id)) {
			
			if(strtolower($this->servers->server_data['os']) == 'windows') {
				/* Еще одна причина не использовать Windows */
				$this->_show_message(lang('server_command_not_available_for_windows'), site_url('admin/server_control/main/' . $id), lang('next'));
				return false;
			}
			
			// Получение прав на сервер
			$this->users->get_server_privileges($this->servers->server_data['id']);
			
			if(!$this->users->auth_servers_privileges['CONSOLE_VIEW']) {
				$this->_show_message(lang('server_command_no_console_privileges'), site_url('admin/server_control/main/' . $id));
				return false;
			}
			
			/* Код закомментирован. Сервер может зависнуть, он будет недоступен, но данные консоли можно получить */
			//~ if(!$this->servers->server_status()) {
				//~ $this->_show_message(lang('server_command_server_down'), site_url('admin/server_control/main/' . $id));
				//~ return false;
			//~ }
			
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
			if($this->servers->server_data['ds_id'] 
			&& $this->servers->server_data['control_protocol'] == 'ssh'
			&& (!$this->servers->server_data['ssh_host']
				OR !$this->servers->server_data['ssh_login']
				OR !$this->servers->server_data['ssh_password']
				)
			){
				$this->_show_message(lang('server_command_ssh_not_set'), site_url('admin/server_control/main/' . $id), lang('next'));
				return false;	
			}
			
			/*
			 * Есть ли модуль SSH
			 */
			if($this->servers->server_data['ds_id'] 
			&& $this->servers->server_data['control_protocol'] == 'ssh'
			&& (!in_array('ssh2', $ext_list))
			){
				$this->_show_message(lang('server_command_ssh_not_module'), site_url('admin/server_control/main/' . $id), lang('next'));
				return false;	
			}
			
			
			/* 
			 * Заданы ли данные TELNET у DS сервера 
			 * 
			 * Если сервер является удаленным, используется telnet
			 * и заданы хост, логин и пароль то все впорядке,
			 * иначе отправляем пользователю сообщение
			 * 
			*/
			
			if($this->servers->server_data['ds_id'] 
			&& $this->servers->server_data['control_protocol'] == 'telnet'
			&& (!$this->servers->server_data['telnet_host']
				OR !$this->servers->server_data['telnet_login']
				OR !$this->servers->server_data['telnet_password']
				)
			){
				$this->_show_message(lang('server_command_telnet_not_set'), site_url('admin/server_control/main/' . $id), lang('next'));
				return false;	
			}
			
			if(!$this->servers->server_data['script_get_console']) {
				$this->_show_message(lang('server_command_console_not_param'), site_url('admin/server_control/main/' . $id));
				return false;
			}
			
			/* Директория в которой располагается сервер */
			$dir = $this->servers->server_data['script_path'] . '/' . $this->servers->server_data['dir'];
			
			//if($response = $this->servers->command('screen -S ' . $this->servers->server_data['screen_name'] . ' -X hardcopy ' . $dir . '/console.txt && cat ' . $dir . '/console.txt', $this->servers->server_data)) {
			
			$command = $this->servers->command_generate($this->servers->server_data, 'get_console');
			
			if($response = $this->servers->command($command, $this->servers->server_data)) {
				
				//$this->tpl_data['content'] = $response;
				
				if(!$this->servers->server_data['ds_id']) {
					$file_contents = $this->servers->read_local_file($dir . '/' . 'console.txt');
				} else {
					$file_contents = $this->servers->read_remote_file($this->servers->server_data['ftp_path'] . '/' . $this->servers->server_data['dir'] . '/' . 'console.txt');
				}
				
				if(!$file_contents) {
					if($this->users->auth_data['is_admin']) {
						// Отображаем админу его информацию
						$message = lang('server_command_no_data');
						$adm_message = '<p align="center">' . lang('server_command_cmd') . ': <code>' . array_pop($this->servers->commands) . '</code></p>';
						$adm_message .= '<p align="center">' . lang('server_command_file') . ': <strong>"' . $dir . '/console.txt"</strong></p>';
					} else {
						$message = lang('server_command_no_data');
						$adm_message = '';
					}
					
					$this->_show_message($message . $adm_message);
					
					return false;
				} else {
					$file_contents = str_replace("\n", "<br>", $file_contents);
					$file_contents = '<p>' . lang('server_command_console') . ':</p><p align="left"><code>' . $file_contents . '</code></p>';
					
					$this->_show_message($file_contents, site_url('admin/server_control/main/' . $id));
					return true;
					//$this->tpl_data['content'] = '<code>' . $file_contents. '</code>';
				}
				
			} else {
				$message = lang('server_command_no_data');
				
				if($this->users->auth_data['is_admin']) {
					$message .= '<p>' . lang('server_command_sent_cmd') . ':<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
				}
				
				$this->_show_message($message, site_url('admin/server_control/main/' . $id));
				return false;
			}
			
		} else {
			$this->_show_message(lang('server_command_server_not_found'), site_url('admin'), lang('next'));
			return false;
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	/**
	 * 
	 * Запуск сервера
	 * 
	*/
	public function start($id, $confirm = false)
    {
		if(!$this->check_errors($id)){
			$this->servers->get_server_data($id);
		}
		
		/* Получены ли необходимые данные о сервере */
		if($this->servers->server_data) {
			
			// Получение прав на сервер
			$this->users->get_server_privileges($this->servers->server_data['id']);
			
			/* 
			 * Проверка прав на управление сервером
			 * Права пользователя храняться в $this->users->user_privileges
			 * Серверы, которыми владеет пользователь $this->users->user_servers
			 * Информация о сервере хранится в $this->servers->server_data
			 * 
			 * Если в привилегиях пользователя стоит 2, то он имеет права
			 * на запуск любых серверов
			 * Если в привилегиях стоит 1, то он имеет права на запуск
			 * лишь своих серверов, в этом случае нужно еще проверить -
			 * находится ли сервер в списке
			*/
			if($this->users->auth_privileges['srv_start']			// Право на запуск серверов
				&& $this->users->auth_servers_privileges['SERVER_START']	// Право на запуск этого сервера
			) {
				
				/* Проверка SSH и Telnet */
				if (false == $this->_check_ssh() OR false == $this->_check_telnet()) {
					$this->_show_message();
					return false;
				}

				/* Заданы ли параметры запуска */
				if (!$this->servers->server_data['script_start']){
					$this->_show_message(lang('server_command_start_not_param'));
					return false;
				}
				
				
				/* Подтверждение 
				 * Чтобы избежать случаев случайного запуска сервера
				*/
				if($confirm == $this->security->get_csrf_hash()) {
					if($response = $this->servers->start($this->servers->server_data)) {
						/* 
						 * В некоторых случаях (так обычно и бывает)
						 * strpos($response, 'blablabla') может возвращать 0, 
						 * а нам нужен именно false
						 */
						if (strpos($response, 'Server is already running') !== false) {
							/* Сервер запущен ранее */
							$message = lang('server_command_server_is_already_running', site_url('server_command/restart/'. $id), site_url('server_command/stop/' . $id));
							$log_data['msg'] = 'Server is already running';		
						} elseif($this->servers->server_status() OR strpos($response, 'Server started') !== false) {
							/* Сервер успешно запущен */
							$message = lang('server_command_started');
							$log_data['msg'] = 'Start server success';
						} elseif(strpos($response, 'file not found') !== false) {
							/* Не найден исполняемый файл */
							$message = lang('server_command_start_file_not_found');
							$log_data['msg'] = 'Executable file not found';
						} elseif(strpos($response, 'file not executable') !== false) {
							/* Нет прав на запуск файла */
							$message = lang('server_command_start_file_not_executable');
							$log_data['msg'] = 'File not executable';
						} elseif(strpos($response, 'Server not started') !== false) {
							$message = lang('server_command_start_failed');
							
							if($this->users->auth_data['is_admin']) {
								$message .= lang('server_command_start_adm_msg');
								
								$message .= '<p>' . lang('server_command_sent_cmd') . ':<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
							}
							
							$log_data['msg'] = 'Start server failed';
						} else {
							$message = lang('server_command_cmd_sended');
							$log_data['msg'] = 'Command sended';
						}
						
						//$this->tpl_data['content'] .= '<p></p><a href="/admin/server_control/main/' . $this->uri->rsegment(3) . '">Назад, к управлению сервером</a></p>';
					
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'start';
						$log_data['user_name'] = $this->users->auth_login;
						$log_data['server_id'] = $this->servers->server_data['id'];
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
					
					} else {
						$message = lang('server_command_start_failed');
						
						if($this->users->auth_data['is_admin']) {
							$message .= lang('server_command_start_adm_msg');
							$message .= '<p>' . lang('server_command_sent_cmd') . '<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
						}
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'start';
						$log_data['user_name'] = $this->users->auth_login;
						$log_data['server_id'] = $this->servers->server_data['id'];
						$log_data['msg'] = 'Start server Error';
						$log_data['log_data'] = $this->servers->errors;
						$this->panel_log->save_log($log_data);
					}
					
					$this->_show_message($message, site_url('admin/server_control/main/' . $id), lang('next'));
					return true;
					
				} else {
					/* Пользователь не подвердил намерения */
					$confirm_tpl['message'] = lang('server_command_start_confirm');
					$confirm_tpl['confirmed_url'] = site_url('server_command/start/' . $this->servers->server_data['id'] . '/' . $this->security->get_csrf_hash());
					$this->tpl_data['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
				}
				
			}else{
				$this->_show_message(lang('server_command_no_start_privileges'), site_url('admin/server_control/main/' . $id), lang('next'));
				return false;
			}
		} else {
			$this->_show_message(lang('server_command_server_not_found'), site_url('admin'), lang('next'));
			return false;
		}

		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	/*
	 * 
	 * Остановка сервера
	 * 
	*/
	public function stop($id, $confirm = false)
    {
		// Проверка, авторизован ли юзверь
		if($this->users->auth_id){

			if(!$this->check_errors($id)){
				$this->servers->get_server_data($id);
			}
			
			if($this->servers->server_data){
				
				// Получение прав на сервер
				$this->users->get_server_privileges($this->servers->server_data['id']);
				
				/* 
				 * Проверка прав на управление сервером
				 * Права пользователя храняться в $this->users->user_privileges
				 * Серверы, которыми владеет пользователь $this->users->user_servers
				 * Информация о сервере хранится в $this->servers->server_data
				 * 
				 * Если в привилегиях пользователя стоит 2, то он имеет права
				 * на запуск любых серверов
				 * Если в привилегиях стоит 1, то он имеет права на запуск
				 * лишь своих серверов, в этом случае нужно еще проверить -
				 * находится ли сервер в списке
				*/
				if($this->users->auth_privileges['srv_stop']			// Право на запуск серверов
					&& $this->users->auth_servers_privileges['SERVER_STOP']	// Право на запуск этого сервера
				) {
					
					/* Проверка SSH и Telnet */
					if (false == $this->_check_ssh() OR false == $this->_check_telnet()) {
						$this->_show_message();
						return false;
					}
	
					/* Заданы ли параметры запуска */
					if (!$this->servers->server_data['script_stop']){
						$this->_show_message(lang('server_command_stop_not_param'));
						return false;
					}
					
					/* Подтверждение 
					 * Чтобы избежать случаев случайного запуска сервера
					*/
					if($confirm == $this->security->get_csrf_hash()){
						if($response = $this->servers->stop($this->servers->server_data)){
							
							/* 
							 * В некоторых случаях (так обычно и бывает)
							 * strpos($response, 'blablabla') может возвращать 0, 
							 * а нам нужен именно false
							 */
							if(strpos($response, 'Coulnd\'t find a running server') !== false) {
								$message = lang('server_command_running_server_not_found');
								$log_data['msg'] = 'Coulnd\'t find a running server';
							} elseif(strpos($response, 'Server stopped') !== false) {
								$message = lang('server_command_stopped');
								$log_data['msg'] = 'Stop server success';
							} elseif(strpos($response, 'file not found') !== false) {
								/* Не найден исполняемый файл */
								$message = lang('server_command_start_file_not_found');
								$log_data['msg'] = 'Executable file not found';
							} elseif(strpos($response, 'file not executable') !== false) {
								/* Нет прав на запуск файла */
								$message = lang('server_command_start_file_not_executable');
								$log_data['msg'] = 'File not executable';
							} elseif(strpos($response, 'Server not stopped') !== false) {
								$message = lang('server_command_stop_failed');
								
								if($this->users->auth_data['is_admin']) {
									$message .= lang('server_command_start_adm_msg');
									$message .= '<p>' . lang('server_command_sent_cmd') . ':<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
								}
								
								$log_data['msg'] = 'Stop server failure';
							} else {
								$message = lang('server_command_cmd_sended');
								$log_data['msg'] = 'Command sended';
							}
							
							// Сохраняем логи
							$log_data['type'] = 'server_command';
							$log_data['command'] = 'stop';
							$log_data['user_name'] = $this->users->auth_login;
							$log_data['server_id'] = $this->servers->server_data['id'];
							$log_data['log_data'] = $response;
							$this->panel_log->save_log($log_data);
						} else {
							$message = 'Ошибка остановки';
							
							if($this->users->auth_data['is_admin']) {
								$message .= '<p>' . lang('server_command_sent_cmd') . ':<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
							}
							
							// Сохраняем логи
							$log_data['type'] = 'server_command';
							$log_data['command'] = 'stop';
							$log_data['user_name'] = $this->users->auth_login;
							$log_data['server_id'] = $this->servers->server_data['id'];
							$log_data['msg'] = 'Stop server error';
							$log_data['log_data'] = $this->servers->errors;
							$this->panel_log->save_log($log_data);
						}
						
						$this->_show_message($message, site_url('admin/server_control/main/' . $id), lang('next'));
						return true;
						
					}else{
						/* Пользователь не подвердил намерения */
						$confirm_tpl['message'] = lang('server_command_stop_confirm');
						$confirm_tpl['confirmed_url'] = site_url('server_command/stop/' . $this->servers->server_data['id'] . '/' . $this->security->get_csrf_hash());
						$this->tpl_data['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
					}
					
				}else{
						$this->_show_message(lang('server_command_no_stop_privileges'), site_url('admin/server_control/main/' . $id), lang('next'));
						return false;
				}
			} else {
				$this->_show_message(lang('server_command_server_not_found'), site_url('admin'), lang('next'));
				return false;
			}
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
		
	}
	
	
	/*
	 * 
	 * Перезагрузка сервера
	 * 
	*/
	public function restart($id, $confirm = false)
    {
		if(!$this->check_errors($id)){
			$this->servers->get_server_data($id);
		}
		
		if($this->servers->server_data){
			
			// Получение прав на сервер
			$this->users->get_server_privileges($this->servers->server_data['id']);
			
			/* 
			 * Проверка прав на управление сервером
			 * Права пользователя храняться в $this->users->user_privileges
			 * Серверы, которыми владеет пользователь $this->users->user_servers
			 * Информация о сервере хранится в $this->servers->server_data
			 * 
			 * Если в привилегиях пользователя стоит 2, то он имеет права
			 * на запуск любых серверов
			 * Если в привилегиях стоит 1, то он имеет права на запуск
			 * лишь своих серверов, в этом случае нужно еще проверить -
			 * находится ли сервер в списке
			*/
			if($this->users->auth_privileges['srv_restart']
				&& $this->users->auth_servers_privileges['SERVER_RESTART']
			) {
				
				/* Проверка SSH и Telnet */
				if (false == $this->_check_ssh() OR false == $this->_check_telnet()) {
					$this->_show_message();
					return false;
				}

				/* Заданы ли параметры запуска */
				if (!$this->servers->server_data['script_restart']){
					$this->_show_message(lang('server_command_restart_not_param'));
					return false;
				}

				/* Подтверждение 
				 * Чтобы избежать случаев случайного запуска сервера
				*/
				if($confirm == $this->security->get_csrf_hash()){
					if($response = $this->servers->restart($this->servers->server_data)){
						
						/* 
						 * В некоторых случаях (так обычно и бывает)
						 * strpos($response, 'blablabla') может возвращать 0, 
						 * а нам нужен именно false
						 */
						if(strpos($response, 'Coulnd\'t find a running server') !== false) {
							$message = lang('server_command_restart_running_server_not_found');
							$log_data['msg'] = 'Coulnd\'t find a running server';
						} elseif(strpos($response, 'Server restarted') !== false) {
							$message = lang('server_command_restarted');
							$log_data['msg'] = 'Server restarted';
						} elseif(strpos($response, 'file not found') !== false) {
							/* Не найден исполняемый файл */
							$message = lang('server_command_start_file_not_found');
							$log_data['msg'] = 'Executable file not found';
						} elseif(strpos($response, 'file not executable') !== false) {
							/* Нет прав на запуск файла */
							$message = lang('server_command_start_file_not_executable');
							$log_data['msg'] = 'File not executable';
						} elseif(strpos($response, 'Server not restarted') !== false) {
							$message = lang('server_command_restart_failed');
							
							if($this->users->auth_data['is_admin']) {
								$message .= lang('server_command_start_adm_msg');
								$message .= '<p>' . lang('server_command_sent_cmd') . ':<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
							}
							
							$log_data['msg'] = 'Server not restarted';
						} else {
							$message = lang('server_command_cmd_sended');
							$log_data['msg'] = 'Command sended';
						}
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['user_name'] = $this->users->auth_login;
						$log_data['server_id'] = $this->servers->server_data['id'];
						$log_data['msg'] = 'Restart server success';
						$log_data['log_data'] = $response;
						$this->panel_log->save_log($log_data);
					
					}else{
						$message = lang('server_command_restart_failed');
						
						if($this->users->auth_data['is_admin']) {
							$message .= '<p>' . lang('server_command_sent_cmd') . ':<br /><code>' . array_pop($this->servers->commands) . '</code></p>';
						}
						
						// Сохраняем логи
						$log_data['type'] = 'server_command';
						$log_data['command'] = 'restart';
						$log_data['user_name'] = $this->users->auth_login;
						$log_data['server_id'] = $this->servers->server_data['id'];
						$log_data['msg'] = 'Restart server error';
						$log_data['log_data'] = $this->servers->errors;
						$this->panel_log->save_log($log_data);
					}
					
					$this->_show_message($message, site_url('admin/server_control/main/' . $id), lang('next'));
					return true;
					
				}else{
					/* Пользователь не подвердил намерения */
					$confirm_tpl['message'] = lang('server_command_restart_confirm');
					$confirm_tpl['confirmed_url'] = site_url('server_command/restart/' . $this->servers->server_data['id'] . '/' . $this->security->get_csrf_hash());
					$this->tpl_data['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
				}
			}else{
					$message = lang('server_command_no_restart_privileges');
					$this->_show_message($message, site_url('admin/server_control/main/' . $id), lang('next'));
					return false;
					
					break;
			}
		}else{
			$message = lang('server_command_server_not_found');
			$this->_show_message($message, site_url('admin'), lang('next'));
			return false;
		}
	
		$this->parser->parse('main.html', $this->tpl_data);
		
	}
	
	/*
	 * 
	 * Обновление сервера
	 * 
	*/
	public function update($id, $confirm = false)
    {
		if(!$this->check_errors($id)){
			$this->servers->get_server_data($id);
		}
		
		if($this->servers->server_data){
			
			// Получение прав на сервер
			$this->users->get_server_privileges($this->servers->server_data['id']);
			
			/* 
			 * Проверка прав на управление сервером
			 * Права пользователя храняться в $this->users->user_privileges
			 * Серверы, которыми владеет пользователь $this->users->user_servers
			 * Информация о сервере хранится в $this->servers->server_data
			 * 
			*/
			if ($this->users->auth_servers_privileges['SERVER_UPDATE']) {
				
				/* Проверка SSH и Telnet */
				if (false == $this->_check_ssh() OR false == $this->_check_telnet()) {
					$this->_show_message();
					return false;
				}

				/* Заданы ли параметры запуска */
				if (!$this->servers->server_data['script_update']) {
					$this->_show_message(lang('server_command_update_not_param'));
					return false;
				}

				/* Подтверждение 
				 * Чтобы избежать случаев случайного обновления
				*/
				if ($confirm == $this->security->get_csrf_hash()) {
					
					/* Прямое обновление
					 * устарело, теперь обновление через cron */
					//~ if ($response = $this->servers->update($this->servers->server_data)) {
						//~ $message = lang('server_command_cmd_sended');
						//~ 
						//~ // Сохраняем логи
						//~ $log_data['type'] = 'server_command';
						//~ $log_data['command'] = 'update';
						//~ $log_data['user_name'] = $this->users->auth_login;
						//~ $log_data['server_id'] = $this->servers->server_data['id'];
						//~ $log_data['msg'] = 'Server update success';
						//~ $log_data['log_data'] = $response;
						//~ $this->panel_log->save_log($log_data);
					//~ 
					//~ } else {
						//~ $message = lang('server_command_update_failed');
						//~ 
						//~ // Сохраняем логи
						//~ $log_data['type'] = 'server_command';
						//~ $log_data['command'] = 'update';
						//~ $log_data['user_name'] = $this->users->auth_login;
						//~ $log_data['server_id'] = $this->servers->server_data['id'];
						//~ $log_data['msg'] = 'Server update error';
						//~ $log_data['log_data'] = 'Command: ' . array_pop($this->servers->commands) . "\nResponse: \n" . $response;
						//~ $this->panel_log->save_log($log_data);
					//~ }
					//~ 
					
					$sql_data['server_id'] = $id;
			
					$sql_data['name'] = 'Update server';
					$sql_data['code'] = 'server_update';
					$sql_data['command'] = '';
					$sql_data['date_perform'] = time();
					
					// Добавляем задание
					$this->db->insert('cron', $sql_data);
					
					// Сохраняем логи
					$log_data['type'] = 'server_task';
					$log_data['command'] = 'add_task';
					$log_data['user_name'] = $this->users->user_login;
					$log_data['server_id'] = $id;
					$log_data['msg'] = 'Add new task';
					$log_data['log_data'] = 'Name: ' . $sql_data['name'];
					$this->panel_log->save_log($log_data);

					$this->_show_message(lang('server_command_cmd_sended'), site_url('admin/server_control/main/' . $id), lang('next'));
					return true;
					
				} else {
					/* Пользователь не подвердил намерения */
					$confirm_tpl['message'] = lang('server_command_update_confirm');
					$confirm_tpl['confirmed_url'] = site_url('server_command/update/' . $this->servers->server_data['id'] . '/' . $this->security->get_csrf_hash());
					$this->tpl_data['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
				}
			} else {
					$message = lang('server_command_no_update_privileges');
					$this->_show_message($message, site_url('admin/server_control/main/' . $id), lang('next'));
					return false;
					
					break;
			}
		}else{
			$message = lang('server_command_server_not_found');
			
			$this->_show_message($message, site_url('admin'));
			return false;
		}
		
		$this->parser->parse('main.html', $this->tpl_data);

	}
	
}

/* End of file server_command.php */
/* Location: ./application/controllers/server_command.php */
