<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/

use \Myth\Controllers\BaseController;

class Log_view extends BaseController {

	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('log_view');
        $this->load->library('pagination');
        
        if ($this->users->check_user()) {
			
			$this->load->helper('date');
			
			//Base Template
			$this->tpl['title'] = lang('log_view_title_index');
			$this->tpl['heading'] = lang('log_view_heading_index');
			$this->tpl['content'] = '';
			$this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
			$this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
			
        }else{
            redirect('auth');
        }
    }
    
    // -----------------------------------------------------------------
    
    // Отображение информационного сообщения
	function _show_message($message = false, $link = false, $link_text = false)
	{
		
		if (!$message) {
			$message = lang('error');
		}
		
		if (!$link) {
			$link = site_url('admin');
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
	
	// -----------------------------------------------------------------
	
	/**
	 * Переопределение метода для page
	*/
	public function _remap($method, $params = array())
	{
		if ($method == 'page' or $method == 'index') {
			return call_user_func_array(array($this, 'index'), $params);
		}
		
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
		
		show_404();
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Выводит список логов
	*/
	function _list_log($limit = 100, $offset = false)
	{
		$panel_logs = $this->panel_log->get_log(false, $limit, $offset); // Логи сервера в админпанели
		
		if (is_array($panel_logs)) {
			$i = 0;
			foreach($panel_logs as $log) {
				$logs[$i]['log_id'] = 			$log['id'];
				$logs[$i]['log_date'] = 		unix_to_human($log['date'], true, 'eu');
				$logs[$i]['log_server_id'] = 	$log['server_id'];
				$logs[$i]['log_user_name'] = 	$log['user_name'];
				$logs[$i]['log_command'] = 		$log['command'];
				$logs[$i]['log_type'] = 			$log['type'];
				
				$i ++;
			}
			return $logs;
		}
		
		return array();
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение данных фильтра для вставки в шаблон
	 */
	private function _get_tpl_filter($filter = false)
	{
		if (!$filter) {
			$filter = $this->users->get_filter('panel_log');
		}
		
		$actions = array(
			'0' => '---',
			'cron' => 'Cron',
			'auth' => 'Auth',
			'server_command' => 'Server command',
		);

		foreach($actions as $key => $value) {
			$action_options[ $key ] = $value;
		}
		
		$tpl['filter_command']			= isset($filter['command']) ? $filter['command'] : '';
		$tpl['filter_user']			= isset($filter['user_name']) ? $filter['user_name'] : '';
		$tpl['filter_contents']		= isset($filter['contents']) ? $filter['contents'] : '';
		
		$default = isset($filter['type']) ? $filter['type'] : null;
		$tpl['filter_action_dropdown'] 	= form_dropdown('filter_action', $action_options, $default);
		
		return $tpl;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Отображение списка логов
	*/
	function index($offset = 0) 
	{
		/* Доступ только для админа */
		if (false == $this->users->auth_data['is_admin']) {
			show_404();
		}
		
		$filter = $this->users->get_filter('panel_log');
		$this->panel_log->set_filter($filter);
		
		$local_tpl = $this->_get_tpl_filter($filter);
		
		/* Постраничная навигация */
		$config['base_url'] = site_url('log_view/page');
		$config['total_rows'] = $this->panel_log->get_count_all_log();
		$config['per_page'] = 100;
		$config['full_tag_open'] = '<p id="pagination">';
		$config['full_tag_close'] = '</p>';
		
		$this->pagination->initialize($config); 
		$local_tpl['pagination'] = $this->pagination->create_links();
		
		/* Список логов */
		$local_tpl['log_list'] = $this->_list_log(100, $offset);

		$this->tpl['content'] .= $this->parser->parse('log_list.html', $local_tpl, true);
		
		$this->parser->parse('main.html', $this->tpl);
	}
    
    // -----------------------------------------------------------------
    
    
    function view($id = false)
    {
		$this->load->model('servers');
		
		/* Проверочки */
		if(!$id) {
			$this->_show_message(lang('log_view_id_param_not_specified'));
			return false;
		}
				
		// Получаем содержимое лога
		$where = array('id' => $id);
		$log_list = $this->panel_log->get_log($where, 1);
		
		if (empty($log_list)) {
			$this->_show_message(lang('log_view_record_not_found'));
			return false;
		}

		/* Если пользователь не админ, то делаем проверочки на права доступа к логам */
		if (!$this->users->auth_data['is_admin']) {
			
			/* Проверки для простых пользователей, которым
			 * есть доступ только для логов, относящийс к серверам
			*/
			
			/* Лог должен быть найден и относиться к серверу */
			if(!$log_list OR !$log_list[0]['server_id']) {
				$this->_show_message(lang('log_view_record_not_found'));
				return false;
			}

			/* Если сервер, указанный в логе не найден */
			//~ if(!$this->servers->server_data) {
				//~ $this->_show_message(lang('log_view_record_not_found'));
				//~ return false;
			//~ }
			
			/* Получаем права на сервер */
			$this->users->get_server_privileges($log_list[0]['server_id']);
			
			/* У пользователя на этот сервер должны быть права */
			if(!$this->users->auth_servers_privileges['VIEW']) {
				$this->_show_message(lang('log_view_record_not_found'));
				return false;
			}
			
			/* Если пользователю нельзя видеть rcon пароль */
			if(!$this->users->auth_servers_privileges['CHANGE_RCON'] && strtolower($log_list[0]['command']) == 'rcon_password') {
				$this->_show_message(lang('log_view_log_access_denied'), '/admin/server_control/main/' . $log_list[0]['server_id']);
				return false;
			}
		}
		
		/* Все проверки пройдены! */
		
		/* Получаем данные сервера, если лог относится к серверу */
		if ($log_list[0]['server_id']) { $this->servers->get_server_data($log_list[0]['server_id']);}
		
		$local_tpl['log_id'] = $log_list[0]['id'];
		$local_tpl['log_date'] = unix_to_human($log_list[0]['date'], true, 'eu');
		$local_tpl['log_type'] = $log_list[0]['type'];
		$local_tpl['log_command'] = $log_list[0]['command'];
		$local_tpl['log_user'] = $log_list[0]['user_name'];
		$local_tpl['log_user_name'] = $log_list[0]['user_name'];
		$local_tpl['server_id'] = $log_list[0]['server_id'];
		if ($this->servers->server_data) { $local_tpl['server_name'] = $this->servers->server_data['name'];}
		$local_tpl['log_msg'] = $log_list[0]['msg'];
		$local_tpl['log_data'] = $log_list[0]['log_data'];
		
		$this->tpl['content'] .= $this->parser->parse('log_info.html', $local_tpl, true);
		
		$this->parser->parse('main.html', $this->tpl);
	}
	
}

/* End of file log_view.php */
/* Location: ./application/controllers/log_view.php */
