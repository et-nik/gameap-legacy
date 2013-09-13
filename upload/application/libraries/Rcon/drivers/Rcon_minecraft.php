<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rcon_minecraft extends CI_Driver {
	
	var $fp;
	var $_id = 0;
	
	function connect() 
	{
		return $this->CI->rcon->source->connect();
    }

	function command($command) 
	{
		return $this->CI->rcon->source->command($command);
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка игроков на сервере
	 * 
	*/
	function get_players()
	{
		
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка карт на серввере
	 *  
	*/
	function get_maps()
	{
		return array();
	}
}
