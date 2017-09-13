<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
*/

use Myth\Mail\BaseMailer;

class Users extends CI_Model {

	/* Данные авторизованного пользователя */
	var $auth_id 		= false;
	var $auth_login 	= false;

    var $auth_privileges 			= array();	// Базовые привилегии
    var $auth_servers_privileges 	= array();	// Привилегии на отдельные серверы
    var $auth_data 					= array();	// Данные пользователя

    /* Данные пользователя */
    var $user_id 		= false;
    var $user_login		= false;
    var $user_password	= false;

    var $tpl_data;							// Данные для шаблона

    var $user_privileges 		= array();	// Базовые привилегии
    var $servers_privileges 	= array();	// Привилегии на отдельные серверы
    var $user_data 				= array();	// Данные пользователя

    /* Фильтр списка пользователей */
    private $_filter_users_list	= array(
        'login' 			=> false,

        'register_before' 	=> false,
        'register_after' 	=> false,

        'last_visit_before' => false,
        'last_visit_after' 	=> false,
    );

	/* Массив с id пользователей, которые будут получены
	 * функцией get_users_list
	 */
	private $_where_in 			= array();

    /* Списки и пользователи которые получает авторизованный пользователь */
    var $users_list = array();				// Список пользователей

    private $_set_privileges	= array();

    /* Все базовые привилегии */
    var $all_user_privileges = array(
		'srv'					=> '{lang_base_privileges_srv}',					// Привилегии на серверы
		'srv_global' 			=> '{lang_base_privileges_srv_global}',				// Глобальные серверные права
		'srv_start' 			=> '{lang_base_privileges_srv_start}',				// Запуск серверов
		'srv_stop' 				=> '{lang_base_privileges_srv_stop}',				// Остановка серверов
		'srv_restart' 			=> '{lang_base_privileges_srv_restart}',			// Перезапуск серверов

		'usr'					=> '{lang_base_privileges_usr}',					// Привилегии на пользователей
		'usr_create' 			=> '{lang_base_privileges_usr_create}',				// Создание пользователей
		'usr_edit' 				=> '{lang_base_privileges_usr_edit}',				// Редактирование пользователей
		'usr_edit_privileges' 	=> '{lang_base_privileges_usr_edit_privileges}',	// Редактирование привилегий пользователей
		'usr_delete' 			=> '{lang_base_privileges_usr_delete}',				// Удаление пользователей
	);

    // Все серверные привилегии
    var $all_privileges = array(
		'VIEW' 					=> '{lang_servers_privileges_view}',				// Отображение сервера в списке
		'RCON_SEND' 			=> '{lang_servers_privileges_rcon_send}',			// Отправка ркон команд
		'CHANGE_RCON' 			=> '{lang_servers_privileges_change_rcon}',			// Смена пароля
		'FAST_RCON' 			=> '{lang_servers_privileges_fast_rcon}',			// Fast rcon
		'PLAYERS_KICK' 			=> '{lang_servers_privileges_players_kick}',		// Кик игроков
		'PLAYERS_BAN' 			=> '{lang_servers_privileges_players_ban}',			// Бан игроков
		'PLAYERS_CH_NAME' 		=> '{lang_servers_privileges_players_chname}',		// Смена имени игрокам
		'CHANGE_MAP' 			=> '{lang_servers_privileges_change_map}',			// Смена карты
		'SERVER_START' 			=> '{lang_servers_privileges_start}',				// Старт сервера
		'SERVER_STOP' 			=> '{lang_servers_privileges_stop}',				// Остановка сервера
		'SERVER_RESTART' 		=> '{lang_servers_privileges_restart}',				// Перезапуск сервера
		'SERVER_SOFT_RESTART' 	=> '{lang_servers_privileges_soft_restart}',		// Мягкий перезапуск
		'SERVER_CHAT_MSG' 		=> '{lang_servers_privileges_chat_msg}',			// Сообщение в чат
		'SERVER_SET_PASSWORD' 	=> '{lang_servers_privileges_set_password}',		// Задание пароля на сервер
		'SERVER_UPDATE' 		=> '{lang_servers_privileges_update}',				// Обновление сервера
		'SERVER_SETTINGS' 		=> '{lang_servers_privileges_settings}',			// Настройки
		'CONSOLE_VIEW' 			=> '{lang_servers_privileges_console_view}',		// Просмотр консоли
		'TASK_MANAGE' 			=> '{lang_servers_privileges_task_manage}',			// Управление заданиями
		'UPLOAD_CONTENTS' 		=> '{lang_servers_privileges_upload_contents}',		// Загрузка контента
		'CHANGE_CONFIG'			=> '{lang_servers_privileges_change_config}',		// Редактирование конфигов
		'LOGS_VIEW' 			=> '{lang_servers_privileges_log_view}',			// Просмотр логов
    );

    // Группы пользователей
    var $users_groups = array(
		1 						=> '{lang_users_group_user}',
		10 						=> '{lang_users_group_content_manager}',

		50 						=> '{lang_users_group_support1}',
		51 						=> '{lang_users_group_support2}',
		52 						=> '{lang_users_group_support3}',

		90 						=> '{lang_users_group_server_manager}',
		100 					=> '{lang_users_group_admin}',
    );

	// ----------------------------------------------------------------

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->load->helper('safety');
        $this->load->helper('date');
        $this->load->library('encrypt');
    }

    // ----------------------------------------------------------------

	private function _decode_servers_privileges($privileges_json)
    {
		$servers_privileges = json_decode($privileges_json, true);

		foreach($this->all_privileges as $key => $value) {
			$servers_privileges[$key] = isset($servers_privileges[$key]) ? $servers_privileges[$key] : 0;
		}

		return $servers_privileges;
	}

	// ----------------------------------------------------------------

	private function _decode_base_privileges($privileges_json)
    {
		$base_privileges = json_decode($privileges_json, true);

		foreach($this->all_user_privileges as $key => $value) {
			/*
			 * key в привилегиях меньше 3х знаков
			 * используется для обозначения категории
			*/
			if(strlen($key) > 3) {
				$base_privileges[$key] = isset($base_privileges[$key]) ? $base_privileges[$key] : 0;
			}
		}

		return $base_privileges;
	}

	// ----------------------------------------------------------------

	private function _get_server_privileges($server_id, $servers_privileges = false)
	{
		if (!$servers_privileges) {
			$servers_privileges =& $this->servers_privileges;
		}

		if (isset($servers_privileges[$server_id])) {
			return $servers_privileges[$server_id];
		}

		foreach ($this->all_privileges as $key => $value) {
			$servers_privileges[$server_id][$key] = 0;
		}

		return $servers_privileges[$server_id];
	}

	// ----------------------------------------------------------------

	/**
	 * Проверка подсети админа
	 */
	private function _check_subnet()
	{
		if (is_array($this->config->config['admin_ip'])) {
			/* В конфигурации задан список белых IP
			 * Если IP, с которого происходит авторизация нет в белом списке,
			 * то авторизация неудачна
			 */
			if (!in_subnet($this->input->ip_address(), $this->config->config['admin_ip'])) {
				return false;
			}


		} else {
			$ip_ex = explode(' ', $this->config->config['admin_ip']);

			/* В конфигурации задан список белых IP
			 * Если IP, с которого происходит авторизация нет в белом списке,
			 * то авторизация неудачна
			 */
			if (!in_subnet($this->input->ip_address(), $ip_ex)) {
				return false;
			}
		}

		return true;
	}

    // ----------------------------------------------------------------

    /**
     * Проверка пользователя
     *
     * @return bool
    */
    function check_user()
    {
        $user_id    = (int)$this->input->cookie('user_id', true);
        $user_hash  = $this->input->cookie('hash', true);

        if ($this->config->item('auth_check_ip')) {
			$md5_ipua = md5($this->input->ip_address() . $this->input->user_agent());
		} else {
			$md5_ipua = md5($this->input->user_agent());
		}

        if ($user_id && $user_hash) {
            $query = $this->db->get_where('users', array('id' => $user_id, 'hash' => $user_hash . $md5_ipua), 1);
            $this->auth_data = $query->row_array();
        } else {
            return false;
        }

        if ($query->num_rows() > 0) {

			 // Проверка на разрешенные IP
            if ($this->auth_data['is_admin'] && isset($this->config->config['admin_ip'])) {
				if (!$this->_check_subnet()) {
					return false;
				}
			}

			$this->auth_id 						= (int) $user_id;
			$this->auth_login 					= $this->auth_data['login'];
			$this->auth_data['balance'] 		= (int)$this->encrypt->decode($this->auth_data['balance']);
			$this->auth_data['modules_data'] 	= (isset($this->auth_data['modules_data'])) ? json_decode($this->auth_data['modules_data'], true) : array();
			$this->auth_data['notices'] 		= (isset($this->auth_data['notices'])) ? json_decode($this->auth_data['notices'], true) : array();

			$this->auth_data['privileges'] 			= $this->_decode_base_privileges($this->auth_data['privileges']);
			//~ $this->auth_data['servers_privileges'] 	= $this->_decode_servers_privileges($this->auth_data['servers_privileges']);

			// Что-то вроде костыля
			$this->auth_privileges = $this->auth_data['privileges'];
			$this->auth_servers_privileges = $this->get_server_privileges();

			// TODO: Save last_auth to cache
			// Обновление данных авторизации
			$this->update_user(array('last_auth' => now()));

            return true;
        } else {
            return false;
        }
    }

    // ----------------------------------------------------------------

    /**
     * Подсчет пользователей в базе. Учитываются фильтры
     */
    function count_all_users()
    {
		!$this->_filter_users_list['login'] OR $this->db->like('login', $this->_filter_users_list['login']);

		!$this->_filter_users_list['register_before'] 	OR $this->db->where('reg_date <', $this->_filter_users_list['register_before']);
		!$this->_filter_users_list['register_after'] 	OR $this->db->where('reg_date >', $this->_filter_users_list['register_after']);

		!$this->_filter_users_list['last_visit_before'] OR $this->db->where('last_auth <', $this->_filter_users_list['last_visit_before']);
		!$this->_filter_users_list['last_visit_after'] 	OR $this->db->where('last_auth >', $this->_filter_users_list['last_visit_after']);

		return $this->db->count_all_results('users');
	}

    // ----------------------------------------------------------------

    /**
     * Авторизация пользователя
     * Проверка логина и пароля
     *
     * @param string		логин
     * @param string		пароль (не хеш)
     * @param string		тип авторизации (по логину или по email)
     * @return int|bool		возвращает ID пользователя, в случае успеха и false в случае неудачи.
    */
    function user_auth($user_login = '', $user_password = '', $type = 'login')
    {
        if(!$user_login OR !$user_password){
            return false;
        }

        switch ($type) {
			default:
				$query = $this->db->get_where('users', array('login' => $user_login), 1);
				break;
			case 'email':
				$query = $this->db->get_where('users', array('email' => $user_login), 1);
				break;
		}

        if ($query->num_rows() > 0) {

            $this->user_data = $query->row_array();
            $user_data = &$this->user_data;

            // Проверка на разрешенные IP
            if ($user_data['is_admin'] && isset($this->config->config['admin_ip'])) {
				if (!$this->_check_subnet()) {
					return false;
				}
			}

			// Используется blowfish
			$password_hash = hash_password($user_password, $this->user_data['password']);

        } else {
            return false;
        }

        // Проверка пароля
        if ($password_hash == $this->user_data['password']) {
            $this->auth_id 		= (int) $user_data['id'];
            $this->auth_login 	= $user_data['login'];
            $this->auth_data 	= $user_data;
            $this->auth_data['balance'] = (int)$this->encrypt->decode($user_data['balance']);

            return $this->auth_id;
        } else {
            return false;
        }
    }


    // ----------------------------------------------------------------

    /**
     * Получает данные пользователя
     *
     * $this->users->get_user_data(48);
     * $this->users->get_user_data(array('login' => 'gameap'));
     *
     * @param int|array   id пользователя|массив с условиями
     * @return array
    */
    function get_user_data($user_id = false)
    {
        if(!$user_id){
            return false;
        }

        if(is_array($user_id)) {
			$where = $user_id;
		} else {
			$where = array('id' => $user_id);
		}

        $query = $this->db->get_where('users', $where, 1);
        $user_data = $query->row_array();

        if (!$user_data) {
			return false;
		}

		$user_data['balance'] 				= (int)$this->encrypt->decode($user_data['balance']);
		$user_data['modules_data'] 			= ($user_data['modules_data'] != '') ? json_decode($user_data['modules_data'], true) : array();
		$user_data['notices'] 				= ($user_data['notices'] != '') ? json_decode($user_data['notices'], true) : array();
		$user_data['privileges'] 			= $this->_decode_base_privileges($user_data['privileges']);
		//~ $user_data['filters']				= json_decode($user_data['filters']);

		$this->user_data 			= $user_data;
		$this->user_privileges 		= &$user_data['privileges'];
		//~ $this->servers_privileges 	= &$user_data['servers_privileges'];

        return $user_data;
    }

    // ----------------------------------------------------------------

    /**
     * Добавление нового пользователя
     *
     * @param array
     * @return bool
    */
    function add_user($user_data)
    {
        return (bool)$this->db->insert('users', $user_data);
    }

    // ----------------------------------------------------------------

    /**
     * Удаление пользователя
    */
    public function delete_user($id)
    {
		return (bool)$this->db->delete('users', array('id' => $id));
	}

    // ----------------------------------------------------------------

    /**
     * Редактирование пользователя
     *
     * @param array - новые данные
     * @param string - id пользователя, либо массив с where
     * @return bool
    */
    public function update_user($user_data, $user_id = false)
    {
        if(!$user_id){
            $user_id = $this->auth_id;
        }

        if(!$user_id OR !$user_data){
            return false;
        }

        if(!is_array($user_id)) {
			$this->db->where('id', $user_id);
		} else {
			 $this->db->where($user_id);
		}

        return (bool)$this->db->update('users', $user_data);
    }

    //-----------------------------------------------------------

    /**
     * Задать/обновить уведомление пользователю
     *
     * @param string 	уникальное имя уведомления
     * @param string 	текст уведомления
     * @param array 	внутренние данные уведомления
     * @param int		ID пользователя
     */
    public function set_notice($name, $text = '', $ntdata = array(), $user_id = 0)
    {
		if ($user_id) {
			$this->get_user_data($user_id);
			$user_data =& $this->user_data;
		} else {

			if (!$this->auth_id) {
				return false;
			}

			$user_data =& $this->auth_data;
		}

		$user_data['notices'][$name]['text'] = $text;
		$user_data['notices'][$name]['data'] = $ntdata;

		$sql_data['notices'] = json_encode($user_data['notices']);

		return $this->update_user($sql_data, $user_data['id']);
	}

    //-----------------------------------------------------------

	/**
     * Обновляет поле с данными для модулей
     *
     * @param id 	 	id сервера
     * @param array 	новые данные
     * @param string	имя модуля
     * @return bool
     *
    */
	public function update_modules_data($user_id, $data, $module_name, $erase = false)
	{
		$user_data = $this->get_user_data($user_id);

		if (!$erase) {
			$user_data['modules_data'][$module_name] = isset($user_data['modules_data'][$module_name]) && is_array($user_data['modules_data'][$module_name])
				? array_merge($user_data['modules_data'][$module_name], $data)
				: $data;
		}
		else {
			$user_data['modules_data'][$module_name] = $data;
		}

		$modules_data_json = json_encode($user_data['modules_data']);

		$sql_data['modules_data'] = $modules_data_json;

		return (bool) $this->update_user($sql_data, $user_id);
	}

	//-----------------------------------------------------------

	/**
     * Обновляет пользовательские фильтры
     *
    */
	public function update_filter($filter_name, $data, $user_id = false)
	{
		if (!$user_id) {
			$user_data =& $this->auth_data;
		} else {
			$this->get_user_data($user_id);
			$user_data =& $this->user_data;
		}

		$filters = json_decode($user_data['filters'], true);
		$filters[ $filter_name ] = $data;
		$sql_data['filters'] = json_encode($filters);

		return (bool) $this->update_user($sql_data, $user_id);
	}

	//-----------------------------------------------------------

	/**
     * Обновляет пользовательские фильтры
     *
    */
	public function get_filter($filter_name, $user_id = false)
	{
		if (!$user_id) {
			$user_data =& $this->auth_data;
		} else {
			$this->get_user_data($user_id);
			$user_data =& $this->user_data;
		}

		$filters = json_decode($user_data['filters'], true);
		if (is_array($filters) && array_key_exists($filter_name, $filters)) {
			return $filters[ $filter_name ];
		} else {
			return array();
		}
	}

    // ----------------------------------------------------------------

    /**
     * Возвращает массив с некоторыми данными пользователя
     * для вставки их в tpl_data
     *
     * @return array
    */
    function tpl_userdata($user_id = false, $user_data = false)
    {
        $this->load->helper('date');

        if (!$user_id) {
            $user_data = $this->auth_data;
        } else {
			if (!empty($this->user_data)) {
				$user_data = $this->user_data;
			} else {
				$user_data = $this->get_user_data($user_id);
			}
        }

        if (!$user_data) {
			return false;
		}

        $tpl_data['id'] 				= $user_data['id'];
        $tpl_data['user_id'] 			= $user_data['id'];
        $tpl_data['user_login'] 		= $user_data['login'];
        $tpl_data['user_name'] 			= $user_data['name'];
        $tpl_data['user_email'] 		= $user_data['email'];
        $tpl_data['balance'] 			= $user_data['balance'];

        $tpl_data['user_reg_date'] = unix_to_human($user_data['reg_date'], true, 'eu');
        $tpl_data['user_last_auth'] = unix_to_human($user_data['last_auth'], true, 'eu');

        return $tpl_data;
    }

    // ----------------------------------------------------------------

    /**
     * Получение привилегий на отдельные серверы
     *
     * @param integer
     * @param integer
     * @return array
    */
    function get_server_privileges($server_id = false, $user_id = false)
    {
        $user_privileges = array();

        if (!$user_id) {
            $user_id = $this->auth_id;

            if ($this->auth_data['is_admin']) {
				// У админа имеются все привилегии
				foreach($this->all_privileges as $key => $value) {
					$user_privileges[$key] = 1;
				}

				$this->auth_servers_privileges = $user_privileges;
				return $user_privileges;
			}
        }

        if (!is_numeric($user_id)) {
            return array();
        }

        if (!$server_id) {
            $where = array('user_id' => $user_id);
        } else {
            $where = array('user_id' => $user_id, 'server_id' => $server_id);
        }

        $query = $this->db->get_where('servers_privileges', $where, 1);
        $row_array = $query->row_array();

        if (!empty($row_array)) {
			$array_privileges = json_decode($row_array['privileges'], true);

			foreach($array_privileges as $key => $value) {
				if (array_key_exists($key, $this->all_privileges)) {
					$user_privileges[$key] = $value;
				}
			}
		}

        // Заполнение пустых привилегий
        foreach ($this->all_privileges as $key => $value)
        {
            if(!array_key_exists($key, $user_privileges)){
                $user_privileges[$key] = 0;
            }
        }

        /* Если не запрашиваются данные другого пользователя
         * в этом случае записываем еще в $this->servers_privileges
        */

        if ($user_id != $this->auth_id) {
			$this->servers_privileges 		= $user_privileges;
		} else {
			$this->auth_servers_privileges 	= $user_privileges;
		}

        return $user_privileges;
    }

    // ----------------------------------------------------------------

    /**
     * Задать привилегию для сервера
     * Для обновления привилегий в базе данных, нужно использовать
     * метод update_server_privileges
     *
     * @param string
     * @param string
     * @param integer
     * @param integer
     * @return string
    */
    public function set_server_privileges($privilege_name, $rule, $server_id, $user_id = false)
    {
        if (!$user_id) {
            $user_id = $this->auth_id;
        } else {
            $user_id  = (int)$user_id;
        }

        $this->_set_privileges[] = array(
			'user_id' =>            $user_id,
			'server_id' =>          $server_id,
			'privilege_name' =>     $privilege_name,
			'privilege_value' =>    $rule,
        );
    }

    // ----------------------------------------------------------------

    /**
     * Обновляет данные серверных привилегий в базе данных
     *
     * @param integer
     * @param integer
     * @param array
     * @return bool
    */
    public function update_server_privileges($user_id = null, $server_id = null, $privileges = array())
    {
		if (!empty($privileges)) {
			$this->_set_privileges = $privileges;
		}

		if (!empty($this->_set_privileges)) {

			$user_id 	? $this->db->where('user_id', $user_id) : null;
			$server_id 	? $this->db->where('server_id', $server_id) : null;

			foreach($this->_set_privileges as $array) {
				$set_privileges[ $array['privilege_name'] ] = $array['privilege_value'];
			}

			if ($this->db->count_all_results('servers_privileges') > 0) {
				$this->db->where(array('user_id' => $user_id, 'server_id' => $server_id));
				return $this->db->update('servers_privileges', array('privileges' => json_encode($set_privileges)));
			} else {
				return $this->db->insert('servers_privileges', array('user_id' => $user_id, 'server_id' => $server_id, 'privileges' => json_encode($set_privileges)));
			}
		}

		return;
	}

    // ----------------------------------------------------------------

    /**
     * Получение списка пользователей
     *
     * @param int  		лимит списка
     * @param int 		смещение
     * @param string	префикс названия ключей в массиве, по умолчанию user_
     *
     * @return array
    */
    public function tpl_users_list($limit = null, $offset = 0, $prefix = 'user_')
    {
        $this->load->helper('date');

        if(empty($this->users_list)){
			$this->get_users_list(false, $limit, $offset);
		}

		$list = array();
        $num = -1;
        foreach ($this->users_list as $users){
            $num++;

            $list[$num] = $users;
            $list[$num][$prefix . 'id'] 		= $users['id'];
            $list[$num][$prefix . 'login'] 		= $users['login'];
            $list[$num][$prefix . 'reg_date'] 	= unix_to_human($users['reg_date'], true, 'eu');
            $list[$num][$prefix . 'last_auth'] 	= unix_to_human($users['last_auth'], true, 'eu');
            $list[$num][$prefix . 'balance'] 	= $users['balance'];
        }

        return $list;
    }

    //-----------------------------------------------------------
	/**
     * Получение списка пользователей
     *
     * @param array - условия для выборки
     * @param int
     *
     * @return array
     *
    */
    function get_users_list($where = false, $limit = 9999, $offset = 0, $no_filter = false)
    {
		/*
		 * В массиве $where храняться данные для выборки.
		 * Например:
		 * 		$where = array('id' => 1);
		 * в этом случае будет выбран пользователь id которого = 1
		 *
		*/
		if($where) {
			$this->db->where($where);
		}

		if (!empty($this->_where_in) && is_array($this->_where_in)) {
			$this->db->where_in('id', $this->_where_in);
		}

		if ($no_filter != true) {
			!$this->_filter_users_list['login'] OR $this->db->like('login', $this->_filter_users_list['login']);

			!$this->_filter_users_list['register_before'] 	OR $this->db->where('reg_date <', $this->_filter_users_list['register_before']);
			!$this->_filter_users_list['register_after'] 	OR $this->db->where('reg_date >', $this->_filter_users_list['register_after']);

			!$this->_filter_users_list['last_visit_before'] OR $this->db->where('last_auth <', $this->_filter_users_list['last_visit_before']);
			!$this->_filter_users_list['last_visit_after'] 	OR $this->db->where('last_auth >', $this->_filter_users_list['last_visit_after']);
		}

		$query = $this->db->get('users', $limit, $offset);

		if($query->num_rows() > 0){

			$this->users_list = $query->result_array();

			/* Конвертирование данных */
			foreach($this->users_list as &$user) {
				$user['balance'] 		= (int)$this->encrypt->decode($user['balance']);
				$user['modules_data'] 	= !empty($user['modules_data']) ? json_decode($user['modules_data'], true) : array();
				$user['notices'] 		= !empty($user['notices']) ? json_decode($user['notices'], true) : array();
			}

			return $this->users_list;

		}else{
			$this->users_list = array();
			return NULL;
		}
	}

    // ----------------------------------------------------------------

    /**
     * Проверяет, существует ли пользователь с данным id, логином
     *
     * @return bool
    */
    function user_live($string, $type = 'id') {

		$type = strtolower($type);

        switch($type){
            case('id'):
				$this->db->where('id', $string);
				break;

            case('login'):
				$this->db->where('login', $string);
				break;

            case('email'):
               $this->db->where('email', $string);
				break;

			default:
				return false;
				break;
        }

        return (bool)($this->db->count_all_results('users') > 0);
    }

    // ----------------------------------------------------------------

    /**
     * Получаем hash пользователя, случайной строки
     * @return string
     *
    */
    function get_user_hash()
    {
        $this->load->helper('safety');
        $hash 	= md5(generate_code(10) . $this->input->ip_address());

        if ($this->config->item('auth_check_ip')) {
			$md5_ipua = md5($this->input->ip_address() . $this->input->user_agent());
		} else {
			$md5_ipua = md5($this->input->user_agent());
		}

        $this->update_user(array('hash' => $hash . $md5_ipua, 'last_auth' => time()));

        return $hash;
    }

    // ----------------------------------------------------------------

    /**
     * Получаем код для восстановления пароля пользователя
     * @param - id пользователя
     * @return string
     *
    */
    function get_user_recovery_code($user_id)
    {
		$user_data = $this->get_user_data($user_id, false, true);
		return $user_data['recovery_code'];
    }

    //-----------------------------------------------------------

	/**
     * Задает фильтры для получения пользоватей с определенными данными
    */
	function set_filter($filter)
	{
		if (is_array($filter)) {
			!isset($filter['login']) OR $this->_filter_users_list['login'] = $filter['login'];

			!isset($filter['register_before']) OR $this->_filter_users_list['register_before'] = $filter['register_before'];
			!isset($filter['register_after']) OR $this->_filter_users_list['register_after'] = $filter['register_after'];

			!isset($filter['last_visit_before']) OR $this->_filter_users_list['last_visit_before'] = $filter['last_visit_before'];
			!isset($filter['last_visit_after']) OR $this->_filter_users_list['last_visit_after'] = $filter['last_visit_after'];
		}
	}

	/**
	 * Очистка фильтров списка
	 */
	function clear_filter()
	{
		$this->_filter_users_list	= array(
			'login' 			=> false,

			'register_before' 	=> false,
			'register_after' 	=> false,

			'last_visit_before' => false,
			'last_visit_after' 	=> false,
		);
	}

	// ----------------------------------------------------------------

	/**
	 * Задает список пользователей
	 */
	function where_in($list = array())
	{
		$this->_where_in = $list;
	}


    // ----------------------------------------------------------------

    /**
     * Задает код для восстановления пароля пользователя
     * @param - id пользователя
     * @return string
     *
    */
    function set_user_recovery_code($user_id)
    {
		$this->load->helper('safety');
        $code = generate_code(20);

        $this->update_user(array('recovery_code' => $code), $user_id);

        return $code;
	}

	// ----------------------------------------------------------------

    /**
     * Отправка сообщения пользователю на почту
     * @return string
    */
	function send_mail($subject = '<empty>', $message = '', $user_id = false)
	{
		if ($user_id) {
			if ($user_id != $this->auth_id) {
				$user_data = $this->get_user_data($user_id, false, true);
			} else {
				$user_data =& $this->auth_data;
			}
		} else {
			$user_data =& $this->user_data;
		}

		$user_name = $user_data['name'] ? $user_data['name']  : $user_data['login'];
		$message = str_replace('{user_name}', $user_name, $message);
		$message = str_replace('{user_balance}', $user_data['balance'], $message);

		try {
            $mailer = new BaseMailer([
                'to' => $user_data['email'],
                'from' => [$this->config->config['system_email'], $this->config->config['email_sender_name']],
                'subject' => $subject,
                'message' => $message,
            ]);

            $result = (bool)$mailer->send();

        } catch (\Exception $e) {
            $this->panel_log->save_log([
                'type' => 'send_mail',
                'command' => 'send_mail',
                'msg' => 'Send mail error',
                'log_data' => $e->getMessage()
            ]);

            $result = false;
        }

		return $result;
	}

    // ----------------------------------------------------------------

    /**
     * Отправка сообщения администратору на почту
     * @return string
    */
    function admin_msg($subject = '<empty>', $message)
    {
		$admin_list = $this->get_users_list(array('is_admin' => '1'), 1000);

		if (empty($admin_list)) {
			// Админов нет
			return false;
		}

		foreach($admin_list as $admin_data) {
			$email_list[] = $admin_data['email'];
		}

		try {
            $mailer = new BaseMailer([
                'to' => $email_list,
                'from' => [$this->config->config['system_email'], $this->config->config['email_sender_name']],
                'subject' => $subject,
                'message' => $message,
            ]);

            $result = (bool)$mailer->send();

        } catch (\Exception $e) {
            $this->panel_log->save_log([
                'type' => 'admin_msg',
                'command' => 'send_mail',
                'msg' => 'Send mail error',
                'log_data' => $e->getMessage()
            ]);

            $result = false;
        }

        return $result;
    }
}
