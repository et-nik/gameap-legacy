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
		$players = explode(",", $result[1]);
		$rplayers = array();

		foreach($players as $str) {
			if ($str == '') {
				continue;
			}
			
			$rplayers[] = array(
						'user_name' => $str, 
						'steam_id' => '',
						'user_id' => $str,
						'user_ip' => '',
						'user_time' => '',
			);
		}
		
		return $rplayers;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Получение списка карт на серввере
	 *  
	*/
	function get_maps()
	{
		return array();
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Смена rcon пароля
	 *  
	*/
	function change_rcon($rcon_password = '')
	{
		$file = 'server.properties'; // Конфиг файл
		$file_contents = $this->CI->servers->read_file($file);
		
		/* Ошибка чтения, либо файл не найден */
		if(!$file_contents) {
			return false;
		}

		$file_contents = change_value_on_file($file_contents, 'rcon.password', $rcon_password);
		return $this->CI->servers->write_file($file, $file_contents, $this->CI->servers->server_data);
	}
}
