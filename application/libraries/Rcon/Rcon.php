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

class Rcon extends CI_Driver_Library {

	public $valid_drivers;
    public $CI;
    
    var $host;
	var $port;
	var $password;
	var $engine;
	var $engine_version;
	var $rcon_connect;
	
	var $errors = false;
	
	// ----------------------------------------------------------------
    
    public function __construct()
    {
        $this->CI =& get_instance();
        
        $this->CI->config->load('drivers');
        $drivers = $this->CI->config->item('drivers');
        $this->valid_drivers = $drivers['rcon'];
    }
    
    // ----------------------------------------------------------------
    
    /**
     * Задать значения хоста, порта, пароля, движка, версии движка
     * 
     * 
    */
    public function set_variables($host, $port, $password, $engine, $engine_version = 1)
    {
		$this->host 			= $host;
		$this->port 			= $port;
		$this->password 		= $password;
		$this->engine 			= strtolower($engine);
		$this->engine_version 	= $engine_version;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Соединение с сервером
	 * 
	 * @return bool
	*/
	public function connect()
	{
		if (false == in_array($this->engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->engine . ' not found';
			return false;
		}
		
		$this->rcon_connect = $this->{$this->engine}->connect();
		
		return (bool)$this->rcon_connect;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Отключение от сервера
	 * 
	 * @return null
	 */
	public function disconnect()
	{
		if (!$this->engine) {
			return;
		}
		
		if (method_exists($this->{$this->engine}, 'disconnect')) {
			$this->{$this->engine}->disconnect();
		}
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Отправка rcon команды на сервер
	 * 
	 * @param string
	 * @return string
	*/
	public function command($command)
	{
		$engine = $this->engine;
		
		if (false == in_array($this->engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->engine . ' not found';
			return false;
		}
		
		if (false == in_array($this->engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->engine . ' not found';
			return false;
		}

		$rcon_string = $this->$engine->command($command);
		
		return $rcon_string;
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка игроков на сервере
	 * 
	*/
	public function get_players()
	{
		$engine = $this->engine;
		
		if (false == in_array($this->engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->engine . ' not found';
			return false;
		}
		
		return $this->$engine->get_players();
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка карт
	 *  
	*/
	public function get_maps()
	{
		$engine = $this->engine;
		
		if (false == in_array($this->engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->engine . ' not found';
			return false;
		}
		
		return $this->$engine->get_maps();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Смена rcon пароля
	 *  
	*/
	public function change_rcon($rcon_password)
	{
		$this->CI->load->helper('patterns_helper');
		
		$engine = $this->engine;
		
		if (false == in_array($this->engine, $this->valid_drivers)) {
			$this->errors = 'Driver' . $this->engine . ' not found';
			return false;
		}
		
		return $this->$engine->change_rcon($rcon_password);
	}
}
