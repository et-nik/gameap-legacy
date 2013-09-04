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
class Log_view extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('log_view');
        
        if ($this->users->check_user()) {
			
			$this->load->helper('date');
			
			//Base Template
			$this->tpl_data['title'] = lang('log_view_title_index');
			$this->tpl_data['heading'] = lang('log_view_heading_index');
			$this->tpl_data['content'] = '';
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, TRUE);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), TRUE);
			
        }else{
            redirect('auth');
        }
    }
    
    // Отображение информационного сообщения
	private function show_message($message = FALSE, $link = FALSE, $link_text = FALSE)
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
	
		$local_tpl_data['message'] = $message;
		$local_tpl_data['link'] = $link;
		$local_tpl_data['back_link_txt'] = $link_text;
		$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
		$this->parser->parse('main.html', $this->tpl_data);
	}
    
    function view($type = FALSE, $id = FALSE)
    {
		/* Проверочки */
		if(!$type) {
			$this->show_message(lang('log_view_type_param_not_specified'));
			return FALSE;
		} elseif(!$id) {
			$this->show_message(lang('log_view_id_param_not_specified'));
			return FALSE;
		}
		
		switch($type) {
			case 'server':
				$this->load->model('servers');
				
				// Получаем содержимое лога
				$where = array('id' => $id);
				$log_list = $this->panel_log->get_log($where, 1);
				
				/* Лог должен быть найден и относиться к серверу */
				if(!$log_list OR !$log_list[0]['server_id']) {
					$this->show_message(lang('log_view_record_not_found'));
					return FALSE;
				}
				
				/* Получаем данные сервера */
				$this->servers->get_server_data($log_list[0]['server_id']);
				
				/* Если сервер, указанный в логе не найден */
				if(!$this->servers->server_data) {
					$this->show_message(lang('log_view_record_not_found'));
				}
				
				/* Если пользователь не админ, то делаем проверочки на права доступа к логам */
				if (!$this->users->auth_data['is_admin']) {
					/* Получаем права на сервер */
					$this->users->get_server_privileges($log_list[0]['server_id']);
					
					/* У пользователя на этот сервер должны быть права */
					if(!$this->users->servers_privileges['VIEW']) {
						$this->show_message(lang('log_view_record_not_found'));
						return FALSE;
					}
					
					/* Если пользователю нельзя видеть rcon пароль */
					if(!$this->users->servers_privileges['CHANGE_RCON'] && strtolower($log_list[0]['command']) == 'rcon_password') {
						$this->show_message(lang('log_view_log_access_denied'), '/admin/server_control/main/' . $log_list[0]['server_id']);
						return FALSE;
					}
				}
				
				/* Все проверки пройдены! */
				//print_r($log_list);
				
				$local_tpl_data['log_id'] = $log_list[0]['id'];
				$local_tpl_data['log_date'] = unix_to_human($log_list[0]['date'], TRUE, 'eu');
				$local_tpl_data['log_type'] = $log_list[0]['type'];
				$local_tpl_data['log_command'] = $log_list[0]['command'];
				$local_tpl_data['log_user'] = $log_list[0]['user_name'];
				$local_tpl_data['log_user_name'] = $log_list[0]['user_name'];
				$local_tpl_data['server_id'] = $log_list[0]['server_id'];
				$local_tpl_data['server_name'] = $this->servers->server_data['name'];
				$local_tpl_data['log_msg'] = $log_list[0]['msg'];
				$local_tpl_data['log_data'] = $log_list[0]['log_data'];
				
				$this->tpl_data['content'] .= $this->parser->parse('log_info.html', $local_tpl_data, TRUE);
				
				break;
			default:
				$this->show_message(lang('log_view_type_param_wrong'));
				return FALSE;
				break;
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
}

/* End of file log_view.php */
/* Location: ./application/controllers/log_view.php */
