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
	
	private $_prompt 	= '>';
	private $_timeout 	= 30;

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
		$file_perm['exists'] 		= false;
		$file_perm['readable'] 		= false;
		$file_perm['writable'] 		= false;
		$file_perm['executable'] 	= false;
		
		$file_name 	= basename($file);
		$file_dir 	= dirname($file);
		
		switch ($this->os) {
			case 'windows':
				
				$file_perm['exists'] 		= true;
				$file_perm['readable'] 		= true;
				$file_perm['writable'] 		= true;
				$file_perm['executable'] 	= true;
			
				break;
			
			default:
				$result = $this->command('ls ' . $file_dir . ' --color=none -l | grep ^- | grep ' . $file_name . ' --color=none');
				$result = explode("\n", $result);
				
				foreach($result as &$values) {
					if ($values == '') {
						continue;
					}
					
					$values_exp = explode(" ", $values);
					// Удаление пустых значений
					$values_exp = array_values(array_diff($values_exp, array(null)));
					
					if ($values_exp[8] != $file_name) {
						continue;
					}
					
					/* С побитовыми операциями не дружу, поэтому способ извращенский =) */
					
					// Значение $values
					// Debian: -rwxr-xr-x  1 root        root     3361 Feb  8 15:02 server.sh
					// CentOS: -rwxrwxrwx. 1 root root 3361 Mar 21 02:10 server.sh
					
					$file_perm['exists'] 		= true;
					$file_perm['readable'] 		= preg_match('/^\-r..r..r../i', $values_exp[0]);
					$file_perm['writable'] 		= preg_match('/^\-.w..w..w./i', $values_exp[0]);
					$file_perm['executable'] 	= preg_match('/^\-..[xs]..[xs]..[xt]/', $values_exp[0]);
					break;
				}
				
				break;
		}

		if (!$file_perm['exists']) {
			throw new Exception(lang('server_command_file_not_found', $file_name) . ' (Telnet)');
		}
		
		if (strpos($privileges, 'r') !== false && !$file_perm['readable']) {
			throw new Exception(lang('server_command_file_not_readable', $file_name) . ' (Telnet)');
		}
		
		if (strpos($privileges, 'w') !== false && !$file_perm['writable']) {
			throw new Exception(lang('server_command_file_not_writable', $file_name) . ' (Telnet)');
		}
		
		if (strpos($privileges, 'x') !== false && !$file_perm['executable']) {
			throw new Exception(lang('server_command_file_not_executable', $file_name) . ' (Telnet)');
		}

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
		} elseif ($this->_connection) {
			// Разрываем соединение со старым сервером
			$this->disconnect();
		}

		if (!$ip OR !$port) {
			throw new Exception(lang('server_command_empty_connect_data') . ' (Telnet)');
		}
		
		$this->ip = $ip;
		$this->port = $port;
		
		switch ($this->os) {
			case 'windows':
				$this->_prompt = '>';
				break;
			
			default:
				// linux
				$this->_prompt = '~$';
				break;
		}
		
		$this->_connection = @fsockopen($this->ip, $this->port, $errno, $errstr, 10);

		if (!$this->_connection) {
			throw new Exception(lang('server_command_connection_failed') . ' (Telnet)');
		}
		
		stream_set_blocking($this->_connection, 1);
		//~ @stream_set_timeout($this->_connection, 30);
		
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
			throw new Exception(lang('server_command_empty_auth_data') . ' (Telnet)');
		}

		$this->_read_till("in:");
		$this->_write( $login . "\r\n");
		$this->_read_till("rd:");
		$this->_write( $password . "\r\n");
		$auth_string = $this->_read_till(array($this->_prompt, ':>', 'ailed', 'incorrect'));
		
		/* В Windows при неудачной попытке пишется "Login Failed"
		 * В Linux при неудачной попытке пишется "Login incorrect"
		*/
		if (strpos($auth_string, 'Login Failed') !== false OR strpos($auth_string, 'Login incorrect') !== false) {
			throw new Exception(lang('server_command_auth_failed') . ' (Telnet)');
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
			throw new Exception(lang('server_command_not_connected') . ' (Telnet)');
		}
		
		if (!$command) {
			throw new Exception(lang('server_command_empty_command') . ' (Telnet)');
		}
		
		$this->_write($command . "\r\n");
		sleep(2);
		$result = explode("\n", $this->_read_till($this->_prompt));
		
		
		$last_element = count($result)-1;
		unset($result[0]);
		
		if ($last_element && strpos($result[$last_element], '>') !== false) {
			unset($result[$last_element]);
		} elseif ($last_element && strpos($result[$last_element], '~$') !== false) {
			unset($result[$last_element]);
		}
		
		$result = trim(implode("\n", $result));
		
		if ($this->os == 'windows') {
			$result = iconv('CP866', 'UTF-8//TRANSLIT', $result);
		}

		return $result;
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
		if ($this->_connection) {
			$this->command('exit');
            fclose($this->_connection);
		}
        
        $this->_connection = NULL;
	}

	// ----------------------------------------------------------------

	private function _write($buffer) 
	{
		if(!$this->_connection) { return false;}
		
		$buffer = str_replace(chr(255),chr(255).chr(255),$buffer);
        fwrite($this->_connection,$buffer);
    }
	
	// ----------------------------------------------------------------

	private function _getc() 
	{
		if(!$this->_connection) { return false;}
		return fgetc($this->_connection);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Получает ответ сервера после нахождения стоп слов $what
	 * $what может быть как строкой, так и массивом
	 * 
	 * @param string or array
	*/
	private function _read_till($what) 
	{
		$buf = '';
		$time = 0;
		
		if (is_string($what)) {
			$symbols[] = $what;
			$what = $symbols;
		}

		while (1) {
			$IAC = chr(255);

            $DONT = chr(254);
			$DO = chr(253);

			$WONT = chr(252);
			$WILL = chr(251);

			$theNULL = chr(0);

			$c = $this->_getc();

			if ($c === false) {
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

				foreach($what as &$stop_symbols) {
					/*
					 * Если в тексте найден символ prompt, то команда считается выполненной. Обычно это >
					 * Но некоторые програамы используют символы, например wget, пример:
					 * 	==> TYPE I ... done.  ==> CWD /Files/RE01 ... done.
					 *	==> SIZE main.zip ... 156
					 *	==> PASV ... done.    ==> RETR main.zip ... done.
					 *	Length: 156
					 */
					if ($stop_symbols == substr($buf,strlen($buf)-strlen($stop_symbols))
						&& '=>' != substr($buf, strlen($buf)-2)
					) {
						return $buf;
					}
				}

				continue;
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
	
	// ----------------------------------------------------------------

	function __destruct() 
	{
		$this->disconnect();
	}
}

/* End of file Control_telnet.php */
/* Location: ./application/libraries/Control/drivers/Control_telnet.php */
