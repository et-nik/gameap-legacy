<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rcon_rust extends CI_Driver {
	
	var $fp;
	var $_id = 0;
	
	function connect() 
	{
		return $this->CI->rcon->source->connect();
    }
    
    function disconnect() 
	{
		$this->CI->rcon->source->disconnect();
    }

	function command($command) 
	{
		return $this->CI->rcon->source->command($command);
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players()
	{
		$this->CI->load->helper('games');
		
		$return = array();
		if ($result = $this->command('status')) {
			$return = array();
			
			// id name ping connected addr 
			// 76561198041740340 "ACE ;j" 125 2919s 176.51.201.18
			// 76561198042221933 "RK" 78 869s 176.51.246.210
			
			$pattern = '!\s*(\d*)\s*\"(.*?)\"\s*\d*\s*(\d*s)\s*([0-9\.]*)!si';
			$matches = get_matches($pattern, $result);
			
			$count = count($matches);
			$a = 0;
			while ($a < $count) {
				$return[] = array(
						'user_name' 	=> htmlspecialchars($matches[$a]['2']), 
						'user_id' 		=> $matches[$a]['1'],
						'steam_id' 		=> steamid64_to_steamid($matches[$a]['1']),
						'user_ip' 		=> $matches[$a]['4'],
						'user_time' 	=> $matches[$a]['3'],
					);
				
				$a++;
			}
			
		}
		
		return $return;
		
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
		$this->CI->load->helper('ds');
		$this->CI->load->helper('string');
		
		$server_data =& $this->CI->servers->server_data;

		$dir = get_ds_file_path($server_data);
		
		$file = $dir . 'serverdata/cfg/server.cfg'; // Конфиг файл
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
