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
 * Работа с shell на локальном сервере
 *
 * Библиотека для работы с удаленными серверами
 * через SSH
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9-dev3
*/
 
class Control_local extends CI_Driver {
	
	// ---------------------------------------------------------------------
	
	public function check()
	{
		$disabled_functions = explode(',', ini_get('disable_functions'));
		
		if (in_array('exec', $disabled_functions)) {
			throw new Exception('exec_disabled');
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
		if (!file_exists($file)) {
			throw new Exception('file_not_found');
			return false;
		}
		
		if (strpos($privileges, 'r') !== false && !is_readable($file)) {
			throw new Exception('file_not_readable');
			return false;
		}
		
		if (strpos($privileges, 'w') !== false && !is_writable($file)) {
			throw new Exception('file_not_writable');
			return false;
		}
		
		if (strpos($privileges, 'x') !== false && !is_executable($file)) {
			throw new Exception('file_not_executable');
			return false;
		}
		
		return true;
	}
	
	// ---------------------------------------------------------------------
	
	function connect($ip = false, $port = 0)
	{
		return true;
	}
	
	// ---------------------------------------------------------------------
	
	function auth($login, $password)
	{
		return true;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Выполнение команды
	*/
	function command($command)
	{
		exec($command, $output);
		return implode("\n", $output);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Выполнение команды
	*/
	function exec($command) 
	{
		return $this->command($command);
	}
	
	// ---------------------------------------------------------------------

	function disconnect()
	{
		return;
	}
	
}


/* End of file Control_local.php */
/* Location: ./application/libraries/Control/drivers/Control_local.php */
