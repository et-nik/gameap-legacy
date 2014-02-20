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

/**
 * Работа с SSH
 *
 * Библиотека для работы с удаленными серверами
 * через SSH
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
*/
 
class Control_ssh extends CI_Driver {
	
	var $_connection 	= false;
	var $errors 		= '';
	
	private $_auth		= false;
	
	// ---------------------------------------------------------------------
	
	public function check()
	{
		if(!in_array('ssh2', get_loaded_extensions())){
			throw new Exception('server_command_ssh_not_module');
		}
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 * @param str	файл
	 * @param str 	строка с правами (rwx)
	 */
	public function check_file($file, $privileges = '')
	{
		return true;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Соединение с SSH
	*/
	function connect($ip = false, $port = 22)
	{
		if (!$ip OR !$port) {
			throw new Exception('server_command_empty_connect_data');
		}
		
		$this->_auth = false;
		@$this->_connection = ssh2_connect($ip, $port);
		
		if (!$this->_connection) {
			throw new Exception('server_command_connection_failed');
		}

		return $this->_connection;
	}
	
	// ----------------------------------------------------------------
	
	function auth($login, $password)
	{
		if (!$this->_connection) {
			throw new Exception('server_command_not_connected');
		}
		
		if(!$login) {
			throw new Exception('server_command_empty_auth_data');
		}

		if (!@ssh2_auth_password($this->_connection, $login, $password)) {
			throw new Exception('server_command_login_failed');
		}
		
		$this->_auth = true;
		return true;
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Выполнение команды
	*/
	function command($command)
	{
		if (!$this->_connection OR !$this->_auth) {
			throw new Exception('server_command_not_connected');
		}
		
		if (!$command) {
			throw new Exception('server_command_empty_command');
		}
		
		$stream = ssh2_exec($this->_connection, $command);

		stream_set_blocking($stream, true);
		$data = stream_get_contents($stream);	
		
		return $data;
	}
	
	// ----------------------------------------------------------------

	/**
	 * Выполнение команды
	*/
	function exec($command) 
	{
		return $this->command($command);
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Отключение
	*/
	function disconnect()
	{
		if ($this->_connection && $this->_auth) {
			$this->command('exit');
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * 
	*/
	function __destruct()
	{
		$this->disconnect();
	}
}


/* End of file Control_ssh.php */
/* Location: ./application/libraries/Control/drivers/Control_ssh.php */
