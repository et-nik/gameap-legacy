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
		$rplayers = array();
		$players = array();
		
		$result = $this->command('list');
		$result = explode("\n", $result);
		
		if (isset($result[1])) {
			$players = explode(",", $result[1]);
		}
		
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
		$this->CI->load->helper('ds');
		$this->CI->load->helper('string');
		
		$server_data =& $this->CI->servers->server_data;

		$dir = get_ds_file_path($server_data);
		
		$file = $dir . 'server.properties'; // Конфиг файл
		$file_contents = read_ds_file($file, $server_data);
		
		/* Ошибка чтения, либо файл не найден */
		if(!$file_contents) {
			return false;
		}

		$file_contents 	= change_value_on_file($file_contents, 'rcon.password', $rcon_password);
		$write_result 	= write_ds_file($file, $file_contents, $server_data);
		
		return $write_result;
	}
}
