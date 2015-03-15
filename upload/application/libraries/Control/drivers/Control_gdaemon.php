<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

/**
 * Библиотека для работы с удаленными серверами
 * через GameAP Daemon
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		1.0-dev
*/
 
class Control_gdaemon extends CI_Driver {
	
	var $ip				= false;
	var $port 			= 31707;
	
	var $crypt_key		= "";
	var $client_key		= "";
	
	var $_connection 	= false;
	var $errors 		= '';
	
	private $_auth		= false;
	
	// -----------------------------------------------------------------
	
	function _encode($value, $secret_key)
	{
		if (strlen($value)%16) {
			$value = $value . str_repeat(chr(16-strlen($value)%16), 16-strlen($value)%16);
		} else {
			$value = $value . str_repeat(chr(16), 16);
		}

		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secret_key, $value, MCRYPT_MODE_ECB));
	}
	
	// -----------------------------------------------------------------

	function _decode($value, $secret_key)
	{
		$value = base64_decode(trim($value));
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secret_key, $value, MCRYPT_MODE_ECB), "\x00..\x1F");
	}
	
	// -----------------------------------------------------------------
	
	public function check()
	{
		
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 * @param str	файл
	 * @param str 	строка с правами (rwx)
	 */
	public function check_file($file, $privileges = '')
	{
		$file_name = basename($file);
		
		return true;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 */
	private function _check_file_windows($file, $matches, $privileges = '')
	{
		return true;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Чтение данных из потока
	 */
	private function _read()
	{
		$buffer = "";

		while (@!$buffer[strlen($buffer)-1] == "\n" & !feof($this->_connection)) {
			$buffer .= fgets($this->_connection);
		}

		//~ return iconv("UTF-8", "UTF-8//IGNORE", $this->_decode($buffer, $this->crypt_key));
		return $this->_decode($buffer, $this->crypt_key);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Ключ дополняется, либо урезается до 16 байт
	 */
	private function _fix_crypt_key()
	{
		if (strlen($this->crypt_key) < 16) {
			$this->crypt_key = $this->crypt_key . str_repeat('*', 16-strlen($this->crypt_key));
		} else if (strlen($this->crypt_key) > 16) {
			$this->crypt_key = substr($this->crypt_key, 0, 16);
		}
	}
	
	// -----------------------------------------------------------------
	
	function connect($ip = false, $port = 0)
	{
		if ($this->_connection && $this->ip == $ip) {
			/* Уже соединен с этим сервером, экономим электроэнергию */
			return;
		} elseif ($this->_connection) {
			// Разрываем соединение со старым сервером
			$this->disconnect();
		}

		$port OR $port = 31707;
		
		if (!$ip OR !$port) {
			throw new Exception(lang('server_command_empty_connect_data') . ' (GDaemon)');
		}
		
		$this->ip = $ip;
		$this->port = $port;
		
		// Соединение с сервером
		$this->_connection = @fsockopen($this->ip, $this->port, $errno, $errstr, 10); 
		
		if (!$this->_connection) {
			throw new Exception(lang('server_command_connection_failed') . ' (GDaemon)');
		}
		
		stream_set_timeout($this->_connection, 15);
		
		$this->_auth = false;
		return $this->_connection;
	}
	
	// -----------------------------------------------------------------
	
	function auth($login, $password)
	{
		if ($this->_auth == true) {
			return true;
		}

		if(!$password) {
			throw new Exception(lang('server_command_empty_auth_data') . ' (GDaemon)');
		}
		
		fwrite($this->_connection, "getkey\n");

		$this->crypt_key 	= $password;
		$this->_fix_crypt_key();
		
		$this->client_key 	= $this->_read();
		
		if (!preg_match("/^[a-zA-Z0-9]{16}$/", $this->client_key)) {
			throw new Exception(lang('server_command_login_failed') . ' (GDaemon)');
		}
		
		$this->_auth = true;
		return true;
	}

	// -----------------------------------------------------------------
	
	/**
	 * Выполнение команды
	*/
	function command($command)
	{
		if (!$command) {
			throw new Exception(lang('server_command_empty_command') . ' (GDaemon)');
		}
		
		if (!$this->_connection OR !$this->_auth) {
			throw new Exception(lang('server_command_not_connected') . ' (GDaemon)');
		}
		
		$send_json = json_encode(array(
			'key' 		=> $this->client_key,
			'commands' 	=> array(
								$command,
							),
			'type' 		=> "commands"
		));
		
		$encode_string = $this->_encode($send_json, $this->crypt_key);

		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			throw new Exception(lang('server_command_get_response_failed') . ' (GDaemon)');
		}
		
		return implode("\n", $contents['command_results']);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Выполнение команды
	*/
	function exec($command) 
	{
		return $this->command($command);
	}
	
	// -----------------------------------------------------------------

	function disconnect()
	{
		$this->crypt_key		= "";
		$this->client_key		= "";
		
		if (!$this->_connection && is_resource($this->_connection)) {
			return;
		}
	
		fwrite($this->_connection, "exit\n");
		fclose($this->_connection);
	}
	
}


/* End of file Control_local.php */
/* Location: ./application/libraries/Control/drivers/Control_local.php */
