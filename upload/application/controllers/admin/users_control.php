<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
class Users_control extends CI_Controller {
    
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
        $this->lang->load('profile');
        $this->lang->load('users');
        $check = $this->users->check_user();
        
        if($check){
            //Base Template
            $this->tpl_data['title'] 		= lang('users_title_index');
            $this->tpl_data['heading'] 		= lang('users_heading_index');
            $this->tpl_data['content'] 		= '';
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
			$link = 'javascript:history.back()';
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
    
    
    // ----------------------------------------------------------------
    
    /**
     * Управление пользователями
     * 
     * Через функцию этого контролера происходит
     * управление web-пользователями панели:
     *  - добавление пользователей
     *  - удаление пользователей
     *  - редактирование
     *  - изменение привилегий на серверы
    */
    public function index()
    {
		//Проверка, есть ли права
		if(!$this->users->auth_privileges['usr_edit']){
			redirect('admin');
        }

		$this->tpl_data['content'] .= $this->parser->parse('web_users/web_users.html', $this->tpl_data, TRUE);

		$tpl_local_data['users_list'] = $this->users->tpl_users_list();
		$this->tpl_data['content'] .= $this->parser->parse('web_users/web_users_list.html', $tpl_local_data, TRUE);
        
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    // ----------------------------------------------------------------

    /**
     * Добавление новых пользователей
     * 
    */
    public function add()
    {
        if($this->users->user_id){

			//Проверка, есть ли права на добавление
            if(!$this->users->auth_privileges['usr_create']){
                    redirect('admin');
            }
            
            $this->load->library('form_validation');
			$this->load->helper('form');
			$this->load->model('password');
			
			$this->form_validation->set_rules('login', lang('login'), 'trim|required|is_unique[users.login]|max_length[32]|min_length[3]|xss_clean');
            $this->form_validation->set_rules('password', lang('password'), 'trim|required|max_length[64]|md5');
            $this->form_validation->set_rules('email', 'E-Mail', 'trim|required|is_unique[users.email]|valid_email');
                
            $i = -1;
			foreach($this->users->all_user_privileges as $key => $value) {
				$i++;
				/* Правила проверки формы */
				$this->form_validation->set_rules($key, $value, 'trim|max_length[1]|integer');
					
				/* Данные для шаблона */
				if(strlen($key) > 3) {
					$local_tpl_data['privileges_list'][$i]['privilege_name'] = $value;
					$local_tpl_data['privileges_list'][$i]['privilege_option'] = form_checkbox($key, 1);
				} else {
					$local_tpl_data['privileges_list'][$i]['privilege_name'] = '<p class="hr">' . $value . '</p>';
					$local_tpl_data['privileges_list'][$i]['privilege_option'] = '&nbsp;';
				}
			}

			if ($this->form_validation->run() == FALSE) {
				//$this->tpl_data['content'] .= 'Ошибка добавления пользователя';
				$this->tpl_data['content'] .= $this->parser->parse('web_users/web_users_add.html', $local_tpl_data, TRUE);	
			} else {
				
				foreach($this->users->all_user_privileges as $key => $value) {
			
					if(strlen($key) > 3) {
						$new_privileges[$key] = (bool)$this->input->post($key);
					}
				}
				
				$sql_data['privileges'] = json_encode($new_privileges);
								
				$sql_data['reg_date'] = time();
				$sql_data['login'] = $this->input->post('login', TRUE);
				$sql_data['password'] = $this->input->post('password', TRUE);
				$sql_data['password'] = $this->password->encryption($sql_data['password'], array('login' => $sql_data['login'],
																								 'reg_date' => $sql_data['reg_date'],
																								)
				);
			
				if ($this->users->add_user($sql_data)) {   
					$this->show_message(lang('users_usr_add_sucessful'), site_url('admin/users_control'), lang('users_back_to_users'));
					return TRUE;
				}
				
				//Форма проверена, данные записаны в базу   
			}
            
            //Проверка на то, что пользователь авторизован
        }
    
        $this->parser->parse('main.html', $this->tpl_data);
        
        // Конец функции добавления пользователя
    }
    
    // ----------------------------------------------------------------

    /**
     * Привилегии пользователя
     * 
    */
    public function servers_privileges($user_id = null, $server_id = null)
    {
        if($this->users->user_id){
            
            $user_id = (int)$user_id;
            $server_id = (int)$server_id;
            
            $this->tpl_data['heading'] = lang('users_heading_index');
            
            //Проверка, есть ли права на добавление
            if(!$this->users->auth_privileges['usr_edit_privileges']){
                    $this->tpl_data['content'] .= lang('users_no_privileges_for_edit_privileges');
            }else{
                
                if (!$user_id) {
					$this->show_message(lang('users_empty_id'));
					return FALSE;
                }
                
                if (!$this->users->user_live($user_id, 'ID')) {
					$this->show_message(lang('users_id_unavailable'));
					return FALSE;
				}
                
                $local_tpl_data = $this->users->tpl_userdata($user_id);
                
                $this->tpl_data['heading'] .= '&nbsp;::&nbsp;' . $local_tpl_data['user_login'];
                
                /* 
                 * Не указан ID сервера
                 * Показываем список серверов
                */
                if(!$server_id){
					
					$this->load->model('servers');
					
					$this->servers->get_server_list(FALSE, FALSE, array('enabled' => '1'));
					$local_tpl_data['servers_list'] = $this->servers->tpl_data();

					$this->tpl_data['content'] .= $this->parser->parse('web_users/select_server.html', $local_tpl_data, TRUE);
                    
                    $this->parser->parse('main.html', $this->tpl_data);
                    return FALSE;
                }
                    
                $user_privileges = $this->users->get_server_privileges($server_id, $user_id, TRUE);

                $num = -1;
                foreach ($user_privileges as $privilege_name => $privilege_value)
                {
                    $num++;
                    
                    $chechbox_data = array(
						'name'        => 'newsletter',
						'id'          => 'newsletter',
						'value'       => 'accept',
						'checked'     => TRUE,
						'style'       => 'margin:10px',
					);
                    
                    $local_tpl_data['privilege_list'][$num]['form_checkbox'] = form_checkbox($privilege_name, '1', $privilege_value);
                    $local_tpl_data['privilege_list'][$num]['human_name'] = $this->users->all_privileges[$privilege_name];
                }
                
                $local_tpl_data['server_id'] = $server_id;
                
                $this->tpl_data['content'] .= $this->parser->parse('web_users/web_users_privileges.html', $local_tpl_data, TRUE);
            }
        }
        
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    
    // ----------------------------------------------------------------

    /**
     * Сохранить привилегииs
     * 
    */
    public function save_servers_privileges($user_id = null, $server_id = null)
    {
        if($this->users->user_id){
            
            $user_id = (int)$user_id;
            $server_id = (int)$server_id;
            
            //Проверка, есть ли права на редактирование привилегий
            if(!$this->users->auth_privileges['usr_edit_privileges']){
                    $this->tpl_data['content'] .= lang('users_no_privileges_for_edit_privileges');
            }else{
				
				/* Получаем данные редактируемого пользователя, но записываем их лишь в переменную $user_data */
                $user_data = $this->users->get_user_data($user_id, FALSE, TRUE);
				
				 /* В целях безопасности, редактировать администратора может только он сам */
                if($user_data['is_admin'] == 1 AND $user_data['id'] != $this->users->user_id){
					
					$local_tpl_data['message'] = lang('users_edit_admin_denied');
						
					$local_tpl_data['link'] = site_url('admin/users_control');
					$local_tpl_data['back_link_txt'] = 'Вернуться';
						
					$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
					$this->parser->parse('main.html', $this->tpl_data);
					return FALSE;
				}
                
                if (!$user_id) {
					$this->show_message(lang('users_empty_id'));
					return FALSE;
                }
                
                if (!$this->users->user_live($user_id, 'ID')) {
					$this->show_message(lang('users_id_unavailable'));
					return FALSE;
				}

                if(!$server_id){
					$this->show_message(lang('users_empty_id_server'));
					return FALSE;
                }

                //$this->load->model('servers');
                
                $local_tpl_data = $this->users->tpl_userdata($user_id);
                $this->tpl_data['heading'] .= '&nbsp;::&nbsp;' . $local_tpl_data['user_login'];
                
                // Сохранение привилегии
                foreach ($this->users->all_privileges as $privilege_name => $privilege_human_name)
                {
                    $privilege_value = (bool)$this->input->post($privilege_name, TRUE);
                    $this->users->set_server_privileges($privilege_name, $privilege_value, $server_id, $user_id);
                }
                
                $local_tpl_data = array();
                $local_tpl_data['message'] = lang('users_srv_privileges_saved');
                $local_tpl_data['link'] = site_url('admin/users_control');
                $local_tpl_data['back_link_txt'] = 'Вернуться к пользователям';
                $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
            }
        }
        
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    // ----------------------------------------------------------------

    /**
     * Редактирование пользователя
     * 
    */
    public function edit($user_id = FALSE)
    {
        if($this->users->user_id){
            
            $user_id = (int)$user_id;
            
            //Проверка, есть ли права на добавление
            if(!$this->users->auth_privileges['usr_edit']){
                    $this->tpl_data['content'] .= lang('users_no_privileges_for_edit');
            }else{
                
                if (!$user_id) {
					$this->show_message(lang('users_empty_id'));
					return FALSE;
                }
                
                $local_tpl_data = array();
                
                /* Получаем данные редактируемого пользователя, но записываем их лишь в переменную $user_data */
                $user_data = $this->users->get_user_data($user_id, FALSE, TRUE);
                
                /* В целях безопасности, редактировать администратора может только он сам */
                if ($user_data['is_admin'] == 1 AND $user_data['id'] != $this->users->user_id) {
					$this->show_message(lang('users_edit_admin_denied'), site_url('admin/users_control'));
					return FALSE;
				}
                
				if(!$this->input->post('user_edit_submit')){
					$local_tpl_data = $this->users->tpl_userdata($user_id, $user_data);
					$this->tpl_data['content'] .= $this->parser->parse('web_users/user_edit.html', $local_tpl_data, TRUE);
				}else{
					$this->load->library('form_validation');
					$this->load->model('password');
					
					$this->form_validation->set_rules('name', 'Имя', 'trim|xss_clean');
					$this->form_validation->set_rules('email', 'E-Mail', 'trim|required|valid_email');
					$this->form_validation->set_rules('new_password', 'Пароль', 'trim|md5');
					
					if (!$this->form_validation->run()){
								$this->tpl_data['content'] .= '<p>' . lang('users_form_unavailable') . '</p>';
					}else{
						
						if($this->input->post('new_password') !== ''){
							$this->load->model('password');
							
							$password_encrypt = $this->input->post('new_password', TRUE);
							$password_encrypt = $this->password->encryption($password_encrypt, $user_data);
							$user_new_data['password'] = $password_encrypt;
						}
						
						$user_new_data['name'] = $this->input->post('name', TRUE);
						$user_new_data['email'] = $this->input->post('email', TRUE);

						$this->users->update_user($user_new_data, $user_data['id']);
								
						$local_tpl_data = array();
						$local_tpl_data['message'] 			= lang('users_usr_data_saved');
						$local_tpl_data['link'] 			= site_url('admin/users_control');
						$local_tpl_data['back_link_txt'] 	= lang('users_back_to_users');
						$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
					}
					
				}
                
            }
        }
        
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    // ----------------------------------------------------------------

    /**
     * Удаление пользователя
     * 
    */
    public function delete($user_id = null, $confirm = FALSE)
    {
        if($this->users->user_id){
            
            $user_id = (int)$user_id;
            
            //Проверка, есть ли права на добавление
            if(!$this->users->auth_privileges['usr_delete']){
                    $this->tpl_data['content'] .= lang('users_no_privileges_for_delete');
            } else {
				
                if($confirm == $this->security->get_csrf_hash()) {
					if($user_id) {
						
						 /* Получаем данные редактируемого пользователя, но записываем их лишь в переменную $user_data */
						$user_data = $this->users->get_user_data($user_id, FALSE, TRUE);
						
						/* В целях безопасности, администратора нельзя удалить */
						if ($user_data['is_admin']) {
							
							$local_tpl_data['message'] = lang('users_delete_admin_denied');
								
							$local_tpl_data['link'] = site_url('admin/users_control');
							$local_tpl_data['back_link_txt'] = lang('back');
								
							$this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
							$this->parser->parse('main.html', $this->tpl_data);
							return FALSE;
						}
						
						$this->users->delete_user($user_id);
						$this->show_message(lang('users_usr_deleted'), site_url('admin/users_control'));
						return TRUE;
					}
				} else {
					/* Пользователь не подвердил намерения */
					$confirm_tpl['message'] 		= lang('users_delete_confirm');
					$confirm_tpl['confirmed_url'] 	= site_url('admin/users_control/delete/' . $user_id . '/' . $this->security->get_csrf_hash());
					$this->tpl_data['content'] 		.= $this->parser->parse('confirm.html', $confirm_tpl, TRUE);
				}

            }
        }
        
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    // ----------------------------------------------------------------

    /**
     * Редактирование пользователя
     * 
    */
    public function base_privileges($user_id = FALSE)
    {
		//Проверка, есть ли права на добавление
		if(!$this->users->auth_privileges['usr_edit_privileges']) {
			$this->show_message(lang('users_no_privileges_for_edit_privileges'), site_url('admin'));
			return FALSE;
		}
		
		if(!$user_id) {
			$this->show_message(lang('users_empty_id'), site_url('admin/users_control'));
			return FALSE;
		}
		
		if(!$this->users->get_user_data($user_id, TRUE)){
			$this->show_message(lang('users_id_unavailable'), site_url('admin/users_control'));
			return FALSE;
		}

		/* Задание переменных */
		$local_tpl_data = array();
		$local_tpl_data['user_id'] = $user_id;
		
		/* Загрузка моделей, библиотек, хелперов */
		$this->load->library('form_validation');
		$this->load->helper('form');
  
        $i = -1;
		foreach($this->users->all_user_privileges as $key => $value) {
			$i++;
			/* Правила проверки формы */
			$this->form_validation->set_rules($key, $value, 'trim|max_length[1]|integer');
			
			/* Данные для шаблона */
			if(strlen($key) > 3) {
				$local_tpl_data['privileges_list'][$i]['privilege_name'] = $value;
				$local_tpl_data['privileges_list'][$i]['privilege_option'] = form_checkbox($key, 1, $this->users->user_privileges[$key]);
			} else {
				$local_tpl_data['privileges_list'][$i]['privilege_name'] = '<p class="hr">' . $value . '</p>';
				$local_tpl_data['privileges_list'][$i]['privilege_option'] = '&nbsp;';
			}
		}
		
        
        if ($this->form_validation->run() == FALSE) {
			$this->tpl_data['content'] = $this->parser->parse('web_users/base_privileges_edit.html', $local_tpl_data, TRUE);
			
		} else {
			foreach($this->users->all_user_privileges as $key => $value) {
				
				if(strlen($key) > 3) {
					$new_privileges[$key] = (bool)$this->input->post($key);
				}
			}
			
			$user_data['privileges'] = json_encode($new_privileges);

			if($this->users->update_user($user_data, $user_id)) {
				$this->show_message(lang('users_base_privileges_saved'), site_url('admin/users_control') , lang('next'));
				
				/* Отправляем информацию админам */
				$subject = lang('users_mail_subject_change_privileges');
				$message = lang('users_mail_message_change_privileges', $this->users->user_data['login'], $this->users->auth_data['login']);
				$this->users->admin_msg($subject, $message);
				return TRUE;
			} else {
				$this->show_message(lang('unknown_error'));
				return FALSE;
			}
		}
        
        $this->parser->parse('main.html', $this->tpl_data);
    }
}
