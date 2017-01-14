<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rcon_hurtworld extends CI_Driver {
	
	var $fp;
	var $_id = 0;
	
	function connect() 
	{
		return false;
    }
    
    function disconnect() 
	{

    }

	function command($command) 
	{

	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players()
	{
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Получение списка карт на серввере
	*/
	function get_maps()
	{
		return array();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Смена rcon пароля
	*/
	function change_rcon($rcon_password = '')
	{
		
	}
}
