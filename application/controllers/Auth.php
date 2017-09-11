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

class Auth extends BaseController {
	
	public $tpl = array();
	public $user_data = array();
	
	// Список запрещенных логинов при регистрации
	private $_denied_logins = array('administrator', 'admin', 'system', 'gameap', 'root', 'scripts');
	
	// -----------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		
		$this->load->library('form_validation');
		$this->load->model('users');
		$this->load->helper('safety');
		
		$this->lang->load('auth');
		
		$this->tpl['menu'] = '';
		$this->tpl['profile'] = '';
		$this->tpl['content'] = '';
		
		$this->tpl['title'] 	= lang('auth_title_index');
		$this->tpl['heading'] 	= lang('auth_heading');
	}
	
	// -----------------------------------------------------------------
	
	// Отображение информационного сообщения
	function _show_message($message = false, $link = false, $link_text = false)
	{
		
		if (!$message) {
			$message = lang('error');
		}
		
		if (!$link) {
			$link = site_url('auth/in');
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
	 * Проверка капчи
	*/ 
	private function _check_captcha()
	{
		$this->load->driver('captcha');
		return $this->captcha->check();
	}
	
	// -----------------------------------------------------------------
	
	private function _create_captcha()
	{
		$this->load->driver('captcha');
		return $this->captcha->get_html();
	}
	
	// -----------------------------------------------------------------

	/**
	 * Главная страница
	*/ 
	public function index()
	{
		// Обычно библиотека запущена
		// $this->load->library('parser');
		
		$this->tpl['code'] = '';

		/* Проверяем пользователя */
		if (!$this->users->check_user()) {
			redirect('auth/in');
		} else {
			redirect('admin');
		}
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Авторизация
	 * 
	 * @param code - необязательный параметр для авторизации пользователей, 
	 * подвергшихся брутфорс атаке.
	*/
	public function in($code = false)
	{
		/* Загрузка модели проверки пользователей */
		//$this->load->model('check_user');

		if($code) {
			$this->tpl['code'] = $code;
		} else {
			$this->tpl['code'] = '';
		}
		
		$this->tpl['heading'] 	= lang('auth_heading');
		$this->tpl['title'] 	= lang('auth_title_in');
		
		$this->tpl['captcha'] = '';
		
		$check = $this->users->check_user();
			
		if ($check) {
			/* Перенаправляем пользователя в нужное место, 
			 * если заданы нужные куки location_page 
			*/
			if($location_page = $this->input->cookie('location_page', true)){
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
		}
		
		/* Капча от брутфорса*/
		if(count($this->panel_log->get_log(array('date >' => time() - 300, 'ip' => $_SERVER['REMOTE_ADDR'], 'msg' => 'Authorization Failed'))) > 3) {
			$captcha_login = true;
			$this->tpl['captcha'] = $this->_create_captcha();
		} else {
			$captcha_login = false;
		}

		$this->form_validation->set_rules('user_login', 'Username', 'trim|required|max_length[32]');
		$this->form_validation->set_rules('user_password', 'Password', 'trim|required');

		/* Проверка формы */
		if ($this->form_validation->run() == false){
			//$this->tpl['content'] .= $this->parser->parse('login.html', $this->tpl);

		} else {

			$user_data['login'] = $this->input->post('user_login', true);
			$user_data['password'] = $this->input->post('user_password', true);
			
			if ($captcha_login) {
				// Проверяем капчу
				if (!$this->_check_captcha()) {
					$this->_show_message(lang('auth_captcha_enter_wrong'), site_url('auth/in'));
					return false;
				}
			}

			/* Защита от брутфорса по одному ip */
			if(count($this->panel_log->get_log(array('date >' => time() - 300, 'ip' => $_SERVER['REMOTE_ADDR'], 'msg' => 'Authorization Failed'))) > 5 ) {
				$this->_show_message(lang('auth_repeat_enter_wrong_password'));
				return false;
			}

			/* Защита от брутфорса для определенного пользователя */
			if(count($this->panel_log->get_log(array('date >' => time() - 300, 'user_name' => $user_data['login'], 'msg' => 'Authorization Failed'))) > 10 ) {
				if($this->users->user_live($user_data['login'], 'LOGIN')) {
					$code_is_true = false;

					/* Проверка правильности кода */
					if($code) {
						$where = array('recovery_code' => $code);
						$user_list = $this->users->get_users_list($where, 1);
						
						if(!empty($user_list)) {
							// Код верный, ничего не предпринимаем, не блокируем и разрешаем
							// пользователю авторизоваться, т.к. он подтвердил всё
							$code_is_true = true;
						} else {
							$code_is_true = false;
						}
					}
					
					if(!$code_is_true) {
						/* Код неверный, поэтому делаем всё как обычно */
						
						if(count($this->panel_log->get_log(array('date >' => time() - 86400, 'user_name' => $user_data['login'], 'msg' => 'Bruteforce. Reset code sended'))) < 1 ) {
							/* Количество отправленных писем < 1, значит отправляем пользователю письмо */
							
							$user_code = $this->users->get_user_recovery_code(array('login' => $user_data['login']));
							$user_data = $this->users->get_user_data(array('login' => $user_data['login']));

                            $result = $this->users->send_mail(
                                lang('auth_account_unblock'),
                                $email_message = lang('auth_mail_goto_link') . site_url() . 'auth/in/' . $user_code ,
                                $user_data['login']['id']
                            );
			
							if($result) {
								$this->_show_message(lang('auth_bruteforce_email_send'));
								$log_data['msg'] = 'Bruteforce. Reset code sended';
							} else {
								$this->_show_message(lang('auth_bruteforce_authorization_error'));
								$log_data['msg'] = 'Bruteforce. Reset code send failure';
							}

							// Сохраняем логи отправки email, чтобы не отправлять 1000 раз
							$log_data['type'] = 'auth';
							$log_data['user_name'] = $user_data['login'];
							$this->panel_log->save_log($log_data);
							return false;
						} else {
							$this->_show_message(lang('auth_bruteforce_email_send'));
							return false;
						}
						// Код неверный
					}
					// Пользователь существует
				}
			}
			
			if (filter_var($user_data['login'], FILTER_VALIDATE_EMAIL)) {
				$check = $this->users->user_auth($user_data['login'], $user_data['password'], 'email');
			}
			else {
				$check = $this->users->user_auth($user_data['login'], $user_data['password']);
			}

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
				'expire' => 86500 * 7, // Одна неделя
				'path'   => '/',
				);

				$this->input->set_cookie($cookie);

				$cookie = array(
					'name'   => 'hash',
					'value'  => $hash,
					'expire' => 86500 * 7, // Одна неделя
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
				
				if($location_page = $this->input->cookie('location_page', true)){
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
				
				$this->tpl['content'] = '<p>'. lang('auth_authorization_successful') .'</p>';
				$this->tpl['content'] .= '<a href=' . site_url('admin') . '>' . lang('auth_goto_server_control') . '</a>';
			} else {
				$this->tpl['content'] = '<p>' . lang('auth_authorization_failed') . '</p>';
				//$this->tpl['content'] .= $this->parser->parse('login.html', $this->tpl, true);
				
				// Сохраняем логи
				$log_data['type'] = 'auth';
				$log_data['user_name'] = $this->input->post('user_login', true);
				$log_data['msg'] = 'Authorization Failed';
				$this->panel_log->save_log($log_data);
			}
		}
		/* Конец проверки формы*/

		$this->parser->parse('login.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------------------------------

	/**
	 * Выход
	*/ 
	public function out()
	{
		$this->tpl['menu'] = '';
		$this->tpl['title'] 		= lang('auth_title_out');
		$this->tpl['heading'] 		= lang('auth_heading_out');
		
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
		
		$local_tpl['message'] 			= lang('auth_quit_success');
		$local_tpl['link'] 			= site_url();
		$local_tpl['back_link_txt'] 	= lang('auth_goto_main');
		$this->tpl['content'] 			= $this->parser->parse('info.html', $local_tpl, true);

		$this->parser->parse('main.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------

	/**
	 * Регистрация пользователя
	*/ 
	function register()
	{
		$this->tpl['heading'] 	= lang('auth_title_register');
		$this->tpl['title'] 	= lang('auth_heading_register');
		
		if(!$this->config->config['register_users']) {
			$this->_show_message(lang('auth_registration_closed'), site_url());
			return false;
		}

		$this->form_validation->set_rules('login', 'логин', 'trim|required|alpha_dash|is_unique[users.login]|max_length[32]');
		$this->form_validation->set_rules('password', 'пароль', 'trim|required|max_length[64]|matches[passconf]');
		$this->form_validation->set_rules('passconf', 'подтверждение пароля', 'trim|required|max_length[64]');
		$this->form_validation->set_rules('email', 'email адрес', 'trim|required|is_unique[users.email]|max_length[64]|valid_email');
		
		//~ $this->form_validation->set_rules('image', 'капча', 'trim|required|max_length[12]');
		
		/* Проверка формы */
		if ($this->form_validation->run() == false) {
			$this->tpl['captcha'] = $this->_create_captcha();	
			$this->parser->parse('register.html', $this->tpl);
		} else {
			if (in_array($this->input->post('login'), $this->_denied_logins)) {
				$this->_show_message('Unavailable login', site_url('auth/register'));
				return false;
			}
			
			// Проверяем, правильно ли введено сообщение
			if (!$this->_check_captcha()) {
				// Сохраняем логи
				$log_data['type'] = 'reg';
				$log_data['user_name'] = $this->input->post('login');
				$log_data['msg'] = 'Registration Failed';
				$this->panel_log->save_log($log_data);
				
				$this->_show_message(lang('auth_captcha_enter_wrong'), site_url('auth/register'));
				return false;
			}
			
			$user_data['email'] = $this->input->post('email', true);
			$user_data['reg_date'] = time();
			 
			$user_data['login'] 		= $this->input->post('login');
			$user_data['password'] 		= $this->input->post('password');
			$user_data['password'] 		= hash_password($user_data['password']);
			
			$user_data['privileges'] = json_encode(array(
													'srv_global' 			=> false,
													'srv_start' 			=> true,
													'srv_stop' 				=> true,
													'srv_restart' 			=> true,
													'usr_create' 			=> false,
													'usr_edit' 				=> false,
													'usr_edit_privileges' 	=> false,
													'usr_delete' 			=> false,
			));
			
			$this->users->add_user($user_data);
			
			// Сохраняем логи
			$log_data['type'] = 'reg';
			$log_data['user_name'] = $user_data['login'];
			$log_data['msg'] = 'Registration Successful';
			$this->panel_log->save_log($log_data);
			
			$this->_show_message(lang('auth_registration_successful'));
			return true;
			
		}
	}
	
	
	// -----------------------------------------------------------------

	/**
	 * Восстановление пароля
     * TODO: REFACTORING NEEDED!
	*/ 
	function recovery_password($code = false)
	{
		$this->tpl['heading'] 	= lang('auth_title_recovery_password');
		$this->tpl['title'] 	= lang('auth_heading_recovery_password');

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
				
				$this->load->helper('safety');
				
				$old_password = $user_list['0']['password'];
				
				/* Генерируем новый пароль */
				$new_password = generate_code(8);
				
				$user_data['password'] = hash_password($new_password);
				
				/* Сохраняем пользователя */
				$this->users->update_user($user_data, $user_list['0']['id']);
				
				// Загрузка моделей
				$this->load->helper('url');

                $email_message = "Новые данные \nЛогин: " . $user_list['0']['login'] . "\nПароль: " . $new_password ;
				$result = $this->users->send_mail(lang('auth_recovery_password'), $email_message, $user_list['0']['id']);
					
				if($result){
					$log_data['msg'] = 'Recovery Password Successful';  // Сообщение для логов
					
					// Обновляем код восстановления
					$this->users->set_user_recovery_code($user_list[0]['id']);
					
					// Пишем логи
					$log_data['type'] = 'recovery_password';
					$log_data['user_name'] = $user_list[0]['login'];
					$this->panel_log->save_log($log_data);
					
					$this->_show_message(lang('auth_recovery_msg_send') . ' ' . $user_list[0]['email'], site_url('auth/in'), 'Далее');

					return true;
					
				} else {
					/* Восстанавливаем старый пароль */
					$user_data['password'] = $old_password;
					$this->users->update_user($user_data, $user_list['0']['id']);
					
					$this->_show_message(lang('auth_recovery_msg_send_error'), site_url('auth/in'), 'Далее');
					
					// Пишем логи
					$log_data['msg'] = 'Mail Send Error';				// Сообщение для логов
					$log_data['type'] = 'recovery_password';
					$log_data['user_name'] = $user_list[0]['login'];
					$log_data['log_data'] = "";
					$this->panel_log->save_log($log_data);
					return false;
				}
			}
		}
		
		/* ---------------------------------------------------------- */
		/* Код указан неверно, либо вообще не указан, отображем форму */
		/* ---------------------------------------------------------- */
		
		$this->form_validation->set_rules('login', 'логин', 'trim|max_length[12]');
		$this->form_validation->set_rules('email', 'email адрес', 'trim|max_length[64]|min_length[0]|valid_email');
		
		if ($this->form_validation->run() == false) {
			$this->parser->parse('recovery_password.html', $this->tpl);
		} else {
			
			$login = $this->input->post('login');
			$email = $this->input->post('email');
			
			if(!$login && !$email){
				$this->_show_message(lang('auth_enter_login_or_email'), 'javascript:history.back()');
				return false;
			}

			if($email){
				$where = array('email' => $email);
			}else{
				$where = array('login' => $login);
			}
				
			$user_list = $this->users->get_users_list($where, 1);
			
			/* Существует ли пользователь */
			if(empty($user_list)){
				$this->_show_message(lang('auth_user_not_found'), 'javascript:history.back()');
				return false;
			}
				
			// Получаем код восстановления
			$recovery_code = $this->users->set_user_recovery_code($user_list[0]['id']);
				
			/* -------------------------------------- */
			/* Отправляем код восстановления на почту */
			/* -------------------------------------- */

			// Загрузка моделей
			$this->load->helper('url');

			if(count($this->panel_log->get_log(array('date >' => time() - 86400, 'user_name' => $user_list[0]['login'], 'msg' => 'Send Recovery Code. Email: ' . $user_list[0]['email']))) < 3 ) {

                $url_recovery = site_url('auth/recovery_password/' . $recovery_code);

                $result = $this->users->send_mail(
                    lang('auth_recovery_password'),
                    lang('auth_recovery_mail_goto_link') . ': ' . $url_recovery,
                    $user_list['0']['id']
                );

				if($result){
					$this->_show_message(lang('recovery_recovery_msg_accept_send') . ' ' . $user_list[0]['email'] , site_url('auth/in'), lang('next'));
					$log_data['msg'] = 'Send Recovery Code. Email: ' . $user_list[0]['email'];
				}else{
					$this->_show_message(lang('auth_recovery_msg_send_error'), site_url('auth/in'), 'Далее');
					$log_data['msg'] = 'Mail Send Error';				// Сообщение для логов
				}

				// Сохраняем логи
				$log_data['type'] = 'recovery_password';
				$log_data['user_name'] = $user_list[0]['login'];
				$this->panel_log->save_log($log_data);
				
			} else {
				// Письмо уже отправлено ранее, несколько писем лучше не отправлять
				$this->_show_message(lang('recovery_recovery_msg_accept_send') . ' ' . $user_list[0]['email'] , site_url('auth/in'), lang('next'));
				return false;
			}
			
			
		}
	}
}

/* End of file auth.php */
/* Location: ./application/controllers/auth.php */
