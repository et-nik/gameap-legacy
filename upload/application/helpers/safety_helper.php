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


/*
 * Фукнции для обеспечения безопасности
 * 
*/


// ---------------------------------------------------------------------

if ( ! function_exists('safesql'))
{
	function safesql($string)
	{
		$CI =& get_instance();
		return $CI->db->escape_str($string);
	}
}

// ---------------------------------------------------------------------

/*
 * Генерирует случайную строку
 * 
 * @param int - длина строки
 * @param str - символы для генерации
 * @return str - строка
 * 
*/

if ( ! function_exists('generate_code'))
{
	function generate_code($length = 6, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789")
	{
		$code = "";

		$clen = strlen($chars) - 1;  
		while (strlen($code) < $length) {

				$code .= $chars[mt_rand(0,$clen)];  
		}

		return $code;
	}
}

// ---------------------------------------------------------------------

/*
 * Хеш пароля (старый)
 * В версии 1.0 функция будет вырезана!
 * 
 * @param str - md5 пароля
 * @param arr - данные пользователя
 * @return str - хеш
 * 
*/
if ( ! function_exists('hash_password_old'))
{
	function hash_password_old($user_password, $user_data)
	{
		$user_password = md5($user_password);
		
		$password_md5 = md5($user_password . $user_data['login']);
		$password_md5 = md5($user_password . $password_md5);
		$password_md5 = md5($user_password . $user_data['login'] . $password_md5);
		$password_md5 = md5($user_password . $user_data['reg_date'] . $password_md5);
		$password_md5 = md5($user_password . $user_data['login'] . $user_data['login'] . $user_data['reg_date'] . $password_md5);
		
		return $password_md5;		
	}
}

// ---------------------------------------------------------------------

/**
 * Хеширование пароля
 */
if ( ! function_exists('hash_password'))
{
	function hash_password($password, $verify = false)
	{
		if ($verify) {
			$salt = $verify;
		} else {
			$salt = '$2a$10$'.substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(),mt_rand()))), 0, 22) . '$';
		}
		
		return crypt($password, $salt);
	}
}
