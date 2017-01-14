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

// ------------------------------------------------------------------------

/**
 * Source Installer драйвер
 *
 * Драйвер для установки игровых серверов Source
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8.3
*/

class Installer_source extends CI_Driver {
	
	// ------------------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	 * 
	 * @param string		код игры
	 * @return string		карта
	*/
	private function _get_default_map($game_code = 'cstrike')
	{
		$game_code = strtolower($game_code);
		
		$default_maps = array(
						'csgo' 		=> 'de_dust2',
						'cstrike' 	=> 'de_dust2',
						'dods'		=> '', 			// Не знаю карт
						'garrysmod'	=> '', 			// Не знаю карт
						'hl2mp'		=> '', 			// Не знаю карт
						'l4d'		=> '', 			// Не знаю карт
						'l4d2'		=> '', 			// Не знаю карт
						'tf'		=> 'ctf_2fort',
		);
		
		return in_array($game_code, $default_maps) ? $default_maps[$game_code] : "";
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
		return array($connect_port, $connect_port, $connect_port);
	}
	
	// -----------------------------------------------------------------

	/**
	 * Получает путь к списку карт
	 */
	public function get_maps_path($game_code = 'cstrike')
	{
		return $game_code . '/maps';
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command($game_code = '', $os = 'linux')
	{
		switch(strtolower($os)) {
			case 'windows':
				$start_command = 'srcds.exe -console ';
				break;
				
			default:
				$start_command = './srcds_run ';
				break;
		}
		
		switch(strtolower($game_code)) {
			case 'csgo':
				$start_command .= '-game ' . strtolower($game_code) . ' -usercon -strictportbind -ip {ip} -port {port} +clientport {port} -tickrate {fps} +map {default_map} -maxplayers_override {maxplayers}';
				break;
				
			default:
				$start_command .= '-game ' . strtolower($game_code) . ' +ip {ip} +port {port} +maxplayers {maxplayers} +map {default_map} +sys_ticrate {fps}';
				break;
		}
		
		
		return $start_command;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'cstrike', $os = 'linux', $parameters = array())
	{
		$parameters['maxplayers'] 	= isset($parameters['maxplayers']) ? $parameters['maxplayers'] : 32;
		$parameters['fps'] 			= isset($parameters['fps']) ? $parameters['fps'] : 66;
		$parameters['default_map'] 	= $this->_get_default_map($game_code);
		
		return $parameters;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Правка конфигурационных файлов
	*/
	public function change_config()
	{
		return true;
	}
}
