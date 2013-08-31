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
class Adm_servers extends CI_Controller {
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('adm_servers');
        $this->lang->load('server_control');
        $this->lang->load('main');
        
        if($this->users->check_user()) {
			
			//Base Template
			$this->tpl_data['title'] 	= lang('adm_servers_title_index');
			$this->tpl_data['heading'] 	= lang('adm_servers_heading_index');
			$this->tpl_data['content'] 	= '';
			
			/* Есть ли у пользователя права */
			if(!$this->users->auth_privileges['srv_global']) {
				redirect('admin');
			}
			
			$this->load->model('servers');
			$this->load->library('form_validation');
			$this->load->helper('form');
			
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, TRUE);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), TRUE);
        
        } else {
			redirect('auth');
        }
    }

    // Отображение информационного сообщения
    private function show_message($message = 'Ошибка', $link = FALSE, $link_text = 'Вернуться')
    {
        if(!$link) {
			$link = 'javascript:history.back()';
		}
        
        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    //Главная
    public function index()
    {
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
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
	public function view($type = 'dedicated_servers', $id = FALSE)
	{
		if($this->users->auth_id){
			// Пользователь авторизован
			
			$local_tpl_data = array();
			$error_msg = FALSE;

			switch($type){
				case 'dedicated_servers':
					$this->load->model('servers/dedicated_servers');
				
					$this->tpl_data['title'] 	= lang('adm_servers_title_ds');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_ds');
					
					$parse_file = 'adm_servers/dedicated_servers.html';				// В этом файле обычно меню
					$parse_list_file = 'adm_servers/dedicated_servers_list.html';	// Шаблон списка
					
					if($tpl_list = $this->dedicated_servers->tpl_data_ds()){
						$local_tpl_data['ds_list'] = $tpl_list;
					}else{
						$error_msg = '<p>' . lang('adm_servers_ds_unavailable') .'</p>';
					}
					
					break;
				case 'game_servers':
					//$this->load->model('servers/game_servers');
					
					$this->tpl_data['title'] 	= lang('adm_servers_title_gs');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_gs');
					
					$parse_file = 'adm_servers/game_servers.html';				// В этом файле обычно меню
					$parse_list_file = 'adm_servers/game_servers_list.html';	// Шаблон списка
					
					if($this->servers->get_server_list(FALSE, FALSE, array())){
						$tpl_list = $this->servers->tpl_data();
						$local_tpl_data['servers_list'] = $tpl_list;
					}else{
						$error_msg = '<p>' . lang('adm_servers_gs_unavailable') . '</p>';
					}

					break;
				case 'games':
					$this->load->model('servers/games');
					
					$this->tpl_data['title'] 	= lang('adm_servers_title_games');
					$this->tpl_data['heading']	= lang('adm_servers_heading_games');
					
					$parse_file = 'adm_servers/games.html';			// В этом файле обычно меню
					$parse_list_file = 'adm_servers/games_list.html';	// Шаблон списка
				
					if ($this->games->get_games_list()) {
						$tpl_list = $this->games->tpl_data_games();
						$local_tpl_data['games_list'] = $tpl_list;
					} else {
						$error_msg = '<p>' . lang('adm_servers_games_unavailable') . '</p>';
					}
					
					break;
				
				case 'game_types':
					$this->load->model('servers/game_types');
					$this->load->model('servers/games');
				
					$this->tpl_data['title'] 	= lang('adm_servers_title_gt');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_gt');
					
					$parse_file = 'adm_servers/game_types.html';			// В этом файле обычно меню
					$parse_list_file = 'adm_servers/game_types_list.html';	// Шаблон списка
					
					if($this->game_types->get_gametypes_list()){
						$game_types_list = $this->game_types->tpl_data_game_types();
						//$local_tpl_data['game_type_list'] = $tpl_list;
					}else{
						$error_msg = '<p>' . lang('adm_servers_gt_unavailable') . '</p>';
					}
					
					if(!$error_msg){
						/* Получение игр */
						if($games_list = $this->games->get_games_list()){
							$num = -1;
							$tpl_data = array();
							foreach ($games_list as $games){
								
									// Условие
									$where = array('game_code' => $games['code']);
									
									/* Если у игры нет модификаций, то не отображаем ее */
									if(!$this->game_types->get_gametypes_list($where)){
										continue;
									}
									
									$num++;
									$tpl_data[$num]['gt_list'] = $this->game_types->tpl_data_game_types();

									$tpl_data[$num]['game_name'] = $games['name'];
									$tpl_data[$num]['game_code'] = $games['code'];
									$tpl_data[$num]['game_start_code'] = $games['start_code'];
									$tpl_data[$num]['game_engine'] = $games['engine'];
									$tpl_data[$num]['game_engine_version'] = $games['engine_version'];

									
									//$local_tpl_data = $this->parser->parse('adm_servers/game_types_list.html', $tpl_data, TRUE);
									
								}

							$local_tpl_data['games_list'] = $tpl_data;
							
						}else{
							$error_msg .= '<p>' . lang('adm_servers_games_unavailable') . '</p>';
						}
					}

					break;
					
				default:
					redirect('/adm_servers/view/dedicated_servers');
					break;
			}

			// Верхняя оболочка, в качестве меню
			if(isset($parse_file)){
				$this->tpl_data['content'] .= $this->parser->parse($parse_file, $local_tpl_data, TRUE);
			}
			
			/* Если ошибок никаких, то отображаем список */
			if(!$error_msg){
				$this->tpl_data['content'] .= $this->parser->parse($parse_list_file, $local_tpl_data, TRUE);
			}else{
				$this->tpl_data['content'] .= $error_msg;
			}
			
		}else{
			redirect();
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
		
	}

	
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
	public function add($type = 'dedicated_servers', $param_1 = FALSE, $param_2 = FALSE)
	{
		if($this->users->auth_id){
			// Пользователь авторизован
			
			$local_tpl_data = array();
			$error_msg = FALSE;

			/* Параметры для форм, задание правил проверки
			 * title страниц, файлы шаблонов 
			*/
			switch($type){
				case 'dedicated_servers':
					$this->load->model('servers/dedicated_servers');
				
					// Добавление выделенного сервера
					$this->tpl_data['title'] 	= lang('adm_servers_title_add_ds');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_add_ds');
					
					$tpl_file_add = 'adm_servers/dedicated_servers_add.html';
					
					/* Проверка формы */
					$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('os', lang('os'), 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('control_protocol', lang('adm_servers_control_protocol'), 'trim|max_length[8]|xss_clean');
					$this->form_validation->set_rules('location', lang('adm_servers_location'), 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('provider', lang('adm_servers_provider'), 'trim|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('ip', lang('ip'), 'trim|required|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ram', lang('adm_servers_ram'), 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('cpu', lang('adm_servers_cpu'), 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('steamcmd_path', lang('adm_servers_steamcmd_path'), 'trim|max_length[256]|xss_clean');
					
					$this->form_validation->set_rules('ssh_host', lang('adm_servers_ssh_host'), 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ssh_login', 'SSH login', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ssh_password', 'SSH password', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ssh_path', lang('adm_servers_path_to_executable_file'), 'trim|max_length[256]|xss_clean');
					
					$this->form_validation->set_rules('telnet_host', lang('adm_servers_telnet_host'), 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('telnet_login', 'Telnet login', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('telnet_password', 'Telnet password', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('telnet_path', lang('adm_servers_path_to_executable_file'), 'trim|max_length[256]|xss_clean');
					
					$this->form_validation->set_rules('ftp_host', lang('adm_servers_ftp_host'), 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ftp_login', 'FTP login', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ftp_password', 'FTP password', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ftp_path', lang('adm_servers_path_to_executable_file'), 'trim|max_length[256]|xss_clean');

					break;
					
				case 'game_servers':
					$this->load->model('servers/dedicated_servers');
					$this->load->model('servers/games');
					
					// Добавление нового сервера
					$this->tpl_data['title'] 	= lang('adm_servers_title_add_gs');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_add_gs');
					
					// Получаем данные DS для шаблона
					$local_tpl_data['ds_list'] = $this->dedicated_servers->tpl_data_ds();
					
					// Получаем данные игр для шаблона
					$local_tpl_data['games_list'] = $this->games->tpl_data_games();
					
					if(empty($this->games->games_list)) {
						$this->show_message(lang('adm_servers_empty_games_list', base_url() . 'adm_servers/add/games'));
						return FALSE;
					}
					
					$tpl_file_add = 'adm_servers/game_servers_add.html';
					
					/* Проверка формы */
					$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]|xss_clean');
					
					$this->form_validation->set_rules('server_ip', lang('ip'), 'trim|max_length[64]|min_length[4]|xss_clean');
					$this->form_validation->set_rules('server_port', lang('port'), 'trim|required|integer|max_length[6]|min_length[2]|xss_clean');
					
					$this->form_validation->set_rules('rcon', 'RCON password', 'trim|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('code',  lang('adm_servers_game_code'), 'trim|required|max_length[32]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('game_type', lang('adm_servers_game_type'), 'trim|required|integer|xss_clean');
					$this->form_validation->set_rules('dir', lang('adm_servers_server_dir'), 'trim|required|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ds_id', lang('dedicated_server'), 'trim|numeric|max_length[11]|xss_clean');
					
					$this->form_validation->set_rules('screen_name', lang('dedicated_server'), 'trim|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('su_user', lang('adm_servers_user_start'), 'trim|max_length[64]|xss_clean');
					
					$this->form_validation->set_rules('script_start', lang('adm_servers_command_start'), 'trim|max_length[256]|xss_clean');
					$this->form_validation->set_rules('script_stop', lang('adm_servers_command_stop'), 'trim|max_length[256]|xss_clean');
					$this->form_validation->set_rules('script_restart', lang('adm_servers_command_restart'), 'trim|max_length[256]|xss_clean');
					$this->form_validation->set_rules('script_status', lang('adm_servers_command_status'), 'trim|max_length[256]|xss_clean');
					$this->form_validation->set_rules('script_update', lang('adm_servers_command_update'), 'trim|max_length[256]|xss_clean');
					$this->form_validation->set_rules('script_get_console', lang('adm_servers_command_get_console'), 'trim|max_length[256]|xss_clean');
					
					$ds_id = (int)$this->input->post('ds_id');
					
					/* Проверка, существует ли DS */
					if(!$this->servers->ds_server_live($ds_id) && $ds_id !== 0){
						$this->tpl_data['content'] .= '<p>' . lang('adm_servers_selected_ds_unavailable') . '</p>';
						return FALSE;
					}
					
					
					break;
					
				case 'games':
					$this->load->model('servers/games');
				
					$this->tpl_data['title'] 	= lang('adm_servers_title_add_game');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_add_game');
					
					$tpl_file_add = 'adm_servers/games_add.html';
					
					$this->form_validation->set_rules('code', 'код', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('start_code', 'старт код', 'trim|required|max_length[32]|min_length[2]|xss_clean');
					$this->form_validation->set_rules('name', 'название игры', 'trim|required|max_length[32]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('engine', 'движок', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('engine_version', 'версия движка', 'trim|numeric|max_length[11]|xss_clean');
					
					$this->form_validation->set_rules('app_id', 'app_id', 'trim|integer|max_length[32]|xss_clean');
					$this->form_validation->set_rules('app_set_config', 'app_set_config', 'trim|max_length[32]|xss_clean');

					break;
				
				case 'game_types':
					$this->load->model('servers/game_types');
					$this->load->model('servers/games');
				
					$this->tpl_data['title'] 	= lang('adm_servers_title_add_game_type');
					$this->tpl_data['heading'] 	= lang('adm_servers_heading_add_game_type');
					
					$this->form_validation->set_rules('code', 'код игры', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('name', 'название игры', 'trim|required|max_length[32]|min_length[3]|xss_clean');
					
					$this->form_validation->set_rules('disk_size', 'размер диска', 'trim|numeric|required|max_length[11]|xss_clean');
					
					if($tpl_list = $this->games->tpl_data_games()) {
						$local_tpl_data['games_list'] = $tpl_list;
					}
					
					if(empty($this->games->games_list)) {
						$this->show_message(lang('adm_servers_empty_games_list', base_url() . 'adm_servers/add/games'));
						return FALSE;
					}
					
					$tpl_file_add = 'adm_servers/game_types_add.html';
					break;
				default:
					redirect('');
					break;
			}
			
			/* Проверяем форму */
			if ($this->form_validation->run() == FALSE) {
				
				//$this->show_message(validation_errors(), $link = 'javascript:history.back()');
				
				if(!isset($tpl_file_add)){
					$this->show_message('', $link = 'javascript:history.back()');
					return FALSE;
				}else{
					$local_tpl_data['message'] = '';
					$local_tpl_data['back_link_txt'] = lang('back');
					$local_tpl_data['link'] = 'javascript:history.back()';
					$this->tpl_data['content'] .= $this->parser->parse($tpl_file_add, $local_tpl_data, TRUE);
				}
				
					
			} else {
				
				/* 
				 * Проверка пройдена
				 * Подготовка данных и отправка их в базу 
				 * 
				 * Все данные проходят XSS фильтрацию, это указывается добавлением параметра
				 * xss_clean при задании правил, либо через заданием TRUE:
				 * $this->input->post('name', TRUE);
				 * 
				*/
				
				$local_tpl_data = array();

				switch($type){
					case 'dedicated_servers':
						/* Удаленные серверы */
					
						$sql_data['name'] = $this->input->post('name');
						$sql_data['os'] = $this->input->post('os');
						$sql_data['control_protocol'] = $this->input->post('control_protocol');
						$sql_data['location'] = $this->input->post('location');
						$sql_data['provider'] = $this->input->post('provider');
						$sql_data['ip'] = $this->input->post('ip');
						$sql_data['ram'] = (int)$this->input->post('ram');
						$sql_data['cpu'] = (int)$this->input->post('cpu');
						
						$sql_data['steamcmd_path'] = $this->input->post('steamcmd_path');
						
						$sql_data['ssh_host'] = $this->input->post('ssh_host');
						$sql_data['ssh_login'] = $this->input->post('ssh_login');
						$sql_data['ssh_password'] = $this->input->post('ssh_password');
						$sql_data['ssh_path'] = $this->input->post('ssh_path');
						
						$sql_data['telnet_host'] = $this->input->post('telnet_host');
						$sql_data['telnet_login'] = $this->input->post('telnet_login');
						$sql_data['telnet_password'] = $this->input->post('telnet_password');
						$sql_data['telnet_path'] = $this->input->post('telnet_path');
						
						$sql_data['ftp_host'] = $this->input->post('ftp_host');
						$sql_data['ftp_login'] = $this->input->post('ftp_login');
						$sql_data['ftp_password'] = $this->input->post('ftp_password');
						$sql_data['ftp_path'] = $this->input->post('ftp_path');
						
						/* 
						 * Проверка указандых данных ssh, telnet, ftp
						 * чтобы пароль подходил
						*/
						
						if(!empty($sql_data['ssh_host'])) {
							$ssh_data = explode(':', $sql_data['ssh_host']);
							
							if(!isset($ssh_data[1])) {
								$ssh_data[1] = 22;
							}
							
							$connection = ssh2_connect($ssh_data[0], $ssh_data[1]);

							$ssh_password = $sql_data['ssh_password'];
							
							/* Если не удалось соединиться или неверные данные */
							if (!$connection OR !ssh2_auth_password($connection, $sql_data['ssh_login'], $ssh_password)) {
								$this->show_message(lang('adm_servers_ssh_data_unavailable'), 'javascript:history.back()');
								return FALSE;
							}
							
						}
						
						if(!empty($sql_data['ftp_host'])) {
							$ftp_data = explode(':', $sql_data['ftp_host']);
							
							if(!isset($ftp_data[1])) {
								$ftp_data[1] = 21;
							}
	
							$connection = ftp_connect($ftp_data[0], $ftp_data[1]);
							
							$ftp_password = $sql_data['ftp_password'];
							
							/* Если не удалось соединиться или неверные данные */
							if (!$connection OR !ftp_login($connection, $sql_data['ftp_login'], $ftp_password)) {
								$this->show_message(lang('adm_servers_ftp_data_unavailable'), 'javascript:history.back()');
								return FALSE;
							}
						}
						
						// Добавление сервера
						if ($this->dedicated_servers->add_dedicated_server($sql_data)) {
							$local_tpl_data['message'] = lang('adm_servers_add_server_successful');
						} else {
							$local_tpl_data['message'] = lang('adm_servers_add_server_failed');
						}
						
						$local_tpl_data['link'] = site_url('adm_servers/view/dedicated_servers');
						$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_servers');
						
						break;

					case 'game_servers':
						/* Игровые серверы */
						
						$this->load->model('servers/dedicated_servers');
						$this->load->model('servers/game_types');
						
						// Форма проверена, все хорошо, добавляем сервер
						$sql_data['name'] = $this->input->post('name');
						
						/* Если ip сервера не указан, то используем ip ДС */
						//if(!$this->input->post('server_ip')){
						//	$sql_data['server_ip'] = $this->input->post('server_ip');
						//}
						
						$sql_data['server_ip'] 		= $this->input->post('server_ip');
						$sql_data['server_port'] 	= $this->input->post('server_port');
						$sql_data['enabled'] 		= (int)(bool)$this->input->post('enabled');
						$sql_data['installed'] 		= '1';
						
						$sql_data['rcon'] 			= $this->input->post('rcon');
						$sql_data['game'] 			= $this->input->post('code');
						$sql_data['game_type'] 		= $this->input->post('game_type');
						$sql_data['dir'] 			= $this->input->post('dir');
						$sql_data['ds_id'] 			= $this->input->post('ds_id');
						
						$sql_data['screen_name'] 	= $this->input->post('screen_name');
						$sql_data['su_user'] 		= $this->input->post('su_user');
						
						//$sql_data['script_path'] 	= $this->input->post('script_path');
						$sql_data['script_start'] 	= $this->input->post('script_start');
						$sql_data['script_stop'] 	= $this->input->post('script_stop');
						$sql_data['script_restart'] = $this->input->post('script_restart');
						$sql_data['script_status'] 	= $this->input->post('script_status');
						$sql_data['script_update'] 	= $this->input->post('script_update');
						$sql_data['script_get_console'] 	= $this->input->post('script_get_console');
						
						$sql_data['maps_path'] = '/' . $sql_data['game'] . '/maps';
						
						/* Чтобы ид модификации был правильный и подходил для выбранной игры */
						$where = array('id' => $sql_data['game_type'], 'game_code' => $sql_data['game']);
						if(!$this->game_types->get_gametypes_list($where, 1)) {
							$this->show_message(lang('adm_servers_game_type_select_wrong'));
							return FALSE;
						}
						
						/* Если некоторые данные не заполнены, то берем их из данных о
						 * модификации */
						/*
						if($sql_data['script_start'] == ''
							OR $sql_data['script_stop'] == ''
							OR $sql_data['script_restart'] == ''
							OR $sql_data['script_status'] == ''
						) {
							$this->game_types->get_gametypes_list(array('id' => $sql_data['game_type']), 1);
							
							if($sql_data['script_start'] == '') {
								$sql_data['script_start'] = $this->game_types->game_types_list[0]['script_start'];
							}
						}
						*/

						$local_tpl_data = array();
						

						// Добавление сервера
						if($this->dedicated_servers->add_game_server($sql_data)){
							$local_tpl_data['message'] = lang('adm_servers_add_server_successful');
						}else{
							$local_tpl_data['message'] = lang('adm_servers_add_server_failed');
						}
						
						$local_tpl_data['link'] = site_url('adm_servers/view/game_servers');
						$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_servers');
						
						break;
						
					case 'games':
						
						$sql_data['code'] 			= $this->input->post('code');
						$sql_data['start_code'] 	= $this->input->post('start_code');
						$sql_data['name'] 			= $this->input->post('name');
						$sql_data['engine'] 		= $this->input->post('engine');
						$sql_data['engine_version'] = $this->input->post('engine_version');
						
						$sql_data['app_id'] 		= $this->input->post('app_id');
						$sql_data['app_set_config'] = $this->input->post('app_set_config');
						
						/* Убираем кавычки из app_set_config */
						$sql_data['app_set_config'] = str_replace('\'', '', $sql_data['app_set_config']);
						$sql_data['app_set_config'] = str_replace('"', '', $sql_data['app_set_config']);
						$sql_data['app_set_config'] = str_replace('	', '', $sql_data['app_set_config']);
						
						if($this->games->add_game($sql_data)){
							$local_tpl_data['message'] = lang('adm_servers_add_game_successful');
						}else{
							$local_tpl_data['message'] = lang('adm_servers_add_game_failed');
						}
						
						$local_tpl_data['link'] = site_url('adm_servers/view/games');
						$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_games');
						

						break;
					
					case 'game_types':
					
						$sql_data['game_code'] = $this->input->post('code');
						$sql_data['name'] = $this->input->post('name');
						$sql_data['disk_size'] = $this->input->post('disk_size');
						
						if($this->game_types->add_game_type($sql_data)) {
							$local_tpl_data['message'] = lang('adm_servers_add_game_type_successful');
						} else {
							$local_tpl_data['message'] = lang('adm_servers_add_game_type_failed');
						}

						$local_tpl_data['link'] = site_url('adm_servers/edit/game_types/' . mysql_insert_id());
						$local_tpl_data['back_link_txt'] = 'Далее';
						
						break;
						
				}
				
				$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
			
			}

		}else{
			redirect('');
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
		
	}
	
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
	public function delete($type = 'dedicated_servers', $id = FALSE, $confirm = FALSE)
	{
		if($this->users->auth_id){
			// Пользователь авторизован
			
			$local_tpl_data = array();
			$error_msg = FALSE;
			
			$id = $id;

			if ($confirm == $this->security->get_csrf_hash()) {
							
				/* Пользователь подтвердил удаление */
				
				switch($type) {
					case 'dedicated_servers':
						$this->load->model('servers/dedicated_servers');
						
						if (!$this->dedicated_servers->get_ds_list(array('id' => $id))) {
							$this->show_message(lang('adm_servers_selected_ds_unavailable'), site_url('adm_servers/view/dedicated_servers'));
							return FALSE;
						}
						
						if ($this->servers->get_server_list(FALSE, FALSE, array('ds_id' => $id))) {
							$this->show_message(lang('adm_servers_ds_contains_game_servers'), site_url('adm_servers/view/dedicated_servers'));
							return FALSE;
						}

						if ($this->dedicated_servers->del_dedicated_server($id)) {	
							$local_tpl_data['message'] = lang('adm_servers_delete_server_successful');
						} else {
							$local_tpl_data['message'] = lang('adm_servers_delete_server_failed');
						}
									
						$local_tpl_data['link'] 			= site_url('adm_servers/view/dedicated_servers');
						$local_tpl_data['back_link_txt'] 	= lang('adm_servers_back_to_servers');
						
						break;
						
					case 'game_servers':
						if(!$this->servers->get_server_data($id)) {
							$this->show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/game_servers'));
							return FALSE;
						}
						
						/* Удаление директории на выделенном сервере */
						if(isset($this->servers->server_data['dir'])) {
							switch($this->servers->server_data['os']) {
							case 'Windows':
								$command = 'rmdir /S ' . $this->servers->server_data['dir'];
								$result = $this->servers->command_windows($command, $this->servers->server_data);
								break;
							default:
								// Linux
								$command = 'rm -rf ' . $this->servers->server_data['dir'];
								$result = $this->servers->command($command, $this->servers->server_data);
								break;
							}
						}
						
						if($this->servers->delete_game_server($id)) {
							$local_tpl_data['message'] = lang('adm_servers_delete_server_successful');
						}else{
							$local_tpl_data['message'] = lang('adm_servers_delete_server_failed');
						}
							
						$local_tpl_data['link'] = site_url('adm_servers/view/game_servers');
						$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_servers');
						
						break;
						
					case 'games':
						$this->load->model('servers/games');
						
						if(!$this->games->get_games_list(array('code' => $id))) {
							$this->show_message(lang('adm_servers_game_not_found'), site_url('adm_servers/view/games'));
							return FALSE;
						}
						
						if($this->servers->get_server_list(FALSE, FALSE, array('game' => $id))) {
							$this->show_message(lang('adm_servers_game_contains_game_servers'), site_url('adm_servers/view/games'));
							return FALSE;
						}
						
						if($this->games->delete_game($id)){
							$local_tpl_data['message'] = lang('adm_servers_delete_game_successful');
						}else{
							$local_tpl_data['message'] = lang('adm_servers_delete_game_failed');
						}
							
						$local_tpl_data['link'] 			= site_url('adm_servers/view/games');
						$local_tpl_data['back_link_txt'] 	= lang('adm_servers_back_to_games');
						
						break;
						
					case 'game_types':
						$this->load->model('servers/game_types');
						
						if(!$this->game_types->get_gametypes_list(array('id' => $id))) {
							$this->show_message(lang('adm_servers_game_type_not_found'), site_url('adm_servers/view/game_types'));
							return FALSE;
						}
						
						if($this->servers->get_server_list(FALSE, FALSE, array('game_type' => $id))) {
							$this->show_message(lang('adm_servers_game_type_contains_game_servers'), site_url('adm_servers/view/game_types'));
							return FALSE;
						}
						
						/* Удаление модификации */
						if($this->game_types->delete_game_type($id)){
							$local_tpl_data['message'] = lang('adm_servers_delete_game_type_successful');
						}else{
							$local_tpl_data['message'] = lang('adm_servers_delete_game_type_failed');
						}
							
						$local_tpl_data['link'] 			= site_url('adm_servers/view/game_types');
						$local_tpl_data['back_link_txt'] 	= lang('adm_servers_back_to_game_types');
						
						break;
					default:
						$local_tpl_data['message'] 			= lang('adm_servers_unknown_page');
						$local_tpl_data['link'] 			= site_url('/adm_servers/view/game_types');
						$local_tpl_data['back_link_txt'] 	= lang('adm_servers_back_to_game_types');
						break;
				}
				
				// Отображаем инфо сообщение
				$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
							
			} else {
				
				/* Пользователь не подвердил */
				
				switch($type){
					case 'dedicated_servers':
						$confirm_tpl['message'] = lang('adm_servers_delete_ds_confirm');
						$confirm_tpl['confirmed_url'] = site_url('adm_servers/delete/dedicated_servers/'. $id . '/' . $this->security->get_csrf_hash());
						break;
					case 'game_servers':
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
				
				$this->tpl_data['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, TRUE);
			}

		}else{
			redirect('');
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
		
	}
	
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
	public function edit($type = 'dedicated_servers', $id = FALSE, $param_2 = FALSE)
	{
		$local_tpl_data = array();
		$error_msg = FALSE;
		
		switch($type){
			case 'dedicated_servers':
				$this->load->model('servers/dedicated_servers');
				
				if (!$this->dedicated_servers->get_ds_list(array('id' => $id), 1)) {
					$this->show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/dedicated_servers'));
					return FALSE;
				}
				
				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/dedicated_servers_control.html';
					
				$tpl_list = $this->dedicated_servers->tpl_data_ds();
				$local_tpl_data = $tpl_list[0];
				
				//if(in_array('ssh2', get_loaded_extensions()));
				$options = array('ssh' => 'SSH', 'telnet' => 'Telnet');
				
				$local_tpl_data['control_protocol'] = form_dropdown('control_protocol', $options, $this->dedicated_servers->ds_list['0']['control_protocol']);
				
				$local_tpl_data['steamcmd_path'] 	= $this->dedicated_servers->ds_list['0']['steamcmd_path'];
				$local_tpl_data['ssh_host'] 		= $this->dedicated_servers->ds_list['0']['ssh_host'];
				$local_tpl_data['ssh_login'] 		= $this->dedicated_servers->ds_list['0']['ssh_login'];
				$local_tpl_data['ssh_path'] 		= $this->dedicated_servers->ds_list['0']['ssh_path'];
				
				$local_tpl_data['telnet_host'] 		= $this->dedicated_servers->ds_list['0']['telnet_host'];
				$local_tpl_data['telnet_login'] 	= $this->dedicated_servers->ds_list['0']['telnet_login'];
				$local_tpl_data['telnet_path'] 		= $this->dedicated_servers->ds_list['0']['telnet_path'];
				
				$local_tpl_data['ftp_host'] 		= $this->dedicated_servers->ds_list['0']['ftp_host'];
				$local_tpl_data['ftp_login'] 		= $this->dedicated_servers->ds_list['0']['ftp_login'];
				$local_tpl_data['ftp_path'] 		= $this->dedicated_servers->ds_list['0']['ftp_path'];
				
				// Получаем список серверов на DS
				$gs = $this->servers->get_game_servers_list(array('ds_id' => $id));
				
				$local_tpl_data['servers_list'] = $this->servers->tpl_data();
					
				// Редактирование основных параметров
				if($this->input->post('edit_ds')){
						
					/* 
					 * Правила для формы
					 * 
					 * Документация:
					 * http://cidocs.ru/213/libraries/form_validation.html
					 * 
					*/
					$this->form_validation->set_rules('name', 'название', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('os', 'операционная система', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('location', 'расположение', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('provider', 'провайдер', 'trim|max_length[64]|min_length[3]|xss_clean');
					$this->form_validation->set_rules('ip', 'IP', 'trim|required|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ram', 'RAM', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('cpu', 'CPU', 'trim|max_length[64]|xss_clean');
				}
					
					// Редактирование данных доступа к серверу (пароли ftp, ssh)
				if($this->input->post('edit_access_ds')){
					$this->form_validation->set_rules('steamcmd_path', 'путь к SteamCMD', 'trim|max_length[256]|xss_clean');
					
					$this->form_validation->set_rules('ssh_host', 'SSH хост', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ssh_login', 'SSH логин', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ssh_password', 'SSH пароль', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ssh_path', 'Путь SSH', 'trim|max_length[256]|xss_clean');
						
					$this->form_validation->set_rules('telnet_host', 'Telnet хост', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('telnet_login', 'Telnet логин', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('telnet_password', 'Telnet пароль', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('telnet_path', 'Путь Telnet', 'trim|max_length[256]|xss_clean');
						
					$this->form_validation->set_rules('ftp_host', 'FTP хост', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ftp_login', 'FTP логин', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ftp_password', 'FTP пароль', 'trim|max_length[64]|xss_clean');
					$this->form_validation->set_rules('ftp_path', 'Путь FTP', 'trim|max_length[256]|xss_clean');
						
					$this->form_validation->set_rules('control_protocol', 'Протокол управления', 'trim|min_length[3]|max_length[16]|xss_clean');
				}
				
				break;
				
			case 'game_servers':
				$this->load->model('servers/dedicated_servers');
				$this->load->model('servers/games');
				$this->load->model('servers/game_types');
				$this->load->helper('form');
				
				//if(!$game_servers_list = $this->servers->get_game_servers_list(array('id' => $id), 1)){
				//	$this->show_message('Сервера с таким ID не существует', '/adm_servers/view/game_servers');
				//	return FALSE;
				//}
				
				if(!$this->servers->get_server_data($id)){
					$this->show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/game_servers'));
					return FALSE;
				}

				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/game_servers_control.html';

				$servers_list = $this->servers->tpl_data();
				
				$local_tpl_data = $servers_list[0];
				$local_tpl_data['information'] = array();
						
				// Для tpl
				$local_tpl_data['screen_name'] 			= $this->servers->server_data['screen_name'];
				$local_tpl_data['su_user'] 				= $this->servers->server_data['su_user'];
				$local_tpl_data['server_dir'] 			= $this->servers->server_data['dir'];
				$local_tpl_data['game_type_id']			= $this->servers->server_data['game_type'];
				
				/* Если будет пусто, то будут вписаны данные из типа игры
				 * чтобы в textarea оставались пустые данные, берем их не из $this->servers->server_data
				 * а из $this->servers->servers_list[0] 
				 * 
				 * P.S. Не знаю даже как удобнее, но при запуске сервера, данные
				 * будут браться из типа игры.
				*/
				$local_tpl_data['script_start'] 		= $this->servers->servers_list[0]['script_start'];
				$local_tpl_data['script_stop'] 			= $this->servers->servers_list[0]['script_stop'];
				$local_tpl_data['script_restart'] 		= $this->servers->servers_list[0]['script_restart'];
				$local_tpl_data['script_status'] 		= $this->servers->servers_list[0]['script_status'];
				$local_tpl_data['script_update'] 		= $this->servers->servers_list[0]['script_update'];
				$local_tpl_data['script_get_console'] 	= $this->servers->servers_list[0]['script_get_console'];
				
				/* Получаем абсолютный путь к корневой директории с сервером и к исполняемым файлам */
				if ($this->servers->server_data['ds_id'] === '0') {
					$local_tpl_data['full_server_path'] = $this->servers->server_data['local_path'] . '/' . $this->servers->server_data['dir'];
					$local_tpl_data['script_path'] = $this->servers->server_data['local_path'];
				} else {
					if ($this->servers->server_data['control_protocol'] == 'ssh') {
						$local_tpl_data['full_server_path'] = $this->dedicated_servers->ds_list[0]['ssh_path'] . '/' . $this->servers->server_data['dir'];
						$local_tpl_data['script_path'] = $this->dedicated_servers->ds_list[0]['ssh_path'];
					} elseif($this->servers->server_data['control_protocol'] == 'ssh') {
						$local_tpl_data['full_server_path'] = $this->servers->server_data['telnet_path'] . '/' . $this->servers->server_data['dir'];
						$local_tpl_data['script_path'] = $this->servers->server_data['telnet_path'];
					} elseif($this->servers->server_data['os'] == 'Windows') {
						$local_tpl_data['full_server_path'] = $this->servers->server_data['telnet_path'] . '/' . $this->servers->server_data['dir'];
						$local_tpl_data['script_path'] = $this->servers->server_data['telnet_path'];
					} else {
						$local_tpl_data['full_server_path'] = $this->servers->server_data['ssh_path'] . '/' . $this->servers->server_data['dir'];
						$local_tpl_data['script_path'] = $this->servers->server_data['ssh_path'];
					}
				}
				
				// Модификация
				$where = array('game_code' => $servers_list[0]['server_game']);
				$gametypes_list = $this->game_types->get_gametypes_list($where);
				
				foreach($gametypes_list as $list) {
					$options[$list['id']] = $list['name'];
				}
				
				$local_tpl_data['game_type_dropdown'] 		= form_dropdown('game_type', $options, $this->servers->server_data['game_type']);
				$local_tpl_data['server_enabled_checkbox'] 	= form_checkbox('enabled', 'accept', $this->servers->server_data['enabled']);
				
				// Заменяем двойные кавычки на html символы
				$local_tpl_data['script_start'] 	= str_replace('"', '&quot;', $local_tpl_data['script_start'] );
				$local_tpl_data['script_stop'] 		= str_replace('"', '&quot;', $local_tpl_data['script_stop'] );
				$local_tpl_data['script_restart'] 	= str_replace('"', '&quot;', $local_tpl_data['script_restart'] );
				$local_tpl_data['script_status'] 	= str_replace('"', '&quot;', $local_tpl_data['script_status'] );
				$local_tpl_data['script_update'] 	= str_replace('"', '&quot;', $local_tpl_data['script_update'] );
				$local_tpl_data['script_get_console'] 	= str_replace('"', '&quot;', $local_tpl_data['script_get_console'] );
				
				/* Информация о DS */
				if ($this->servers->server_data['ds_id']) {
					
					$local_tpl_data['ds_name'] 		= $this->dedicated_servers->ds_list[0]['name'];
					$local_tpl_data['ds_id'] 		= $this->dedicated_servers->ds_list[0]['id'];
					$local_tpl_data['ds_location'] 	= $this->dedicated_servers->ds_list[0]['location'];
					$local_tpl_data['ds_provider'] 	= $this->dedicated_servers->ds_list[0]['provider'];
				} else {
					// Сервер локальный
					$local_tpl_data['ds_name'] 	= lang('adm_servers_local_server');
					$local_tpl_data['ds_id'] 	= 0;
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
				
				$local_tpl_data['log_list'] = array();
				
				$log_num = 0;
				$i = 0;
				$count_i = count($server_plogs);
				while($i < $count_i){
					
					if($log_num == 15) {
						break;
					}
					
					$local_tpl_data['log_list'][$i]['log_id'] = $server_plogs[$i]['id'];
					$local_tpl_data['log_list'][$i]['log_date'] = unix_to_human($server_plogs[$i]['date'], TRUE, 'eu');
					$local_tpl_data['log_list'][$i]['log_server_id'] = $server_plogs[$i]['server_id'];
					$local_tpl_data['log_list'][$i]['log_user_name'] = $server_plogs[$i]['user_name'];
					$local_tpl_data['log_list'][$i]['log_command'] = $server_plogs[$i]['command'];
					
					
					/* Код действия на понятный язык */
					switch($server_plogs[$i]['type']){
						case 'server_rcon':
							$local_tpl_data['log_list'][$i]['log_type'] = lang('server_control_rcon_send');
							$log_num ++;
							break;
							
						case 'server_command':
							$local_tpl_data['log_list'][$i]['log_type'] = lang('server_control_command');
							$log_num ++;
							break;
							
						case 'server_update':
							$local_tpl_data['log_list'][$i]['log_type'] = lang('server_control_update');
							$log_num ++;
							break;
						case 'server_task':
							$local_tpl_data['log_list'][$i]['log_type'] = lang('server_control_srv_task');
							$log_num ++;
							break;
							
						case 'server_settings':
							$local_tpl_data['log_list'][$i]['log_type'] = lang('server_control_settings');
							$log_num ++;
							break;
							
						case 'server_files':
							$local_tpl_data['log_list'][$i]['log_type'] = lang('server_control_file_operation');
							$log_num ++;
							break;
							
						default:
							// Тип лога неизвестен, удаляем его из списка (не из базы)
							unset($local_tpl_data['log_list'][$i]);
							break;
					}
					
					$i ++;
				}
				
				/* ------------------------------ */
				/* Различная информация о сервере */
				/* ------------------------------ */
				
				
				if ($this->servers->server_data['installed'] == '0') {
					$local_tpl_data['information'][]['text'] = lang('adm_servers_serv_not_installed') . '<br />';
				} elseif($this->servers->server_data['installed'] == '1') {
					$local_tpl_data['information'][]['text'] = lang('adm_servers_serv_installed') . '<br />';
				}elseif($this->servers->server_data['installed'] == '2') {
					$local_tpl_data['information'][]['text'] = lang('adm_servers_serv_installed_proccess') . '<br />';
				}
				
				/* 
				 * --------------------------------------------
				 * Проверка, имеются ли параметры в настройках 
				 * --------------------------------------------
				*/

				/* Допустимые алиасы */
				$allowable_aliases = json_decode($this->servers->server_data['aliases_list'], TRUE);
				/* Значения алиасов на сервере */
				$server_aliases = json_decode($this->servers->server_data['aliases'], TRUE);

				/* Прогон по алиасам */
				if ($allowable_aliases && !empty($allowable_aliases)) {
					$empty_alias = '';
					
					/* Если параметр пуст, то выводим сообщение с предупреждением */
					foreach ($allowable_aliases as $alias) {
						if(!isset($server_aliases[$alias['alias']]) OR empty($server_aliases[$alias['alias']])) {
							$empty_alias .= '"' . $alias['desc'] . '", ';
						}
					}
				}
				
				if ($empty_alias != '') {
					$local_tpl_data['information'][]['text'] = lang('adm_servers_gs_empty_settings') . ': ' . $empty_alias;
				}

				/* 
				 * --------------------------------------------
				 * Правила для формы
				 * --------------------------------------------
				*/	
				$this->form_validation->set_rules('name', 'название', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					
				$this->form_validation->set_rules('server_ip', 'IP сервера', 'trim|max_length[64]|min_length[4]|xss_clean');
				$this->form_validation->set_rules('server_port', 'порт сервера', 'trim|required|integer|max_length[6]|min_length[2]|xss_clean');
				
				$this->form_validation->set_rules('rcon', 'RCON пароль', 'trim|max_length[64]|min_length[3]|xss_clean');
				$this->form_validation->set_rules('game_type', 'модификация (тип игры)', 'trim|required|integer|xss_clean');
				$this->form_validation->set_rules('dir', 'директория сервера', 'trim|required|max_length[64]|xss_clean');
				
				$this->form_validation->set_rules('screen_name', 'имя screen', 'trim|max_length[64]|min_length[3]|xss_clean');
				$this->form_validation->set_rules('su_user', 'Пользователь Linux', 'trim|max_length[64]|xss_clean');
				
				$this->form_validation->set_rules('script_start', 'команда запуска', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_stop', 'команда остановки', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_restart', 'команда перезагрузки', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_status', 'команда проверки статуса', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_update', 'команда обновления', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_get_console', 'команда получения консоли', 'trim|max_length[256]|xss_clean');
				
				break;
				
			case 'games':
				$this->load->model('servers/games');
				
				if(!$this->games->get_games_list(array('code' => $id), 1)){
					$this->show_message(lang('adm_servers_game_not_found'), site_url('adm_servers/view/games'));
					return FALSE;
				}
				
				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/games_control.html';

				$tpl_list = $this->games->tpl_data_games();
				$local_tpl_data = $tpl_list[0];
					
				/* Правила для проверки формы */
				$this->form_validation->set_rules('name', 'название', 'trim|required|max_length[64]|min_length[3]|xss_clean');
				$this->form_validation->set_rules('code', 'код игры', 'trim|required|max_length[32]|min_length[3]|xss_clean');
				$this->form_validation->set_rules('start_code', 'старт код', 'trim|required|max_length[64]|min_length[3]|xss_clean');
					
				$this->form_validation->set_rules('engine', 'движок', 'trim|required|max_length[64]|min_length[3]|xss_clean');
				$this->form_validation->set_rules('engine_version', 'версия движка', 'trim|required|max_length[64]|xss_clean');
				
				$this->form_validation->set_rules('app_id', 'app_id', 'trim|integer|max_length[32]|xss_clean');
				$this->form_validation->set_rules('app_set_config', 'app_set_config', 'trim|max_length[32]|xss_clean');
			
				break;
				
			case 'game_types':
				$this->load->model('servers/game_types');
				
				if(!$gt_list = $this->game_types->get_gametypes_list(array('id' => $id))){
					$this->show_message(lang('adm_servers_game_type_not_found'), site_url('adm_servers/view/game_types'));
					return FALSE;
				}
				
				// Файл шаблона с формой
				$tpl_file_edit = 'adm_servers/game_types_control.html';
					
				$tpl_list = $this->game_types->tpl_data_game_types();
				$local_tpl_data = $tpl_list[0];
				
				$local_tpl_data['cfg_list'] 	= array();
				$local_tpl_data['cdir_list'] 	= array();
				$local_tpl_data['ldir_list'] 	= array();
				$local_tpl_data['frcon_list'] 	= array();
				$local_tpl_data['aliases_list'] = array();
				
				if($json_decode = json_decode($gt_list[0]['config_files'], TRUE)) {
					
					$i = 0;
					foreach($json_decode as $array) {
						$local_tpl_data['cfg_list'][$i]['id'] 			= $i;
						$local_tpl_data['cfg_list'][$i]['desc'] 	= form_input('cfg_desc[]', $array['desc']);
						$local_tpl_data['cfg_list'][$i]['file'] 	= form_input('cfg_file[]', $array['file']);
						$i ++;
					}
					
					// $local_tpl_data['cfg_list'] = $json_decode;
				}
				
				if($json_decode = json_decode($gt_list[0]['content_dirs'], TRUE)) {
					
					$i = 0;
					foreach($json_decode as $array) {
						$local_tpl_data['cdir_list'][$i]['id'] 				= $i;
						$local_tpl_data['cdir_list'][$i]['desc'] 			= form_input('cdir_desc[]', $array['desc']);
						$local_tpl_data['cdir_list'][$i]['path'] 			= form_input('cdir_path[]', $array['path']);
						$local_tpl_data['cdir_list'][$i]['allowed_types'] 	= form_input('cdir_allowed_types[]', $array['allowed_types']);
						$i ++;
					}
					
					// $local_tpl_data['cdir_list'] = $json_decode;
				}
				
				if($json_decode = json_decode($gt_list[0]['log_dirs'], TRUE)) {
					
					$i = 0;
					foreach($json_decode as $array) {
						$local_tpl_data['ldir_list'][$i]['id'] 				= $i;
						$local_tpl_data['ldir_list'][$i]['desc'] 			= form_input('ldir_desc[]', $array['desc']);
						$local_tpl_data['ldir_list'][$i]['path'] 			= form_input('ldir_path[]', $array['path']);
						$local_tpl_data['ldir_list'][$i]['allowed_types'] 	= form_input('ldir_allowed_types[]', $array['allowed_types']);
						$i ++;
					}
					
					// $local_tpl_data['ldir_list'] = $json_decode;
				}
				
				if($json_decode = json_decode($gt_list[0]['fast_rcon'], TRUE)) {
					
					$i = 0;
					foreach($json_decode as $array) {
						$local_tpl_data['frcon_list'][$i]['id'] 			= $i;
						$local_tpl_data['frcon_list'][$i]['desc'] 			= form_input('frcon_desc[]', 	$array['desc']);
						$local_tpl_data['frcon_list'][$i]['rcon_command'] 	= form_input('frcon_command[]', $array['rcon_command']);
						$i ++;
					}
					// $local_tpl_data['frcon_list'] = $json_decode;
				}
				
				if($json_decode = json_decode($gt_list[0]['aliases'], TRUE)) {

					$i = 0;
					foreach($json_decode as $array) {
						$local_tpl_data['aliases_list'][$i]['id'] 			= $i;
						$local_tpl_data['aliases_list'][$i]['alias'] 		= form_input('alias_name[]', $array['alias']);
						$local_tpl_data['aliases_list'][$i]['desc'] 		= form_input('alias_desc[]', $array['desc']);
						$local_tpl_data['aliases_list'][$i]['only_admins'] 	= form_checkbox('alias_only_admins[' . $i . ']', 'accept', $array['only_admins']);
						$i ++;
					}
					
					//$local_tpl_data['aliases_list'] = $json_decode;
					
				}
				
				
				/*
				 * Данные для проверки формы 
				*/
				
				$this->form_validation->set_rules('name', 'название', 'trim|required|max_length[64]|min_length[3]|xss_clean');
				$this->form_validation->set_rules('game_code', 'код игры', 'trim|required|max_length[32]|min_length[3]|xss_clean');
				
				/* Параметры запуска */
				$this->form_validation->set_rules('execfile_linux', 'исполняемый файл linux', 'trim|max_length[32]|xss_clean');
				$this->form_validation->set_rules('execfile_windows', 'исполняемый файл windows', 'trim|max_length[32]|xss_clean');
				
				$this->form_validation->set_rules('script_start', 'команда запуска', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_stop', 'команда остановки', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_restart', 'команда перезагрузки', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_status', 'команда проверки статуса', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_update', 'команда обновления', 'trim|max_length[256]|xss_clean');
				$this->form_validation->set_rules('script_get_console', 'команда получения консоли', 'trim|max_length[256]|xss_clean');
				
				/* Сведения о cfg файлах */
				$this->form_validation->set_rules('cfg_desc[]', 'описание конф. файла', 'trim|max_length[64]|xss_clean');
				$this->form_validation->set_rules('cfg_file[]', 'конф. файл', 'trim|xss_clean');
				
				/* Сведения о контент директориях */
				$this->form_validation->set_rules('cdir_desc[]', 'описание контент директории', 'trim|max_length[64]|xss_clean');
				$this->form_validation->set_rules('cdir_path[]', 'путь к контент директории', 'trim|xss_clean');
				$this->form_validation->set_rules('cdir_allowed_types[]', 'разрешенные типы файлов контент директории', 'trim|max_length[64||xss_clean');
				
				/* Сведения о лог директориях */
				$this->form_validation->set_rules('ldir_desc[]', 'описание лог директории', 'trim|max_length[64]|xss_clean');
				$this->form_validation->set_rules('ldir_path[]', 'путь к лог директории', 'trim|xss_clean');
				$this->form_validation->set_rules('ldir_allowed_types[]', 'разрешенные типы файлов лог директории', 'trim|max_length[64]|xss_clean');
			
				/* Сведения о fast rcon командах */
				$this->form_validation->set_rules('frcon_desc[]', 'описание fast rcon команды', 'trim|max_length[64]|xss_clean');
				$this->form_validation->set_rules('frcon_command[]', 'fast rcon команда', 'trim|max_length[64]|xss_clean');
				
				/* Сведения об алиасах */
				$this->form_validation->set_rules('alias_name[]', 'имя алиаса', 'trim|max_length[64]|xss_clean');
				$this->form_validation->set_rules('alias_desc[]', 'описание алиаса', 'trim|max_length[64]|xss_clean');
				$this->form_validation->set_rules('alias_only_admins[]', 'только для администраторов', 'trim|xss_clean');
				
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
		if ($this->form_validation->run() == FALSE) {
			
			//------------------------------------
			//		Отображение формы и/или ошибок
			//------------------------------------
			$this->tpl_data['content'] .= $this->parser->parse($tpl_file_edit, $local_tpl_data, TRUE);
		} else {
			
			//------------------------------------
			//		Форма проверена, все впорядке
			//------------------------------------
			
			switch($type){
				case 'dedicated_servers':
					/* Выделенные серверы */
					if($this->input->post('edit_ds')){
						
							// Форма проверена, все хорошо, добавляем сервер
							$sql_data['name'] = $this->input->post('name');
							$sql_data['os'] = $this->input->post('os');
							$sql_data['location'] = $this->input->post('location');
							$sql_data['provider'] = $this->input->post('provider');
							$sql_data['ip'] = $this->input->post('ip');
							$sql_data['ram'] = (int)$this->input->post('ram');
							$sql_data['cpu'] = (int)$this->input->post('cpu');
					}
					
					// Редактирование данных доступа к серверу (пароли ftp, ssh)
					if($this->input->post('edit_access_ds')) {
							$sql_data['steamcmd_path'] = $this->input->post('steamcmd_path');
							$sql_data['control_protocol'] = $this->input->post('control_protocol');
							$sql_data['ssh_host'] = $this->input->post('ssh_host');
							$sql_data['ssh_login'] = $this->input->post('ssh_login');
							$sql_data['ssh_password'] = $this->input->post('ssh_password');
							$sql_data['ssh_path'] = $this->input->post('ssh_path');
							
							$sql_data['telnet_host'] = $this->input->post('telnet_host');
							$sql_data['telnet_login'] = $this->input->post('telnet_login');
							$sql_data['telnet_password'] = $this->input->post('telnet_password');
							$sql_data['telnet_path'] = $this->input->post('telnet_path');
							
							$sql_data['ftp_host'] = $this->input->post('ftp_host');
							$sql_data['ftp_login'] = $this->input->post('ftp_login');
							$sql_data['ftp_password'] = $this->input->post('ftp_password');
							$sql_data['ftp_path'] = $this->input->post('ftp_path');	
					}
					
					/* 
					 * Проверка указандых данных ssh, telnet, ftp
					 * чтобы пароль подходил
					*/
					
					//print_r($this->dedicated_servers->ds_list);
					
					if(!empty($sql_data['ssh_host'])) {
						$ssh_data = explode(':', $sql_data['ssh_host']);
						
						if(!isset($ssh_data[1])) {
							$ssh_data[1] = 22;
						}
						
						$connection = ssh2_connect($ssh_data[0], $ssh_data[1]);
						
						/* Пароль не задан, берем из базы */
						if(empty($sql_data['ssh_password'])) {
							$ssh_password = $this->dedicated_servers->ds_list['0']['ssh_password'];
						} else {
							$ssh_password = $sql_data['ssh_password'];
						}
						
						/* Если не удалось соединиться или неверные данные */
						if (!$connection OR !ssh2_auth_password($connection, $sql_data['ssh_login'], $ssh_password)) {
							$this->show_message(lang('adm_servers_ssh_data_unavailable'), 'javascript:history.back()');
							return FALSE;
						}
						
					}
					
					if(!empty($sql_data['ftp_host'])) {
						$ftp_data = explode(':', $sql_data['ftp_host']);
						
						if(!isset($ftp_data[1])) {
							$ftp_data[1] = 21;
						}

						$connection = ftp_connect($ftp_data[0], $ftp_data[1]);
						
						/* Пароль не задан, берем из базы */
						if(empty($sql_data['ftp_password'])) {
							$ftp_password = $this->dedicated_servers->ds_list['0']['ftp_password'];
						} else {
							$ftp_password = $sql_data['ftp_password'];
						}
						
						/* Если не удалось соединиться или неверные данные */
						if (!$connection OR !ftp_login($connection, $sql_data['ftp_login'], $ftp_password)) {
							$this->show_message(lang('adm_servers_ftp_data_unavailable'), 'javascript:history.back()');
							return FALSE;
						}
					}
					
					if($this->dedicated_servers->edit_dedicated_server($id, $sql_data)){
						$local_tpl_data['message'] = lang('adm_servers_server_data_changed');
					}else{
						$local_tpl_data['message'] = lang('adm_servers_error_server_edit');
					}
							
					$local_tpl_data['link'] = site_url('adm_servers/view/dedicated_servers');
					$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_servers');
					
					
					break;
				case 'game_servers':
					/* Игровые серверы */
					
					$sql_data['name'] = $this->input->post('name');
					
					$sql_data['server_ip'] = $this->input->post('server_ip');
					$sql_data['server_port'] = $this->input->post('server_port');
					
					//$sql_data['rcon'] = $this->input->post('rcon');
					//$sql_data['game'] = $this->input->post('code');
					$sql_data['dir'] = $this->input->post('dir');
					$sql_data['game_type'] = $this->input->post('game_type');
					$sql_data['enabled'] = (int)(bool)$this->input->post('enabled');
					//$sql_data['ds_id'] = $this->input->post('ds_id');
					
					$sql_data['screen_name'] = $this->input->post('screen_name');
					$sql_data['su_user'] = $this->input->post('su_user');
					
					$sql_data['script_start'] = $this->input->post('script_start');
					$sql_data['script_stop'] = $this->input->post('script_stop');
					$sql_data['script_restart'] = $this->input->post('script_restart');
					$sql_data['script_status'] = $this->input->post('script_status');
					$sql_data['script_update'] = $this->input->post('script_update');
					$sql_data['script_get_console'] = $this->input->post('script_get_console');
					
					/* Чтобы ид модификации был правильный и подходил для выбранной игры */
					$where = array('id' => $sql_data['game_type'], 'game_code' => $this->servers->server_data['game']);
					if(!$this->game_types->get_gametypes_list($where, 1)) {
						$this->show_message(lang('adm_servers_game_type_select_wrong'));
						return FALSE;
					}
					
					/* RCON */
					if($this->input->post('rcon') != '') {
						/* Собственно смена rcon пароля */
						$this->servers->change_rcon($this->input->post('rcon'));
						$sql_data['rcon'] = $this->encrypt->encode($this->input->post('rcon'));
					}
				
					if($this->servers->edit_game_server($id, $sql_data)){
						$local_tpl_data['message'] = lang('adm_servers_server_data_changed');
					}else{
						$local_tpl_data['message'] = lang('adm_servers_error_server_edit');
					}
							
					$local_tpl_data['link'] = site_url('adm_servers/view/game_servers');
					$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_servers');

					
					break;
				case 'games':
					/* Игры */
					
					$sql_data['name'] 			= $this->input->post('name');
					$sql_data['code'] 			= $this->input->post('code');
					$sql_data['start_code'] 	= $this->input->post('start_code');
					$sql_data['engine'] 		= $this->input->post('engine');
					$sql_data['engine_version'] = $this->input->post('engine_version');
					
					$sql_data['app_id'] 		= $this->input->post('app_id');
					$sql_data['app_set_config'] = $this->input->post('app_set_config');
					
					/* Убираем кавычки из app_set_config */
					$sql_data['app_set_config'] = str_replace('\'', '', $sql_data['app_set_config']);
					$sql_data['app_set_config'] = str_replace('"', '', $sql_data['app_set_config']);
					$sql_data['app_set_config'] = str_replace('	', '', $sql_data['app_set_config']);
				
					if($this->games->edit_game($id, $sql_data)){
						$local_tpl_data['message'] = lang('adm_servers_game_data_changed');
					}else{
						$local_tpl_data['message'] = lang('adm_servers_error_game_edit');
					}
							
					$local_tpl_data['link'] 			= site_url('adm_servers/view/games');
					$local_tpl_data['back_link_txt'] 	= lang('adm_servers_back_to_games');

					break;
				
				case 'game_types':
					/* Типы игр */
					
					$sql_data['name'] 				= $this->input->post('name');
					$sql_data['game_code'] 			= $this->input->post('game_code');
					
					$sql_data['execfile_linux'] 	= $this->input->post('execfile_linux');
					$sql_data['execfile_windows'] 	= $this->input->post('execfile_windows');
					
					$sql_data['script_start'] 		= $this->input->post('script_start');
					$sql_data['script_stop'] 		= $this->input->post('script_stop');
					$sql_data['script_restart'] 	= $this->input->post('script_restart');
					$sql_data['script_status'] 		= $this->input->post('script_status');
					$sql_data['script_update'] 		= $this->input->post('script_update');
					$sql_data['script_get_console'] = $this->input->post('script_get_console');
					
					/*
					 * ----------------------------
					 * 	Перебор конф. файлов
					 * ----------------------------
					*/
					$cfg_list['desc'] 			= $this->input->post('cfg_desc');
					$cfg_list['file'] 			= $this->input->post('cfg_file');
					$cfg_list['delete'] 		= $this->input->post('cfg_delete');
					
					if(!empty($cfg_list['file'])) {
						$i = -1;
						foreach($cfg_list['file'] as $file) {
							$i ++;
							
							/* Пустые значения выкидываем */
							if($file == '') {
								continue;
							}
							
							/* Пустые значения выкидываем */
							if($cfg_list['desc'][$i] == '') {
								continue;
							}
							
							/* Значение должно быть удалено */
							if(isset($cfg_list['delete'][$i])) {
								continue;
							}
							
							$config_files[$i]['desc'] = $cfg_list['desc'][$i];
							$config_files[$i]['file'] = str_replace('..' , '', $file); // Двойные точки заменяем для безопасности (чтобы не перебраться в директорию выше)
						}
						
						if(isset($config_files)) {
							$sql_data['config_files'] = json_encode($config_files);
						}
					}
					
					/*
					 * ----------------------------
					 * 	Перебор контент директорий
					 * ----------------------------
					*/
					$cdir_list['desc'] 			= $this->input->post('cdir_desc');
					$cdir_list['path'] 			= $this->input->post('cdir_path');
					$cdir_list['allowed_types'] = $this->input->post('cdir_allowed_types');
					$cdir_list['delete'] 		= $this->input->post('cdir_delete');
					
					if(!empty($cdir_list['path'])) {
						$i = -1;
						foreach($cdir_list['path'] as $path) {
							$i ++;
							
							/* Пустые значения выкидываем */
							if($path == '') {
								continue;
							}
							
							/* Пустые значения выкидываем */
							if($cdir_list['desc'][$i] == '') {
								continue;
							}
							
							/* Пустые значения выкидываем */
							if($cdir_list['allowed_types'][$i] == '') {
								continue;
							}
							
							/* Значение должно быть удалено */
							if(isset($cdir_list['delete'][$i])) {
								continue;
							}
							
							$content_dirs[$i]['desc'] 			= $cdir_list['desc'][$i];
							$content_dirs[$i]['path'] 			= str_replace('..' , '', $path); // Двойные точки заменяем для безопасности (чтобы не перебраться в директорию выше)
							$content_dirs[$i]['allowed_types'] 	= $cdir_list['allowed_types'][$i];
						}
						
						if(isset($content_dirs)) {
							$sql_data['content_dirs'] = json_encode($content_dirs);
						}
					}

					/*
					 * ----------------------------
					 * 	Перебор лог директорий
					 * ----------------------------
					*/
					$ldir_list['desc'] 			= $this->input->post('ldir_desc');
					$ldir_list['path'] 			= $this->input->post('ldir_path');
					$ldir_list['allowed_types'] = $this->input->post('ldir_allowed_types');
					$ldir_list['delete'] 		= $this->input->post('ldir_delete');
					
					if(!empty($ldir_list['path'])) {
						$i = -1;
						foreach($ldir_list['path'] as $path) {
							$i ++;
							
							/* Пустые значения выкидываем */
							if($path == '') {
								continue;
							}
							
							/* Пустые значения выкидываем */
							if($ldir_list['desc'][$i] == '') {
								continue;
							}
							
							/* Пустые значения выкидываем */
							if($ldir_list['allowed_types'][$i] == '') {
								continue;
							}
							
							/* Значение должно быть удалено */
							if(isset($ldir_list['delete'][$i])) {
								continue;
							}
							
							$log_dirs[$i]['desc'] 			= $ldir_list['desc'][$i];
							$log_dirs[$i]['path'] 			= str_replace('..' , '', $path); // Двойные точки заменяем для безопасности (чтобы не перебраться в директорию выше)
							$log_dirs[$i]['allowed_types'] 	= $ldir_list['allowed_types'][$i];
						}
						
						if(isset($log_dirs)) {
							$sql_data['log_dirs'] = json_encode($log_dirs);
						}
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
							$sql_data['fast_rcon'] = json_encode($fast_rcon);
						}
					}
					
					/*
					 * ----------------------------
					 * 	Перебор алиасов
					 * ----------------------------
					*/
					$aliases_list['alias'] 		= $this->input->post('alias_name');
					$aliases_list['desc'] 		= $this->input->post('alias_desc');
					$aliases_list['only_admins'] = $this->input->post('alias_only_admins');
					$aliases_list['delete'] 	= $this->input->post('alias_delete');
					
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
								$aliases_list['only_admins'][$i] = FALSE;
							}
							
							/* Значение должно быть удалено */
							if(isset($aliases_list['delete'][$i])) {
								continue;
							}
							
							$aliases[$i]['alias'] 			= $alias;
							$aliases[$i]['desc'] 			= $aliases_list['desc'][$i];
							$aliases[$i]['only_admins'] 	= (bool)$aliases_list['only_admins'][$i];
						}
						
						if(isset($aliases)) {
							$sql_data['aliases'] = json_encode($aliases);
						}
					}

					if($this->game_types->edit_game_type($id, $sql_data)){
						$local_tpl_data['message'] = lang('adm_servers_game_type_data_changed');
					}else{
						$local_tpl_data['message'] = lang('adm_servers_error_game_type_edit');
					}

					$local_tpl_data['link'] = site_url('adm_servers/view/game_types/' . $id);
					$local_tpl_data['back_link_txt'] = lang('adm_servers_back_to_game_types');
					break;
			}
			
			$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
		
	}

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

		$local_tpl_data = array();
		$this->dedicated_servers->get_ds_list();
		
		// Получаем данные игр для шаблона
		$local_tpl_data['games_list'] = $this->games->tpl_data_games();
		
		if(empty($this->games->games_list)) {
			$this->show_message(lang('adm_servers_empty_games_list', base_url() . 'adm_servers/add/games'));
			return FALSE;
		}
		
		$this->tpl_data['title'] 	= lang('adm_servers_title_install_game_server');
		$this->tpl_data['heading'] 	= lang('adm_servers_heading_install_game_server');
		
		$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|min_length[3]|xss_clean');
		$this->form_validation->set_rules('server_ip', lang('ip'), 'trim|max_length[64]|min_length[3]|xss_clean');
		$this->form_validation->set_rules('server_port', lang('port'), 'trim|required|max_length[64]|min_length[3]|xss_clean');
		//$this->form_validation->set_rules('rcon', 'RCON пароль', 'trim|max_length[64]|min_length[3]|xss_clean');
		$this->form_validation->set_rules('code', 'игра', 'trim|required|max_length[64]|min_length[3]|xss_clean');
		$this->form_validation->set_rules('game_type', 'модификация', 'trim|required|integer|xss_clean');
		$this->form_validation->set_rules('ds_id', 'выделенный сервер', 'trim|integer|max_length[16]|xss_clean');
		$this->form_validation->set_rules('dir', 'директория', 'trim|required|max_length[64]|min_length[3]|xss_clean');
		//$this->form_validation->set_rules('screen_name', 'имя screen', 'trim|required|max_length[64]|min_length[3]|xss_clean');

		if ($this->form_validation->run() == FALSE) {

			// Получаем данные DS для шаблона
			$local_tpl_data['ds_list'] = $this->dedicated_servers->tpl_data_ds();
			
			$this->tpl_data['content'] = $this->parser->parse('adm_servers/install_game_server.html', $local_tpl_data, TRUE);
		} else {
			
			$new_gs['name'] 		= $this->input->post('name');
			$new_gs['server_ip'] 	= $this->input->post('server_ip');
			$new_gs['server_port'] 	= $this->input->post('server_port');
			//$new_gs['rcon'] 		= $this->input->post('rcon'); // Ркон задается случайной строкой после установки
			$new_gs['game'] 		= $this->input->post('code');
			$new_gs['game_type'] 	= $this->input->post('game_type');
			$new_gs['ds_id'] 		= $this->input->post('ds_id');
			$new_gs['dir'] 			= $this->input->post('dir');
			$new_gs['enabled']		= '1';
			$new_gs['installed']	= '0';
			
			if(!$new_gs['server_ip'] && $new_gs['ds_id']) {
				$i = 0;
				foreach($this->dedicated_servers->ds_list as $array) {
					if($new_gs['ds_id'] == $array['id']) {
						$new_gs['server_ip'] = $array['ip'];
					}
				}
			} else {
				$new_gs['server_ip'] = '127.0.0.1';
			}
			
			if(!$new_gs['server_ip'] && $new_gs['ds_id'] !== '0') {
				$this->show_message(lang('adm_servers_selected_ds_unavailable'));
				return FALSE;
			}
			
			$game_data = $this->games->get_games_list(array('code' => $new_gs['game']), 1);
			
			$new_gs['maps_path'] = $game_data[0]['start_code'] . '/maps';
			$new_gs['screen_name'] = $new_gs['game'] . '_' . random_string('alnum', 6) . '_' . $new_gs['server_port'];
			
			/* Чтобы ид модификации был правильный и подходил для выбранной игры */
			$where = array('id' => $new_gs['game_type'], 'game_code' => $new_gs['game']);
			if(!$this->game_types->get_gametypes_list($where, 1)) {
				$this->show_message(lang('adm_servers_game_type_select_wrong'));
				return FALSE;
			}
			
			/* Закоментированный код для установки без помощи 
			 * cron модуля */
			 
			/*
			 * 
			if($new_gs['ds_id']) {
				$new_gs['os'] = $this->dedicated_servers->ds_list[$new_gs['ds_id']]['os'];
				
				// Данные для игрового сервера из машины
				$new_gs['os'] = $this->server_ds_data['os'];
				
				$new_gs['local_server'] = 0;
				
				$new_gs['control_protocol'] = $this->dedicated_servers->ds_list[$new_gs['ds_id']]['control_protocol'];

				$new_gs['ssh_host'] 	= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['ssh_host'];
				$new_gs['ssh_login'] 	= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['ssh_login'];
				$new_gs['ssh_password'] = $this->dedicated_servers->ds_list[$new_gs['ds_id']]['ssh_password'];
				$new_gs['ssh_passwd'] 	= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['ssh_password'];
				$new_gs['ssh_path'] 	= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['ssh_path'];
				
				$new_gs['telnet_host'] 		= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['telnet_host'];
				$new_gs['telnet_login'] 	= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['telnet_login'];
				$new_gs['telnet_password'] 	= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['telnet_password'];
				$new_gs['telnet_path'] 		= $this->dedicated_servers->ds_list[$new_gs['ds_id']]['telnet_path'];
				
			} else {
				// Устанавливаем на локальном сервере
				$new_gs['os'] 			= $this->config->config['local_os'];
				$new_gs['script_path'] 	= $this->config->config['local_script_path'];
				$new_gs['local_path'] 	= $this->config->config['local_script_path'];
				$new_gs['local_server'] = 1;
			}
			*/
			
			if ($this->games->get_games_list(array('code'=> $new_gs['game']), 1)) {

				if($this->games->games_list[0]['app_id']) {
					/* Закоментированный код для установки без помощи 
					* cron модуля */
					
					//steamcmd +login anonymous +force_install_dir ../czero +app_set_config 90 mod czero +app_update 90 validate +quit
					//$cmd['app'] = '';
					
					//($this->games->games_list[0]['app_set_config']) {
					//	$cmd['app'] .= '+app_set_config "' . $this->games->games_list[0]['app_set_config'] . '" ';
					//}
					
					//$cmd['app'] .= '+app_update ' . $this->games->games_list[0]['app_id'];
				} else {
					/*
					 * Для игры не задан или не существует парамера app_update для SteamCMD
					*/
					$this->show_message(lang('adm_servers_no_steamcmd_data'));
					return FALSE;
				}
				
				
			} else {
				// Игры не существует
				$this->show_message(lang('adm_servers_base_not_contains_game'));
				return FALSE;
			}
			
			//~ /* 
			 //~ * Заданы ли данные SSH у DS сервера 
			 //~ * 
			 //~ * Если сервер является удаленным, используется telnet
			 //~ * и заданы хост, логин и пароль то все впорядке,
			 //~ * иначе отправляем пользователю сообщение
			 //~ * 
			//~ */
			//~ if($this->servers->server_data['ds_id'] 
			//~ && $this->servers->server_data['control_protocol'] == 'ssh'
			//~ && (!$this->servers->server_data['ssh_host']
				//~ OR !$this->servers->server_data['ssh_login']
				//~ OR !$this->servers->server_data['ssh_password']
				//~ )
			//~ ){
				//~ $this->show_message(lang('server_command_ssh_not_set'));
				//~ return FALSE;	
			//~ }
			//~ 
			//~ /*
			 //~ * Есть ли модуль SSH
			 //~ */
			//~ if($this->servers->server_data['ds_id'] 
			//~ && $this->servers->server_data['control_protocol'] == 'ssh'
			//~ && (!in_array('ssh2', $ext_list))
			//~ ){
				//~ $this->show_message(lang('server_command_ssh_not_module'));
				//~ return FALSE;	
			//~ }
			//~ 
			//~ 
			//~ /* 
			 //~ * Заданы ли данные TELNET у DS сервера 
			 //~ * 
			 //~ * Если сервер является удаленным, используется telnet
			 //~ * и заданы хост, логин и пароль то все впорядке,
			 //~ * иначе отправляем пользователю сообщение
			 //~ * 
			//~ */
			//~ 
			//~ if($this->servers->server_data['ds_id'] 
			//~ && $this->servers->server_data['control_protocol'] == 'telnet'
			//~ && (!$this->servers->server_data['telnet_host']
				//~ OR !$this->servers->server_data['telnet_login']
				//~ OR !$this->servers->server_data['telnet_password']
				//~ )
			//~ ){
				//~ $this->show_message(lang('server_command_telnet_not_set'));
				//~ return FALSE;	
			//~ }
			
			// Добавление сервера
			if($this->servers->add_game_server($new_gs)) {
				$this->show_message(lang('adm_servers_server_to_be_installed'), site_url('adm_servers/edit/game_servers/' . mysql_insert_id()), lang('adm_servers_go_to_settings'));
				return TRUE;
			} else {
				$this->show_message(lang('adm_servers_add_game_failed'));
				return FALSE;
			}
			
			/* Закоментированный код для установки без помощи 
			 * cron модуля */
			/*
			$cmd['login'] = '+login anonymous';
			$cmd['install_dir'] = '+force_install_dir ' . $new_gs['dir'];
			
			switch($new_gs['os']){
			case 'Windows':
				$command = '&& ./steamcmd.exe ' . $cmd['login'] . ' ' . $cmd['install_dir'] . ' ' . $cmd['app'] . ' validate +quit';
				$result = $this->servers->command_windows($command, $new_gs);
				break;
			default:
				$command = '&& ./steamcmd.sh ' . $cmd['login'] . ' ' . $cmd['install_dir'] . ' ' . $cmd['app'] . ' validate +quit';
				$result = $this->servers->command($command, $new_gs);
				break;
			}
			* 
			*/
			
			
			//echo $result . "\n";
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	/**
	 * Переустановка игрового сервера
	 * 
	 * 
	*/
	function reinstall_game_server($id, $confirm = FALSE)
	{
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');
		
		$local_tpl_data['content'] = '';
		
		if(!$this->servers->get_server_data($id)){
			$this->show_message(lang('adm_servers_server_not_found'), site_url('adm_servers/view/game_servers'));
			return FALSE;
		}
		
		if ($confirm == $this->security->get_csrf_hash()) {
			
			/* Удаление директории на выделенном сервере */
			//~ if (isset($this->servers->server_data['dir'])) {
				//~ switch($this->servers->server_data['os']) {
				//~ case 'Windows':
					//~ $command = 'rmdir /S ' . $this->servers->server_data['dir'];
					//~ $result = $this->servers->command_windows($command, $this->servers->server_data);
					//~ break;
				//~ default:
					//~ // Linux
					//~ $command = 'rm -rf ' . $this->servers->server_data['dir'];
					//~ $result = $this->servers->command($command, $this->servers->server_data);
					//~ break;
				//~ }
			//~ }
			
			$sql_data['installed'] = 0;
			
			if ($this->servers->edit_game_server($id, $sql_data)) {
				
				//~ /* Удаление директории на выделенном сервере */
				//~ if(isset($this->servers->server_data['dir'])) {
					//~ switch($this->servers->server_data['os']) {
					//~ case 'Windows':
						//~ $command = 'rmdir /S ' . $this->servers->server_data['dir'];
						//~ $result = $this->servers->command_windows($command, $this->servers->server_data);
						//~ break;
					//~ default:
						//~ // Linux
						//~ $command = 'rm -rf ' . $this->servers->server_data['dir'];
						//~ $result = $this->servers->command($command, $this->servers->server_data);
						//~ break;
					//~ }
				//~ }
				
				$this->show_message(lang('adm_servers_server_will_be_reinstalled'), site_url('adm_servers/edit/game_servers/' . $id), lang('next'));
				return TRUE;
			} else {
				$this->show_message(lang('adm_servers_error_server_edit'), site_url('adm_servers/edit/game_servers/' . $id), lang('next'));
				return FALSE;
			}
			
		} else {

			$confirm_tpl['message'] = lang('adm_servers_reinstall_gs_confirm');
			$confirm_tpl['confirmed_url'] = site_url('adm_servers/reinstall_game_server/'. $id . '/' . $this->security->get_csrf_hash());

			$this->tpl_data['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, TRUE);
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	/**
	 * Создание дубликата игровой модификации
	 * 
	 * 
	*/
	function dublicate_game_type($id, $confirm = FALSE)
	{
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');
		
		$local_tpl_data['content'] = '';
		$local_tpl_data['gt_id'] = (int)$id;
		
		// Получаем данные игр для шаблона
		$local_tpl_data['games_list'] = $this->games->tpl_data_games();
					
		if(empty($this->games->games_list)) {
			$this->show_message(lang('adm_servers_empty_games_list', base_url() . 'adm_servers/add/games'));
			return FALSE;
		}
		
		/* Существует ли модификация */
		if(!$gt_list = $this->game_types->get_gametypes_list(array('id' => $id))){
			$this->show_message(lang('adm_servers_game_type_not_found'), site_url('adm_servers/view/game_types'));
			return FALSE;
		}
		
		$this->form_validation->set_rules('name', lang('name'), 'trim|required|max_length[64]|xss_clean');
		$this->form_validation->set_rules('code', lang('game'), 'trim|required|max_length[64]|xss_clean');
		
		if ($this->form_validation->run() == FALSE) {

			/* Если были ошибки проверки формы, то отображаем ошибки, если нет, то отображаем форму */
			if ($validation_errors = validation_errors()) {
				$this->show_message();
				return FALSE;
			} else {
				$this->tpl_data['content'] = $this->parser->parse('adm_servers/dublicate_game_type.html', $local_tpl_data, TRUE);
			}
			
		} else {
			$sql_data = $gt_list[0];
			unset($sql_data['id']);
			$sql_data['game_code'] = $this->input->post('code');
			$sql_data['name'] = $this->input->post('name');

			if($this->game_types->add_game_type($sql_data)) {
				$local_tpl_data['message'] = lang('adm_servers_add_game_type_successful');
			} else {
				$local_tpl_data['message'] = lang('adm_servers_add_game_type_failed');
			}
			
			$this->show_message($local_tpl_data['message'], site_url('adm_servers/edit/game_types/' . mysql_insert_id()), lang('next')); 
			return TRUE;
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}

}
/* End of file adm_servers.php */
/* Location: ./application/controllers/adm_servers.php */
