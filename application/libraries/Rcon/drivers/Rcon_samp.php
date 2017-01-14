<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @author Edward McKnight (EM-Creations.co.uk)
 */

/* *****************************************************************
// SampRcon.class.php
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

class Rcon_samp extends CI_Driver {
    private $sock = null;
    
    // ----------------------------------------------------------------
    
    /**
    * Returns an array of rcon commands. 
    * @return Array
    * (
    *   Array[0] => "echo"
    *   ...
    * )
    *   ...
    * @see getServerVariables()
    */
    public function getCommandList() {
        $commands = $this->rconSend('cmdlist');

        foreach($commands as &$command) {
            $command = trim($command);
        }
        return $commands;
    }
    
    // ----------------------------------------------------------------

    /**
    * Returns an array of server variables. 
    * @return Array[]
    *  (
    *   ['variableName'] = variableValue
    *   ...
    *  )
    * @see getCommandList
    */
    public function getServerVariables() {
        $aVariables = $this->rconSend('varlist');
        unset($aVariables[0]);
        $aReturn = array();

        foreach($aVariables as $sString) {
            preg_match('/(.*)=[\s]+(.*)/', $sString, $aMatches);

            if($aMatches[2][0] == '"') {
                preg_match('/\"(.*)\"[\s]+\(/', $aMatches[2], $aTemp);
                $aReturn[trim($aMatches[1])] = $aTemp[1];
            } else {
                preg_match('/(.*?)\s+\(/', $aMatches[2], $aTemp);
                $aReturn[trim($aMatches[1])] = $aTemp[1];
            }
        }
        return $aReturn;
    }
    
    // ----------------------------------------------------------------

    private function rconSend($command, $delay=1.0) {
        fwrite($this->sock, $this->assemblePacket($command));

        if ($delay === false) {
            return;
        }

        $result = array();
        $iMicrotime = microtime(true) + $delay;

        while (microtime(true) < $iMicrotime) {
            $temp = substr(fread($this->sock, 128), 13);

            if (strlen($temp)) {
                $result[] = $temp;
            } else {
                break;
            }
        }
        
        return implode("\n", $result);
    }
    
    // ----------------------------------------------------------------

    private function assemblePacket($command) {
        $sPacket = "SAMP";
        $sPacket .= chr(strtok($this->server, "."));
        $sPacket .= chr(strtok("."));
        $sPacket .= chr(strtok("."));
        $sPacket .= chr(strtok("."));
        $sPacket .= chr($this->port & 0xFF);
        $sPacket .= chr($this->port >> 8 & 0xFF);
        $sPacket .= "x";

        $sPacket .= chr(strlen($this->password) & 0xFF);
        $sPacket .= chr(strlen($this->password) >> 8 & 0xFF);
        $sPacket .= $this->password;
        $sPacket .= chr(strlen($command) & 0xFF);
        $sPacket .= chr(strlen($command) >> 8 & 0xFF);
        $sPacket .= $command;

        return $sPacket;
    }
    
    // ----------------------------------------------------------------
    
    /**
    * Attempts to connect to the server and returns whether it was successful.
    * @return boolean
    */
    public function connect() {
        $connected = false;
        
        $packet = "SAMP";
        $packet .= chr(strtok($this->server, "."));
        $packet .= chr(strtok("."));
        $packet .= chr(strtok("."));
        $packet .= chr(strtok("."));
        $packet .= chr($this->port & 0xFF);
        $packet .= chr($this->port >> 8 & 0xFF);
        $packet .= "p0101";
        
        $this->sock = fsockopen("udp://" . $this->host, $this->port, $errorNum, $errorString, 2);
        socket_set_timeout($this->sock, 2);
        
        fwrite($this->sock, $packet);

        if(fread($this->sock, 10)) {
            if(fread($this->sock, 5) == 'p0101') {
                $connected = true;
            }
        }
        return $connected;
    }
    
    // ----------------------------------------------------------------
    
    /**
    * Execute an rcon command.
    * @param $command command to execute
    * @param $delay delay time, if you don't expect any data back set this to false
    * @return Output from command
    */
    public function command($command, $delay=1.0) 
    {
        return $this->rconSend($command, $delay);
    }

    /**
    * Closes the connection
    */
    function __desctruct() {
        @fclose($this->sock);
    }
    
    // ----------------------------------------------------------------
	
	/*
	 * Получение списка игроков на сервере
	 * 
	*/
	function get_players()
	{
		return array();
	}
	
	// ----------------------------------------------------------------
	
	/*
	 * Получение списка карт на серввере
	 *  
	*/
	function get_maps()
	{
		return array();
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
		
		$file = $dir. 'server.cfg'; // Конфиг файл
		$file_contents = read_ds_file($file, $server_data);
		
		/* Ошибка чтения, либо файл не найден */
		if(!$file_contents) {
			return false;
		}

		$file_contents 	= change_value_on_file($file_contents, 'rcon_password', $rcon_password);
		$write_result 	= write_ds_file($file, $file_contents, $server_data);
		
		return $write_result;
	}
}
