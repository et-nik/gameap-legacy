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
 * San-Andreas Multiplayer Installer драйвер
 *
 * Драйвер для установки игровых серверов GTA: San-Andreas Multiplayer
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9
*/

class Installer_samp extends CI_Driver {
	
	// ------------------------------------------------------------------------
	
	/**
	 * Список стандартных карт
	*/
	private function _get_default_map($game_code = '')
	{
		return true;
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
	public function get_maps_path($game_code = 'samp')
	{
		return '';
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command($game_code = '', $os = 'linux')
	{
		switch(strtolower($os)) {
			case 'windows':
				$start_command = "samp03svr.exe";
				break;
				
			default:
				$start_command = './samp03svr';
				break;
		}
		
		return $start_command;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение настроек для сервера по умолчанию
	*/
	public function get_default_parameters($game_code = 'samp', $os = 'linux', $parameters = array())
	{
		$parameters['maxplayers'] 	= isset($this->_parameters['maxplayers']) ? $this->_parameters['maxplayers'] : 32;
		
		return array();
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Правка конфигурационных файлов
	*/
	public function change_config()
	{
		$CI =& get_instance();
		
		$file = 'server.cfg';
		$dir = get_ds_file_path($this->server_data);
		
		$file_contents = read_ds_file($dir . $file, $this->server_data);
		
		// Костыль. Меняет права файла на 666
		if(strtolower($this->_os) != 'windows') {
			send_command('chmod 666 {dir}/server.cfg', $this->server_data);
		}
		
		// Установка портов
		$file_contents = change_value_on_file($file_contents, 'port', $this->server_data['server_port']);
		$file_contents = change_value_on_file($file_contents, 'rcon_password', random_string('alnum', 8));
		
		$write_result = write_ds_file($dir . $file, $file_contents, $this->server_data);
		
		return true;
	}
}
