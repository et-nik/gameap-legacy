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


// -----------------------------------------

function safesql($string)
{
	$CI =& get_instance();
	return $CI->db->escape_str($string);
}

// -----------------------------------------

/*
 * Генерирует случайную строку
 * 
 * @param int - длина строки
 * @param str - символы для генерации
 * @return str - строка
 * 
*/

function generate_code($length = 6, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789")
{
    $code = "";

    $clen = strlen($chars) - 1;  
    while (strlen($code) < $length) {

            $code .= $chars[mt_rand(0,$clen)];  
    }

    return $code;
}

// -----------------------------------------

/*
 * Хеш пароля
 * 
 * @param str - md5 пароля
 * @param arr - данные пользователя
 * @return str - хеш
 * 
*/

function hash_password($user_password, $user_data)
{
	$password_md5 = md5($user_password . $user_data['login']);
	$password_md5 = md5($user_password . $password_md5);
	$password_md5 = md5($user_password . $user_data['login'] . $password_md5);
	$password_md5 = md5($user_password . $user_data['reg_date'] . $password_md5);
	$password_md5 = md5($user_password . $user_data['login'] . $user_data['login'] . $user_data['reg_date'] . $password_md5);
	
	return $password_md5;		
}
