<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, true);
        $this->parser->parse('main.html', $this->tpl_data);
    }

    /**
     * Главная страница
     * 
    */
    public function index()
    {
		/* Загружаем модель */
		$this->load->model('servers');
		
		$this->servers->get_server_list($this->users->auth_id);
		
		$local_tpl_data['servers_list'] = $this->servers->tpl_data();
		$local_tpl_data['url'] = site_url('/admin/servers_log/list_logs');
			
		$this->tpl_data['content'] .= $this->parser->parse('servers/select_server.html', $local_tpl_data, true);
			
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
		
		$this->tpl_data['content'] .= $this->parser->parse('servers/servers_log.html', $this->tpl_data, true);
		
		if(!$server_id){
			// Отображение списка серверов, доступных пользователю
			
			$this->servers->get_server_list($this->users->auth_id);
			$local_tpl_data['servers_list'] = $this->servers->tpl_data();
			$this->tpl_data['content'] .= $this->parser->parse('servers/log_select_server.html', $local_tpl_data, true);
			
			$this->parser->parse('main.html', $this->tpl_data);
			return false;
		}
		
		$this->servers->get_server_data($server_id);
		
		/* Если сервер не локальный и не настроен FTP, то выдаем ошибку */
		if($this->servers->server_data['ds_id'] && !$this->servers->server_data['ftp_host']) {
			$this->_show_message(lang('server_files_ftp_not_set'), site_url('admin/servers_log'));
			return false;
		}
		
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
				$local_tpl_data['ldir_list'] = $ldir_list;
				foreach($ldir_list as $array) {
					$i ++;
					$local_tpl_data['ldir_list'][$i]['id_dir'] = $i;
				}
			} else {
				$local_tpl_data['ldir_list'] = array();
			}
			
			$local_tpl_data['server_id'] = $server_id;
			
			$this->tpl_data['content'] .= $this->parser->parse('servers/select_log.html', $local_tpl_data, true);
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
		
		$local_tpl_data['id_dir'] = $id_dir;		
		$dir = $ldir_list[$id_dir]['path'];
		
		// Получаем массив с расширением файлов
		$allowed_types = explode('|', $ldir_list[$id_dir]['allowed_types']);
		$count_allowed_types = count($allowed_types);
		
		$i = 0;
		while($i < $count_allowed_types){
			/* Прогоняем по каждому расширению */
			
			$file_ext = $allowed_types[$i];
			$this->logs->list_server_log($file_name, $file_ext, $dir, $log_limit, $log_date);
			
			/* Небыло ли ошибок */
			if($this->logs->errors) {
				// Ошибки были, выводим их
				$this->_show_message($this->logs->errors, site_url('admin/servers_log'));
				
				// Сохраняем логи ошибок
				
				$log_data['type'] = 'server_log';
				$log_data['command'] = 'list_log';
				$log_data['user_name'] = $this->users->user_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = $this->logs->errors;
				$log_data['log_data'] = $this->logs->errors;
				$this->panel_log->save_log($log_data);

				return false;
			}
			
			$i ++;
		}
		
		$local_tpl_data['log_list'] 	= $this->logs->filter_logs($log_sort, $log_limit, $log_date);
		$local_tpl_data['server_id'] 	= $server_id;
		$local_tpl_data['param'] 		= $id_dir;
			
		$local_tpl_data['file_name'] = $file_name;
		$local_tpl_data['log_limit'] = $log_limit;
				
		$this->tpl_data['content'] .= $this->parser->parse('servers/log_list.html', $local_tpl_data, true);
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Просмотр файлов логов
     * 
    */
	public function view($server_id = false, $id_dir = false, $file_log = false)
    {
		$this->tpl_data['content'] .= $this->parser->parse('servers/servers_log.html', $this->tpl_data, true);
		
		$this->load->model('servers');
		
		if(!$server_id){
			$this->servers->get_server_list($this->users->auth_id);
			$local_tpl_data['servers_list'] = $this->servers->tpl_data();
			$local_tpl_data['param'] = $param;
			
			$this->tpl_data['content'] .= $this->parser->parse('servers/log_select_server.html', $local_tpl_data, true);
			
			$this->parser->parse('main.html', $this->tpl_data);
			return false;
		}
		
		/*
		if(!in_array($param, $this->allow_param)){
			$this->tpl_data['content'] .= '<p>Неправильный параметр view</p>';
			$this->parser->parse('main.html', $this->tpl_data);
			return false;
		}
		*/
		
		$this->servers->get_server_data($server_id);
		
		/* Если сервер не локальный и не настроен FTP, то выдаем ошибку */
		if($this->servers->server_data['ds_id'] && !$this->servers->server_data['ftp_host']){
			$this->_show_message(lang('server_files_ftp_not_set'), site_url('admin/servers_log'));
			return false;
		}
		
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
		
		$local_tpl_data['id_dir'] = $id_dir;		
		$dir = $ldir_list[$id_dir]['path'];
		$file_ext = $ldir_list[$id_dir]['allowed_types'];
		
		/* Разрешено ли расширение файла */
		$allowed_types = explode('|', $file_ext);
		$file_ext = end(explode(".", $file_log));
		if(!in_array($file_ext, $allowed_types)){
			$this->_show_message(lang('servers_log_file_type_unavailable'), site_url('admin/servers_log'));
			return false;
		}
		
		
		$log_content = $this->logs->get_log($dir, $file_log);
		
		if(!$log_content) {
			$this->_show_message($this->servers->errors);
			
			/* Сохраняем логи */
			$log_data['type'] = 'server_files';
			$log_data['command'] = 'read_log';
			$log_data['user_name'] = $this->users->auth_login;
			$log_data['server_id'] = $this->servers->server_data['id'];
			$log_data['msg'] = 'Read file error';
			$log_data['log_data'] = 'File: ' . $file_log . ' Dir: ' . $dir . "\n";
			$this->panel_log->save_log($log_data);
			
			return false;
		}
		
		$local_tpl_data['log_contents'] = $log_content;
		
		// Кодировка
		//$local_tpl_data['log_contents'] = iconv('windows-1251', 'UTF-8', $local_tpl_data['log_contents']);
		
		$local_tpl_data['server_id'] = $server_id;
		//$local_tpl_data['param'] = $param;
				
		$this->tpl_data['content'] .= $this->parser->parse('servers/view_log.html', $local_tpl_data, true);
			
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
