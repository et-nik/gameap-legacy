<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
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
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
*/
 
class Control_telnet extends CI_Driver {

	/* (c) thies@thieso.net */

	var $_connection 	= false;
	var $_auth			= false;
	var $errors = '';
	
	private $ip;
	private $port;
	
	private $_prompt = ':> ';
	
	public function check()
	{
		return true;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 * @param str	файл
	 * @param str 	строка с правами (rwx)
	 */
	public function check_file($file, $privileges = '')
	{
		return true;
	}

	// ----------------------------------------------------------------

	/**
	 * Соединение с Telnet
	*/
	function connect($ip = false, $port = 23)
	{
		if ($this->ip && $this->ip == $ip && $this->_connection) {
			/* Уже соединен с этим сервером */
			return true;
		}

		if (!$ip OR !$port) {
			throw new Exception('empty_connect_data');
		}
		
		$this->ip = $ip;
		$this->port = $port;
		
		$this->_connection = @fsockopen($this->ip, $this->port);
		@socket_set_timeout($this->_connection, 5);

		if (!$this->_connection) {
			throw new Exception('connection_failed');
		}
		
		$this->_auth = false;
		return $this->_connection;
	}

	// ----------------------------------------------------------------

	/**
	 * Авторизация
	*/
	function auth($login, $password)
	{
		if ($this->_auth == true) {
			return true;
		}
		
		if(!$login OR !$password) {
			throw new Exception('empty_auth_data');
		}

		$this->_read_till("ogin: ");
		$this->_write( $login . "\r\n");
		$this->_read_till("word: ");
		$this->_write( $password . "\r\n");
		$auth_string = $this->_read_till($this->_prompt);
		
		/* В Windows при неудачной попытке пишется "Login Failed"
		 * В Linux при неудачной попытке пишется "Login incorrect"
		*/
		if (strpos($auth_string, 'Login Failed') !== false OR strpos($auth_string, 'Login incorrect') !== false) {
			throw new Exception('auth_failed');
		}

		$this->_write("\r\n");
		$this->_read_till($this->_prompt);
		
		$this->_auth = true;
		return true;
	}
	
	// ----------------------------------------------------------------

	/**
	 * Выполнение команды
	*/
	function command($command)
	{
		if (!$this->_connection OR !$this->_auth) {
			throw new Exception('not_connected');
		}
		
		if (!$command) {
			throw new Exception('empty_command');
		}
		
		$this->_write($command . "\r\n");

		$result = explode("\n", $this->_read_till($this->_prompt));

		$last_element = count($result)-1;
		unset($result[0]);
		if (strpos($result[$last_element], '>') !== false) {
			unset($result[$last_element]);
		} elseif (strpos($result[$last_element], '~$') !== false) {
			unset($result[$last_element]);
		}

		return trim(implode("\n", $result));
	}
	
	// ----------------------------------------------------------------

	/**
	 * Выполнение команды
	*/
	function exec($command) 
	{
		return $this->command($command);
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
		if(!$this->_connection) { return false;}
		
		$buffer = str_replace(chr(255),chr(255).chr(255),$buffer);
        fwrite($this->_connection,$buffer);
    }


	function _getc() 
	{
		if(!$this->_connection) { return false;}
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



/* End of file Control_telnet.php */
/* Location: ./application/libraries/Control/drivers/Control_telnet.php */
