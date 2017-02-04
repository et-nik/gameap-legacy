<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dedicated_servers extends CI_Model {

	var $ds_list = array();				// Список удаленных серверов

    var $errors; 						// Строка с ошибкой (если имеются)

    private $_commands = array();
    private $_errors	= false;

    private $_scripts_default = array(
		'script_start' 			=> 'su {user} -c -- \'gameap-starter -t start -d {dir} -c {command}\'',
		'script_stop' 			=> 'gameap-starter -t stop -d {dir} -u {user}',
		'script_restart' 		=> 'gameap-starter -t restart -d {dir} -n {name} -i {ip} -p {port} -c "{command}" -u {user}',
		'script_status' 		=> 'gameap-starter -t status -d {dir} -u {user}',
		'script_get_console' 	=> '',
		'script_send_command' 	=> '',
    );

    public $available_control_protocols = array('gdaemon');

    //-----------------------------------------------------------

    public function __construct()
	{
		parent::__construct();
	}

	//-----------------------------------------------------------

	/**
	 * Дефолтные параметры
	 */
	private function _get_default_script($param = 'script_start')
	{
		return $this->_scripts_default[$param];
	}

	//-----------------------------------------------------------

	/**
     * Шифровка паролей
     *
     * @param array
     * @return bool
     *
    */
	function _encrypt_passwords($data) {

		$this->load->library('encrypt');

        if (isset($data['gdaemon_login'])) {
            $data['gdaemon_login'] = $this->encrypt->encode($data['gdaemon_login']);
        }

        if (isset($data['gdaemon_password'])) {
            $data['gdaemon_password']= $this->encrypt->encode($data['gdaemon_password']);
        }

        if (isset($data['gdaemon_keypass'])) {
            $data['gdaemon_keypass'] = $this->encrypt->encode($data['gdaemon_keypass']);
        }

		return $data;
	}

	//-----------------------------------------------------------

    /*
     * Проверяет директорию на необходимые права
    */
	private function _check_path($path)
	{
		if (!is_dir($path)) {
			/* Это не директория */
			$this->errors = "Dir " . $path . " not found";
			return false;
		}

		return true;
	}

	//-----------------------------------------------------------

	/**
     * Добавление выделенного сервера
     *
     * @param array
     * @return bool
     *
    */
	function add_dedicated_server($data)
	{
		$data = $this->_encrypt_passwords($data);

		return (bool)$this->db->insert('dedicated_servers', [
		    'name'              => isset($data['name']) ? $data['name'] : '',
		    'disabled'          => isset($data['disabled']) ? $data['name'] : 0,
		    'os'                => isset($data['os']) ? $data['os'] : '',
		    'location'          => isset($data['location']) ? $data['location'] : '',
		    'provider'          => isset($data['provider']) ? $data['provider'] : '',
		    'ip'                => isset($data['ip']) ? $data['ip'] : '',
		    'ram'               => isset($data['ram']) ? $data['ram'] : '',
		    'cpu'               => isset($data['cpu']) ? $data['cpu'] : '',
		    'work_path'         => isset($data['work_path']) ? $data['work_path'] : '',
		    'steamcmd_path'     => isset($data['steamcmd_path']) ? $data['steamcmd_path'] : '',
		    'gdaemon_host'      => isset($data['gdaemon_host']) ? $data['gdaemon_host'] : '',
		    'gdaemon_login'     => isset($data['gdaemon_login']) ? $data['gdaemon_login'] : '',
		    'gdaemon_password'  => isset($data['gdaemon_password']) ? $data['gdaemon_password'] : '',
		    'gdaemon_privkey'   => isset($data['gdaemon_privkey']) ? $data['gdaemon_privkey'] : '',
		    'gdaemon_pubkey'    => isset($data['gdaemon_pubkey']) ? $data['gdaemon_pubkey'] : '',
		    'gdaemon_keypass'   => isset($data['gdaemon_keypass']) ? $data['gdaemon_keypass'] : '',
		    'script_start'      => isset($data['script_start']) ? $data['script_start'] : '',
		    'script_stop'       => isset($data['script_stop']) ? $data['script_stop'] : '',
		    'script_restart'    => isset($data['script_restart']) ? $data['script_restart'] : '',
		    'script_status'     => isset($data['script_status']) ? $data['script_status'] : '',
		    'script_get_console' => isset($data['script_get_console']) ? $data['script_get_console'] : '',
		    'script_send_command' => isset($data['script_send_command']) ? $data['script_send_command'] : '',
		    'modules_data'      => isset($data['modules_data']) ? $data['modules_data'] : ''
        ]);
	}

	//-----------------------------------------------------------

	/**
     * Удаление выделенного сервера
     *
     * @param array
     * @return bool
     *
    */
	function del_dedicated_server($id)
	{
		return (bool)$this->db->delete('dedicated_servers', array('id' => $id));
	}

	//-----------------------------------------------------------

	/**
     * Получение списка удаленных сервров (машин)
     *
     * @param array - условия для выборки
     * @param int
     *
     * @return array
     *
    */
    function get_ds_list($where = false, $limit = 99999)
    {
		$this->load->library('encrypt');

		/*
		 * В массиве $where храняться данные для выборки.
		 * Например:
		 * 		$where = array('id' => 1);
		 * в этом случае будет выбран сервер id которого = 1
		 *
		*/

		if(is_array($where)){
			$query = $this->db->get_where('dedicated_servers', $where, $limit);
		}else{
			$query = $this->db->get('dedicated_servers');
		}

		$this->ds_list = array();

		if ($query->num_rows() > 0) {

			$this->ds_list = $query->result_array();

			/* Выполняем необходимые действия с данными
			 * Расшифровываем пароли, преобразуем списки из json в понятный массив */
			$i = 0;
			$count_ds_list = count($this->ds_list);
			while($i < $count_ds_list) {

				$ds_ip = $this->ds_list[$i]['ip'];
                $this->ds_list[$i]['ip'] = json_decode($ds_ip, true);

				if (!$this->ds_list[$i]['ip']) {
					/* Строка с данными не является json, в этом случае присваиваем первому
					 * массиву значение этой строки
					 * Сделано для совместимости со старыми версиями после обновления
					*/
					$this->ds_list[$i]['ip'] = array();
					$this->ds_list[$i]['ip'][] = $ds_ip;
				}

				unset($ds_ip);

				$this->ds_list[$i]['modules_data'] 		= json_decode($this->ds_list[$i]['modules_data'], true);

				$this->ds_list[$i]['gdaemon_privkey']   = $this->ds_list[$i]['gdaemon_privkey'];
				$this->ds_list[$i]['gdaemon_pubkey']    = $this->ds_list[$i]['gdaemon_pubkey'];
				$this->ds_list[$i]['gdaemon_keypass']   = $this->encrypt->decode($this->ds_list[$i]['gdaemon_keypass']);
				$this->ds_list[$i]['gdaemon_login']		= $this->encrypt->decode($this->ds_list[$i]['gdaemon_login']);
				$this->ds_list[$i]['gdaemon_password']	= $this->encrypt->decode($this->ds_list[$i]['gdaemon_password']);

				// Скрипты запуска
				$this->ds_list[$i]['script_start'] = $this->ds_list[$i]['script_start']
					OR $this->ds_list[$i]['script_start'] = $this->_get_default_script('script_start');

				$this->ds_list[$i]['script_stop'] = $this->ds_list[$i]['script_stop']
					OR $this->ds_list[$i]['script_stop'] = $this->_get_default_script('script_stop');

				$this->ds_list[$i]['script_restart'] = $this->ds_list[$i]['script_restart']
					OR $this->ds_list[$i]['script_restart'] = $this->_get_default_script('script_restart');

				$this->ds_list[$i]['script_status'] = $this->ds_list[$i]['script_status']
					OR $this->ds_list[$i]['script_status'] = $this->_get_default_script('script_status');

				$this->ds_list[$i]['script_get_console'] = $this->ds_list[$i]['script_get_console']
					OR $this->ds_list[$i]['script_get_console'] = $this->_get_default_script('script_get_console');

				$this->ds_list[$i]['script_send_command'] = $this->ds_list[$i]['script_send_command']
					OR $this->ds_list[$i]['script_send_command'] = $this->_get_default_script('script_send_command');

				$i ++;
			}

			return $this->ds_list;

		}else{
			return array();
		}
	}

	// ----------------------------------------------------------------

    /**
     * Проверяет, существует ли выделенный сервер с данным id
     * Параметру id может быть передан id сервера, либо массив where
     *
     * @return bool
    */
    function ds_live($id = false)
    {
		if (false == $id) {
			return false;
		}

		if (is_array($id)) {
			$this->db->where($id);
		} else {
			$this->db->where(array('id' => $id));
		}

		return (bool)($this->db->count_all_results('dedicated_servers') > 0);
    }

    // -----------------------------------------------------------

	/**
	 * Массив с id выделенных серверов
	 */
	function select_ids($ds_ids)
	{
		if (empty($ds_ids)) {
			return false;
		}

		$this->db->where_in('id', $ds_ids);
	}

    // ----------------------------------------------------------------

    /**
     * Получает данные выделенного сервера
     *
     * @return bool
    */
    function get_ds_data($id = false)
    {
		if (false == $id) {
			return false;
		}

		$where = array('id' => $id);
		$this->get_ds_list($where, 1);

		if (isset($this->ds_list[0])) {
			return $this->ds_list[0];
		} else {
			return false;
		}
	}

	//-----------------------------------------------------------

	/**
     * Редактирование выделенного сервера
     *
     * @param id - id сервера
     * @param array - новые данные
     * @return bool
     *
    */
	function edit_dedicated_server($id, $data)
	{
        if (isset($data['gdaemon_password']) && $data['gdaemon_password'] == '') {
            unset($data['gdaemon_password']);
        }

        if (isset($data['gdaemon_keypass']) && $data['gdaemon_keypass'] == '') {
            unset($data['gdaemon_keypass']);
        }

        $data = $this->_encrypt_passwords($data);
		$this->db->where('id', $id);

		return (bool)$this->db->update('dedicated_servers', $data);
	}

	//-----------------------------------------------------------

	/**
     * Обновляет поле с данными для модулей
     *
     * @param id 	 	id сервера
     * @param array 	новые данные
     * @param string	имя модуля
     * @param bool
     *
     * @return bool
     *
    */
	function update_modules_data($id, $data, $module_name, $erase = false)
	{
		$ds_data = $this->get_ds_data($id);

		if (!$erase) {
			$ds_data['modules_data'][$module_name] = isset($ds_data['modules_data'][$module_name]) && is_array($ds_data['modules_data'][$module_name])
													? array_merge($ds_data['modules_data'][$module_name], $data)
													: $data;
		}
		else {
			$ds_data['modules_data'][$module_name] = $data;
		}

		$sql_data['modules_data'] = json_encode($ds_data['modules_data']);

		return (bool)$this->edit_dedicated_server($id, $sql_data);
	}

	//-----------------------------------------------------------

	/**
     * Получение данных выделенного сервера для шаблона
     * (вырезаны ненужные данные - пароли и пр.)
     *
     *
    */
	function tpl_data_ds()
    {
		$num = -1;

		if(!$this->ds_list){
			$this->get_ds_list();
		}

		if ($this->ds_list) {

			foreach ($this->ds_list as $dedicated_servers) {
				$num++;

				$tpl_data[$num]['ds_name'] = $dedicated_servers['name'];
				$tpl_data[$num]['ds_location'] = $dedicated_servers['location'];
				$tpl_data[$num]['ds_provider'] = $dedicated_servers['provider'];
				$tpl_data[$num]['ds_os'] = $dedicated_servers['os'];
				$tpl_data[$num]['ds_ram'] = $dedicated_servers['ram'];
				$tpl_data[$num]['ds_cpu'] = $dedicated_servers['cpu'];
				$tpl_data[$num]['ds_id'] = $dedicated_servers['id'];

				/* Список IP адресов */
				$tpl_data[$num]['ds_ip'] = implode(', ', $dedicated_servers['ip']);

				/* Количество игровых серверов */
				$this->db->count_all();

				$this->db->where('ds_id', $dedicated_servers['id']);
				$this->db->from('servers');
				$tpl_data[$num]['servers_count'] = $this->db->count_all_results();

			}

			return $tpl_data;

		} else {
			return array();
		}
	}

	// ----------------------------------------------------------------

	/*
	 * Проверка занятости портов
	 *
	 * @param str, array
	*/
	function check_ports($ds_id, $ports, $server_ip = false)
	{
		$this->db->where('ds_id', $ds_id);

		if ($server_ip) {
			$this->db->where('server_ip', $server_ip);
		}

		$this->db->where_in('server_port', $ports);
		$this->db->or_where_in('query_port', $ports);
		$this->db->or_where_in('rcon_port', $ports);

		return (bool)$this->db->count_all_results('servers');
	}
}
