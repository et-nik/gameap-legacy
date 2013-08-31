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
class Auth extends CI_Controller {
	
	var $tpl_data = array();
	var $user_data = array();
	
	public function __construct()
    {
        parent::__construct();
        
		$this->load->database();
		
        $this->load->library('form_validation');
        $this->load->model('users');
        $this->load->helper('safety');
        $this->load->helper('captcha');
        
        $this->lang->load('auth');
        
        $this->tpl_data['menu'] = '';
        $this->tpl_data['profile'] = '';
        $this->tpl_data['content'] = '';
        
        $this->tpl_data['title'] 	= lang('auth_title_index');
		$this->tpl_data['heading'] 	= lang('auth_heading');
    }
    
	// Отображение информационного сообщения
    private function show_message($message = 'Ошибка', $link = FALSE, $link_text = 'Вернуться')
    {
        if(!$link) {
			$link = site_url('auth/in');
		}
        
        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    private function check_captcha($word)
    {
			// Удаление старой капчи
			$expiration = time()-7200; // Двухчасовое ограничение
			
			$this->db->delete('capthca', array('captcha_time <' => $expiration));
			
			// Проверяем капчу
			$query = $this->db->get_where('captcha', array('word' => $word, 'ip_address' => $this->input->ip_address(), 'captcha_time >' => $expiration), 1);
			
			if($query->num_rows > 0) {
				return FALSE;
			}
			
			return TRUE;
	}
	
	public function index()
	{
		// Обычно библиотека запущена
        // $this->load->library('parser');
        $this->tpl_data['code'] = '';

        /* Проверяем пользователя */
        $check = $this->users->check_user();

        if (!$check) {
            //$data['content'] = $this->parser->parse('login.html', $data, TRUE);
            redirect('auth/in');
        } else {
			
			/* После header в этом случае
			 * команды exit не нужно
			*/
			redirect('admin');
        }
	}
	
    //-----------------------------------------------------------
	
	/**
     * Авторизация
     * 
     * @param code - необязательный параметр для авторизации пользователей, 
     * подвергшихся брутфорс атаке.
     * 
     *
    */
    public function in($code = FALSE)
    {
        /* Загрузка модели проверки пользователей */
        //$this->load->model('check_user');

        if($code) {
			$this->tpl_data['code'] = $code;
		} else {
			$this->tpl_data['code'] = '';
		}
		
		$this->tpl_data['heading'] 	= lang('auth_heading');
        $this->tpl_data['title'] 	= lang('auth_title_in');
        
        $check = $this->users->check_user();
			
		if($check){
			/* Перенаправляем пользователя в нужное место, 
			 * если заданы нужные куки location_page 
			*/
			if($location_page = $this->input->cookie('location_page', TRUE)){
				// Уничтожаем куки
				$cookie = array(
						'name'   => 'location_page',
						'path'   => '/',
					);

				$this->input->set_cookie($cookie);
				redirect($location_page);
			}else{
				redirect('admin');
			}

			exit;
		}

        $this->form_validation->set_rules('user_login', 'Username', 'trim|required|max_length[32]|xss_clean');
        $this->form_validation->set_rules('user_password', 'Password', 'trim|required|md5');

        /* Проверка формы */
		if ($this->form_validation->run() == FALSE){
			//$this->tpl_data['content'] .= $this->parser->parse('login.html', $this->tpl_data);

        } else {

			$user_data['login'] = $this->input->post('user_login', TRUE);
			$user_data['password'] = $this->input->post('user_password', TRUE);
			
			/* Защита от брутфорса по одному ip */
			if(count($this->panel_log->get_log(array('date >' => time() - 300, 'ip' => $_SERVER['REMOTE_ADDR'], 'msg' => 'Authorization Failed'))) > 5 ) {
				$this->show_message(lang('auth_repeat_enter_wrong_password'));
				return FALSE;
			}
			
			/* Защита от брутфорса для определенного пользователя */
			if(count($this->panel_log->get_log(array('date >' => time() - 300, 'user_name' => $user_data['login'], 'msg' => 'Authorization Failed'))) > 5 ) {
				if($this->users->user_live($user_data['login'], 'LOGIN')) {
					$code_is_true = FALSE;

					/* Проверка правильности кода */
					if($code) {
						$where = array('recovery_code' => $code);
						$user_list = $this->users->get_users_list($where, 1);
						
						if(!empty($user_list)) {
							// Код верный, ничего не предпринимаем, не блокируем и разрешаем
							// пользователю авторизоваться, т.к. он подтвердил всё
							$code_is_true = TRUE;
						} else {
							$code_is_true = FALSE;
						}
					}
					
					if(!$code_is_true) {
						/* Код неверный, поэтому делаем всё как обычно */
						
						if(count($this->panel_log->get_log(array('date >' => time() - 86400, 'user_name' => $user_data['login'], 'msg' => 'Bruteforce. Reset code sended'))) < 1 ) {
							/* Количество отправленных писем < 1, значит отправляем пользователю письмо */
							
							$user_code = $this->users->get_user_recovery_code(array('login' => $user_data['login']));
							$user_data = $this->users->get_user_data(array('login' => $user_data['login']));
							
							$this->load->library('email');
							
							$this->email->from($this->config->config['system_email'], 'АдминПанель');
							$this->email->to($user_data['email']); 

							$this->email->subject(lang($lang['auth_account_unblock']));
							
							$email_message = lang('auth_mail_goto_link') . site_url() . 'auth/in/' . $user_code ;
							
							$this->email->message($email_message);
			
							if($this->email->send()) {
								$this->show_message(lang('auth_bruteforce_email_send'));
								$log_data['msg'] = 'Bruteforce. Reset code sended';
							} else {
								$this->show_message(lang('auth_bruteforce_authorization_error'));
								$log_data['msg'] = 'Bruteforce. Reset code send failure';
							}
							
							//print_r($this->email->print_debugger());

							// Сохраняем логи отправки email, чтобы не отправлять 1000 раз
							$log_data['type'] = 'auth';
							$log_data['user_name'] = $user_data['login'];
							$this->panel_log->save_log($log_data);
							return FALSE;
						} else {
							$this->show_message(lang('auth_bruteforce_email_send'));
							return FALSE;
						}
						// Код неверный
					}
					// Пользователь существует
				}
			}

            $check = $this->users->user_auth($user_data['login'], $user_data['password']);

			/* Если все сходится, то задаем куки*/
			if($check) {
				
				$this->user_data['user_id'] = $check;
				$this->user_data['user_login'] = $user_data['login'];
				$this->user_data['password'] = $user_data['password'];
				
				$hash = $this->users->get_user_hash();
				
				// Задаем куки
				$cookie = array(
				'name'   => 'user_id',
				'value'  => $this->user_data['user_id'],
				'expire' => '86500',
				'path'   => '/',
				);

				$this->input->set_cookie($cookie);

				$cookie = array(
					'name'   => 'hash',
					'value'  => $hash,
					'expire' => '86500',
					'path'   => '/',
				);

				$this->input->set_cookie($cookie);
				
				// Сохраняем логи
				$log_data['type'] = 'auth';
				$log_data['user_name'] = $user_data['login'];
				$log_data['msg'] = 'Authorization Successful';
				$this->panel_log->save_log($log_data);
				
				/* Перенаправляем пользователя в нужное место, 
				 * если заданы нужные куки location_page */
				if($location_page = $this->input->cookie('location_page', TRUE)){
					// Уничтожаем куки
					$cookie = array(
							'name'   => 'location_page',
							'path'   => '/',
						);

					$this->input->set_cookie($cookie);
					redirect($location_page);
				}else{
					redirect('admin');
				}

				exit;
				
				$this->tpl_data['content'] = '<p>'. lang('auth_authorization_successful') .'</p>';
				$this->tpl_data['content'] .= '<a href=' . site_url('admin') . '>' . lang('auth_goto_server_control') . '</a>';
			} else {
				$this->tpl_data['content'] = '<p>' . lang('auth_authorization_failed') . '</p>';
				//$this->tpl_data['content'] .= $this->parser->parse('login.html', $this->tpl_data, TRUE);
				
				// Сохраняем логи
				$log_data['type'] = 'auth';
				$log_data['user_name'] = $this->input->post('user_login', TRUE);
				$log_data['msg'] = 'Authorization Failed';
				$this->panel_log->save_log($log_data);
			}
        }
        /* Конец проверки формы*/

        $this->parser->parse('login.html', $this->tpl_data);
    }
    
    public function out()
    {
		$this->tpl_data['menu'] = '';
        $this->tpl_data['title'] 		= lang('auth_title_out');
        $this->tpl_data['heading'] 		= lang('auth_heading_out');
		
		$cookie = array(
			'name'   => 'user_id',
			'path'   => '/',
		);

		$this->input->set_cookie($cookie);

		$cookie = array(
					'name'   => 'user_hash',
					'path'   => '/',
		);

		$this->input->set_cookie($cookie);
		
		$local_tpl_data['message'] 			= lang('auth_quit_success');
		$local_tpl_data['link'] 			= site_url();
		$local_tpl_data['back_link_txt'] 	= lang('auth_goto_main');
		$this->tpl_data['content'] 			= $this->parser->parse('info.html', $local_tpl_data, TRUE);

        $this->parser->parse('main.html', $this->tpl_data);
	}
	
	function register()
    {
        
        $this->tpl_data['heading'] 	= lang('auth_title_register');
        $this->tpl_data['title'] 	= lang('auth_heading_register');
        
        if(!$this->config->config['register_users']) {
			$this->show_message(lang('auth_registration_closed'), site_url());
			return FALSE;
		}

        $this->form_validation->set_rules('login', 'логин', 'trim|required|is_unique[users.login]|max_length[32]|xss_clean');
		$this->form_validation->set_rules('password', 'пароль', 'trim|required|max_length[64]|matches[passconf]|md5|xss_clean');
		$this->form_validation->set_rules('passconf', 'подтверждение пароля', 'trim|required|max_length[64]|xss_clean');
		$this->form_validation->set_rules('email', 'email адрес', 'trim|required|is_unique[users.email]|max_length[64]|valid_email|xss_clean');
		
		$this->form_validation->set_rules('image', 'капча', 'trim|required|max_length[12]|xss_clean');
        
        /* Проверка формы */
		if ($this->form_validation->run() == FALSE){
			
			// Слово для капчи
			$cap['word'] = rand(1000, 9999);
			
			// Создаем капчу
			$vals = array(
				'word'	 		=> $cap['word'],
				'img_path'	 	=> './uploads/security/',
				'img_url'	 	=> site_url() . 'uploads/security/',
				'font_path'	 	=> './system/fonts/U1Uabbif.ttf',
				'img_width'	 	=> 300,
				'img_height' 	=> 50,
				'expiration' 	=> 7200
				);

			$captcha = create_captcha($vals);
			$this->tpl_data['captcha'] = $captcha['image'];
			
			$data = array(
				'captcha_time'	=> $captcha['time'],
				'ip_address'	=> $this->input->ip_address(),
				'word'	 		=> $captcha['word']
			);
			
			$query = $this->db->insert_string('captcha', $data);
			$this->db->query($query);
            
             $this->parser->parse('register.html', $this->tpl_data);
        } else {
			
			// Загрузка модели для шифровки пароля
			$this->load->model('password');
			
			// Проверяем, правильно ли введено сообщение
			if(!$this->check_captcha($this->input->post('image'))){
				// Сохраняем логи
				$log_data['type'] = 'reg';
				$log_data['user_name'] = $this->input->post('login');
				$log_data['msg'] = 'Registration Failed';
				$this->panel_log->save_log($log_data);
				
				$this->show_message(lang('auth_captcha_enter_wrong'), site_url('auth/register'));
				return FALSE;
			}
			
			$user_data['email'] = $this->input->post('email', TRUE);
			$user_data['reg_date'] = time();
			 
            $user_data['login'] = $this->input->post('login');
            $user_data['password'] = $this->input->post('password');
            $user_data['password'] = $this->password->encryption($user_data['password'], array('login' => $user_data['login'],
                                                                                             'reg_date' => $user_data['reg_date'],
                                                                                            )
			);
			
			$user_data['privileges'] = json_encode(array(
													'srv_global' 			=> FALSE,
													'srv_start' 			=> TRUE,
													'srv_stop' 				=> TRUE,
													'srv_restart' 			=> TRUE,
													'usr_create' 			=> FALSE,
													'usr_edit' 				=> FALSE,
													'usr_edit_privileges' 	=> FALSE,
													'usr_delete' 			=> FALSE,
			));
			
			$this->users->add_user($user_data);
			
			// Сохраняем логи
			$log_data['type'] = 'reg';
			$log_data['user_name'] = $user_data['login'];
			$log_data['msg'] = 'Registration Successful';
			$this->panel_log->save_log($log_data);
			
			$this->show_message(lang('auth_registration_successful'));
			return TRUE;
			
		}
	}
	
	function recovery_password($code = FALSE)
	{
		$this->tpl_data['heading'] 	= lang('auth_title_recovery_password');
        $this->tpl_data['title'] 	= lang('auth_heading_recovery_password');

        /* Если ключ указан */
        if($code) {
			$where = array('recovery_code' => $code);
			$user_list = $this->users->get_users_list($where, 1);
			
			/* 
			 * Если запись пользователя с таким кодом имеется, то отправляем
			 * пользователю новый пароль
			 * 
			 * Если отсутствует, то как ни в чем нибывало отображаем форму
			 * восстановления пароля
			*/
			
			if(!empty($user_list)) {
				/* -------------------------------------- */
				/* Пользователь с кодом найден, шаманим   */
				/* -------------------------------------- */
				
				$this->load->model('password');
				$this->load->helper('safety');
				
				$old_password = $user_list['0']['password'];
				
				/* Генерируем новый пароль */
				$new_password = generate_code(8);
				
				$user_data['password'] = $this->password->encryption(md5($new_password), array('login' => $user_list['0']['login'],
                                                                                          'reg_date' => $user_list['0']['reg_date']));
                
                /* Сохраняем пользователя */
                $this->users->update_user($user_data, $user_list['0']['id']);
                
                // Загрузка моделей
				$this->load->library('email');
				$this->load->helper('url');
					
				$this->email->from($this->config->config['system_email'], 'АдминПанель');
				$this->email->to($user_list[0]['email']); 

				$this->email->subject(lang('auth_recovery_password'));
				
				$email_message = "Новые данные \nЛогин: " . $user_list['0']['login'] . "\nПароль: " . $new_password ;
				
				$this->email->message($email_message);	
					
				if($this->email->send()){
					$log_data['msg'] = 'Recovery Password Successful';  // Сообщение для логов
					
					// Обновляем код восстановления
					$this->users->get_user_recovery_code($user_list[0]['id']);
					
					// Пишем логи
					$log_data['type'] = 'recovery_password';
					$log_data['user_name'] = $user_list[0]['login'];
					$this->panel_log->save_log($log_data);
					
					$this->show_message(lang('auth_recovery_msg_send') . ' ' . $user_list[0]['email'], site_url('auth/in'), 'Далее');
					
					/* Пригодится для дебага */
					//echo $this->email->print_debugger();
			
					return TRUE;
					
				} else {
					/* Восстанавливаем старый пароль */
					$user_data['password'] = $old_password;
					$this->users->update_user($user_data, $user_list['0']['id']);
					
					$this->show_message(lang('auth_recovery_msg_send_error'), site_url('auth/in'), 'Далее');
					
					// Пишем логи
					$log_data['msg'] = 'Mail Send Error';				// Сообщение для логов
					$log_data['type'] = 'recovery_password';
					$log_data['user_name'] = $user_list[0]['login'];
					$log_data['log_data'] = $this->email->print_debugger();
					$this->panel_log->save_log($log_data);
					return FALSE;
				}
			}
		}
		
		/* ---------------------------------------------------------- */
		/* Код указан неверно, либо вообще не указан, отображем форму */
		/* ---------------------------------------------------------- */
        
        $this->form_validation->set_rules('login', 'логин', 'trim|max_length[12]|xss_clean');
		$this->form_validation->set_rules('email', 'email адрес', 'trim|max_length[64]|min_length[0]|valid_email|xss_clean');
        
		if ($this->form_validation->run() == FALSE){
			$this->parser->parse('recovery_password.html', $this->tpl_data);
		}else{
			
			$login = $this->input->post('login');
			$email = $this->input->post('email');
			
			if(!$login && !$email){
				$this->show_message(lang('auth_enter_login_or_email'), 'javascript:history.back()');
				return FALSE;
			}

			if($email){
				$where = array('email' => $email);
			}else{
				$where = array('login' => $login);
			}
				
			$user_list = $this->users->get_users_list($where, 1);
			
			/* Существует ли пользователь */
			if(empty($user_list)){
				$this->show_message(lang('auth_user_not_found'), 'javascript:history.back()');
				return FALSE;
			}
				
			// Получаем код восстановления
			$recovery_code = $this->users->get_user_recovery_code($user_list[0]['id']);
				
			/* -------------------------------------- */
			/* Отправляем код восстановления на почту */
			/* -------------------------------------- */

			// Загрузка моделей
			$this->load->library('email');
			$this->load->helper('url');
				
			$this->email->from($this->config->config['system_email'], 'АдминПанель');
			$this->email->to($user_list[0]['email']); 

			$this->email->subject(lang('auth_recovery_password'));
			$url_recovery = site_url('auth/recovery_password/' . $recovery_code);
			$this->email->message(lang('auth_recovery_mail_goto_link') . ': ' . $url_recovery);	
				
			if($this->email->send()){
				$this->show_message(lang('recovery_recovery_msg_accept_send') . ' ' . $user_list[0]['email'] , site_url('auth/in'), 'Далее');
				$log_data['msg'] = 'Send Recovery Code. Email: ' . $user_list[0]['email'];
			}else{
				$this->show_message(lang('auth_recovery_msg_send_error'), site_url('auth/in'), 'Далее');
				$log_data['msg'] = 'Mail Send Error';				// Сообщение для логов
			}
				
			/* Пригодится для дебага */
			//echo $this->email->print_debugger();
			
			// Сохраняем логи
			$log_data['type'] = 'recovery_password';
			$log_data['user_name'] = $user_list[0]['login'];
			$this->panel_log->save_log($log_data);
			
			
		}
	}
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */
