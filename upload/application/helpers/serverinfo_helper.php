<?php

// Simple Source/GoldSRC server info collector by morpheus
function cutchar(&$string)
{
	$char = substr($string, 0, 1);
	$string = substr($string, 1);
	return $char;
}

function cutbyte(&$string)
{
    $byte = ord(substr($string, 0, 1));
    $string = substr($string, 1);
    return $byte;
}

function cutstring(&$string)
{
    $str = substr($string, 0, StrPos($string, chr(0)));
    $string = substr($string, StrPos($string, chr(0))+1);
    return $str;
}
  
function cutshort(&$string)
{
    $short = substr($string, 0, 2);
    list(,$short) = @unpack("S", $short);
    $string = substr($string, 2);
    return $short;
}
  
function cutlong(&$string)
{
    $long = substr($string, 0, 4);
    list(,$long) = @unpack("l", $long);
    $string = substr($string, 4);
    return $long;
}
  
function pastelong($long)
{
   return pack("l", $long);
}
  
function cutfloat(&$string)
{
    $float = substr($string, 0, 4);
    list(,$float) = @unpack("f", $float);
    $string = substr($string, 4);
    return $float;
}
  
function request($request,$host,$port)
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

    if(!$st = request("\x69",$host,$port)) {
		return false;
	}
    
    $st = substr($st, 4);
    if(substr($st, 0, 1) != "\x6A") return false; else return true;
}
  
function A2S_INFO($host, $port) 
{
    $st = request("\x54Source Engine Query",$host,$port);
    if (!$st) {
		return FALSE;
	}
	
    $st = substr($st, 4);
    
    if (substr($st, 0, 1) == "\x49") {
		$result['Type'] = cutchar($st); // Char: 'I' (0x49) - For Source
		$result['Version'] = cutbyte($st); // Byte: Network version
		$result['Server Name'] = cutstring($st); // String: The server's name, eg: "Recoil NZ CS Server #1"
		$result['Map'] = cutstring($st); // String: The current map being played, eg: "de_dust"
		$result['Game Directory'] = cutstring($st); // String: The name of the folder containing the game files, eg: "cstrike"
		$result['Game Description'] = cutstring($st); // String: A friendly string name for the game type, eg: "Counter Strike: Source"
		$result['AppID'] = cutshort($st); // Short: Steam Application ID
		$result['Number of players'] = cutbyte($st); // Byte: The number of players currently on the server
		$result['Maximum players'] = cutbyte($st); // Byte: Maximum allowed players for the server
		$result['Number of bots'] = cutbyte($st); // Byte: Number of bot players currently on the server
		$result['Dedicated'] = cutchar($st); // Char: 'l' for listen, 'd' for dedicated, 'p' for SourceTV
		$result['OS'] = cutchar($st); // Char: Host operating system. 'l' for Linux, 'w' for Windows
		$result['Password'] = cutbyte($st); // Byte: If set to 0x01, a password is required to join this server
		$result['Secure'] = cutbyte($st); // Byte: if set to 0x01, this server is VAC secured
		$result['Game Version'] = cutstring($st); // String: The version of the game, eg: "1.0.0.14"
	} elseif (substr($st, 0, 1) == "\x6D") {
		$result['Type'] = cutchar($st); // Char: 'm' (0x6D) - For GoldSrc
		$result['Game IP'] = cutstring($st); // String: Game Server IP address and port
		$result['Server Name'] = cutstring($st); // String: The server's name, eg: "Recoil NZ CS Server #1"
		$result['Map'] = cutstring($st); // String: The current map being played, eg: "de_dust"
		$result['Game Directory'] = cutstring($st); // String: The name of the folder containing the game files, eg: "cstrike"
		$result['Game Description'] = cutstring($st); // String: A friendly string name for the game type, eg: "Counter  Strike: Source"
		$result['Number of players'] = cutbyte($st); // Byte: The number of players currently on the server
		$result['Maximum players'] = cutbyte($st); // Byte: Maximum allowed players for the server
		$result['Version'] = cutbyte($st); // Byte: Network version
		$result['Dedicated'] = cutchar($st); // Char: 'l' for listen, 'd' for dedicated, 'p' for SourceTV
		$result['OS'] = cutchar($st); // Char: Host operating system. 'l' for Linux, 'w' for Windows
		$result['Password'] = cutbyte($st); // Byte: If set to 0x01, a password is required to join this server
		$result['IsMod'] = cutbyte($st); // Byte: If set to 0x01, this byte is followed by ModInfo
		$result['Secure'] = cutbyte($st); // Byte: if set to 0x01, this server is VAC secured
		$result['Number of bots'] = cutbyte($st); // Byte: Number of bot players currently on the server
      
		if ($result['IsMod'] == 1) {
			$result['URLInfo'] = cutstring($st); // String: URL containing information about this mod
			$result['URLDL'] = cutstring($st); // String: URL to download this mod
			$result['Nul'] = cutbyte($st); // Byte: 0x00
			$result['ModVersion'] = cutlong($st); // Long: Version of the installed mod
			$result['ModSize'] = cutlong($st); // Long: The download size of this mod
			$result['SvOnly'] = cutbyte($st); // Byte: If 1 this is a server side only mod
			$result['ClDLL'] = cutbyte($st); // Byte: If 1 this mod has a custom client dll
		}
    } else {
		return FALSE;
	}
	
    return $result;
}

function A2S_SERVERQUERY_GETCHALLENGE($host, $port) 
{
    $st = request("\x57",$host,$port);
    if (!$st) return false;
    $st = substr($st, 4);
    if (substr($st, 0, 1) != "\x41") return false; else return cutlong(substr($st, 1));
}
  
function A2S_RULES($host, $port, $challenge) 
{
    $st = request("\x56".pastelong($challenge),$host,$port);
    if (!$st) return false;
    $st=substr($st, 4);
    if (substr($st, 0, 1) == "\x41") {
      $challenge = cutlong(substr($st, 1));
      $st = request("\x56".pastelong($challenge),$host,$port);
      if (!$st) return false;
      $st = substr($st, 4);
    }
    if (substr($st, 0, 1) != "\x45") return false;
    $result['Type'] = cutchar($st); // Char: Should be equal to 'E'
    $result['Num Rules'] = cutshort($st); // Short: The number of rules reported in response
    for ($i = 1; $i <= $result['Num Rules']; $i++) {
      $result['Rule Name'][$i] = cutstring($st); // String: The name of the rule
      $result['Rule Value'][$i] = cutstring($st); // String: The rule's value
    }
    return $result;
}

function A2S_PLAYER($host, $port, $challenge) 
{
    $st = request("\x55".pastelong($challenge),$host,$port);
    if (!$st) return false;
    $st = substr($st, 4);
    if (substr($st, 0, 1) == "\x41") {
      $challenge = cutlong(substr($st, 1));
      $st = request("\x55".pastelong($challenge),$host,$port);
      if (!$st) return false;
      $st = substr($st, 4);
    }
    if (substr($st, 0, 1) != "\x44") return false;
    $result['Type'] = cutchar($st); // Char: Should be equal to 'D'
    $result['Num Players'] = cutbyte($st); // Byte: The number of players reported in response
    for ($i = 1; $i <= $result['Num Players']; $i++) {
      $result['Index'][$i] = cutbyte($st); // Byte: The index into [0.. Num Players] for this entry
      $result['Player Name'][$i] = cutstring($st); // String: Player's name
      $result['Kills'][$i] = cutlong($st); // Long: Number of kills this player has
      $result['Time connected'][$i] = cutfloat($st); // Float: The time in seconds this player has been connected
    }
    return $result;
}

/**
 * Gets the status of the target server.
 * @param string    $host    domain or ip address
 * @param int    $port    default(25565)
*/
function minecraft_get_status($host = '127.0.0.1', $port = 25565) 
{

	//Transform domain to ip address.
	if (substr_count($host , '.') != 4) $host = gethostbyname($host);

	//Get timestamp for the ping
	$start = microtime(true);

	//Connect to the server
	if(!$socket = @stream_socket_client('tcp://'.$host.':'.$port, $errno, $errstr, $this->timeout)) {

		//Server is offline
		return false;


	} else {

		stream_set_timeout($socket, $this->timeout);

		//Write and read data
		fwrite($socket, "\xFE\x01");
		$data = fread($socket, 2048);
		fclose($socket);
		if($data == null) return false;

		//Calculate the ping
		$ping = round((microtime(true)-$start)*1000);

		//Evaluate the received data
		if (substr((String)$data, 3, 5) == "\x00\xa7\x00\x31\x00"){

			$result = explode("\x00", mb_convert_encoding(substr((String)$data, 15), 'UTF-8', 'UCS-2'));
			$motd = preg_replace("/(§.)/", "",$result[1]);

		}else{

			$result = explode('§', mb_convert_encoding(substr((String)$data, 3), 'UTF-8', 'UCS-2'));

			$motd = "";
			foreach ($result as $key => $string) {
				if($key != sizeof($result)-1 && $key != sizeof($result)-2 && $key != 0) {
					$motd .= '§'.$string;
				}
			}

			$motd = preg_replace("/(§.)/", "", $motd);

		}
		//Remove all special characters from a string
		$motd = preg_replace("/[^[:alnum:][:punct:] ]/", "", $motd);

		//Set variables
		$res = array();
		$res['hostname'] = $host;
		$res['version'] = $result[0];
		$res['motd'] = $motd;
		$res['players'] = $result[sizeof($result)-2];
		$res['maxplayers'] = $result[sizeof($result)-1];
		$res['ping'] = $ping;

		//return obj
		return $res;
	}
}

function get_matches($pattern, $string, $matches_return = null)
{
	
	/*
		Функция для парсинга
		
		get_string($pattern, $string, $matches_return);
		
		$pattern - регулярное выражение
		$string - строка из которой необходимо выбрать нужное
		$matches_return - возвращаемые вхождения
		
		
		в string можно передавать любые данные
		например, данные полученные через ркон командой status
		
		hostname:  UMI7EPATOP CEPBEP - HLDM.ORG
		version :  48/1.1.2.1/Stdio 5787 secure  (70)
		tcp/ip  :  31.31.202.96:27015
		map     :  tau_cannon at: 0 x, 0 y, 0 z
		players :  3 active (32 max)

		#      name userid uniqueid frag time ping loss adr
		# 1 "uBaH_KpuBopyKoB" 319 BOT   6  3:15:46    0    0
		# 4  "Nitro" 305 STEAM_0:0:785980079   5 02:34   52    0 176.32.12.6:27005
		# 6 "Dima^8^zombie" 289 STEAM_0:0:225349380   8 06:19    7    0 128.71.75.49:27005
		3 users
		
		
		
		Регулярное выражение
		
		# 4  "Nitro" 305 STEAM_0:0:785980079   5 02:34   52    0 176.32.12.6:27005
		\#(\s*)(\d*)(\s*)"(.*?)"(\s*)(\d*)(\s*)([a-zA-Z0-9\_\:]*?)(\s*)(\d*)(\s*)([0-9\:]*)(\s*)(\d*)(\s*)(\d*)(\s*)(.*?) 
	
	*/

	$string_expl = explode("\n", $string);

	$mreturn = array();
	$a = 0;
	$b = -1;
		
	while ($a < count($string_expl))
    {
			
		$matches = null;
	
		$preg_match = preg_match($pattern, $string_expl[$a], $matches);

		//print_r($matches);
			
		if($preg_match)
        {	
			$b++;
			$mreturn[$b] = $matches;
		}
			
		$a++;
	}
		
	return $mreturn;

}


/**
 * Получает при помощи регулярных выражений из полученной status строки 
 * список игроков и их данные
 * 
 * 
*/
function get_players($string, $engine = 'GoldSource'){
	
	/*
	* Получение информации об игроках
	* 
	* 
	* ----- GoldSource ----
	* 
	* # name userid uniqueid frag time ping loss adr
	* # 4  "Nitro" 305 STEAM_0:0:785980079   5 02:34   52    0 176.32.12.6:27005
	* 
	* arr[1] - порядковый номер
	* arr[4] - ник пользователя
	* arr[6] - id пользователя
	* arr[8] - steam_id пользователя
	* arr[10] - фраги
	* arr[12] - время
	* arr[14] - пинг
	* arr[16] - потери (loss)
	* arr[18] - IP
	* arr[19] - Port
	* 
	* ----- Source ----
	* 
	* # userid name uniqueid connected ping loss state adr
	* # 997 "Morrigan" STEAM_0:1:62410515 09:56 90 0 active 91.144.129.95:27005
	* 
	* arr[2] - id пользователя
	* arr[4] - ник пользователя
	* arr[6] - steam_id
	* arr[8] - время
	* arr[10] - пинг
	* arr[12] - потери (loss)
	* arr[14] - состояние
	* arr[16] - ip
	* arr[17] - порт
	* 
	* 
	*/
	
	$engine = strtolower($engine);

	if($string){
		
		$return = FALSE;
		
		switch($engine) {
			case 'goldsource':
				$pattern = '!#([\s]*)(\d*)([\s]*)"(.*?)"(\s*)(\d*)(\s*)([a-zA-Z0-9\_\:]*)(\s*)([0-9\-]*)(\s*)([0-9\:]*)(\s*)(\d*)(\s*)(\d*)(\s*)(.*):(\d*)!si';
				break;
			case 'source':
				$pattern = '!#([\s]*)(\d*)([\s]*)"(.*?)"(\s*)([a-zA-Z0-9\_\:]*)(\s*)([0-9\:]*)(\s*)(\d*)(\s*)(\d*)(\s*)([a-zA-Z0-9\_\:]*)(\s*)(.*):(\d*)!si';
				break;
		}
		
		$matches = get_matches($pattern, $string);
		
		for ($a = 0; $a < count($matches); $a++) {
			switch($engine){
				case 'goldsource':
					$return[] = array(
						'user_name' => $matches[$a]['4'], 
						'steam_id' => $matches[$a]['8'],
						'user_id' => $matches[$a]['6'],
						'user_ip' => $matches[$a]['18'],
						'user_time' => $matches[$a]['12'],
					);
								
					break;
				case 'source':
					$return[] = array(
						'user_name' => $matches[$a]['4'], 
						'steam_id' => $matches[$a]['6'],
						'user_id' => $matches[$a]['2'],
						'user_ip' => $matches[$a]['16'],
						'user_time' => $matches[$a]['8'],
					);
					
					break;
			}
			  
		}
		
		return $return;
	}
}
