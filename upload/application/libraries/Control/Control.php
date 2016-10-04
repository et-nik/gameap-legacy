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
 * Работа с выделенным сервером
 *
 * @package		Game AdminPanel
 * @category	Driver Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9-dev3
*/
 
class Control extends CI_Driver_Library {
	
	protected $CI;
	
	public $path;
	public $os;
	
	public $ip;
	public $port;

	protected $driver 			= false;
	
	private $_sended_commands 	= array();		// Отправленные команды
	private $_commands_result 	= array();		// Результаты отправленных команд
	
	private $_no_sudo			= false;		// Не добавлять sudo
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('string');
		$this->CI->lang->load('server_command');
		
		$this->valid_drivers = array('gdaemon');
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Добавляет cd к команде
	 */
	private function _path_proccess($path) 
	{
		$path = reduce_double_slashes($path);
		
		switch($this->os) {
			case 'windows':
				$path = str_replace('/', "\\", $path);
				$path = "cd /D " . $path;
				break;
			default:
				$path = "cd " . $path;
				break;
		}
		
		return $path;
	}	

	// ---------------------------------------------------------------------
    
    /*
     * Добавляет sudo 
     * sudo добавляется только к файлам, к обычным командам не добавляется
     * 
     * Примеры:
     * $this->_add_sudo('wget http://site.com/file.zip'); 						// wget http://site.com/file.zip
     * $this->_add_sudo('./server.sh start'); 									// sudo ./server.sh start
     * $this->_add_sudo('./server.sh start && ./server restart'); 				// sudo ./server.sh start && sudo ./server.sh restart
     * $this->_add_sudo('wget http://site.com/file.zip && ./server.sh start');  // wget http://site.com/file.zip && sudo ./server.sh start
     * 
    */
	private function _add_sudo($command) 
	{
		if ($this->_no_sudo) {
			return $command;
		}
		
		$commands = explode('&&', $command);
		
		if (count($commands) > 1) {
			foreach($commands as &$value) {
				$return[] = $this->_add_sudo(trim($value));
			}
			return implode(' && ', $return);
		} else {
			$command = $commands[0];
		}
		
		$explode = explode(' ', $command);
		// Удаление пустых значение в массиве
		// $explode = array_values(array_filter($explode,function($el){ return !empty($el);}));
		if (strpos($explode[0], '.sh') === false) {
			return $command;
		}

		switch($this->os) {
			case 'ubuntu':
			case 'debian':
			case 'linux':
			case 'centos':
				$command =  'sudo ' . $command;
				break;
				
			default:
				/* Для windows никаких sudo не требуется */
				//$command = $command;
				break;
		}
		
		return $command;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Проверяет необходимые права на файл
	 * 
	 * @param str	файл
	 * @param str 	строка с правами (rwx)
	 */
	private function _check_file($file, $privileges = '')
	{
		return $this->{$this->driver}->check_file($file, $privileges);
	}
	
	// ---------------------------------------------------------------------
	
	public function set_data($ds)
	{
		$this->os		= isset($ds['os']) ? strtolower($ds['os']) : '';
		$this->path		= isset($ds['path']) ? $ds['path'] : '';
	}
	
	// ---------------------------------------------------------------------
	
	public function set_driver($driver) 
	{
		if (!in_array($driver, $this->valid_drivers)) {
			return false;
		}
		
		$this->driver = $driver;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Обращает слэш Linux в обратный слэш Windows
	 * 
	 * У некоторых команд Windows возникают ошибки со слэшами
	 * например, не будет работать
	 * 		mkdir dir/my_dir
	 * будет работать
	 * 		mkdir dir\mydir
	 */
	private function _slash_reverse($command)
	{
		if ($this->os != 'windows') {
			return $command;
		}
		
		$commands = explode('&&', $command);
		
		if (count($commands) > 1) {
			foreach($commands as &$value) {
				$return[] = $this->_slash_reverse(trim($value));
			}
			return implode(' && ', $return);
		} else {
			$command = $commands[0];
		}

		$cmd_tokens = explode(' ', $command);
		
		switch($cmd_tokens[0]) {
			case 'mkdir':
				$cmd_tokens[1] = str_replace('/', '\\', $cmd_tokens[1]);
				break;
		}
		
		$command = implode(' ', $cmd_tokens);
		
		return $command;
	}
	
	private function _reset_limits()
	{
		$this->_limits			= array(
			'ram'	=> 0, 		// RAM limit
			'cpu'	=> 0, 		// CPU Load limit
		);
	}

	// ---------------------------------------------------------------------
	
	/**
	 * Отправка команды на сервер
	 */
	private function _single_command($command = '', $path = false) 
	{
		if (!$this->driver) {
			throw new Exception('Driver no set');
		}

		// Проверяем, существует ли файл в команде. 
		// Проверяются файлы .sh и .exe, если это команда, например wget, то проверки не будет
		$explode = explode(' ', $command);

		if (strpos(str_replace('./', '', $explode[0]), '\\') === false 
			&& strpos(str_replace('./', '', $explode[0]), '/') === false
		) {
			$file = $path . '/' . $explode[0];
		}
		else {
			$file = $explode[0];
		}

		$cd 		= $path ? $this->_path_proccess($path) . ' && ' : '';
		$command 	= $this->_slash_reverse($command);
		$command 	= $this->_add_sudo($command);

		// Костыль для карт типа $2000$
		if ($this->os != 'windows') {
			$command    = str_replace('$', '\\\\\$', $command);
		}
		
		// Подготовка полной команды
		$final_command = $cd . $command;
		$this->_sended_commands[] = $final_command;
		
		if (strpos($file, '.sh') !== false OR strpos($file, '.exe') !== false) {
			$this->_check_file($file, 'x');
		}
		
		return $this->{$this->driver}->command($final_command);
	}
	
	// ---------------------------------------------------------------------
	
	public function connect($ip, $port) 
	{
		if (!$this->driver) {
			throw new Exception('Driver no set');
		}

		if ($this->ip != $ip && $this->port != $port) {
			$this->clear_commands();
			$this->_reset_limits();
		}
		
		$this->ip 	= $ip;
		$this->port = $port;
		
		// Проверка возможности работы с драйвером
		$this->{$this->driver}->check();

		return $this->{$this->driver}->connect($ip, $port);
	}
	
	// ---------------------------------------------------------------------
	
	public function disconnect() 
	{
		return $this->{$this->driver}->disconnect();
	}
	
	// ---------------------------------------------------------------------
	
	public function clear_commands() 
	{
		$this->_sended_commands = array();
		$this->_commands_result = array();
	}
	
	// ---------------------------------------------------------------------
	
	public function auth($login, $password) 
	{
		if (!$this->driver) {
			throw new Exception('Driver no set');
		}
		
		if ($login == 'root') {
			// Зачем от root'а использовать sudo? =)
			$this->_no_sudo = true;
		}
		
		return $this->{$this->driver}->auth($login, $password);
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Отправляет команду на сервер. Аргумент command может принимать
	 * как строковое значение, так и массив
	 */
	public function command($command = '', $path = false)
	{
		$path = $path ? $path : $this->path;
		
		if (is_array($command)) {
			// Отправка массива
			foreach ($command as $cmd) {
				$cmd_result = '';
				
				try {
					$cmd_result = $this->_single_command($cmd, $path);
				} catch (Exception $e) {
					$this->_commands_result[] = $e->getMessage();
					continue;
				}
				
				$this->_commands_result[] = $cmd_result;
			}
			
			return;
		} else {
			$this->_commands_result[] = $this->_single_command($command, $path);
			return end($this->_commands_result);
		}
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Алиас command
	 */
	public function exec($command = '', $path = false)
	{
		return $this->command($command, $path);
	}

	// ---------------------------------------------------------------------
	
	public function get_sended_commands()
	{
		return $this->_sended_commands;
	}
	
	// ---------------------------------------------------------------------
	
	public function get_last_command()
	{
		return end($this->_sended_commands);
	}
	
	// ---------------------------------------------------------------------
	
	public function get_commands_result()
	{
		$results = array();
		
		$i = 0;
		$count = count($this->_sended_commands);
		reset($this->_sended_commands);
		while($i < $count) {
			if (isset($this->_commands_result[$i])) {
				$command_result =& $this->_commands_result[$i];
			}
			
			$results[] = $this->_sended_commands[$i] . PHP_EOL . $command_result;
			$i ++;
		}

		return $results;
	}
}

/* End of file Control.php */
/* Location: ./application/libraries/Control/Control.php */
