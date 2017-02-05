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

use \Myth\Controllers\BaseController;

class Index extends BaseController {

	//Template
	var $tpl = array();

	var $user_data = array();
	var $server_data = array();

	// Количество игроков на сервере
	var $players = 0;

	// -----------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('users');
		$this->lang->load('main');

		if($this->users->check_user()) {
			//Base Template
			$this->tpl['title'] 	= lang('ap_title');
			$this->tpl['heading'] 	= lang('ap_header');
			$this->tpl['content'] = '';
			$this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
			$this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);


		} else {
			redirect('auth');
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Получение данных фильтра для вставки в шаблон
	 */
	private function _get_tpl_filter($filter = false)
	{
		$this->load->model('servers');

		if (!$filter) {
			$filter = $this->users->get_filter('servers_list');
		}

		$this->servers->select_fields('game, server_ip');

		$games_array 	= array();
		$ip_array		= array();

		if ($servers_list = $this->servers->get_list()) {
			foreach($servers_list as $server) {
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

        $games_option = array();
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

	public function index()
	{
		$this->load->helper('form');

		/* Загрузка модели управления игровыми серверами*/
		$this->load->model('servers');

		$this->load->helper('games');
		$this->load->model('servers/games');

		$local_tpl = array();

		$this->games->get_active_games_list();
		$local_tpl['games_list'] = $this->games->tpl_data_games();

		$filter 		= $this->users->get_filter('servers_list');
		$local_tpl += $this->_get_tpl_filter($filter);

		$this->servers->set_filter($filter);

		/* Если количество серверов больше 0 */
		if ($this->servers->get_servers_list($this->users->auth_id)) {

			$num = 0;

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

					$gs_data =  array('server_id' => $this->server_data['id'],
																'server_name' => $this->server_data['name'],
																'server_game' => $this->server_data['game'],
																'server_ip' => $this->server_data['server_ip'] . ':' . $this->server_data['server_port'],
																'server_expires' => date ('d.m.Y',$this->server_data['expires']),
																'server_js_privileges'	=> $js_privileges,
																'commands' => $server_commands);

					// Вставляем данные сервера в массив
					$local_tpl['games_list'] = game_server_insert($gs_data, $local_tpl['games_list']);

			}

			$local_tpl['games_list'] = clean_games_list($local_tpl['games_list']);
		} else {
			$local_tpl['games_list'] = array();
		}

		$this->tpl['content'] = $this->parser->parse('servers/servers_list_main.html', $local_tpl, true);
		$this->parser->parse('main.html', $this->tpl);
	}
}

/* End of file index.php */
/* Location: ./application/controllers/admin/index.php */
