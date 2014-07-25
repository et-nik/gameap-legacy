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

/**
 * @desc Функция проверяет принадлежность IP-адреса к подсети
 * http://forum.dklab.ru/viewtopic.php?t=21305
 *
 * @author chinsay <my-nickname@gmail.com>
 * @param string $ip
 * @param string $net
 * @return bool
 */
if ( ! function_exists('in_subnet'))
{
	function in_subnet($ip, $net)
	{
		if (is_array($net)) {
			foreach ($net as $net1) {
				if ($return = in_subnet($ip, $net1)) {
					return $return;
				}
			}
			return false;
		}
		
		if(strpos($net,"/") > -1) {
			list($net_ip,$net_mask) = explode("/",$net,2);
			$subnet_octets = array_map("intval",explode(".",$net_ip,4));
			if(preg_match("/^\d+$/",$net_mask)) {
				$mask_octets = str_repeat("1",(int)$net_mask);
				$mask_octets = str_pad($mask_octets,32,'0');
				$mask_octets = substr(chunk_split($mask_octets,8,"."),0,-1);
				$mask_octets = explode(".",$mask_octets,4);
				$mask_octets = array_map("bindec",$mask_octets);
			}
			else {
				$mask_octets = array_map("intval",explode(".",$net_mask));
				if(count($mask_octets) !== 4) return false;
			}
		}
		else {
			$subnet_octets = explode(".",$net);
			if(count($subnet_octets) !== 4) return false;
			$subnet_octets = array_map("intval",$subnet_octets);
			$mask_octets = array();
			foreach($subnet_octets as $o) $mask_octets[] = ( $o ? 255 : 0 );
		}
	
		$subnet_masked = array();
		foreach($subnet_octets as $k => $cur) $subnet_masked[] = $cur & $mask_octets[$k];
	
		$ip_octets = array_map("intval",explode(".",$ip,4));
		if(count($ip_octets) !== 4) return false;
		$ip_masked = array();
		foreach($ip_octets as $k => $cur) $ip_masked[] = $cur & $mask_octets[$k];
	
		return ( $subnet_masked === $ip_masked );
	}
}
