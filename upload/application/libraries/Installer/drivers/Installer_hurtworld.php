<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2015, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

// -----------------------------------------------------------------

/**
 * HurtWorld Installer драйвер
 *
 * Драйвер для установки игровых серверов HurtWorld
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		1.1
*/

class Installer_hurtworld extends CI_Driver {
	
	// -----------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	*/
	private function _get_default_map($game_code = 'hurtworld')
	{
		$game_code = strtolower($game_code);
		
		$default_maps = array(
						'hurtworld' => 'DiemensLand',
		);
		
		return in_array($game_code, $default_maps) ? $default_maps[$game_code] : "";
	}
	
	public function get_ports($connect_port = 0)
	{
		return array($connect_port, $connect_port + 1000, 0);
	}
	
	// -----------------------------------------------------------------

	/**
	 * Получает путь к списку карт
	 */
	public function get_maps_path($game_code = 'hurtworld')
	{
		return '';
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command($game_code = '', $os = 'linux')
	{
		switch(strtolower($os)) {
			case 'windows':
				$start_command = 'Hurtworld.exe ';
				$start_command .= '-batchmode -nographics -exec ""host {port};queryport {query_port};maxplayers {maxplayers};servername {hostname}"" -logfile output.txt';
				
				break;
				
			default:
				// $start_command = './Hurtworld.x86 ';
				$start_command = './Hurtworld.x86_64 ';
				$start_command .= '-batchmode -nographics -exec "host {port};queryport {query_port};maxplayers {maxplayers};servername {hostname}" -logfile output.txt';
				break;
		}

		return $start_command;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'hurtworld', $os = 'linux', $parameters = array())
	{
		!empty($parameters['hostname']) 		OR $parameters['hostname'] 			= 'HurtWorld Server';
		!empty($parameters['port']) 			OR $parameters['port'] 				= $this->server_data['server_port'];
		!empty($parameters['rcon_password']) 	OR $parameters['rcon_password'] 	= random_string('alnum', 8);
		!empty($parameters['maxplayers']) 		OR $parameters['maxplayers'] 		= 10;

		return $parameters;
	}
	
	// -----------------------------------------------------------------
	
	public function change_server_data(&$server_data)
	{
		
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Правка конфигурационных файлов
	*/
	public function change_config()
	{
		return true;
	}
}
