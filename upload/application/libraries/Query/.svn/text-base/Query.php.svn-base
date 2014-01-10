<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/

// ------------------------------------------------------------------------

class Query extends CI_Driver_Library {
	
	public $valid_drivers;
    public $CI;
    
    public $engine = 'goldsource';
    public $engine_version = 1;
    var $errors = false;
    
    function __construct()
    {
        $this->CI =& get_instance();
        
        $this->CI->config->load('drivers');
        $drivers = $this->CI->config->item('drivers');
        $this->valid_drivers = $drivers['query'];
    }
    
    function set_engine($engine = 'goldsource', $engine_version = 1)
	{
		$this->engine = strtolower($engine);
		$this->engine_version = $engine_version;
	}
    
    // ------------------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players($host, $port = 0, $engine = false)
	{
		if (is_array($host)) {
			
			/* Данные переданы в массиве в первом параметре 
			 * 
			 * Передача параметров обычным способом
			 * $this->query->get_status('31.31.202.96', 27015, 'goldsource');
			 * 
			 * Передача в массиве в первом параметре функции
			 * $server_data = array('server_port' => '31.31.202.96', 'server_port' => 27015, 'engine' => 'golsource');
			 * $this->query->get_status($server_data);
			 * 
			 * Второй способ удобен, если вы получаете массив с данными игрового сервера
			 * непосредственно из модели servers ( get_server_data ) а после
			 * передаете его сразу в query
			*/
			
			$port = $host['server_port'];
			$engine = strtolower($host['engine']);
			$host = $host['server_ip'];
		}
		
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_players($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение информации о сервере
	*/
	function get_info($host, $port = 0, $engine = false)
	{
		if (is_array($host)) {
			
			/* Данные переданы в массиве в первом параметре 
			 * 
			 * Передача параметров обычным способом
			 * $this->query->get_status('31.31.202.96', 27015, 'goldsource');
			 * 
			 * Передача в массиве в первом параметре функции
			 * $server_data = array('server_port' => '31.31.202.96', 'server_port' => 27015, 'engine' => 'golsource');
			 * $this->query->get_status($server_data);
			 * 
			 * Второй способ удобен, если вы получаете массив с данными игрового сервера
			 * непосредственно из модели servers ( get_server_data )
			*/
			
			$port = $host['server_port'];
			$engine = strtolower($host['engine']);
			$host = $host['server_ip'];
		}
		
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_info($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение переменных сервера
	*/
	function get_rules($host, $port = 0, $engine = false)
	{
		if (is_array($host)) {
			
			/* Данные переданы в массиве в первом параметре 
			 * 
			 * Передача параметров обычным способом
			 * $this->query->get_status('31.31.202.96', 27015, 'goldsource');
			 * 
			 * Передача в массиве в первом параметре функции
			 * $server_data = array('server_port' => '31.31.202.96', 'server_port' => 27015, 'engine' => 'golsource');
			 * $this->query->get_status($server_data);
			 * 
			 * Второй способ удобен, если вы получаете массив с данными игрового сервера
			 * непосредственно из модели servers ( get_server_data )
			*/
			
			$port = $host['server_port'];
			$engine = strtolower($host['engine']);
			$host = $host['server_ip'];
		}
		
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_rules($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Статус сервера
	*/
	function get_status($host, $port = 0, $engine = false)
	{
		if (is_array($host)) {
			
			/* Данные переданы в массиве в первом параметре 
			 * 
			 * Передача параметров обычным способом
			 * $this->query->get_status('31.31.202.96', 27015, 'goldsource');
			 * 
			 * Передача в массиве в первом параметре функции
			 * $server_data = array('server_port' => '31.31.202.96', 'server_port' => 27015, 'engine' => 'golsource');
			 * $this->query->get_status($server_data);
			 * 
			 * Второй способ удобен, если вы получаете массив с данными игрового сервера
			 * непосредственно из модели servers ( get_server_data )
			*/
			
			$port = $host['server_port'];
			$engine = strtolower($host['engine']);
			$host = $host['server_ip'];
		}
		
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_status($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Пинг сервера
	*/
	function ping($host, $port = 0, $engine = false)
	{
		if (is_array($host)) {
			
			/* Данные переданы в массиве в первом параметре 
			 * 
			 * Передача параметров обычным способом
			 * $this->query->get_status('31.31.202.96', 27015, 'goldsource');
			 * 
			 * Передача в массиве в первом параметре функции
			 * $server_data = array('server_port' => '31.31.202.96', 'server_port' => 27015, 'engine' => 'golsource');
			 * $this->query->get_status($server_data);
			 * 
			 * Второй способ удобен, если вы получаете массив с данными игрового сервера
			 * непосредственно из модели servers ( get_server_data )
			*/
			
			$port = $host['server_port'];
			$engine = strtolower($host['engine']);
			$host = $host['server_ip'];
		}
		
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->ping($host, $port);
	}
    
    
}
