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
 
// ------------------------------------------------------------------------

/**
 * Логи
 *
 * Просмотр логов игрового сервера
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.3.3 [13.04.2013]
 */
class Servers_log extends CI_Controller {
	
	var $tpl_data = array();
	//var $allow_param = array('mods', 'server');
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('server_files');
        $this->lang->load('servers_log');
        
        if ($this->users->check_user()) {
			//Base Template
			$this->tpl_data['title'] 		= lang('servers_log_title_index');
			$this->tpl_data['heading'] 		= lang('servers_log_header_index');
			$this->tpl_data['content'] 		= '';
			$this->tpl_data['menu'] 		= $this->parser->parse('menu.html', $this->tpl_data, true);
			$this->tpl_data['profile'] 		= $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
        
        }else{
            redirect('auth');
        }
    }
    
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
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl_data);
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
		
		$tpl_data['filter_name']			= isset($filter['name']) ? $filter['name'] : '';
		$tpl_data['filter_ip']				= isset($filter['ip']) ? $filter['ip'] : '';
		
		$tpl_data['filter_ip_dropdown']		= form_multiselect('filter_ip[]', $ip_array, $tpl_data['filter_ip']);
		
		$default = isset($filter['game']) ? $filter['game'] : null;
		$tpl_data['filter_games_dropdown'] 	= form_multiselect('filter_game[]', $games_option, $default);
		
		return $tpl_data;
	}

    /**
     * Главная страница
     * 
    */
    public function index()
    {
		$this->load->model('servers');
		$this->load->model('servers/games');
		$this->load->helper('games');
		
		$filter = $this->users->get_filter('servers_list');
		$local_tpl = $this->_get_tpl_filter($filter);
		
		$this->servers->set_filter($filter);
		$this->servers->get_servers_list($this->users->auth_id);
		
		$local_tpl['url'] 			= site_url('/admin/servers_log/list_logs');
		$local_tpl['games_list'] = servers_list_to_games_list($this->servers->servers_list);
			
		$this->tpl_data['content'] .= $this->parser->parse('servers/select_server.html', $local_tpl, true);
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Просмотр файлов логов
     * 
    */
	public function list_logs($server_id = false, $id_dir = false)
	{
		$this->load->model('servers');
		$this->load->helper('ds');
		
		if(!$server_id){
			// Отображение списка серверов, доступных пользователю
			
			$this->servers->get_server_list($this->users->auth_id);
			$local_tpl['servers_list'] = $this->servers->tpl_data();
			$this->tpl_data['content'] .= $this->parser->parse('servers/log_select_server.html', $local_tpl, true);
			
			$this->parser->parse('main.html', $this->tpl_data);
			return false;
		}
		
		$this->servers->get_server_data($server_id);
				
		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		if(!$this->users->auth_servers_privileges['LOGS_VIEW']){
			$this->_show_message(lang('servers_log_no_privileges'), site_url('admin/servers_log'));
			return false;
		}
		
		/* Если не задан id директории то отображаем список с директориями 
		 * $id_dir может быть равно 0, это id лог директории */
		if($id_dir === false){
			
			$ldir_list = json_decode($this->servers->server_data['log_dirs'], true);
			
			if($ldir_list) {
				$i = -1;
				$local_tpl['ldir_list'] = $ldir_list;
				foreach($ldir_list as $array) {
					$i ++;
					$local_tpl['ldir_list'][$i]['id_dir'] = $i;
				}
			} else {
				$local_tpl['ldir_list'] = array();
			}
			
			$local_tpl['server_id'] = $server_id;
			
			$this->tpl_data['content'] .= $this->parser->parse('servers/select_log.html', $local_tpl, true);
			$this->parser->parse('main.html', $this->tpl_data);
			return false;
		}
		
		$this->load->model('servers/logs');

		/* Параметры фильтрации логов */
		if($this->input->post('submit_filter')){
			$file_name = $this->input->post('file_name');
			$log_date = (int)$this->input->post('date');
			$log_limit = (int)$this->input->post('limit');
			$log_sort = $this->input->post('sort');
		}else{
			$file_name = '';
			$log_date = 604800;
			$log_limit = 100;
			$log_sort = 'DESC';
		}
		
		/* Получаем данные из json */
		$ldir_list = json_decode($this->servers->server_data['log_dirs'], true);
		
		if(!array_key_exists($id_dir, $ldir_list)){
			$this->_show_message(lang('servers_log_dir_unavailable'), site_url('admin/servers_log'));
			return false;
		}
		
		$local_tpl['id_dir'] = $id_dir;		
		$dir = $ldir_list[$id_dir]['path'];
		
		// Получаем массив с расширением файлов
		$allowed_types = explode('|', $ldir_list[$id_dir]['allowed_types']);
		$count_allowed_types = count($allowed_types);
		
		try {
			$this->logs->list_server_log($file_name, $allowed_types, $dir, $log_limit, $log_date);
		} catch (Exception $e) {
			$this->_show_message($e->getMessage(), site_url('admin/servers_log'));
			
			// Сохраняем логи ошибок
			$log_data['type'] = 'server_log';
			$log_data['command'] = 'list_log';
			$log_data['user_name'] = $this->users->auth_login;
			$log_data['server_id'] = $this->servers->server_data['id'];
			$log_data['msg'] = $e->getMessage();
			$log_data['log_data'] = "Directory: {$dir}";
			$this->panel_log->save_log($log_data);
			
			return false;
		}
		
		$local_tpl['log_list'] 	= $this->logs->filter_logs($log_sort, $log_limit, $log_date);
		$local_tpl['server_id'] 	= $server_id;
		$local_tpl['param'] 		= $id_dir;
			
		$local_tpl['file_name'] = $file_name;
		$local_tpl['log_limit'] = $log_limit;
				
		$this->tpl_data['content'] .= $this->parser->parse('servers/log_list.html', $local_tpl, true);
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Просмотр файлов логов
     * 
    */
	public function view($server_id = false, $id_dir = false, $file_log = false)
    {
		$this->load->helper('ds');
		$this->load->model('servers');
		
		if(!$server_id){
			$this->servers->get_server_list($this->users->auth_id);
			$local_tpl['servers_list'] = $this->servers->tpl_data();
			$local_tpl['param'] = $param;
			
			$this->tpl_data['content'] .= $this->parser->parse('servers/log_select_server.html', $local_tpl, true);
			
			$this->parser->parse('main.html', $this->tpl_data);
			return false;
		}

		$this->servers->get_server_data($server_id);

		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		if(!$this->users->auth_servers_privileges['LOGS_VIEW']){
			$this->_show_message(lang('servers_log_no_privileges'), site_url('admin/servers_log'));
			return false;
		}
		
		$this->load->model('servers/logs');
		
		/* Получаем данные из json */
		$ldir_list = json_decode($this->servers->server_data['log_dirs'], true);
		
		if (!array_key_exists($id_dir, $ldir_list)) {
			$this->_show_message(lang('servers_log_dir_unavailable'), site_url('admin/servers_log'));
			return false;
		}
		
		$local_tpl['id_dir'] = $id_dir;		
		$dir = $ldir_list[$id_dir]['path'];
		$file_ext = $ldir_list[$id_dir]['allowed_types'];
		
		/* Разрешено ли расширение файла */
		$allowed_types = explode('|', $file_ext);
		$file_ext = end(explode(".", $file_log));
		if(!in_array($file_ext, $allowed_types)){
			$this->_show_message(lang('servers_log_file_type_unavailable'), site_url('admin/servers_log'));
			return false;
		}
		
		// Получение директории
		$dir = get_ds_file_path($this->servers->server_data) . $dir;

		try {
			$log_content = read_ds_file($dir . '/' . $file_log, $this->servers->server_data);
		} catch (Exception $e) {
			$this->_show_message($e->getMessage());
			
			/* Сохраняем логи */
			$log_data['type'] = 'server_files';
			$log_data['command'] = 'read_log';
			$log_data['user_name'] = $this->users->auth_login;
			$log_data['server_id'] = $this->servers->server_data['id'];
			$log_data['msg'] = $e->getMessage();
			$log_data['log_data'] = 'File: ' . $file_log . ' Dir: ' . $dir . "\n";
			$this->panel_log->save_log($log_data);
			
			return false;
		}
		
		$local_tpl['log_contents'] = $log_content;
		
		// Кодировка
		//$local_tpl['log_contents'] = iconv('windows-1251', 'UTF-8', $local_tpl['log_contents']);
		
		$local_tpl['server_id'] = $server_id;
		//$local_tpl['param'] = $param;
				
		$this->tpl_data['content'] .= $this->parser->parse('servers/view_log.html', $local_tpl, true);
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	
	// ----------------------------------------------------------------

    /**
     * Фильтр логов (просмотр отдельных логов, чата, подключений и пр.)
     * 
    */
	public function filter()
    {
		$this->tpl_data['content'] .= $this->parser->parse('servers/servers_log.html', $this->tpl_data, true);
		$this->parser->parse('main.html', $this->tpl_data);
	}
}
