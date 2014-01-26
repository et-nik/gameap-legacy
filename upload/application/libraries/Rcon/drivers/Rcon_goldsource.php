<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Модель для работы с рконом 
 * Modyfy by ET-NiK 
*/

class Rcon_goldsource extends CI_Driver {
	
	var $fp;
	var $challenge_number;

	public function connect()
	{
		$this->fp = fsockopen("udp://" . $this->host, $this->port);

		if ($this->fp) {
			$this->getchallengenumber();
			return true;
		} else {
			return false;
		}
	}
	
	public function disconnect()
	{
		return fclose($this->fp) ? true : false;
	}
	
	public function command($command)
	{
		$return = $this->rconcommand("\xff\xff\xff\xffrcon $this->challenge_number \"$this->password\" $command");
		// Вырезаем лишние символы
		$return = $this->cut_symbols($return);
		
		return $return;
	}
	
	private function getchallengenumber()
	{
		$this->challenge_number = trim($this->rconcommand("\xff\xff\xff\xffchallenge rcon"));
		
		if (!empty($this->challenge_number)) {
			$_challenge = explode(" ", $this->challenge_number);
			$this->challenge_number = $_challenge["2"];
			return $this->challenge_number;
		} else {
			return false;
		}
	}
	
	private function rconcommand($command)
	{
		fputs($this->fp, $command, strlen($command));
		$buffer =  fread($this->fp, 1);
		$status = socket_get_status($this->fp);
		$buffer .= fread($this->fp, $status["unread_bytes"]);
		return $buffer;
	}
	
	
	/* Вырезает лишние символы и кракозябры
	 * из ответа сервера
	 * 
	 * string @string - ответ сервера
	*/
	private function cut_symbols($string)
	{
		$string = str_replace("\xff\xff\xff\xff", "" , $string);
		$string = substr($string, 1);
		
		return $string;
	}
	
	public function config($config)
	{
		$config = strtolower($config);
		$return = explode("is", $this->command($config));
		$return = trim($return[1]);
		$return = str_replace('"', '', $return);
		return $return;
	}
	
	public function setconfig($config, $value)
	{
		$config = strtolower($config);
		$this->command($config.' "'.$value.'"');
		return $this->Config($config);
	}
	
	public function stats()
	{
		$stats = explode("\n", $this->command("stats"));
		$stats_ = explode(" ", trim($stats[1]));
		$stats_all = array();
		foreach($stats_ as $val){
			$val = trim($val);
			if($val != "") $stats_all[] = $val;
		}
		$stats_all_ = array(
			"cpu" => $stats_all[0],
			"in" => $stats_all[1],
			"out" => $stats_all[2],
			"uptime" => array(
				"minutes" => $stats_all[3],
			),
			"users" => $stats_all[4],
			"fps" => $stats_all[5],
		);
		return $stats_all_;
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка игроков на сервере
	 * 
	*/
	function get_players()
	{
		if ($result = $this->command('status')) {
			$return = array();
			
			// # 7 "seeking chiters" 818 HLTV hltv:0/128 delay:0 1:17:53 178.124.124.119:44892
			$pattern = '!#\s*\d*\s*\"(.*?)\"\s*(\d*)\s*([a-zA-Z0-9\_\:]*)\s*(hltv\:0\/128 delay\:0|[a-z\-\:0-9]*)\s*([0-9\:]*)\s*(\s*|\d*)\s*(\s*|\d*)\s*([0-9\.]*):(\d*)!si';
			$matches = get_matches($pattern, $result);
			
			$count = count($matches);
			$a = 0;
			while ($a < $count) {
				$return[] = array(
						'user_name' => htmlspecialchars($matches[$a]['1']), 
						'user_id' => $matches[$a]['2'],
						'steam_id' => $matches[$a]['3'],
						'user_ip' => $matches[$a]['8'],
						'user_time' => $matches[$a]['5'],
					);
				
				$a++;
			}
			
		}
		
		return $return;
		
	}
	
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка карт на серввере
	 * 
	 * @return array
	 *  
	*/
	public function get_maps()
	{
		$maps = array();
		$maps_ = explode("\n", $this->command("maps *"));
		foreach($maps_ as $i => $val){
			if($i != 0){
				$val = trim($val);
				if(!empty($val)){
					$maps__ = explode(".", $val);
					$maps[]['map_name'] = $maps__[0];
				}
			}
		}
		return $maps;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Смена rcon пароля
	 *  
	*/
	function change_rcon($rcon_password = '')
	{
		$file = $this->CI->servers->server_data['start_code'] . '/server.cfg'; // Конфиг файл
		$file_contents = $this->CI->servers->read_file($file);
		
		/* Ошибка чтения, либо файл не найден */
		if(!$file_contents) {
			return false;
		}

		$file_contents = change_value_on_file($file_contents, 'rcon_password', $rcon_password);
		$write_result = $this->CI->servers->write_file($file, $file_contents, $this->CI->servers->server_data);
		
		/* Отправляем новый rcon пароль в консоль сервера*/
		if($write_result && $this->CI->servers->server_status($this->CI->servers->server_data['server_ip'], $this->CI->servers->server_data['server_port'])) {
			$rcon_connect = $this->connect();
			$this->command('rcon_password ' . $rcon_password);
		}
		
		if ($write_result) {
			return true;
		} else {
			return false;
		}
	}
}
