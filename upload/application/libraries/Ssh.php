<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
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
 * @category	Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
*/
 
class Ssh {
	var $_connection = FALSE;
	var $errors = '';
	
	// ----------------------------------------------------------------
	
	/**
	 * Соединение с SSH
	*/
	function connect($ip, $port = 22)
	{
		$this->_connection = ssh2_connect($ip, $port);
		
		if (!$this->_connection) {
			return FALSE;
		}
		
		return $this->_connection;
	}
	
	function auth($login, $password)
	{
		if (!$this->_connection) {
			return FALSE;
		}
		
		if (!ssh2_auth_password($this->_connection, $login, $password)) {
			$this->_connection = FALSE;
			$this->errors = 'Authorization failed';
			return FALSE;
		}
		
		return TRUE;
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Выполнение команды
	*/
	function command($command)
	{
		if (!$this->_connection) {
			return FALSE;
		}
		
		$stream = ssh2_exec($this->_connection, $command);

		stream_set_blocking($stream, true);
		$data = stream_get_contents($stream);	

		return $data;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Отключение
	*/
	function disconnect()
	{
		if ($this->_connection) {
			ssh2_exec($this->_connection, "exit");
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
