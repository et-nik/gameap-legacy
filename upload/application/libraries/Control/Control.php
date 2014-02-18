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
	
	protected $path;
	protected $os;

	protected $driver 			= 'local';
	
	private $_sended_commands 	= array();		// Отправленные команды
	private $_commands_result 	= array();		// Результаты отправленных команд
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('string');
		
		$this->valid_drivers = array('control_ssh', 'control_telnet', 'control_local');
	}
	
	// ---------------------------------------------------------------------
	
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
     * Добавляет sudo к команде
    */
	private function _add_sudo($command) 
	{
		switch($this->os){
			case 'ubuntu':
				$command =  'sudo ' . $command;
				break;
				
			case 'debian':
				$command =  'sudo ' . $command;
				break;
				
			case 'linux':
				$command =  'sudo ' . $command;
				break;
				
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
		$this->path		= isset($ds['path']) ? $this->_path_proccess($ds['path']) : '';
	}
	
	// ---------------------------------------------------------------------
	
	public function set_driver($driver) 
	{
		if (!in_array('control_' . $driver, $this->valid_drivers)) {
			return false;
		}
		
		$this->driver = $driver;
	}

	// ---------------------------------------------------------------------
	
	private function _single_command($command = '', $path = false) 
	{
		if (!isset($this->driver)) {
			return false;
		}

		// Проверяем, существует ли файл в команде. 
		// Проверяются файлы .sh и .exe, если это команда, например wget, то проверки не будет
		try {
			$explode = explode(' ', $command);
			$file = $path . '/' . $explode[0];
			
			if (strpos($file, '.sh') !== false OR strpos($file, '.exe') !== false) {
				$this->_check_file($file, 'x');
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
			return false;
		}

		$cd 		= $path ? $this->_path_proccess($path) : '';
		$command 	= $this->_add_sudo($command);

		// Непосредственная отправка команд
		$final_command = $cd . ' && ' . $command;
		$this->_sended_commands[] = $final_command;

		return $this->{$this->driver}->command($command);
	}
	
	// ---------------------------------------------------------------------
	
	public function connect($ip, $port) 
	{
		if (!isset($this->driver)) {
			return false;
		}
		
		return $this->{$this->driver}->connect($ip, $port);
	}
	
	// ---------------------------------------------------------------------
	
	public function auth($login, $password) 
	{
		if (!isset($this->driver)) {
			return false;
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
			
			$result = '';
			$i 		= 0;
			$count = count($this->_sended_commands);
			while($this->_sended_commands < $count) {
				$result .= repeater('-', 50);
				$result .= $this->_sended_commands[$i] . "\n" . $this->_commands_result[$i] . "\n\n";
				$i ++;
			}
			
		} else {
			$this->_commands_result[] = $this->_single_command($command, $path);
			return end($this->_commands_result);
		}
	}
	
	// ---------------------------------------------------------------------
	
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
}
