<?php

class Valve_rcon extends CI_Model {
	
	var $ip;
	var $port;
	var $password;
	var $engine;
	var $rcon_connect;

	/**
	 * Задание переменных
	*/
	function connect($ip, $port, $password, $engine = 'GoldSource'){
		
		$this->ip 		= $ip;
		$this->port 	= $port;
		$this->password = $password;
		$this->engine 	= strtolower($engine);

		switch($this->engine){
			case 'goldsource':
				$this->load->model('hl_rcon');
				$this->hl_rcon->set_variables($this->ip, $this->port, $this->password);
				$this->rcon_connect = @$this->hl_rcon->connect();
				break;
				
			case 'source':
				$this->load->model('source_rcon');
				$this->source_rcon->set_variables($this->ip, $this->port, $this->password);
				
				if($this->rcon_connect = $this->source_rcon->connect()){
					$this->source_rcon->auth();
				}
				
				break;
		}
		
		return $this->rcon_connect;
	}
	

	function command($command){
		
		if(!$this->rcon_connect){
			return 'Could not connect to server';
		}
		
		switch($this->engine){
			case 'goldsource':
				$rcon_string = $this->hl_rcon->command($command);
				break;
				
			case 'source':
				$rcon_string = $this->source_rcon->rconcommand($command);
				break;
		}
		
		return $rcon_string;
	}
}
