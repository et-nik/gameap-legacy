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

// -----------------------------------------------------------------

/**
 * Source Installer драйвер
 *
 * Драйвер для установки игровых серверов Source
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9.5
*/

class Installer_rust extends CI_Driver {
	
	// -----------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	*/
	private function _get_default_map($game_code = 'rust')
	{
		$game_code = strtolower($game_code);
		
		$default_maps = array(
						'rust' 		=> 'rust_island_2013',
		);
		
		return $default_maps[$game_code];
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получает дополнительные данные сервера
	 * Возвращает массив с тремя портами:
	 * 		1. Порт для подключения
	 * 		2. Query порт
	 * 		3. Rcon порт
	 * 
	 * @param int порт для подключения
	 * @return array
	 * 
	 */
	public function get_ports($connect_port = 0)
	{
		return array($connect_port, $connect_port + 1, $connect_port + 1);
	}
	
	// -----------------------------------------------------------------

	/**
	 * Получает путь к списку карт
	 */
	public function get_maps_path($game_code = 'rust')
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
				$start_command = 'rust_server.exe ';
				break;
				
			default:
				$start_command = './rust_server_x32 ';
				break;
		}
		
		$start_command .= '-batchmode -hostname "Rust Server" -maxplayers {maxplayers} -port {port} -datadir "serverdata/" -oxidedir "save/oxide"';

		return $start_command;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'rust', $os = 'linux', $parameters = array())
	{
		$parameters['maxplayers'] 	= isset($parameters['maxplayers']) ? $parameters['maxplayers'] : 50;

		return $parameters;
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
