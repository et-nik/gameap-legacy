<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014-2015, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

/**
 * Работа с файлами через протокол GDaemon
 *
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		1.0
 */
class Files_gdaemon extends CI_Driver { 
	
	var $hostname		= '';
	var $username		= '';
	var $password		= '';
	var $port 			= 31707;
	
	var $crypt_key		= '';
	var $client_key		= "";
	
	var $_connection 	= false;
	var $errors 		= '';
	
	private $_auth		= false;
	
	private $_max_file_size = 1000000;
	
	// -----------------------------------------------------------------
	
	function __destruct()
	{
		$this->close();
	}
	
	// -----------------------------------------------------------------
	
	function _encode($value, $secret_key)
	{
		if (strlen($value)%16) {
			$value = $value . str_repeat(chr(16-strlen($value)%16), 16-strlen($value)%16);
		} else {
			$value = $value . str_repeat(chr(16), 16);
		}

		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secret_key, $value, MCRYPT_MODE_ECB));
	}
	
	// -----------------------------------------------------------------

	function _decode($value, $secret_key)
	{
		$value = base64_decode(trim($value));
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secret_key, $value, MCRYPT_MODE_ECB), "\x00..\x1F");
	}
	
	// -----------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		// Prep the hostname
		$this->hostname = preg_replace('|.+?://|', '', $this->hostname);
	}
	
	private function _auth()
	{
		$this->_login();
	}
	
	// -----------------------------------------------------------------

	private function _login()
	{
		if ($this->_auth == true) {
			return true;
		}

		if(!$this->password) {
			$this->_error('server_command_empty_auth_data');
		}
		
		fwrite($this->_connection, "getkey\n");

		$this->crypt_key 		= $this->password;
		$this->_fix_crypt_key();
		
		$this->client_key 	= $this->_read();

		if (!preg_match("/^[a-zA-Z0-9]{16}$/", $this->client_key)) {
			$this->_error('server_command_login_failed');
		}
		
		$this->_auth = true;
		return true;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Чтение данных из потока
	 */
	private function _read()
	{
		$buffer = "";

		while (@!$buffer[strlen($buffer)-1] == "\n" & !feof($this->_connection)) {
			$buffer .= fgets($this->_connection);
		}

		return $this->_decode($buffer, $this->crypt_key);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Ключ дополняется, либо урезается до 16 байт
	 */
	private function _fix_crypt_key()
	{
		if (strlen($this->crypt_key) < 16) {
			$this->crypt_key = $this->crypt_key . str_repeat('*', 16-strlen($this->crypt_key));
		} else if (strlen($this->crypt_key) > 16) {
			$this->crypt_key = substr($this->crypt_key, 0, 16);
		}
	}
	
	// -----------------------------------------------------------------
	
	function close()
	{
		$this->crypt_key		= "";
		$this->client_key		= "";
		
		if (!$this->_connection && is_resource($this->_connection)) {
			return;
		}
	
		@fwrite($this->_connection, "exit\n");
		@fclose($this->_connection);
	}
	
	// -----------------------------------------------------------------
	
	function connect($config = array())
	{
		if ($this->_connection && $config['hostname'] == $this->hostname) {
			/* Уже соединен с этим сервером, экономим электроэнергию */
			return;
		} elseif ($this->_connection) {
			// Разрываем соединение со старым сервером
			$this->close();
		}
		
		if (count($config) > 0) {
			$this->initialize($config);
		}

		if (!$this->hostname OR !$this->port) {
			$this->_error('server_command_empty_connect_data');
		}
		
		// Соединение с сервером
		$this->_connection = @fsockopen($this->hostname, $this->port, $errno, $errstr, 10); 
		
		if (!$this->_connection) {
			$this->_error('server_command_connection_failed');
		}
		
		stream_set_timeout($this->_connection, 15);
		
		$this->_auth = false;
		$this->_login();
		
		return true;
	}
	
	// -----------------------------------------------------------------
	
	public function check()
	{
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла
	 * 
	 * @param string 	локальный файл
	 * @param string	удаленный файл
	 * @param string	режим
	 * @param string	привилегии
	 * @return bool
	 */
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		if (filesize($locpath) > $this->_max_file_size) {
			$this->_error('web_ftp_file_big');
		}
		
		$file_contents = file_get_contents($locpath);
		
		$send_json = json_encode(array(
			'key' 				=> $this->client_key,
			'file' 				=> $rempath,
			'contents' 			=> base64_encode($file_contents),
			'type' 				=> "write_file"
		));
		
		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}

		if ($contents['status'] != 10) {
			return false;
		}
		
		return true;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Рекурсивный поиск файла/файлов
	 * 
	 * @param string|array	строка с файлом, либо массив со списком
	 * @param string		директория
	 * @param array			исключающие директории
	 * @param int			глубина рекурсии
	 * @return string		путь к файлу
	*/
	public function search($file, $dir = '/', $exclude_dirs = array(), $depth = 4)
	{
	
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Удаление директории
	 * 
	 * @param string 	
	 * @return bool
	*/
	public function delete_dir($filepath)
	{
		return $this->delete_file($filepath);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Удаление файла
	 * 
	 * @param string
	 * @return bool
	 */
	public function delete_file($filepath)
	{
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 				=> $this->client_key,
			'file' 				=> $filepath,
			'type' 				=> "remove"
		));
		
		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();
		
		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}
		
		if ($contents['status'] == 3) {
			$this->_error('web_ftp_file_not_found');
		}
		
		if ($contents['status'] != 10) {
			return false;
		}
		
		return true;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Загрузка файла с сервера
	 * 
	 * @param string	удаленный файл
	 * @param string	локальный файл
	 * @return bool
	 */
	public function download($rempath, $locpath)
	{
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 				=> $this->client_key,
			'file' 				=> $rempath,
			'type' 				=> "read_file"
		));
		
		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}
		
		if ($contents['status'] == 3) {
			$this->_error('web_ftp_file_not_found');
		}
		
		if ($contents['status'] == 42) {
			$this->_error('web_ftp_file_big');
		}
		
		if ($contents['status'] != 10) {
			return false;
		}
		
		return (false !== file_put_contents($locpath, base64_decode($contents['contents'])));
	}
	
	// -----------------------------------------------------------------

	/**
	 * Scans a directory from a given path
	 *
	 * @access	private
	 * @return	array
	 */
	function _scan_directory($dir, $recursive = FALSE)
	{		

	}
	
	// -----------------------------------------------------------------

	/**
	 * Размер файла
	 */
	function file_size($file)
	{
		if (!$file) {
			$this->_error('server_files_directory_no_set');
		}
		
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 		=> $this->client_key,
			'file' 		=> $file,
			'type' 		=> "filesize"
		));

		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}

		if ($contents['status'] != 10) {
			return 0;
		}
		
		return $contents['filesize'];
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Список файлов
	 */
	public function list_files($path = '.')
	{
		if (!$path) {
			$this->_error('server_files_directory_no_set');
		}
		
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 		=> $this->client_key,
			'dir' 		=> $path,
			'type' 		=> "read_dir"
		));

		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}
		
		$return_list = array();
		
		foreach($contents['list'] as &$file) {
			$return_list[] = $file[0];
		}
		
		return $return_list;
	}
	
	// -----------------------------------------------------------------

	/**
	 * Список файлов с информацией о размере, последнем изменении.
	 * 
	 * @param string
	 * @param array  список расширений файлов
	 */
	function list_files_full_info($path = '.', $extensions = array()) 
	{
		if (!$path) {
			$this->_error('server_files_directory_no_set');
		}
		
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 		=> $this->client_key,
			'dir' 		=> $path,
			'type' 		=> "read_dir"
		));
		

		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");

		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}
		
		$return_list = array();
	
		if (empty($contents['list'])) {
			return array();
		}
		
		foreach($contents['list'] as &$file) {
			$pathinfo = pathinfo($file[0]);
			
			/* Если файл не имеет расширения, а нам нужны файлы с определенным
			 * расширением и не нужны нотисы */
			if (!empty($extensions) && !isset($pathinfo['extension'])) {
				continue;
			}
			
			/* Если заданы расширения $extensions и в массиве нет расширения,
			 * то такой файл пропускаем */
			if (!empty($extensions) && !in_array($pathinfo['extension'], $extensions)) {
				continue;
			}
		
			
			$return_list[] = array('file_name' => basename($file[0]),
									'file_time' => $file[1],
									'file_size' => $file[2],
									'type' => ($file[3]) ? 'd' : 'f',
			);
		}
		
		return $return_list;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Создание директории
	 * 
	 * @param string
	 */
	public function mkdir($path = '', $permissions = 0755)
	{
		if (!$path) {
			$this->_error('server_files_directory_no_set');
		}
		
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 				=> $this->client_key,
			'dir' 				=> $path,
			'permissions' 		=> $permissions,
			'type' 				=> "mkdir"
		));
		
		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();

		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}
		
		return ($contents['status'] == 10);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Переименование файла/директории
	 */
	public function rename($old_file, $new_file)
	{
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}
		
		$send_json = json_encode(array(
			'key' 				=> $this->client_key,
			'old_file' 				=> $old_file,
			'new_file' 				=> $new_file,
			'type' 				=> "move"
		));
		
		$encode_string = $this->_encode($send_json, $this->crypt_key);
		
		fwrite($this->_connection, "command {$encode_string}\n");
		
		$read = $this->_read();
		
		if (!$contents = json_decode($read, true)) {
			$this->_error('server_command_get_response_failed');
		}
		
		return ($contents['status'] == 10);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Перемещение файла/директории
	 */
	public function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file);
	}
	
	// -----------------------------------------------------------------

	/**
	 * Выкидывание исключения
	 *
	 * @access	private
	 * @param	string
	 */
	function _error($msg)
	{
		throw new Exception(lang($msg) . ' (GDaemon)');
	}
}
