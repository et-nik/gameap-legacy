<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @author Edward McKnight (EM-Creations.co.uk)
 */

/* *****************************************************************
// SampQuery.class.php
// Version 1.0
// This class connects to a specific SA-MP server via sockets.
// Copyright 2012 Edward McKnight (EM-Creations.co.uk)
// Creative Commons Attribution-NoDerivs 3.0 Unported License
// http://creativecommons.org/licenses/by-nd/3.0/
// Credits to Westie for the original PHP SA-MP API and inspiration for this API.
* *****************************************************************/

/*
 * Modify by ET-NiK
 * Special for GameAP (http://www.gameap.ru) 
*/

class Query_samp extends CI_Driver {
    private $sock = null;
	var $host;
	var $port;
	
    /**
    * Returns a multidimensional array of detailed player information. 
    * @return Array[]
    * (
    *   [0] => Array
    *	(
    *       [playerid] => playerid
    *       [nickname] => playername
    *       [score] => score
    *       [ping] => pinh
    *	)
    *   ... 
    * )
    * @see getBasicPlayers()
    */
    public function getDetailedPlayers() {
        @fwrite($this->sock, $this->assemblePacket("d"));
        fread($this->sock, 11);

        $playerCount = ord(fread($this->sock, 2));
        $players = array();

        for($i = 0; $i < $playerCount; ++$i) {
            $player['playerid'] = (integer) ord(fread($this->sock, 1));

            $strLen = ord(fread($this->sock, 1));
            $player['nickname'] = (string) fread($this->sock, $strLen);

            $player['score'] = (integer) $this->toInt(fread($this->sock, 4));
            $player['ping'] = (integer) $this->toInt(fread($this->sock, 4));

            $players[$i] = $player;
            unset($player);
        }
        return $players;
    }

    private function toInt($string) {
        if($string === "") {
            return null;
        }

        $int = 0;
        $int += (ord($string[0]));

        if(isset($string[1])) {
            $int += (ord($string[1]) << 8);
        }

        if(isset($string[2])) {
            $int += (ord($string[2]) << 16);
        }

        if(isset($string[3])) {
            $int += (ord($string[3]) << 24);
        }

        if($int >= 4294967294) {
            $int -= 4294967296;
        }
        return $int;
    }

    private function assemblePacket($type) {
        $packet = "SAMP";
        $packet .= chr(strtok($this->host, "."));
        $packet .= chr(strtok("."));
        $packet .= chr(strtok("."));
        $packet .= chr(strtok("."));
        $packet .= chr($this->port & 0xFF);
        $packet .= chr($this->port >> 8 & 0xFF);
        $packet .= $type;

        return $packet;
    }
    
    /**
    * Attempts to connect to the server and returns whether it was successful.
    * @return boolean
    */
    public function connect($host, $port) {
        $connected = false;
        
        $this->host = $host;
        $this->port = $port;
        
        $this->sock = fsockopen("udp://" . $this->host, $this->port, $errorNum, $errorString, 2);
        socket_set_timeout($this->sock, 2);
        
        fwrite($this->sock, $this->assemblePacket("p0101"));

        if(fread($this->sock, 10)) {
            if(fread($this->sock, 5) == 'p0101') {
                $connected = true;
            }
        }
        return $connected;
    }
    
    
    // ------------------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players($host, $port)
	{
		if (!$this->sock) {
			$this->connect($host, $port);
		}
		
		@fwrite($this->sock, $this->assemblePacket("c"));
        fread($this->sock, 11);

        $playerCount = ord(fread($this->sock, 2));
        $players = array();

        if($playerCount > 0) {
            for($i = 0; $i < $playerCount; ++$i) {
                $strLen = ord(fread($this->sock, 1));
                $players[$i] = array
                (
                    "names" => (string) fread($this->sock, $strLen),
                    "score" => (integer) $this->toInt(fread($this->sock, 4)),
                    "connected" => '',
                );
            }
        }
		
		return $players;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение информации о сервере
	*/
	function get_info($host, $port)
	{
		if (!$this->sock) {
			$this->connect($host, $port);
		}
		
		@fwrite($this->sock, $this->assemblePacket("i"));

        fread($this->sock, 11);

        $info = array();

        $info['password'] = (integer) ord(fread($this->sock, 1));
        $info['players'] = (integer) $this->toInt(fread($this->sock, 2));
        $info['maxplayers'] = (integer) $this->toInt(fread($this->sock, 2));

        $strLen = ord(fread($this->sock, 4));
        if(!$strLen) return -1;

        $info['hostname'] = (string) fread($this->sock, $strLen);

        $strLen = ord(fread($this->sock, 4));
        $info['gamemode'] = (string) fread($this->sock, $strLen);

        $strLen = ord(fread($this->sock, 4));
        $info['map'] = (string) fread($this->sock, $strLen);
        

		$info['game'] 			= 'San Andreas Multiplayer';
		$info['game_code'] 		= 'samp';
		$info['version'] 		= '';
		$info['os'] 			= 'Unknown';

		return $info;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение переменных сервера
	*/
	function get_rules($host, $port)
	{
		if (!$this->sock) {
			$this->connect($host, $port);
		}
		
		@fwrite($this->sock, $this->assemblePacket("r"));
        fread($this->sock, 11);

        $ruleCount = ord(fread($this->sock, 2));
        $rules = array();

        for($i = 0; $i< $ruleCount; ++$i) {
            $strLen = ord(fread($this->sock, 1));
            $rule = (string) fread($this->sock, $strLen);

            $strLen = ord(fread($this->sock, 1));
            $rules[$rule] = (string) fread($this->sock, $strLen);
        }
        return $rules;
		
		//~ $rules['rule'] = $request['Rule Name'];
		//~ $rules['value'] = $request['Rule Value'];
		
		return $rules;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Статус сервера
	*/
	function get_status($host, $port)
	{
		return (bool)$this->ping($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Пинг сервера
	*/
	function ping($host, $port)
	{
		if (!$this->sock) {
			$this->connect($host, $port);
		}
		
		$ping = 0;
        $beforeSend = microtime(true);
        @fwrite($this->sock, $this->assemblePacket("r"));
        fread($this->sock, 15);
        $afterReceive = microtime(true);
        
        $ping = ($afterReceive - $beforeSend) * 1000;
        
        return round($ping);
	}
    
}
