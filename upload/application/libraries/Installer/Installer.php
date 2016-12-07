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
 * Installer библиотека
 *
 * Позволяет конфигурировать вновь созданные игровые серверы,
 * правит игровые файлы, дает права на необходимые файлы.
 * Содержит небольшую базу данных игровых параметров.
 *
 * @package		Game AdminPanel
 * @category	Driver Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8.3
*/

class Installer extends CI_Driver_Library {

	private $_CI;

	public $_os	 				= 'linux';
	public $_game_code 			= '';
	public $_engine 			= '';
	public $_engine_version 	= 1;

	public $_parameters_value	= array();

	public $server_data 		= array();

	// -----------------------------------------------------------------

	function __construct()
    {
		$this->_CI =& get_instance();

        $this->_CI->config->load('drivers');
        $this->_CI->load->helper('patterns_helper');
        $this->_CI->load->helper('string');
        $this->_CI->load->helper('ds');
		

        $this->valid_drivers = array('goldsource', 'source', 'hurtworld' , 'minecraft', 'cod4',
										'mta', 'samp', 'rust', 'teamspeak3',
									);
    }

    // -----------------------------------------------------------------

	/**
	 * Задает значение игры и движка
	*/
    public function set_game_variables($game_code, $engine, $engine_version = 1)
    {
		$this->_game_code 			= $game_code;
		$this->_engine 				= strtolower($engine);
		$this->_engine_version 		= $engine_version;
	}

	// -----------------------------------------------------------------

	/**
	 * Задает значение игры и движка
	*/
	public function set_parameters($parameters)
	{
		$this->set_data($parameters);
	}

	// -----------------------------------------------------------------

	/**
	 * Задает значение игры и движка
	*/
	public function set_data($parameters)
	{
		$this->_parameters = $parameters;
	}

	// -----------------------------------------------------------------

	/**
	 * Задает значение операционной системы
	*/
	public function set_os($os = 'linux')
	{
		$this->_os = $os;
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
		if (false == in_array($this->_engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->_engine . ' not found';
			return '';
		}

		return $this->{$this->_engine}->get_ports($connect_port);
	}

	// -----------------------------------------------------------------

	/**
	 * Получает путь к списку карт
	 */
	public function get_maps_path($game_code = 'valve')
	{
		if (false == in_array($this->_engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->_engine . ' not found';
			return '';
		}

		return $this->{$this->_engine}->get_maps_path($this->_game_code);
	}

	// -----------------------------------------------------------------

	/**
	 * Получение параметра запуска игры
	*/
	public function get_start_command()
	{
		if (false == in_array($this->_engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->_engine . ' not found';
			return '';
		}

		return $this->{$this->_engine}->get_start_command($this->_game_code, $this->_os);
	}

	// -----------------------------------------------------------------

	/**
	 * Получение настроек по умолчанию
	*/
	public function get_default_parameters($aliases_values = array())
	{
		if (false == in_array($this->_engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->_engine . ' not found';
			return '';
		}

		return $this->{$this->_engine}->get_default_parameters($this->_game_code, $this->_os, $aliases_values);
	}

	// -----------------------------------------------------------------

	/**
	 * Изменение данных сервера
	*/
	public function change_server_data(&$server_data)
	{
		if (false == in_array($this->_engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->_engine . ' not found';
		}

		if (method_exists($this->{$this->_engine}, 'change_server_data')) {
			$this->{$this->_engine}->change_server_data($server_data);
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Устанавливает нужные значения в конфигурации
	*/
	public function change_config()
	{
		if (false == in_array($this->_engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->_engine . ' not found';
			return false;
		}

		return $this->{$this->_engine}->change_config();
	}
}
