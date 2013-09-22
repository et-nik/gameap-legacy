<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Minecraft Server Status Query
 * @author Julian Spravil <julian.spr@t-online.de> https://github.com/FunnyItsElmo
 * @license Free to use but dont remove the author, license and copyright
 * @copyright © 2013 Julian Spravil
 */
 
/*
 * Modify by ET-NiK
 * Special for GameAP (http://www.gameap.ru) 
*/

class Query_minecraft extends CI_Driver {
	
	private $timeout = 3;
	
	const STATISTIC = 0x00;
	const HANDSHAKE = 0x09;

	private $socket;
	private $players;
	private $info;
	var $challenge = NULL;

	public function __destruct() {
		if (isset($this->socket)) {
			fclose($this->socket);
		}
	}

	public function connect($host, $port = 25565)
	{
		$this->socket = @fsockopen('udp://' . $host, (int)$port, $errno, $errstr, $this->timeout);

		if( $errno || $this->socket === false )
		{
			return FALSE;
		}

		stream_set_timeout($this->socket, 1);
		stream_set_blocking($this->socket, true);

		$this->challenge = $this->getchallenge();
		
		if ($this->challenge) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	private function getchallenge()
	{
		$data = $this->WriteData(0x09);

		if ($data === FALSE) {
			return FALSE;
		}

		return pack('N', $data);
	}
	
	private function WriteData($command, $append = "")
	{
		$command = pack( 'c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04 ) . $append;
		$length  = strlen($command);

		if ($length !== fwrite($this->socket, $command, $length)) {
			return FALSE;
		}

		$data = fread($this->socket, 2048);

		if ($data === false) {
			return FALSE;
		}

		if (strlen( $data ) < 5 || $data[ 0 ] != $command[ 2 ]) {
			return FALSE;
		}

		return substr($data, 5);
	}
	
	private function GetStatus($challenge)
	{
		$data = $this->WriteData(0x00, $challenge . pack( 'c*', 0x00, 0x00, 0x00, 0x00 ) );

		if( !$data ){
			return FALSE;
		}

		$last = '';
		$info = array( );

		$data    = substr( $data, 11 ); // splitnum + 2 int
		$data    = explode( "\x00\x00\x01player_\x00\x00", $data );

		if (count($data) !== 2) {
			return FALSE;
		}

		$players = substr( $data[ 1 ], 0, -2 );
		$data    = explode( "\x00", $data[ 0 ] );
		
		foreach( $data as $key => $value )
		{
			if ( ~$key & 1 ) {
				$last = $value;
			} else if( $last != false ) {
				$info[$last] = $value;
			}
		}

		// Ints
		$info['players']    = (int)$info['numplayers'];
		$info['maxplayers'] = (int)$info['maxplayers'];
		$info['hostport']   = (int)$info['hostport'];

		// Parse "plugins", if any
		if ( $info[ 'plugins' ] ) {
			$data = explode( ": ", $info[ 'plugins' ], 2 );

			$info[ 'rawplugins' ] = $info[ 'plugins' ];
			$info[ 'software' ]   = $data[ 0 ];

			if ( count( $data ) == 2 ) {
				$info[ 'plugins' ] = explode( "; ", $data[ 1 ] );
			}
		} else {
			$info[ 'software' ] = 'Vanilla';
		}

		$this->info = $info;
		
		if ($players) {
			$this->players = explode("\x00", $players);
		}
		
		return $this->info;
	}

	// ------------------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players($host, $port)
	{
		$this->connect($host, $port);
		$this->GetStatus($host, $port);
		
		$players['names'] = $this->players;
		$players['score'] = array();
		$players['connected'] = array();
		
		return $players;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение информации о сервере
	*/
	function get_info($host, $port)
	{
		$this->connect($host, $port);
		$this->GetStatus($host, $port);

		$info['hostname'] 		= $this->info['hostname'];
		$info['map'] 			= $this->info['map'];
		$info['game'] 			= $this->info['software'];
		$info['game_code'] 		= $this->info['game_id'];
		$info['players'] 		= $this->info['players'];
		$info['maxplayers'] 	= $this->info['maxplayers'];
		$info['version'] 		= $this->info['version'];
		$info['password'] 		= 0;
		$info['os'] 			= 'Unknown';

		
		return $info;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение переменных сервера
	*/
	function get_rules($host, $port)
	{
		$rules = array();
		
		return $rules;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Статус сервера
	*/
	function get_status($host, $port)
	{
		if ($this->connect($host, $port)) {
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Пинг сервера
	*/
	function ping($host, $port)
	{
		$request = $this->GetStatus($host, $port);
		return (int)$request['ping'];
	}
}
