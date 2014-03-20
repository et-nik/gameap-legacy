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
			throw new Exception(lang('server_command_exec_disabled') . ' (Local)');
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
		$file_name = basename($file);
		
		if ($this->os == 'windows' && preg_match('/\"\%[A-Z]*\%.*$/s', $file, $matches)) {
			return $this->_check_file_windows($file, $matches, $privileges = '');
		}
		
		if (!file_exists($file)) {
			throw new Exception(lang('server_command_file_not_found', $file_name) . ' (Local)');
		}
		
		if (strpos($privileges, 'r') !== false && !is_readable($file)) {
			throw new Exception(lang('server_command_file_not_readable', $file_name) . ' (Local)');
		}
		
		if (strpos($privileges, 'w') !== false && !is_writable($file)) {
			throw new Exception(lang('server_command_file_not_writable', $file_name) . ' (Local)');
		}
		
		if (strpos($privileges, 'x') !== false && !is_executable($file)) {
			throw new Exception(lang('server_command_file_not_executable', $file_name) . ' (Local)');
		}
		
		return true;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 */
	private function _check_file_windows($file, $matches, $privileges = '')
	{
		$matches[0] = str_replace('"', '', $matches[0]);
					
		/* Для виндовых слешей \ */
		//~ $explode = explode('\\', $matches[0]);
		//~ $file_name = array_pop($explode);
		//~ $file_dir = '"' . implode('\\', $explode) . '"';
		
		/* Для обычных слешей */
		$file_name = basename($matches[0]);
		$file_dir = '"' . dirname($matches[0]) . '"';
		
		$file_dir = str_replace('/', '\\', $file_dir);

		$result = $this->command('dir ' . $file_dir . ' /a:-d /b');
		$result = explode("\n", $result);
		
		foreach($result as &$value) {
			$value = trim($value);
		}
		
		if (in_array($file_name, $result)) {
			$file_perm['exists'] 		= true;
			$file_perm['readable'] 		= true;
			$file_perm['writable'] 		= true;
			$file_perm['executable'] 	= true;
		}
		
		if (!$file_perm['exists']) {
			throw new Exception(lang('server_command_file_not_found', $file_name) . ' (Local)');
		}
		
		if (strpos($privileges, 'r') !== false && !$file_perm['readable']) {
			throw new Exception(lang('server_command_file_not_readable', $file_name) . ' (Local)');
		}
		
		if (strpos($privileges, 'w') !== false && !$file_perm['writable']) {
			throw new Exception(lang('server_command_file_not_writable', $file_name) . ' (Local)');
		}
		
		if (strpos($privileges, 'x') !== false && !$file_perm['executable']) {
			throw new Exception(lang('server_command_file_not_executable', $file_name) . ' (Local)');
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
		if (!$command) {
			throw new Exception(lang('server_command_empty_command') . ' (Local)');
		}
		
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
