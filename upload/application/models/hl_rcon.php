<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Модель для работы с рконом 
 * Modyfy by ET-NiK 
*/

class Hl_rcon extends CI_Model {

	var $host;
	var $port = 27015;
	var $pass;
	private $fp, $challenge_number;
	
	public function __construct(){
        // Call the Model constructor
        parent::__construct();
	}
	
	public function set_variables($host, $port, $password)
	{
		$this->host = $host;
		$this->port = $port;
		$this->pass = $password;
	}
	
	public function connect()
	{
		$this->fp = fsockopen("udp://".$this->host, $this->port);
		
		if($this->fp){
			return $this->getchallengenumber();
		}else{
			return FALSE;
		}
	}
	
	public function disconnect()
	{
		return fclose($this->fp) ? true : false;
	}
	
	public function command($command)
	{
		$return = $this->rconcommand("\xff\xff\xff\xffrcon $this->challenge_number \"$this->pass\" $command");
		// Вырезаем лишние символы
		$return = $this->cut_symbols($return);
		
		return $return;
	}
	
	private function getchallengenumber()
	{
		$this->challenge_number = trim($this->rconcommand("\xff\xff\xff\xffchallenge rcon"));
		
		if(!empty($this->challenge_number)){
			$_challenge = explode(" ", $this->challenge_number);
			$this->challenge_number = $_challenge["2"];
			return $this->challenge_number;
		}else return false;
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
	
	public function info()
	{
		$status = $this->command("stat");
		$data = array();
		
		$status_ = explode("\n", $status);
		
		$map = explode(": ", $status_[3]);
		$map = trim($map[1]); $map = str_replace("at", "", $map);
		
		$players = explode(": ", $status_[4]);
		$players = trim($players[1]);
		$players_active = explode("active", $players);
		$players_max = trim($players_active[1]);
		$players_active = trim($players_active[0]);
		$players_max = trim(str_replace(array("(", ")", "max"), "", $players_max));
		
		$data["players"] = array();
		
		if($players_active > 0){
			for($i = 7; $i < $players_active + 7; $i++){
				$user_ = explode("	", $status_[$i]);
				$user = array(
					"id" => trim($user_[0]),
					"name" => trim(str_replace('"', "", $user_[1])),
					"userid" => trim($user_[2]),
					"uniqueid" => trim($user_[3]),
					"frag" => trim($user_[4]),
					"time" => trim($user_[5]),
					"ping" => trim($user_[6]),
					"loss" => trim($user_[7]),
				);
				$data["players"][] = $user;
			}
		}
		
		$data["hostname"] = $this->config("hostname");
		$data["host"] = $this->config("ip").":".$this->config("port");
		$data["map"] = trim($map);
		
		$data["count_players"] = array(
			"active" => $players_active,
			"max" => $players_max
		);
		
		return $data;
	}
	
	
	/* 
	 * array get_server_maps()
	 * Получает список карт с сервера
	 * возвращает массив
	*/
	public function get_server_maps(){
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
}
