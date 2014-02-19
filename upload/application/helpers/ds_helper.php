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
 * Помошник для работы с выделенными серверами.
 * В функции помошника входит отправка комманд, чтение и загрузка файлов.
 *
 * @package		Game AdminPanel
 * @category	Helpers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9-dev3
*/

// ---------------------------------------------------------------------

/*
 * Замена шоткодов в команде
*/
if ( ! function_exists('replace_shotcodes'))
{
	function replace_shotcodes($command, $server_data)
    {
		$CI =& get_instance();
		$CI->load->helper('string');

		/* В случае использования Windows значение может быть пустым
		 * и параметры собьются */
		if(empty($server_data['screen_name'])) {
			$server_data['screen_name'] = 'null';
		}
		
		$command = str_replace('{command}', 	strip_quotes($server_data['start_command']) , $command);							// Команда запуска игрового сервера (напр. "hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire")
		$command = str_replace('{id}', 			strip_quotes($server_data['id']) 			, $command);							// ID сервера
		$command = str_replace('{script_path}', strip_quotes($server_data['script_path']) 	, $command);
		$command = str_replace('{game_dir}', 	strip_quotes($server_data['dir'])  			, $command);							// Директория с игрой
		$command = str_replace('{dir}', 		strip_quotes($server_data['script_path'] . '/' . $server_data['dir'])  , $command);	// Корневая директория (где скрипт запуска)
		$command = str_replace('{name}', 		strip_quotes($server_data['screen_name']) 	, $command);							// Имя скрина
		$command = str_replace('{ip}', 			strip_quotes($server_data['server_ip']) 	, $command);							// IP сервера для коннекта (может не совпадать с ip дедика)
		$command = str_replace('{port}', 		strip_quotes($server_data['server_port']) 	, $command);							// Порт сервера для коннекта
		$command = str_replace('{game}', 		strip_quotes($server_data['start_code']) 	, $command);							// Игра
		$command = str_replace('{user}', 		strip_quotes($server_data['su_user']) 		, $command);							// Пользователь

		/*-------------------*/
		/* Замена по алиасам */
		/*-------------------*/
		
		/* Допустимые алиасы */
		$allowable_aliases 	= json_decode($server_data['aliases_list'], true);
		/* Значения алиасов на сервере */
		$server_aliases 	= json_decode($server_data['aliases'], true);
		
		/* Прогон по алиасам */
		if($allowable_aliases && !empty($allowable_aliases)){
			foreach ($allowable_aliases as $alias) {
				if(isset($server_aliases[$alias['alias']]) && !empty($server_aliases[$alias['alias']])) {
					$command = str_replace('{' . $alias['alias'] . '}', $server_aliases[$alias['alias']] , $command);	
				}
			}
		}
		
		return $command;
	}
}

// ---------------------------------------------------------------------

/*
 * Соединяется с выделенным сервером, производит авторизацию
 * и отправляет заданную команду
*/
if ( ! function_exists('send_command'))
{
	function send_command($command, $server_data, $path = false)
    {
		$CI =& get_instance();
		$CI->load->driver('control');
		
		$command = $CI->servers->replace_shotcodes($command, $server_data);
		
		$path = $path ? $path : $server_data['script_path'];
		$CI->control->set_data(array('os' => $server_data['os'], 'path' => $path));
		$CI->control->set_driver($server_data['control_protocol']);

		$CI->control->connect($server_data['control_ip'], $server_data['control_port']);
		$CI->control->auth($server_data['control_login'], $server_data['control_password']);
		$result = $CI->control->command($command, $path);
		
		return $result;
	}
}

// ---------------------------------------------------------------------

/*
 * Список отправленных команд на выделенный сервер
*/
if ( ! function_exists('get_sended_commands'))
{
	function get_sended_commands()
    {
		$CI =& get_instance();
		return $CI->control->get_sended_commands();
	}
}

// ---------------------------------------------------------------------

/*
 * Последняя отправленная команда на выделенный сервер
*/
if ( ! function_exists('get_last_command'))
{
	function get_last_command()
    {
		$CI =& get_instance();
		return $CI->control->get_last_command();
	}
}
