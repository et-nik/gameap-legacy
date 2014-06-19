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


// ------------------------------------------------------------------------

/**
 * Ajax для получения базовой информации о серверах
 * Получение статуса серверов (опрос сервера)
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8.6
 * 
*/
class Server_control extends CI_Controller {
	
	// ----------------------------------------------------------------
    
    /**
     * Получает форму со списком игровых типов выбранной игры
    */
	public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model('users');
        $this->lang->load('server_control');
        
        if($this->users->check_user()) {

			$this->load->library('form_validation');
			$this->load->helper('form');
			$this->load->helper('ds');
			
			$this->load->model('servers');
			$this->load->model('servers/dedicated_servers');
			$this->load->model('servers/games');
			$this->load->model('servers/game_types');
		} else {
			show_404();
		}
    }
    
    // -----------------------------------------------------------------------
	
	/**
	 * Обрезка пустых строк консоли
	*/
	private function _crop_console($console_text)
	{
		$console_data = explode("\n", $console_text);
		
		$i = 0;
		$count_console_data = count($console_data);
		while ($i < $count_console_data) {
			if ($console_data[$i] != "") {
				break;
			}
			
			unset($console_data[$i]);
			$i ++;
		}
		
		return implode("\n", $console_data);
	}
    
    /**
	 * 
	 * Проверка rcon команд, некоторые команды могут требовать
	 * дополнительных действий, либо быть запрещены
	 * 
	*/
	private function _check_rcon_command($rcon_command) 
	{
		/* Получаем ркон команду */
		$rcon_command = explode(' ', $rcon_command);
		$rcon_command['0'] = strtolower($rcon_command['0']);

		/* Пользователь, у которого нет прав на смену ркон пароля не имеет права отправлять rcon_password */
		if(!$this->users->auth_servers_privileges['CHANGE_RCON'] && in_array('rcon_password', $rcon_command)) {
			return false;
		}
		
		/* Пользователь, у которого нет прав на выставление пароля на сервер */
		if(!$this->users->auth_servers_privileges['SERVER_SET_PASSWORD'] && in_array('sv_password', $rcon_command)) {
			return false;
		}
		
		switch ($rcon_command['0']) {
			case 'rcon_password':
				// Смена rcon пароля, правка конфиг файлов и тп.
				if(isset($this->servers->server_data['id']) && isset($rcon_command['1'])) {
					$this->servers->change_rcon($rcon_command['1']);
					$sql_data['rcon'] = $rcon_command['1'];
					$this->servers->edit_game_server($this->servers->server_data['id'], $sql_data);
				}
				
				break;
		}
	
		
		return true;
	}
    
    // ----------------------------------------------------------------
    
    /**
     * Получение статуса сервера
    */
    public function get_status($server_id = false)
    {
		if (!$server_id) {
			show_404();
		}
		
		if (false == $this->servers->get_server_data($server_id)) {
			show_404();
		}
		
		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		if (!$this->users->auth_servers_privileges['VIEW']) {
			show_404();
		}
		
		if ($this->servers->server_status($this->servers->server_data['server_ip'], 
											$this->servers->server_data['query_port'], 
											$this->servers->server_data['engine'], 
											$this->servers->server_data['engine_version'])
		){
			$this->output->append_output(1);							
		} else {
			$this->output->append_output(0);
		}
		
	}
	
	// ----------------------------------------------------------------
    
    /**
     * Получение содержимого консоли
    */
    public function get_console($server_id = false)
    {
		if (!$server_id) {
			show_404();
		}
		
		if (false == $this->servers->get_server_data($server_id)) {
			show_404();
		}
		
		// Получение прав на сервер
		$this->users->get_server_privileges($this->servers->server_data['id']);
		
		if (!$this->users->auth_data['is_admin'] && !$this->users->auth_servers_privileges['CONSOLE_VIEW']) {
			show_404();
		}
		
		/*
		 * Список расширений php
		 */
		$ext_list = get_loaded_extensions();
		
		/* 
		 * Заданы ли данные SSH у DS сервера 
		 * 
		 * Если сервер является удаленным, используется telnet
		 * и заданы хост, логин и пароль то все впорядке,
		 * иначе отправляем пользователю сообщение
		 * 
		*/
		if($this->servers->server_data['ds_id'] 
		&& $this->servers->server_data['control_protocol'] == 'ssh'
		&& (!$this->servers->server_data['ssh_host']
			OR !$this->servers->server_data['ssh_login']
			OR !$this->servers->server_data['ssh_password']
			)
		){
			show_404();
		}
		
		/*
		 * Есть ли модуль SSH
		 */
		if($this->servers->server_data['ds_id'] 
		&& $this->servers->server_data['control_protocol'] == 'ssh'
		&& (!in_array('ssh2', $ext_list))
		){
			show_404();
		}
		
		
		/* 
		 * Заданы ли данные TELNET у DS сервера 
		 * 
		 * Если сервер является удаленным, используется telnet
		 * и заданы хост, логин и пароль то все впорядке,
		 * иначе отправляем пользователю сообщение
		 * 
		*/
		
		if($this->servers->server_data['ds_id'] 
		&& $this->servers->server_data['control_protocol'] == 'telnet'
		&& (!$this->servers->server_data['telnet_host']
			OR !$this->servers->server_data['telnet_login']
			OR !$this->servers->server_data['telnet_password']
			)
		){
			show_404();
		}
		
		/* Команда получения консоли не задана */
		if(!$this->servers->server_data['script_get_console']) {
			show_404();
		}
		
		/* Директория в которой располагается сервер */
		$dir = $this->servers->server_data['script_path'] . '/' . $this->servers->server_data['dir'];
		
		$command = $this->servers->command_generate($this->servers->server_data, 'get_console');
		
		try {
			$response = send_command($command, $this->servers->server_data);
			$response = $this->_crop_console($response);
			
			if (version_compare(phpversion(), '5.4.0') == -1) {
				$console_content = str_replace("\n", "<br />\n", htmlspecialchars($response));
			} else {
				$console_content = str_replace("\n", "<br />\n", htmlspecialchars($response, ENT_SUBSTITUTE));
			}

			//~ $console_content = str_replace("\n", "<br />", htmlspecialchars($response));
			$this->output->append_output($console_content);
		} catch (Exception $e) {
			show_404();
		}

	}
	
	// ----------------------------------------------------------------
    
    /**
     * Отправка ркон команды на сервер
    */
    public function send_command($server_id = false)
    {
		if (!$server_id) {
			show_404();
		}
		
		if (false == $this->servers->get_server_data($server_id)) {
			show_404();
		}
		
		// Получение прав на сервер
		$this->users->get_server_privileges($this->servers->server_data['id']);
		
		if (!$this->users->auth_data['is_admin'] && !$this->users->auth_servers_privileges['RCON_SEND']) {
			show_404();
		}

		$this->form_validation->set_rules('command', 'rcon command', 'trim|required|max_length[64]|min_length[1]|xss_clean');
		
		if($this->form_validation->run() == false){
			show_404();
		}
		
		$rcon_command = $this->input->post('command');
		
		if(!$this->servers->server_status($this->servers->server_data['server_ip'], $this->servers->server_data['query_port'])) {
			$this->output->append_output('Server is down');
			return false;
		}
		
		if (strtolower($this->servers->server_data['os']) == 'windows') {
			// Для Windows отправка через RCON

			if(!$this->_check_rcon_command($rcon_command)) {
				show_404();
			}
			
			$this->load->driver('rcon');
							
			$this->rcon->set_variables(
							$this->servers->server_data['server_ip'],
							$this->servers->server_data['rcon_port'],
							$this->servers->server_data['rcon'], 
							$this->servers->servers->server_data['engine'],
							$this->servers->servers->server_data['engine_version']
			);
			
			if($this->rcon->connect()) {
				$this->rcon->command($rcon_command);
			} else {
				$this->output->append_output('Rcon connect error');
			}
		} else {
			// Для Linux отправка прямиков в Screen
			
			$this->load->helper('ds');
			
			$command = $this->servers->server_data['script_send_command'];
			
			if ($command == '' OR $command == './server.sh') {
				$command = './server.sh send_command {dir} {name} {ip} {port} "{command}" {user}';
			}
			
			/* На некоторых серверах могут использоваться двойные кавычки*/
			//~ $command = str_replace('"', "'", $command);

			$command = str_replace('{command}', $rcon_command, $command);
			$send_command = replace_shotcodes($command, $this->servers->server_data);
			
			try {
				send_command($send_command, $this->servers->server_data);
			} catch (Exception $e) {
				$this->output->append_output($e->getMessage());
				return false;
			}

		}
	}
	
	
	
	
	
}

/* End of file server_control.php */
