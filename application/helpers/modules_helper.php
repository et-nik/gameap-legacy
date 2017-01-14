<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014-2016, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
*/

// ------------------------------------------------------------------------

/**
 * Помощник с модулями
 *
 * @package		Game AdminPanel
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8
 */
 
// ------------------------------------------------------------------------

/**
 * Проверяет наличие модуля
 * 
 * @param str - имя модуля
 * @return bool
 * 
*/
if (!function_exists('module_exists')) {
	function module_exists($module)
	{
		$CI =& get_instance();
		$CI->gameap_modules->get_modules_list();

		return in_array($module, $CI->gameap_modules->modules_list);
	}
}

// ------------------------------------------------------------------------
/* End of file modules_helper.php */
/* Location: ./application/helpers/modules_helper.php */
