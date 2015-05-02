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
 * TeamSpeak Installer драйвер
 *
 * Драйвер для установки TeamSpeak 3
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		1.0
*/

class Installer_teamspeak3 extends CI_Driver {
	
	// -----------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	*/
	private function _get_default_map()
	{
		return '';
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
		return array($connect_port, $connect_port + 1, $connect_port + 2);
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
				//~ $start_command = 'RustDedicated.exe ';
				break;
				
			default:
				$start_command = './ts3server_minimal_runscript.sh voice_ip={ip} port={port} default_voice_port={port} filetransfer_ip={ip} filetransfer_port={filetransfer_port} query_ip={ip} query_port={query_port} ';
				break;
		}

		return $start_command;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'rust', $os = 'linux', $parameters = array())
	{
		!empty($parameters['filetransfer_port']) 	OR $parameters['filetransfer_port'] = $this->server_data['server_port']+2;
		return $parameters;
	}
	
	// -----------------------------------------------------------------
	
	public function change_server_data(&$server_data)
	{
		//~ $server_data['rcon'] = '{alias_rcon_password}';
		return true;
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
