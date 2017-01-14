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
 * GoldSource Installer драйвер
 *
 * Драйвер для установки игровых серверов GoldSource
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8.3
*/

class Installer_goldsource extends CI_Driver {
	
	// -----------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	*/
	private function _get_default_map($game_code = 'valve')
	{
		$game_code = strtolower($game_code);
		
		$default_maps = array(
						'ag' 		=> 'ag_crossfire',
						'valve' 	=> 'crossfire',
						'gearbox'	=> 'op4_demise',
						'cstrike' 	=> 'de_dust2',
						'czero'		=> 'de_dust2_cz',
						'dod'		=> 'dod_anzio',
						'dmc'		=> 'dmc_dm2',
						'ricochet'	=> 'rc_deathmatch',
						'tfc'		=> '2fort',
						'svencoop'	=> 'svencoop1',
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
		return array($connect_port, $connect_port, $connect_port);
	}
	
	// -----------------------------------------------------------------

	/**
	 * Получает путь к списку карт
	 */
	public function get_maps_path($game_code = 'valve')
	{
		return $game_code . '/maps';
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command($game_code = '', $os = 'linux')
	{
		switch(strtolower($os)) {
			case 'windows':
				$start_command = 'hlds.exe -console ';
				break;
				
			default:
				$start_command = './hlds_run ';
				break;
		}
		
		$start_command .= '-game ' . strtolower($game_code) . ' +ip {ip} +port {port} +maxplayers {maxplayers} +map {default_map} +sys_ticrate {fps}';
		
		
		return $start_command;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'valve', $os = 'linux', $parameters = array())
	{
		$parameters['maxplayers'] 	= isset($parameters['maxplayers']) ? $parameters['maxplayers'] : 32;
		$parameters['fps'] 			= isset($parameters['fps']) ? $parameters['fps'] : 250;
		$parameters['default_map'] 	= $this->_get_default_map($game_code);
		
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
