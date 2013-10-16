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
	function get_players($host, $port, $engine = false)
	{
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_players($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение информации о сервере
	*/
	function get_info($host, $port, $engine = false)
	{
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_info($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение переменных сервера
	*/
	function get_rules($host, $port, $engine = false)
	{
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_rules($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Статус сервера
	*/
	function get_status($host, $port, $engine = false)
	{
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->get_status($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Пинг сервера
	*/
	function ping($host, $port, $engine = false)
	{
		if ($engine == false) {
			$engine = $this->engine;
		}
		
		return $this->$engine->ping($host, $port);
	}
    
    
}
