<?php
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

class SampQuery {
    private $sock = null;
    private $server = null;
    private $port = null;
	
    /**
    *	Creates a new SampQuery object.
    *	@param $server server hostname
    *	@param $port port of the server
    */
    public function __construct($server, $port=7777)
    {
        $this->server = $server;
        $this->port = $port;

        $this->sock = fsockopen("udp://".$this->server, $this->port, $errorNum, $errorString, 2);
        socket_set_timeout($this->sock, 2);
    }

    /**
    * Returns an array of server information. 
    * @return Array[]
    * (
    *   [password] => 0 or 1
    *   [players] => players
    *   [maxplayers] => maxPlayers
    *   [hostname] => hostName
    *   [gamemode] => gamemode
    *   [map] => map
    * )
    */
    public function getInfo() {
        @fwrite($this->sock, $this->assemblePacket("i"));

        fread($this->sock, 11);

        $serverInfo = array();

        $serverInfo['password'] = (integer) ord(fread($this->sock, 1));

        $serverInfo['players'] = (integer) $this->toInt(fread($this->sock, 2));

        $serverInfo['maxplayers'] = (integer) $this->toInt(fread($this->sock, 2));

        $strLen = ord(fread($this->sock, 4));
        if(!$strLen) return -1;

        $serverInfo['hostname'] = (string) fread($this->sock, $strLen);

        $strLen = ord(fread($this->sock, 4));
        $serverInfo['gamemode'] = (string) fread($this->sock, $strLen);

        $strLen = ord(fread($this->sock, 4));
        $serverInfo['map'] = (string) fread($this->sock, $strLen);

        return $serverInfo;
    }


    /**
    * Returns a multidimensional array of basic player information.
    * @return Array[]
    * (
    *   [0] => Array[]
    *       (
    *           [name] => playerName
    *           [score] => score
    *       )
    *	...
    * )
    * @see getDetailedPlayers()
    */
    public function getBasicPlayers() {
        @fwrite($this->sock, $this->assemblePacket("c"));
        fread($this->sock, 11);

        $playerCount = ord(fread($this->sock, 2));
        $players = array();

        if($playerCount > 0) {
            for($i = 0; $i < $playerCount; ++$i) {
                $strLen = ord(fread($this->sock, 1));
                $players[$i] = array
                (
                    "name" => (string) fread($this->sock, $strLen),
                    "score" => (integer) $this->toInt(fread($this->sock, 4)),
                );
            }
        }
        return $players;
    }


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


    /**
    * Returns an array of server rules.
    * @return Array[]
    * (
    *   [gravity] => gravity
    *   [mapname] => map
    *   [version] => version
    *   [weather] => weather
    *   [weburl] => weburl
    *   [worldtime] => worldtime
    * )
    */
    public function getRules() {
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
    }
    
    /**
    * Returns the server's ping.
    * @return integer
    */
    public function getPing() {
        $ping = 0;
        $beforeSend = microtime(true);
        @fwrite($this->sock, $this->assemblePacket("r"));
        fread($this->sock, 15);
        $afterReceive = microtime(true);
        
        $ping = ($afterReceive - $beforeSend) * 1000;
        
        return round($ping);
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
        $packet .= chr(strtok($this->server, "."));
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
    public function connect() {
        $connected = false;
        fwrite($this->sock, $this->assemblePacket("p0101"));

        if(fread($this->sock, 10)) {
            if(fread($this->sock, 5) == 'p0101') {
                $connected = true;
            }
        }
        return $connected;
    }
    
    /**
    * Closes the connection
    */
    public function close() {
        @fclose($this->sock);
    }
}