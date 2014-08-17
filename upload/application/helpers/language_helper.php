<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 * Modified by Nikita Kuznetsov (ET-NiK)
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Language Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/language_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Lang
 *
 * Fetches a language variable and optionally outputs a form label
 *
 * @access	public
 * @param	string	the language line
 * @param	string	
 * @return	string
 */
if ( ! function_exists('lang'))
{
	function lang($line, $string1 = '', $string2 = '', $string3 = '')
	{
		$CI =& get_instance();
		$line = $CI->lang->line($line);
		
		//~ $arg_list = func_get_args();
		//~ for ($i = 1; $i < $numargs; $i++) {
			//~ echo "Аргумент №$i: " . $arg_list[$i] . "<br />\n";
		//~ }
		
		/* Заменяем %s в строке */
		if ($string1 != '') {
			$line = sprintf($line, $string1, $string2, $string3);
		}

		//~ if ($id != '')
		//~ {
			//~ $line = '<label for="'.$id.'">'.$line."</label>";
		//~ }

		return $line;
	}
}

// ------------------------------------------------------------------------
/* End of file language_helper.php */
/* Location: ./system/helpers/language_helper.php */
