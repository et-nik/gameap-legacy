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
		$result = $this->command('list');
		$result = explode("\n", $result);
		unset($result[0]);
		
		$players = array();
		foreach($result as $str) {
			if ($str == '') {
				continue;
			}
			
			$players[] = array(
						'user_name' => $str, 
						'steam_id' => $str,
						'user_id' => '',
						'user_ip' => '',
						'user_time' => '',
			);
		}
		
		return $players;
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
