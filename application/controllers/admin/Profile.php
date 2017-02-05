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

class Profile extends BaseController {
	
	var $tpl = array();
	var $user_servers_count = 0;
	
	// -----------------------------------------------------------------
	
	public function __construct()
    {
        parent::__construct();
	
		$this->load->database();
        $this->load->model('users');
		$this->lang->load('profile');
        $this->lang->load('main');
        
        if($this->users->check_user()){
			//Base Template
			$this->tpl = $this->users->tpl_userdata();
			$this->tpl['title'] 	= lang('profile_title_index');
			$this->tpl['heading'] 	= lang('profile_header_index');
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
	
	public function index()
    {
		// Загрузка модели работы с серверами
		$this->load->model('servers');
		
		// Получение списка серверов юзера
		$this->servers->get_server_list($this->users->auth_id);
		$local_tpl['servers_list'] = $this->servers->tpl_data();
		
		if($local_tpl['servers_list']){
			$this->user_servers_count = 1;
		}
		
		$this->tpl['content'] .= $this->parser->parse('profile/profile_main.html', array_merge($this->tpl, $local_tpl), true);

        $this->parser->parse('main.html', $this->tpl);
    }
    
    // -----------------------------------------------------------------
    
    /* Редактирование профиля */
    public function edit()
    {
		if($this->users->auth_id){
			
			if(!$this->input->post('profile_edit_submit')){
				$this->tpl['content'] .= $this->parser->parse('profile/profile_edit.html', $this->tpl, true);
			}else{
				$this->load->library('form_validation');
				
				$this->form_validation->set_rules('name', 'Имя', 'trim');
				$this->form_validation->set_rules('email', 'E-Mail', 'trim|required|valid_email');
				
				if (!$this->form_validation->run()) {
					
					if (validation_errors()) {
						$this->_show_message(validation_errors());
						return false;
					}
					
					$this->tpl['content'] .= lang('profile_form_unavailable');
				}else{
					$user_new_data['name'] = $this->input->post('name', true);
					$user_new_data['email'] = $this->input->post('email', true);
					
					/* Подтверждение смены email, если разрешен в конфигурации
					 * Пользователю на текущий email отправляется письмо, 
					 * со ссылкой на подтверждение смены.
					 * 
					 * Текущий email возвращается обратно, а меняется при переходе
					 * по ссылке, в методе change_email_confirm().
					*/
					if ($this->config->config['email_change_confirm'] && $this->users->auth_data['email'] != $user_new_data['email']) {
						
						$change_code = generate_code(20);
						
						$link = site_url('admin/profile/change_email_confirm/' . $change_code);
						$this->users->send_mail(lang('profile_email_change_confirm_subject'), 
												lang('profile_email_change_confirm', $this->users->auth_data['email'], $user_new_data['email'], $link), 
												$this->users->auth_id
						);
						
						$this->users->update_modules_data($this->users->auth_id, 
															array('confirm_change_email' => 
																array('code' => $change_code, 
																'new_email' => $user_new_data['email'])
															),
															'gameap'
						);

						$user_new_data['email'] = $this->users->auth_data['email'];
					}
					
					if ($this->users->update_user($user_new_data, $this->users->auth_data['id'])) {
						$log_data['msg'] = 'Profile edit success';
						$this->_show_message(lang('profile_data_changed'), site_url('admin/profile'), lang('profile'));
					} else {
						$log_data['msg'] = 'Profile edit failed';
						$this->_show_message('Error');
					}
					
					/* Сохраняем логи */
					$log_data['type'] = 'profile';
					$log_data['command'] = 'save_profile';
					$log_data['user_name'] = $this->users->auth_login;
					$log_data['server_id'] = 0;
					$log_data['log_data'] = "";
					$this->panel_log->save_log($log_data);

					return;
				}
				
			}
			
        }

        $this->parser->parse('main.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Подтверждение смены email
	 */
	public function change_email_confirm($confirm_code = false)
	{
		if (!$confirm_code) {
			$this->_show_message('Empty code');
			return;
		}
		
		$modules_data =& $this->users->auth_data['modules_data'];
		
		if (isset($modules_data['gameap']['confirm_change_email']['code']) && $modules_data['gameap']['confirm_change_email']['code'] == $confirm_code) {
			// Код подходит, меняем email
			$sql_data['email'] = $modules_data['gameap']['confirm_change_email']['new_email'];
			
			if ($this->users->update_user($sql_data, $this->users->auth_id)) {
				$log_data['msg'] = 'Profile email changed';
				$this->_show_message(lang('profile_email_changed'), site_url('admin/profile'), lang('profile'));
			} else {
				$log_data['msg'] = 'Email change failed';
				$this->_show_message('Error');
			}
			
			// Опустошаем данные
			$this->users->update_modules_data($this->users->auth_id, array('confirm_change_email' => array()),'gameap');
			
			/* Сохраняем логи */
			$log_data['type'] 		= 'profile';
			$log_data['command'] 	= 'change_email';
			$log_data['user_name'] 	= $this->users->auth_login;
			$log_data['server_id'] 	= 0;
			$log_data['log_data'] 	= "";
			$this->panel_log->save_log($log_data);
			
		} else {
			$this->_show_message('Unknown code');
			return;
		}
	}
	
	// -----------------------------------------------------------------
	
	/* Смена пароля */
	public function change_password()
    {
		if($this->users->auth_id){
			
			if(!$this->input->post('profile_edit_submit')){
				$this->tpl['content'] .= $this->parser->parse('profile/profile_change_password.html', $this->tpl, true);
			}else{
				$this->load->library('form_validation');
				
				$this->form_validation->set_rules('old_password', 'Текущий пароль', 'trim|required');
				$this->form_validation->set_rules('new_password', 'Пароль', 'trim|required|matches[new_password_confirm]');
				$this->form_validation->set_rules('new_password_confirm', 'Подтверждение пароля', 'trim|required');
				
				if (!$this->form_validation->run()){
					
					if (validation_errors()) {
						$this->_show_message(validation_errors());
						return false;
					}
					
					$this->_show_message(lang('profile_form_unavailable'));
					return false;
				}else{
					
					//~ $password_encrypt = hash_password($this->input->post('old_password', true));
					
					$password_encrypt = hash_password($this->input->post('old_password', true), $this->users->auth_data['password']);
					
					if ($password_encrypt != $this->users->auth_data['password']) {
						$this->_show_message(lang('profile_password_unavailable'));
						return false;
					}
					
					$new_password = $this->input->post('new_password', true);
					$new_password = hash_password($new_password);
					
					if ($this->users->update_user(array('password' => $new_password), $this->users->auth_data['id'])) {
						$log_data['msg'] = 'Profile change password success';
						$this->_show_message(lang('profile_password_changed'), site_url('admin/profile'), lang('profile'));
					} else {
						$log_data['msg'] = 'Profile change password failed';
						$this->_show_message('Profile change password failed');
					}
					
					/* Сохраняем логи */
					$log_data['type'] = 'profile';
					$log_data['command'] = 'change_password';
					$log_data['user_name'] = $this->users->auth_login;
					$log_data['server_id'] = 0;
					$log_data['log_data'] = "";
					$this->panel_log->save_log($log_data);
					
					$local_tpl = array();
					$local_tpl['message'] = lang('profile_password_changed');
					$local_tpl['link'] = site_url();
					$local_tpl['back_link_txt'] = lang('profile');
					$this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
					//Смена пароля закончена
				}
				
			}
			
        }

        $this->parser->parse('main.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------
	
	public function server_privileges($server_id = false)
    {
		$this->load->model('servers');
		
		$server_id = (int)$server_id;
		
		$this->tpl['heading'] = lang('profile_server_privileges');
			
		if(!$server_id) {
			$this->_show_message(lang('profile_empty_server_id'), site_url('admin/profile'));
			return false;
		}
		
		$this->servers->get_server_data($server_id, true, true, true);
		
		if(!$this->servers->server_data) {
			$this->_show_message(lang('profile_server_not_found'), site_url('admin/profile'));
			return false;
		}

		$user_privileges = $this->users->get_server_privileges($server_id, false);
		
		if(!$this->users->auth_servers_privileges['VIEW']) {
			$this->_show_message(lang('profile_server_not_found'), site_url('admin/profile'));
			return false;
		}

		$num = -1;
		foreach ($user_privileges as $privilege_name => $privilege_value)
		{
			$num++;
			if($privilege_value == 1){
				$local_tpl['privilege_list'][$num]['privilege_value'] = '<img src="' . base_url('themes/' . $this->config->config['template'] . '/' . $this->config->config['style'] . '/images/yes.png') . '">';
			}else{
				$local_tpl['privilege_list'][$num]['privilege_value'] = '<img src="' . base_url('themes/' . $this->config->config['template'] . '/' . $this->config->config['style'] . '/images/yes.png') . '">';
			}
			
			$local_tpl['privilege_list'][$num]['human_name'] = $this->users->all_privileges[$privilege_name];
		}
		
		$this->tpl['content'] .= $this->parser->parse('profile/server_privileges.html', $local_tpl, true);
		
        $this->parser->parse('main.html', $this->tpl);
    }
}

/* End of file profile.php */
/* Location: ./application/controllers/admin/profile.php */
