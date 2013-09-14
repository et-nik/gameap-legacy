<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Query_goldsource extends CI_Driver {
	
	// ------------------------------------------------------------------------
	
	// Simple Source/GoldSRC server info collector by morpheus
	private function cutchar(&$string)
	{
		$char = substr($string, 0, 1);
		$string = substr($string, 1);
		return $char;
	}
	
	// ------------------------------------------------------------------------

	private function cutbyte(&$string)
	{
		$byte = ord(substr($string, 0, 1));
		$string = substr($string, 1);
		return $byte;
	}
	
	// ------------------------------------------------------------------------

	private function cutstring(&$string)
	{
		$str = substr($string, 0, StrPos($string, chr(0)));
		$string = substr($string, StrPos($string, chr(0))+1);
		return $str;
	}
	
	// ------------------------------------------------------------------------
	  
	private function cutshort(&$string)
	{
		$short = substr($string, 0, 2);
		list(,$short) = @unpack("S", $short);
		$string = substr($string, 2);
		return $short;
	}
	
	// ------------------------------------------------------------------------
	  
	private function cutlong(&$string)
	{
		$long = substr($string, 0, 4);
		list(,$long) = @unpack("l", $long);
		$string = substr($string, 4);
		return $long;
	}
	
	// ------------------------------------------------------------------------
	  
	private function pastelong($long)
	{
	   return pack("l", $long);
	}
	
	// ------------------------------------------------------------------------
	  
	private function cutfloat(&$string)
	{
		$float = substr($string, 0, 4);
		list(,$float) = @unpack("f", $float);
		$string = substr($string, 4);
		return $float;
	}
	
	// ------------------------------------------------------------------------
	  
	private function request($request,$host,$port)
	{
		$request = "\xFF\xFF\xFF\xFF".$request."\x00";
		$fp = @fsockopen('udp://'.$host, $port);
		if (!$fp) return false;
		@fwrite($fp, $request);
		socket_set_timeout($fp, 1);
		$string=fread($fp, 10240);
		@fclose($fp);
		return $string;
	}


	/*
	 * --------------------------------------------------------
	 *  SOURCE 	and GOLDSOURCE
	 * --------------------------------------------------------
	*/

	function A2A_PING($host, $port) 
	{

		if(!$st = $this->request("\x69",$host,$port)) {
			return false;
		}
		
		$st = substr($st, 4);
		if(substr($st, 0, 1) != "\x6A") return false; else return true;
	}
	
	// ------------------------------------------------------------------------
	  
	function A2S_INFO($host, $port) 
	{
		$st = $this->request("\x54Source Engine Query",$host,$port);
		if (!$st) {
			return FALSE;
		}
		
		$st = substr($st, 4);
		
		if (substr($st, 0, 1) == "\x49") {
			$result['Type'] = $this->cutchar($st); // Char: 'I' (0x49) - For Source
			$result['Version'] = $this->cutbyte($st); // Byte: Network version
			$result['Server Name'] = $this->cutstring($st); // String: The server's name, eg: "Recoil NZ CS Server #1"
			$result['Map'] = $this->cutstring($st); // String: The current map being played, eg: "de_dust"
			$result['Game Directory'] = $this->cutstring($st); // String: The name of the folder containing the game files, eg: "cstrike"
			$result['Game Description'] = $this->cutstring($st); // String: A friendly string name for the game type, eg: "Counter Strike: Source"
			$result['AppID'] = $this->cutshort($st); // Short: Steam Application ID
			$result['Number of players'] = $this->cutbyte($st); // Byte: The number of players currently on the server
			$result['Maximum players'] = $this->cutbyte($st); // Byte: Maximum allowed players for the server
			$result['Number of bots'] = $this->cutbyte($st); // Byte: Number of bot players currently on the server
			$result['Dedicated'] = $this->cutchar($st); // Char: 'l' for listen, 'd' for dedicated, 'p' for SourceTV
			$result['OS'] = $this->cutchar($st); // Char: Host operating system. 'l' for Linux, 'w' for Windows
			$result['Password'] = $this->cutbyte($st); // Byte: If set to 0x01, a password is required to join this server
			$result['Secure'] = $this->cutbyte($st); // Byte: if set to 0x01, this server is VAC secured
			$result['Game Version'] = $this->cutstring($st); // String: The version of the game, eg: "1.0.0.14"
		} elseif (substr($st, 0, 1) == "\x6D") {
			$result['Type'] = $this->cutchar($st); // Char: 'm' (0x6D) - For GoldSrc
			$result['Game IP'] = $this->cutstring($st); // String: Game Server IP address and port
			$result['Server Name'] = $this->cutstring($st); // String: The server's name, eg: "Recoil NZ CS Server #1"
			$result['Map'] = $this->cutstring($st); // String: The current map being played, eg: "de_dust"
			$result['Game Directory'] = $this->cutstring($st); // String: The name of the folder containing the game files, eg: "cstrike"
			$result['Game Description'] = $this->cutstring($st); // String: A friendly string name for the game type, eg: "Counter  Strike: Source"
			$result['Number of players'] = $this->cutbyte($st); // Byte: The number of players currently on the server
			$result['Maximum players'] = $this->cutbyte($st); // Byte: Maximum allowed players for the server
			$result['Version'] = $this->cutbyte($st); // Byte: Network version
			$result['Dedicated'] = $this->cutchar($st); // Char: 'l' for listen, 'd' for dedicated, 'p' for SourceTV
			$result['OS'] = $this->cutchar($st); // Char: Host operating system. 'l' for Linux, 'w' for Windows
			$result['Password'] = $this->cutbyte($st); // Byte: If set to 0x01, a password is required to join this server
			$result['IsMod'] = $this->cutbyte($st); // Byte: If set to 0x01, this byte is followed by ModInfo
			$result['Secure'] = $this->cutbyte($st); // Byte: if set to 0x01, this server is VAC secured
			$result['Number of bots'] = $this->cutbyte($st); // Byte: Number of bot players currently on the server
		  
			if ($result['IsMod'] == 1) {
				$result['URLInfo'] = $this->cutstring($st); // String: URL containing information about this mod
				$result['URLDL'] = $this->cutstring($st); // String: URL to download this mod
				$result['Nul'] = $this->cutbyte($st); // Byte: 0x00
				$result['ModVersion'] = $this->cutlong($st); // Long: Version of the installed mod
				$result['ModSize'] = $this->cutlong($st); // Long: The download size of this mod
				$result['SvOnly'] = $this->cutbyte($st); // Byte: If 1 this is a server side only mod
				$result['ClDLL'] = $this->cutbyte($st); // Byte: If 1 this mod has a custom client dll
			}
		} else {
			return FALSE;
		}
		
		return $result;
	}
	
	// ------------------------------------------------------------------------

	function A2S_SERVERQUERY_GETCHALLENGE($host, $port) 
	{
		$st = $this->request("\x57",$host,$port);
		if (!$st) return false;
		$st = substr($st, 4);
		if (substr($st, 0, 1) != "\x41") return false; else return $this->cutlong(substr($st, 1));
	}
	
	// ------------------------------------------------------------------------
	  
	function A2S_RULES($host, $port, $challenge) 
	{
		$st = $this->request("\x56".$this->pastelong($challenge),$host,$port);
		if (!$st) return false;
		$st=substr($st, 4);
		if (substr($st, 0, 1) == "\x41") {
		  $challenge = $this->cutlong(substr($st, 1));
		  $st = $this->request("\x56".$this->pastelong($challenge),$host,$port);
		  if (!$st) return false;
		  $st = substr($st, 4);
		}
		if (substr($st, 0, 1) != "\x45") return false;
		$result['Type'] = $this->cutchar($st); // Char: Should be equal to 'E'
		$result['Num Rules'] = $this->cutshort($st); // Short: The number of rules reported in response
		for ($i = 1; $i <= $result['Num Rules']; $i++) {
		  $result['Rule Name'][$i] = $this->cutstring($st); // String: The name of the rule
		  $result['Rule Value'][$i] = $this->cutstring($st); // String: The rule's value
		}
		return $result;
	}
	
	// ------------------------------------------------------------------------

	function A2S_PLAYER($host, $port, $challenge) 
	{
		$st = $this->request("\x55".$this->pastelong($challenge),$host,$port);
		if (!$st) return false;
		$st = substr($st, 4);
		if (substr($st, 0, 1) == "\x41") {
		  $challenge = $this->cutlong(substr($st, 1));
		  $st = $this->request("\x55".$this->pastelong($challenge),$host,$port);
		  if (!$st) return false;
		  $st = substr($st, 4);
		}
		if (substr($st, 0, 1) != "\x44") return false;
		$result['Type'] = $this->cutchar($st); // Char: Should be equal to 'D'
		$result['Num Players'] = $this->cutbyte($st); // Byte: The number of players reported in response
		for ($i = 1; $i <= $result['Num Players']; $i++) {
		  $result['Index'][$i] = $this->cutbyte($st); // Byte: The index into [0.. Num Players] for this entry
		  $result['Player Name'][$i] = $this->cutstring($st); // String: Player's name
		  $result['Kills'][$i] = $this->cutlong($st); // Long: Number of kills this player has
		  $result['Time connected'][$i] = $this->cutfloat($st); // Float: The time in seconds this player has been connected
		}
		return $result;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players($host, $port)
	{
		$challenge = $this->A2S_SERVERQUERY_GETCHALLENGE($host, $port);
		$request = $this->A2S_PLAYER($host, $port, $challenge);
		
		$players['names'] = $request['Player Name'];
		$players['score'] = $request['Kills'];
		$players['connected'] = $request['Time connected'];
		
		return $players;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение информации о сервере
	*/
	function get_info($host, $port)
	{
		$request = $this->A2S_INFO($host, $port);
		
		$info['hostname'] 		= $request['Server Name'];
		$info['map'] 			= $request['Map'];
		$info['game'] 			= $request['Game Description'];
		$info['game_code'] 		= $request['Game Directory'];
		$info['players'] 		= $request['Number of players'];
		$info['maxplayers'] 	= $request['Maximum players'];
		$info['version'] 		= $request['Version'];
		$info['password'] 		= $request['Password'];
		
		switch ($request['OS']) {
			case 'l': $info['os'] = 'Linux'; break;
			case 'w': $info['os'] = 'Windows'; break;
			default: $info['os'] = 'Unknown'; break;
		}
		
		return $info;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение переменных сервера
	*/
	function get_rules($host, $port)
	{
		$challenge = $this->A2S_SERVERQUERY_GETCHALLENGE($host, $port);
		
		$st = $this->request("\x56".$this->pastelong($challenge),$host,$port);
		
		if (!$st) { return FALSE; }
		
		$st=substr($st, 4);
		
		if (substr($st, 0, 1) == "\x41") {
		  $challenge = $this->cutlong(substr($st, 1));
		  $st = $this->request("\x56".$this->pastelong($challenge),$host,$port);
		  if (!$st) return false;
		  $st = substr($st, 4);
		}
		
		if (substr($st, 0, 1) != "\x45") return false;
		
		$result['Type'] = $this->cutchar($st); // Char: Should be equal to 'E'
		$result['Num Rules'] = $this->cutshort($st); // Short: The number of rules reported in response
		
		for ($i = 1; $i <= $result['Num Rules']; $i++) {
			$rule_name = $this->cutstring($st);
			$rule_value = $this->cutstring($st);

			$rules[$rule_name] = $rule_value;
		}
		
		return $rules;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Статус сервера
	*/
	function get_status($host, $port)
	{
		return (bool)$this->A2A_PING($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Пинг сервера
	*/
	function ping($host, $port)
	{
		return (int)$this->A2A_PING($host, $port);
	}

}
