<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/*
	Basic CS:S Rcon class by Freman.  (V1.00)
	----------------------------------------------
	Ok, it's a completely working class now with with multi-packet responses

	Contact: printf("%s%s%s%s%s%s%s%s%s%d%s%s%s","rc","on",chr(46),"cl","ass",chr(64),"pri","ya",chr(46),2,"y",chr(46),"net")

	Behaviour I've noticed:
		rcon is not returning the packet id.
	
	
	Modify by ET-NiK
	---------------------------------------------
	
*/

define("SERVERDATA_EXECCOMMAND",2);
define("SERVERDATA_AUTH",3);

class Rcon_source extends CI_Driver {
	
	var $fp;
	var $_id = 0;
	
	
	function connect() 
	{
		$this->fp = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
		
		if ($this->fp) {
			stream_set_blocking($this->fp, 0);
			$this->_set_timeout($this->fp, 5);
			$this->auth();
			return true;
		} else {
			return false;
		}
    }
    
    function disconnect() 
	{
		if ($this->fp) {
			fclose($this->fp);
		}
	}

	function auth() 
	{
		if (!$this->fp) {
			return false;
		}
		
		$packid = $this->_write(SERVERDATA_AUTH, $this->password);

		// Real response (id: -1 = failure)
		$ret = $this->_packetread();
		
		if (false == $ret) {
			return false;
		}
		
		if (@$ret[1]['ID'] == -1) {
			return false;
		} else {
			return true;
		}
	}

	function _set_timeout(&$res, $s, $m = 0) 
	{
		if (version_compare(phpversion(),'4.3.0','<')) {
			return socket_set_timeout($res,$s,$m);
		}
		
		return stream_set_timeout($res, $s, $m);
	}

	function _write($cmd, $s1='', $s2='') 
	{
		if (!is_resource($this->fp)) {
			return 0;
		}
		
		// Get and increment the packet id
		$id = ++$this->_id;

		// Put our packet together
		$data = pack("VV", $id, $cmd).$s1.chr(0).$s2.chr(0);

		// Prefix the packet size
		$data = pack("V",strlen($data)).$data;

		// Send packet
		@fwrite($this->fp, $data,strlen($data));
		sleep(1);
		// In case we want it later we'll return the packet id
		return $id;
	}


	function _packetread() 
	{
		
		//Declare the return array
		$retarray = array();
		
		//Fetch the packet size
		while ($size = @fread($this->fp,4)) {
			
			$size = unpack('V1Size',$size);
			//Work around valve breaking the protocol
			
			if ($size["Size"] > 4096) {
				//pad with 8 nulls
				$packet = "\x00\x00\x00\x00\x00\x00\x00\x00".fread($this->fp, 4096);
			} else {
				//Read the packet back
				$packet = @fread($this->fp,$size["Size"]);
			}
			array_push($retarray,unpack("V1ID/V1Response/a*S1/a*S2",$packet));
		}
		
		return $retarray;
	}


	function read() 
	{
		$packets = $this->_packetread();
		$ret = NULL;

		foreach($packets as $pack) {
			if (isset($ret[$pack['ID']])) {
				$ret[$pack['ID']]['S1'] .= $pack['S1'];
				$ret[$pack['ID']]['S2'] .= $pack['S1'];
			} else {
				$ret[$pack['ID']] = array(
					'Response' => $pack['Response'],
					'S1' => $pack['S1'],
					'S2' =>	$pack['S2'],
				);
			}
		}
		
		return $ret;
	}

	function sendcommand($command) 
	{
		//~ $command = '"'.trim(str_replace(' ', '" "', $command)).'"';
		$this->_write(SERVERDATA_EXECCOMMAND, $command,'');
	}

	function command($command) 
	{
		$this->sendcommand($command);

		$ret = $this->read();

		//ATM: Source servers don't return the request id, but if they fix this the code below should read as
		if (isset($ret[$this->_id]['S1'])) {
			return $ret[$this->_id]['S1'];
		}
		else {
			return $ret[0]['S1'];
		}
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
			
			$pattern = '!#([\s]*)(\d*)([\s]*)"(.*?)"(\s*)([a-zA-Z0-9\_\:]*)(\s*)([0-9\:]*)(\s*)(\d*)(\s*)(\d*)(\s*)([a-zA-Z0-9\_\:]*)(\s*)(.*):(\d*)!si';
			$matches = get_matches($pattern, $result);
			
			$count = count($matches);
			$a = 0;
			while ($a < $count) {
				$return[] = array(
						'user_name' => htmlspecialchars($matches[$a]['4']), 
						'steam_id' => $matches[$a]['6'],
						'user_id' => $matches[$a]['2'],
						'user_ip' => $matches[$a]['16'],
						'user_time' => $matches[$a]['8'],
					);
				
				$a++;
			}
			
			return $return;
			
		} else {
			return false;
		}

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
		
		$maps_exp1 = explode("\n", $this->command("maps *"));
		asort($maps_exp1);
		
		foreach($maps_exp1 as $i => $val){
			if($i != 0){
				$val = trim($val);
				if(!empty($val)){
					$maps_exp2 = explode(".", $val);
					$maps[]['map_name'] = $maps_exp2[0];
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
		$this->CI->load->helper('ds');
		$this->CI->load->helper('string');
		
		$server_data =& $this->CI->servers->server_data;

		$dir = get_ds_file_path($server_data);
		
		$file = $dir. $this->CI->servers->server_data['start_code'] . '/cfg/server.cfg'; // Конфиг файл
		$file_contents = read_ds_file($file, $server_data);
		
		/* Ошибка чтения, либо файл не найден */
		if(!$file_contents) {
			return false;
		}
		
		$file_contents 	= change_value_on_file($file_contents, 'rcon_password', $rcon_password);
		$write_result 	= write_ds_file($file, $file_contents, $server_data);
		
		/* Отправляем новый rcon пароль в консоль сервера*/
		if($write_result && $this->CI->servers->server_status($this->CI->servers->server_data['server_ip'], $this->CI->servers->server_data['server_port'])) {
			$rcon_connect = $this->connect();
			$this->command('rcon_password ' . $rcon_password);
		}
		
		return $write_result;
	}
	
}
