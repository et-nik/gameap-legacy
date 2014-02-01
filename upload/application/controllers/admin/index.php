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
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, true);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
			
		
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
		if($this->servers->get_server_list($this->users->auth_id)) {
				
			$this->load->helper('games');
			$this->load->model('servers/games');
				
			$num = 0;
			
			$this->db->select('code, start_code, name, engine, engine_version');
			$this->games->get_games_list();
			
			$local_tpl_data['games_list'] = $this->games->tpl_data_games();
				
			foreach ($this->servers->servers_list as $this->server_data) {
					$server_commands = '';
					
					$num++;
					
					/* Получение id игры в массиве */
					$i = 0;
					$count = count($this->games->games_list);
					while($i < $count) {
						if ($this->server_data['game'] == $this->games->games_list[$i]['code']) {
							$game_arr_id = $i;
							break;
						}
						$i++;
					}
					
					$template = (!isset($this->config->config['template'])) ? 'default' : $this->config->config['template'];
					$style = (!isset($this->config->config['style'])) ? 'default' : $this->config->config['style'];
					
					/* Работает ли сервер 
					 * Начиная с версии 0.8.6 статуст серверов подгружается через AJAX
					*/
					//~ if($this->servers->server_status($this->server_data['server_ip'], $this->server_data['query_port'], $this->games->games_list[$game_arr_id]['engine'], $this->games->games_list[$game_arr_id]['engine_version'])) {
						//~ $server_status['string'] = '<img src="' . base_url() . '/themes/system/images/bullet_green.png" alt="' . lang('enabled') . '"/>';
						//~ $this->server_data['server_status'] = 1;
					//~ } else {
						//~ $server_status['string'] = '<img src="' . base_url() . '/themes/system/images/bullet_red.png" alt="' . lang('disabled') . '"/>';
						//~ $this->server_data['server_status'] = 0;
					//~ }
						
					/* Проверка привилегий на сервер */
					$this->users->get_server_privileges($this->server_data['id']);
					
					/* Строка с привилегиями на сервер для вставки в содержимое javascript 
					 * Т.к. статус сервера подгружается при помощи AJAX, кнопки также подгружаются 
					 * при помощи AJAX в зависимости от статуса, но на некоторые действия
					 * у пользователя может не быть прав (например, перезапуск).
					 * Следующие данные вставляют данные в массив privileges для javascript, чтобы можно было отображать
					 * только доступные пользователю кнопки.
					 * 
					 * privileges['start_3'], где start - привилегия, 3 - id сервера
					 * 
					 * В шаблон следует вставлять тег {server_js_privileges}, он должен располагаться между {servers_list} и {/servers_list}
					 * 
					 * В исходном коде страницы будет примерно следующее:
					 * privileges['start_2'] = 1;privileges['stop_2'] = 0;privileges['restart_2'] = 1;
					 * 
					 * После этого в javascript можно сделать проверки, например
					 * 
					   if (privileges['stop_' + server_id] == 1) {
							$("#stop_privilege").append("Остановка сервера разрешена");
						}
					 * 
					*/
					$js_privileges	= '';
					$js_privileges 	.= 'privileges[\'start_' . $this->server_data['id'] . '\'] = ' . (int)(bool)($this->users->auth_privileges['srv_start'] && $this->users->auth_servers_privileges['SERVER_START']) . ';';
					$js_privileges 	.= 'privileges[\'stop_' . $this->server_data['id'] . '\'] = ' . (int)(bool)($this->users->auth_privileges['srv_stop'] && $this->users->auth_servers_privileges['SERVER_STOP']) . ';';
					$js_privileges 	.= 'privileges[\'restart_' . $this->server_data['id'] . '\'] = ' . (int)(bool)($this->users->auth_privileges['srv_restart'] && $this->users->auth_servers_privileges['SERVER_RESTART']) . ';';

					$this->server_data['expires'] = (int)$this->server_data['expires'];

					$gs_data = $data_slist['servers_list'][] = array('server_id' => $this->server_data['id'],
																'server_name' => $this->server_data['name'],
																'server_game' => $this->server_data['game'],
																'server_ip' => $this->server_data['server_ip'] . ':' . $this->server_data['server_port'],
																'server_expires' => date ('d.m.Y',$this->server_data['expires']),
																'server_js_privileges'	=> $js_privileges,
																'commands' => $server_commands);
						
					// Вставляем данные сервера в массив								
					$local_tpl_data['games_list'] = game_server_insert($gs_data, $local_tpl_data['games_list']);
						
			}
				
			$local_tpl_data['games_list'] = clean_games_list($local_tpl_data['games_list']);
				
			if($num){
				$this->tpl_data['content'] .= $this->parser->parse('servers/servers_list.html', $local_tpl_data, true);
			}
		}
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
}

/* End of file index.php */
/* Location: ./application/controllers/admin/index.php */
