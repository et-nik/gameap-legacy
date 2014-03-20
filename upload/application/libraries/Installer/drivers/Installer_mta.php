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
 * Multi Theft Auto драйвер
 *
 * Драйвер для установки игровых серверов GTA: Multi Theft Auto 
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9
*/

class Installer_mta extends CI_Driver {
	
	// ------------------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	*/
	private function _get_default_map($game_code = '')
	{
		return true;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command($game_code = '', $os = 'linux')
	{
		switch(strtolower($os)) {
			case 'windows':
				$start_command = "mta-server.exe";
				break;
				
			default:
				$start_command = './mta-server';
				break;
		}
		
		return $start_command;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'mta', $os = 'linux', $parameters = array())
	{
		return array();
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Правка конфигурационных файлов
	*/
	public function change_config()
	{
		$CI =& get_instance();
		
		$file = 'mods/deathmatch/mtaserver.conf';
		$dir = get_ds_file_path($this->server_data);
		
		$file_contents = read_ds_file($dir . $file, $this->server_data);
		
		// Установка портов
		$file_contents = change_value_on_file($file_contents, 'serverport', $this->server_data['server_port']);
		$file_contents = change_value_on_file($file_contents, 'httpport', $this->server_data['server_port']);
		
		$write_result = write_ds_file($dir . $file, $file_contents, $this->server_data);
		
		return true;
	}
}
