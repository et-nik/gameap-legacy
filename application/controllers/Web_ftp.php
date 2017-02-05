<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2015, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/
use \Myth\Controllers\BaseController;

class Web_ftp extends BaseController {
	
	//Template
	var $tpl = array();
	
	var $user_data = array();
	var $server_data = array();
	
	// -----------------------------------------------------------------
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('web_ftp');
        $this->lang->load('server_command');
        $this->lang->load('server_control');

        if ($this->users->check_user()) {
			//Base Template
			$this->tpl['title'] 		= lang('server_files_title_index');
			$this->tpl['heading']		= lang('server_files_header_index');
			$this->tpl['content'] 		= '';
			$this->tpl['menu'] 		= $this->parser->parse('menu.html', $this->tpl, true);
			$this->tpl['profile'] 		= $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
        
        } else {
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
			$link = 'javascript:history.back()';
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
		
		$tpl['filter_name']			= isset($filter['name']) ? $filter['name'] : '';
		$tpl['filter_ip']				= isset($filter['ip']) ? $filter['ip'] : '';
		
		$tpl['filter_ip_dropdown']		= form_multiselect('filter_ip[]', $ip_array, $tpl['filter_ip']);
		
		$default = isset($filter['game']) ? $filter['game'] : null;
		$tpl['filter_games_dropdown'] 	= form_multiselect('filter_game[]', $games_option, $default);
		
		return $tpl;
	}

	// ----------------------------------------------------------------

    /**
     * Главная страница
     * 
    */
    public function index()
    {
		/* Загружаем модель */
		$this->load->model('servers');
		$this->load->model('servers/games');
		$this->load->helper('games');
		
		$filter = $this->users->get_filter('servers_list');
		$local_tpl = $this->_get_tpl_filter($filter);
		
		$this->servers->set_filter($filter);
		$this->servers->get_servers_list($this->users->auth_id);
		
		$local_tpl['url'] 			= site_url('web_ftp/server');
		$local_tpl['games_list'] = servers_list_to_games_list($this->servers->servers_list);
			
		$this->tpl['content'] .= $this->parser->parse('servers/select_server.html', $local_tpl, true);
			
		$this->parser->parse('main.html', $this->tpl);
	}
	
	// ----------------------------------------------------------------

    /**
     * Главная страница
     * 
    */
    public function server($server_id = false)
    {
		$this->load->helper('ds');
		$this->load->driver('files');
		
		/* 
		 * Если не указан id сервера, то перенаправляем на
		 * страницу выбора 
		*/
		if(!$server_id){
			redirect('admin/web_ftp');
		}
		
		/* Преобразование id в числовое значение */
		$server_id = (int)$server_id;
		
		/* Загружаем модель работы с сервером */
		$this->load->model('servers');
		
		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		/* Проверка на права загрузки и правки конфигурационный файлов сервера */
		if(!$this->users->auth_servers_privileges['UPLOAD_CONTENTS']
		&& !$this->users->auth_servers_privileges['CHANGE_CONFIG']
		){
			$this->_show_message(lang('server_files_no_privileges'), site_url('admin/servers_files'));
			return false;
		}
		
		$local_tpl = array();
		
		/* Получение данных сервера */
		$this->servers->get_server_data($server_id);
		
		/* Получение данных сервера */
		if (!$this->servers->get_server_data($server_id)) {
			$this->_show_message(lang('server_control_server_not_found'));
			return;
		}
		
		/* Получение данных сервера для шаблона */
		$local_tpl['server_id'] = $this->servers->server_data['id'];

		$this->tpl['content'] .= $this->parser->parse('web_ftp.html', $local_tpl, true);
		$this->parser->parse('main.html', $this->tpl);
	}

}
