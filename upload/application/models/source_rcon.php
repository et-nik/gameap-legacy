<?php

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

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define("SERVERDATA_EXECCOMMAND",2);
define("SERVERDATA_AUTH",3);

class Source_rcon extends CI_Model {
	
	var $host;
	var $port = 27015;
	var $password;
	var $_sock = null;
	var $_id = 0;
	
	public function set_variables($host, $port, $password)
	{
		$this->host = $host;
		$this->port = $port;
		$this->password = $password;
	}

	function connect() 
	{

		$this->_sock = @fsockopen($this->host, $this->port, $errno, $errstr, 30);
		
		if($this->_sock){
			$this->_set_timeout($this->_sock, 1, 500);
			return TRUE;
		}else{
			return FALSE;
		}
			
    }

	function auth() 
	{
		
		$packid = $this->_write(SERVERDATA_AUTH, $this->password);

		// Real response (id: -1 = failure)
		$ret = $this->_packetread();
		
		if ($ret[1]['ID'] == -1) {
			return FALSE;
		}else{
			return TRUE;
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
		// Get and increment the packet id
		$id = ++$this->_id;

		// Put our packet together
		$data = pack("VV", $id, $cmd).$s1.chr(0).$s2.chr(0);

		// Prefix the packet size
		$data = pack("V",strlen($data)).$data;

		// Send packet
		fwrite($this->_sock,$data,strlen($data));

		// In case we want it later we'll return the packet id
		return $id;
	}


	function _packetread() 
	{
		
		//Declare the return array
		$retarray = array();
		
		//Fetch the packet size
		while ($size = @fread($this->_sock,4)) {
			
			$size = unpack('V1Size',$size);
			//Work around valve breaking the protocol
			
			if ($size["Size"] > 4096) {
				//pad with 8 nulls
				$packet = "\x00\x00\x00\x00\x00\x00\x00\x00".fread($this->_sock,4096);
			} else {
				//Read the packet back
				$packet = fread($this->_sock,$size["Size"]);
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
		$command = '"'.trim(str_replace(' ', '" "', $command)).'"';
		$this->_write(SERVERDATA_EXECCOMMAND, $command,'');
	}

	function rconcommand($command) 
	{
		$this->sendcommand($command);

		$ret = $this->read();

		//ATM: Source servers don't return the request id, but if they fix this the code below should read as
		return $ret[$this->_id]['S1'];
		//return $ret[0]['S1'];
	}
}
