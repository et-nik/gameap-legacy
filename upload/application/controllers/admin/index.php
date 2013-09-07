<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

class Index extends CI_Controller {
	
	//Template
	var $tpl_data = array();
	
	var $user_data = array();
	var $server_data = array();
	
	// Количество игроков на сервере
	var $players = 0;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('users');
		$this->lang->load('main');

		if($this->users->check_user()) {
			//Base Template
			$this->tpl_data['title'] 	= lang('ap_title');
			$this->tpl_data['heading'] 	= lang('ap_header');
			$this->tpl_data['content'] = '';
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, TRUE);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), TRUE);
			
		
		} else {
			redirect('auth');
		}
	}
	
	//Главная
	public function index()
	{
		$this->tpl_data['content'] .= '<p><strong>' . lang('ap_wellcome') . '</strong></p>';

		/*
		 * Отправка команд, на которые есть права у пользователя
		*/
			
		/* Загрузка модели управления игровыми серверами*/
		$this->load->model('servers');

		/* Если количество серверов больше 0 */
		if($this->servers->get_server_list($this->users->user_id)) {
				
			$this->load->helper('games');
			$this->load->model('servers/games');
				
			$num = 0;
				
			$local_tpl_data['games_list'] = $this->games->tpl_data_games();
				
			foreach ($this->servers->servers_list as $this->server_data) {
					$server_commands = '';

					$num++;
					
					$template = (!isset($this->config->config['template'])) ? 'default' : $this->config->config['template'];
					$style = (!isset($this->config->config['style'])) ? 'default' : $this->config->config['style'];
						
					/* Работает ли сервер */
					if($this->servers->server_status($this->server_data['server_ip'], $this->server_data['server_port'])) {
						$server_status['string'] = '<img src="' . base_url() . '/themes/system/images/bullet_green.png" alt="' . lang('enabled') . '"/>';
						$this->server_data['server_status'] = 1;
					} else {
						$server_status['string'] = '<img src="' . base_url() . '/themes/system/images/bullet_red.png" alt="' . lang('disabled') . '"/>';
						$this->server_data['server_status'] = 0;
					}
						
					/* Проверка привилегий на сервер */
					$this->users->get_server_privileges($this->server_data['id']);
						
					/* 
					 * Кнопка запуск сервера 
					 * 
					 * Кнопка будет показана в случаях
					 * 	Если сервер остановлен и
					 *  Если пользователь имеет право на запуск серверов и
					 * 	Если у пользователя есть серверная привилегия на запуск
					 * 
					 * аналогично для случаев остановки и перезапуска серверов
					*/

					if($this->server_data['server_status'] == 0				// Сервер остановлен
						&& $this->users->user_privileges['srv_start']		// Право на запуск серверов
						&& $this->users->servers_privileges['SERVER_START']	// Право на запуск этого сервера
					) {
						$server_commands .= '<a class="small green awesome" href=' . site_url() . 'server_command/start/' . $this->server_data['id'] . '>' . lang('start') . '</a>&nbsp;';
					}
						
					/* Кнопка остановка сервера */
					if($this->server_data['server_status'] == 1				// Сервер запущен
						&& $this->users->user_privileges['srv_stop']		// Право на остановку серверов
						&& $this->users->servers_privileges['SERVER_STOP']	// Право на остановку этого сервера
					) {
						$server_commands .= '<a class="small red awesome" href=' . site_url() . 'server_command/stop/' . $this->server_data['id'] . '>' . lang('stop') . '</a>&nbsp;';
					}
					
					/* Кнопка перезапуска сервера */
					if($this->users->user_privileges['srv_restart']				// Право на перезапуск серверов
						&& $this->users->servers_privileges['SERVER_RESTART']	// Право на перезапуск этого сервера
					) {
						$server_commands .= '<a class="small yellow awesome" href=' . site_url() . 'server_command/restart/' . $this->server_data['id'] . '>' . lang('restart') . '</a>&nbsp;';
					}
					
					$server_commands .= '<a class="small awesome" href=' . site_url() . 'admin/server_control/main/' . $this->server_data['id'] . '>' . lang('other_commands') . ' &raquo;</a>&nbsp;';
						
					$this->server_data['expires'] = (int)$this->server_data['expires'];

					$gs_data = $data_slist['servers_list'][] = array('server_name' => $this->server_data['name'],
																'server_game' => $this->server_data['game'],
																'server_ip' => $this->server_data['server_ip'] . ':' . $this->server_data['server_port'],
																'server_expires' => date ('d.m.Y',$this->server_data['expires']),
																'server_status' => $server_status['string'], 
																'commands' => $server_commands);
						
					// Вставляем данные сервера в массив								
					$local_tpl_data['games_list'] = game_server_insert($gs_data, $local_tpl_data['games_list']);
						
			}
				
			$local_tpl_data['games_list'] = clean_games_list($local_tpl_data['games_list']);
				
			if($num){
				$this->tpl_data['content'] .= $this->parser->parse('servers/servers_list.html', $local_tpl_data, TRUE);
			}
		}
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
}

/* End of file index.php */
/* Location: ./application/controllers/admin/index.php */
