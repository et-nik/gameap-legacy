<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 *
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

use \Myth\Controllers\BaseController;

class Users_control extends BaseController {

    //Template
    var $tpl = array();

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
            $this->tpl['title'] 		= lang('users_title_index');
            $this->tpl['heading'] 		= lang('users_heading_index');
            $this->tpl['content'] 		= '';
            $this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
            $this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);

        }else{
            redirect('auth');
        }
    }

    // -----------------------------------------------------------------

	/**
	 * Получение данных фильтра для вставки в шаблон
	 */
	private function _get_tpl_filter($filter = false)
	{
		if (!$filter) {
			$filter = $this->users->get_filter('users_list');
		}

		$tpl['filter_login']				= isset($filter['login']) ? $filter['login'] : '';

		$tpl['filter_register_before']		= isset($filter['register_before']) && $filter['register_before']
													? unix_to_human($filter['register_before'])
													: unix_to_human(now());
		$tpl['filter_register_after']		= isset($filter['register_after']) && $filter['register_after']
													? unix_to_human($filter['register_after'])
													: '';

		$tpl['filter_last_visit_before'] 	= isset($filter['last_visit_before']) && $filter['last_visit_before']
													? unix_to_human($filter['last_visit_before'])
													: unix_to_human(now());
		$tpl['filter_last_visit_after'] 	= isset($filter['last_visit_after']) && $filter['last_visit_after']
													? unix_to_human($filter['last_visit_after'])
													: '';

		return $tpl;
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
    public function index($offset = 0)
    {
		//Проверка, есть ли права
		if(!$this->users->auth_privileges['usr_edit']){
			redirect('admin');
        }

        $filter = $this->users->get_filter('users_list');
        $this->users->set_filter($filter);

        $local_tpl = $this->_get_tpl_filter($filter);

        /* Постраничная навигация */
		$config['base_url'] = site_url('admin/users_control/index');
		$config['uri_segment'] = 4;
		$config['total_rows'] = $this->users->count_all_users();
		$config['per_page'] = 20;
		$config['full_tag_open'] = '<p id="pagination">';
		$config['full_tag_close'] = '</p>';

		$this->pagination->initialize($config);
		$local_tpl['pagination'] = $this->pagination->create_links();

		$local_tpl['users_list'] = $this->users->tpl_users_list($config['per_page'], $offset);
		$this->tpl['content'] .= $this->parser->parse('web_users/web_users_list.html', $local_tpl, true);

        $this->parser->parse('main.html', $this->tpl);
    }

    // ----------------------------------------------------------------

    /**
     * Добавление новых пользователей
     *
    */
    public function add()
    {
		//Проверка, есть ли права на добавление
		if(!$this->users->auth_privileges['usr_create']){
				redirect('admin');
		}

		$this->load->library('form_validation');
		$this->load->helper('form');

		$this->form_validation->set_rules('login', lang('login'), 'trim|required|is_unique[users.login]|max_length[32]|min_length[3]');
		$this->form_validation->set_rules('password', lang('password'), 'trim|required|max_length[64]');
		$this->form_validation->set_rules('email', 'E-Mail', 'trim|required|is_unique[users.email]|valid_email');

		$i = -1;
		foreach($this->users->all_user_privileges as $key => $value) {
			$i++;
			/* Правила проверки формы */
			$this->form_validation->set_rules($key, $value, 'trim|max_length[1]|integer');

			/* Данные для шаблона */
			if(strlen($key) > 3) {
				$local_tpl['privileges_list'][$i]['privilege_name'] = $value;
				$local_tpl['privileges_list'][$i]['privilege_option'] = form_checkbox($key, 1);
			} else {
				$local_tpl['privileges_list'][$i]['privilege_name'] = '<p class="hr">' . $value . '</p>';
				$local_tpl['privileges_list'][$i]['privilege_option'] = '&nbsp;';
			}
		}

		if ($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			//$this->tpl['content'] .= 'Ошибка добавления пользователя';
			$this->tpl['content'] .= $this->parser->parse('web_users/web_users_add.html', $local_tpl, true);
		} else {

			foreach($this->users->all_user_privileges as $key => $value) {

				if(strlen($key) > 3) {
					$new_privileges[$key] = (bool)$this->input->post($key);
				}
			}

			$sql_data['privileges'] = json_encode($new_privileges);

			$sql_data['reg_date'] = time();
			$sql_data['login'] = $this->input->post('login', true);
			$sql_data['password'] = $this->input->post('password', true);
			$sql_data['password'] = hash_password($sql_data['password']);

			if ($this->users->add_user($sql_data)) {
				$this->_show_message(lang('users_usr_add_sucessful'), site_url('admin/users_control'), lang('users_back_to_users'));
				$log_data['msg'] 			= 'Add user successed';
			} else {
				$this->_show_message('Error');
				$log_data['msg'] 			= 'Add user failed';
			}

			// Записываем логи
			$log_data['type'] 			= 'users_control';
			$log_data['command'] 		= 'add_user';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;

			$log_data['log_data'] 		= 'AdminID: ' . $this->users->auth_id
											. ' AdminName: ' . $this->users->auth_login;

			$this->panel_log->save_log($log_data);

			return;
		}

        $this->parser->parse('main.html', $this->tpl);
    }

    // ----------------------------------------------------------------

    /**
     * Привилегии пользователя
     *
    */
    public function servers_privileges($user_id = null, $server_id = null)
    {
		$user_id 	= (int)$user_id;
		$server_id 	= (int)$server_id;

		$this->tpl['heading'] = lang('users_heading_index');

		//Проверка, есть ли права на добавление
		if (!$this->users->auth_privileges['usr_edit_privileges']) {
				$this->tpl['content'] .= lang('users_no_privileges_for_edit_privileges');
		} else {

			if (!$user_id) {
				$this->_show_message(lang('users_empty_id'));
				return false;
			}

			if (!$this->users->user_live($user_id, 'ID')) {
				$this->_show_message(lang('users_id_unavailable'));
				return false;
			}

			$local_tpl = $this->users->tpl_userdata($user_id);

			$this->tpl['heading'] .= '&nbsp;::&nbsp;' . $local_tpl['user_login'];

			/*
			 * Не указан ID сервера
			 * Показываем список серверов
			*/
			if (!$server_id) {

				$this->load->model('servers');

				$this->servers->get_server_list(false, false, array('enabled' => '1'));
				$local_tpl['servers_list'] = $this->servers->tpl_data();

				$this->tpl['content'] .= $this->parser->parse('web_users/select_server.html', $local_tpl, true);

				$this->parser->parse('main.html', $this->tpl);
				return false;
			}

			$user_privileges = $this->users->get_server_privileges($server_id, $user_id, true);

			$num = 0;
			foreach ($user_privileges as $privilege_name => $privilege_value)
			{
				$local_tpl['privilege_list'][$num]['form_checkbox'] 	= form_checkbox($privilege_name, '1', $privilege_value);
				$local_tpl['privilege_list'][$num]['human_name'] 		= $this->users->all_privileges[$privilege_name];

				$num ++;
			}

			$local_tpl['user_id']		= $user_id;		// Костыль
			$local_tpl['server_id'] 	= $server_id;

			$this->tpl['content'] .= $this->parser->parse('web_users/web_users_privileges.html', $local_tpl, true);
		}

        $this->parser->parse('main.html', $this->tpl);
    }


    // ----------------------------------------------------------------

    /**
     * Сохранить привилегииs
     *
    */
    public function save_servers_privileges($user_id = null, $server_id = null)
    {
		$user_id = (int)$user_id;
		$server_id = (int)$server_id;

		if (!is_array($this->input->post())) {
			redirect('admin/users_control/servers_privileges/' . $user_id . '/' . $server_id);
		}

		//Проверка, есть ли права на редактирование привилегий
		if (!$this->users->auth_privileges['usr_edit_privileges']) {
			$this->tpl['content'] .= lang('users_no_privileges_for_edit_privileges');
		} else {

			/* Получаем данные редактируемого пользователя, но записываем их лишь в переменную $user_data */
			if (!$user_data = $this->users->get_user_data($user_id)) {
				$this->_show_message(lang('users_id_unavailable'));
				return false;
			}

			 /* В целях безопасности, редактировать администратора может только он сам */
			if($user_data['is_admin'] && $user_data['id'] != $this->users->auth_id){

				$local_tpl['message'] = lang('users_edit_admin_denied');

				$local_tpl['link'] = site_url('admin/users_control');
				$local_tpl['back_link_txt'] = 'Вернуться';

				$this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
				$this->parser->parse('main.html', $this->tpl);
				return false;
			}

			if (!$user_id) {
				$this->_show_message(lang('users_empty_id'));
				return false;
			}

			if(!$server_id){
				$this->_show_message(lang('users_empty_id_server'));
				return false;
			}

			$local_tpl = $this->users->tpl_userdata($user_id);
			$this->tpl['heading'] .= '&nbsp;::&nbsp;' . $local_tpl['user_login'];

			// Сохранение привилегии
			foreach ($this->users->all_privileges as $privilege_name => $privilege_human_name)
			{
				$privilege_value = (bool)$this->input->post($privilege_name, true);
				$this->users->set_server_privileges($privilege_name, $privilege_value, $server_id, $user_id);
			}

			if ($this->users->update_server_privileges($user_id, $server_id)) {
				$log_data['msg'] 			= 'Save server privileges successed';
				$this->_show_message(lang('users_srv_privileges_saved'), site_url('admin/users_control'), lang('users_back_to_users'));
			} else {
				$log_data['msg'] 			= 'Save server privileges failed';
				$this->_show_message('Error');
			}

			// Записываем логи
			$log_data['type'] 			= 'users_control';
			$log_data['command'] 		= 'edit_privileges';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;

			$log_data['log_data'] 		= 'UserID: ' . $user_id
											. ' AdminID: ' . $this->users->auth_id
											. ' AdminName: ' . $this->users->auth_login;

			$this->panel_log->save_log($log_data);

			return;
		}

        $this->parser->parse('main.html', $this->tpl);
    }

    // ----------------------------------------------------------------

    /**
     * Редактирование пользователя
     *
    */
    public function edit($user_id = false)
    {
		$user_id = (int)$user_id;

		if (!$user_data = $this->users->get_user_data($user_id)) {
			$this->_show_message(lang('users_id_unavailable'));
			return false;
		}

		// Проверка, есть ли права на добавление
		if (! $this->users->auth_privileges['usr_edit']) {
			$this->_show_message(lang('users_no_privileges_for_edit'));
			return false;
		}

		if (! $user_id) {
			$this->_show_message(lang('users_empty_id'));
			return false;
		}

		$local_tpl = array();

		/* В целях безопасности, редактировать администратора может только он сам */
		if ($user_data['is_admin'] && $user_data['id'] != $this->users->auth_id) {
			$this->_show_message(lang('users_edit_admin_denied'), site_url('admin/users_control'));
			return false;
		}

		$this->load->library('form_validation');

		$this->form_validation->set_rules('name', lang('name'), 'trim');
		$this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email');
		$this->form_validation->set_rules('new_password', lang('password'), 'trim');
		$this->form_validation->set_rules('group', lang('group'), 'integer|required');

		if (!$this->form_validation->run()) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			$local_tpl = $this->users->tpl_userdata($user_id, $user_data);
			$local_tpl['groups_dropdown'] = form_dropdown('group', $this->users->users_groups, $user_data['group']);

			$this->tpl['content'] .= $this->parser->parse('web_users/user_edit.html', $local_tpl, true);

		} else {

			if ($this->input->post('new_password') !== ''){
				$password_encrypt = $this->input->post('new_password', true);
				$password_encrypt = hash_password($password_encrypt);
				$user_new_data['password'] = $password_encrypt;
			}

			$user_new_data['name'] 		= $this->input->post('name', true);
			$user_new_data['email'] 	= $this->input->post('email', true);
			$user_new_data['group'] 	= $this->input->post('group', true);

			if ($user_new_data['group'] == 100) {
				$user_new_data['is_admin'] = 1;
			}

			if ($this->users->update_user($user_new_data, $user_data['id'])) {
				$log_data['msg'] 			= 'Update user successed';
				$this->_show_message(lang('users_usr_data_saved'), site_url('admin/users_control'), lang('users_back_to_users'));
			} else {
				$log_data['msg'] 			= 'Update user failed';
				$this->_show_message('Error');
			}

			// Записываем логи
			$log_data['type'] 			= 'users_control';
			$log_data['command'] 		= 'edit_user';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;

			$log_data['log_data'] 		= 'UserID: ' . $user_id
											. ' UserName: ' . $user_data['login']
											. ' AdminID: ' . $this->users->auth_id
											. ' AdminName: ' . $this->users->auth_login;

			$this->panel_log->save_log($log_data);

			return true;
		}

        $this->parser->parse('main.html', $this->tpl);
    }

    // ----------------------------------------------------------------

    /**
     * Удаление пользователя
     *
    */
    public function delete($user_id = null, $confirm = false)
    {
		$user_id = (int)$user_id;

		//Проверка, есть ли права на добавление
		if (!$this->users->auth_privileges['usr_delete']) {
				$this->tpl['content'] .= lang('users_no_privileges_for_delete');
		} else {

			if($confirm == $this->security->get_csrf_hash()) {
				if($user_id) {

					 /* Получаем данные редактируемого пользователя, но записываем их лишь в переменную $user_data */
					$user_data = $this->users->get_user_data($user_id);

					/* В целях безопасности, администратора нельзя удалить */
					if ($user_data['is_admin']) {

						$local_tpl['message'] = lang('users_delete_admin_denied');

						$local_tpl['link'] = site_url('admin/users_control');
						$local_tpl['back_link_txt'] = lang('back');

						$this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
						$this->parser->parse('main.html', $this->tpl);
						return false;
					}

					if ($this->users->delete_user($user_id)) {
						$log_data['msg'] 			= 'Delete user successed';
						$this->_show_message(lang('users_usr_deleted'), site_url('admin/users_control'));
					} else {
						$log_data['msg'] 			= 'Delete user failed';
						$this->_show_message('Error');
					}

					// Записываем логи
					$log_data['type'] 			= 'users_control';
					$log_data['command'] 		= 'delete_user';
					$log_data['server_id'] 		= 0;
					$log_data['user_name'] 		= $this->users->auth_login;
					$log_data['log_data'] 		= 'UserID: ' . $user_id
													. ' UserName: ' . $user_data['login']
													. ' AdminID: ' . $this->users->auth_id
													. ' AdminName: ' . $this->users->auth_login;

					$this->panel_log->save_log($log_data);

					return true;
				}
			} else {
				/* Пользователь не подвердил намерения */
				$confirm_tpl['message'] 		= lang('users_delete_confirm');
				$confirm_tpl['confirmed_url'] 	= site_url('admin/users_control/delete/' . $user_id . '/' . $this->security->get_csrf_hash());
				$this->tpl['content'] 		.= $this->parser->parse('confirm.html', $confirm_tpl, true);
			}

		}

        $this->parser->parse('main.html', $this->tpl);
    }

    // ----------------------------------------------------------------

    /**
     * Редактирование пользователя
     *
    */
    public function base_privileges($user_id = false)
    {
		//Проверка, есть ли права на добавление
		if(!$this->users->auth_privileges['usr_edit_privileges']) {
			$this->_show_message(lang('users_no_privileges_for_edit_privileges'), site_url('admin'));
			return false;
		}

		if(!$user_id) {
			$this->_show_message(lang('users_empty_id'), site_url('admin/users_control'));
			return false;
		}

		if(!$this->users->get_user_data($user_id)){
			$this->_show_message(lang('users_id_unavailable'), site_url('admin/users_control'));
			return false;
		}

		/* Задание переменных */
		$local_tpl = array();
		$local_tpl['user_id'] = $user_id;

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
				$local_tpl['privileges_list'][$i]['privilege_name'] = $value;
				$local_tpl['privileges_list'][$i]['privilege_option'] = form_checkbox($key, 1, $this->users->user_privileges[$key]);
			} else {
				$local_tpl['privileges_list'][$i]['privilege_name'] = '<p class="hr">' . $value . '</p>';
				$local_tpl['privileges_list'][$i]['privilege_option'] = '&nbsp;';
			}
		}

        if ($this->form_validation->run() == false) {

			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}

			$this->tpl['content'] = $this->parser->parse('web_users/base_privileges_edit.html', $local_tpl, true);
		} else {
			foreach($this->users->all_user_privileges as $key => $value) {

				if(strlen($key) > 3) {
					$new_privileges[$key] = (bool)$this->input->post($key);
				}
			}

			$user_data['privileges'] = json_encode($new_privileges);

			if($this->users->update_user($user_data, $user_id)) {
				$this->_show_message(lang('users_base_privileges_saved'), site_url('admin/users_control') , lang('next'));

				/* Отправляем информацию админам */
				$subject = lang('users_mail_subject_change_privileges');
				$message = lang('users_mail_message_change_privileges', $this->users->user_data['login'], $this->users->auth_data['login']);
				$this->users->admin_msg($subject, $message);
				$log_data['msg'] 			= lang('users_mail_subject_change_privileges');
			} else {
				$log_data['msg'] = 'Change privileges failed';
				$this->_show_message(lang('unknown_error'));
			}

			// Записываем логи
			$log_data['type'] 			= 'users_control';
			$log_data['command'] 		= 'save_privileges';
			$log_data['server_id'] 		= 0;
			$log_data['user_name'] 		= $this->users->auth_login;
			$log_data['log_data'] 		= 'UserID: ' . $user_id
											. ' AdminID: ' . $this->users->auth_id
											. ' AdminName: ' . $this->users->auth_login;
			$this->panel_log->save_log($log_data);

			return;
		}

        $this->parser->parse('main.html', $this->tpl);
    }
}
