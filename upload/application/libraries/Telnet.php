<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

/**
 * Работа с Telnet
 *
 * Библиотека для работы с удаленными серверами
 * через Telnet
 * 
 * Класс найден на странице
 * http://marc.info/?l=php-general&m=99394407709109
 *
 * @package		Game AdminPanel
 * @category	Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
*/
 
class Telnet {

	/* (c) thies@thieso.net */

	var $_connection 	= FALSE;
	var $_auth			= FALSE;
	var $errors = '';
	
	private $ip;
	private $port;

	// ----------------------------------------------------------------

	/**
	 * Соединение с Telnet
	*/
	function connect($ip, $port = 23)
	{
		if ($this->ip && $this->ip == $ip && $this->_connection) {
			/* Уже соединен с этим сервером */
			return TRUE;
		}
		
		$this->ip = $ip;
		$this->port = $port;
		
		$this->_connection = fsockopen($this->ip, $this->port);
		socket_set_timeout($this->_connection, 5);

		if (!$this->_connection) {
			return FALSE;
		}
		
		$this->auth = FALSE;
		return $this->_connection;
	}

	// ----------------------------------------------------------------

	/**
	 * Авторизация
	*/
	function auth($login, $password)
	{
		if ($this->auth == TRUE) {
			return NULL;
		}
		
		$this->_read_till("ogin: ");
		$this->_write( $login . "\r\n");
		$this->_read_till("word: ");
		$this->_write( $password . "\r\n");
		$this->_read_till(":> ");

		$this->_write("\r\n");
		$this->_read_till(":> ");
		
		$this->auth = TRUE;
	}

	function command($command)
	{
		if(!$this->_connection) { return FALSE;}
		
		$this->_write($command . "\n\r");

		$result = explode("\n", $this->_read_till(":> "));
		
		$last_element = count($result)-1;
		unset($result[0]);
		if (strpos($result[$last_element], '>') !== FALSE) {
			unset($result[$last_element]);
		}

		return trim(implode("\n", $result));
	}

	// ----------------------------------------------------------------

	/**
	 * Отключение
	*/
	function disconnect() 
	{
		if ($this->_connection){
            fclose($this->_connection);
		}
        
        $this->_connection = NULL;
	}

	function __destruct() 
	{
		$this->disconnect();
	}

	function _write($buffer) {
		if(!$this->_connection) { return FALSE;}
		
		$buffer = str_replace(chr(255),chr(255).chr(255),$buffer);
        fwrite($this->_connection,$buffer);
    }


	function _getc() 
	{
		if(!$this->_connection) { return FALSE;}
		return fgetc($this->_connection);
	}


	function _read_till($what) 
	{
		$buf = '';

		while (1) {
			$IAC = chr(255);

            $DONT = chr(254);
			$DO = chr(253);

			$WONT = chr(252);
			$WILL = chr(251);

			$theNULL = chr(0);

			$c = $this->_getc();

			if ($c === false){
				return $buf;
			}

			if ($c == $theNULL) {
				continue;
			}

			if ($c == "\021") {
				continue;
			}

			if ($c != $IAC) {
				$buf .= $c;

				if ($what ==(substr($buf,strlen($buf)-strlen($what)))) {
					return $buf;
                } else {
					continue;
                }
            }


			$c = $this->_getc();


			if ($c == $IAC) {
				$buf .= $c;
			} else if (($c == $DO) || ($c == $DONT)) {
				$opt = $this->_getc();
				// echo "we wont ".ord($opt)."\n";
				fwrite($this->_connection,$IAC.$WONT.$opt);
			} elseif (($c == $WILL) || ($c == $WONT)) {
				$opt = $this->_getc();
				// echo "we dont ".ord($opt)."\n";
				fwrite($this->_connection,$IAC.$DONT.$opt);
			} else {
				// echo "where are we? c=".ord($c)."\n";
			}

		}

	}
}
