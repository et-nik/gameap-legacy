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
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 * @param str	файл
	 * @param str 	строка с правами (rwx)
	 */
	public function check_file($file, $privileges = '')
	{
		if (!file_exists($file)) {
			throw new Exception('File not found');
			return false;
		}
		
		if (strpos($privileges, 'r') !== false && !is_readable($file)) {
			throw new Exception('File not readable');
			return false;
		}
		
		if (strpos($privileges, 'w') !== false && !is_writable($file)) {
			throw new Exception('File not writable');
			return false;
		}
		
		if (strpos($privileges, 'x') !== false && !is_executable($file)) {
			throw new Exception('File not executable');
			return false;
		}
		
		return true;
	}
}
