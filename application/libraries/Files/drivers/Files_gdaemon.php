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

	var $hostname		        = '';
	var $username		        = '';
	var $password		        = '';

    private $privkey_path       = "";
    private $privkey_pass		= '';

	var $port 			= 31707;

	var $client_key		= "";

	var $_connection 	= false;
	private $_socket;
	var $errors 		= '';

	private $_auth		= false;

	private $_max_file_size = 8000000;
    private $_max_bufsize = 10240;

    private $_timeout = 10;

    private $_CI;

    private $_write_binn;
    private $_read_binn;

    const FSERV_AUTH        = 1;
    const FSERV_FILESEND    = 3;
    const FSERV_READDIR     = 4;
    const FSERV_MKDIR       = 5;
    const FSERV_MOVE        = 6;
    const FSERV_REMOVE      = 7;

    const FSERV_UPLOAD_TO_SERVER        = 1;
    const FSERV_DOWNLOAD_FR_SERVER     = 2;

	// -----------------------------------------------------------------

	function __construct()
	{
		$this->_CI = &get_instance();
        $this->_CI->load->library("binn");

        $this->_write_binn = new Binn();
        $this->_read_binn = new Binn();
	}

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

    // -----------------------------------------------------------------

    private function _binn_free()
    {
        $this->_write_binn->binn_free();
        $this->_write_binn->binn_list();

        $this->_read_binn->binn_free();
        $this->_read_binn->binn_list();
    }

	private function _auth()
	{
		$this->_login();
	}

	// -----------------------------------------------------------------

	private function _login()
	{
        $this->_binn_free();

        $this->_write_binn->add_int16(self::FSERV_AUTH);
        $this->_write_binn->add_str($this->username);
        $this->_write_binn->add_str($this->password);
        $this->_write_binn->add_int16(3); // Set mode DAEMON_SERVER_MODE_FILES

        // $fp = fopen("/home/nikita/Git/GameAP_Daemon2/keys/rsa_priv.pem","r");
        $fp = fopen($this->privkey_path, "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);

        $res = openssl_get_privatekey($priv_key, $this->privkey_pass);
        openssl_private_encrypt($this->_write_binn->get_binn_val() . "\00", $encoded, $res);

        socket_write($this->_socket, $encoded . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        $decrypted = "";
        if (!openssl_private_decrypt($read, $decrypted, $res)) {
			$this->_error('', 'OpenSSL private decrypt error');
			return false;
		}

        if ($decrypted == '') {
            return false;
        }

        $this->_read_binn->binn_open($decrypted);

        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] == 100) {
            $this->_auth = true;
            return true;
        } else {
            $this->_error("server_command_gdaemon_login_failed", $results[1]);
            return false;
        }
	}

	// -----------------------------------------------------------------

	/**
	 * Чтение данных из потока
	 */
	private function _read()
	{
        return substr(socket_read($this->_socket, $this->_max_bufsize), 0, -4);
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
		if ($this->_connection) {
            socket_close($this->_socket);
        }
	}

	// -----------------------------------------------------------------

	function connect($config = array())
	{
		if (count($config) > 0) {
			$this->initialize($config);
		}

		if (!$this->hostname OR !$this->port) {
			$this->_error('server_command_empty_connect_data');
		}

        $this->_connection = @fsockopen($this->hostname, $this->port, $errno, $errstr, 10);

        if (!$this->_connection) {
			$this->_error('server_command_connection_failed');
		}

        $this->_socket   = @socket_import_stream($this->_connection);

        stream_set_timeout($this->_connection, $this->_timeout);
        socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->_timeout, 'usec' => 0));
        socket_set_option($this->_socket, SOL_SOCKET, SO_SNDTIMEO, array('sec'=> $this->_timeout, 'usec' => 0));

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
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = null)
	{
		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}

        if (!file_exists($locpath)) {
            $this->_error('web_ftp_file_not_found');
        }

        if (filesize($locpath) > $this->_max_file_size) {
			$this->_error('web_ftp_file_big');
		}

        if ($permissions == null) {
            $permissions = 666;
        }

        $this->_binn_free();

        $this->_write_binn->add_uint8(self::FSERV_FILESEND);
        $this->_write_binn->add_uint8(self::FSERV_UPLOAD_TO_SERVER);
        $this->_write_binn->add_str($rempath);
        $this->_write_binn->add_uint64(filesize($locpath));
        $this->_write_binn->add_bool(true); // Make dirs
        $this->_write_binn->add_uint8($permissions);

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");

        $read = $this->_read();

        if (!$read) {
            $this->_error('Read socket error');
        }

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("server_command_gdaemon_sendfile_error", $results[1]);
        }

        $handle = fopen($locpath, "r");

        while(!feof($handle)) {
            socket_write($this->_socket, fread($handle, $this->_max_bufsize));
        }

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            $this->_error("server_command_gdaemon_sendfile_error", $results[1]);
        }
        else {
            return true;
        }
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
		if (!$filepath) {
			$this->_error('server_files_directory_no_set');
		}

		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}

        $this->_binn_free();

        $this->_write_binn->add_uint8(self::FSERV_REMOVE);
        $this->_write_binn->add_str($filepath);
        $this->_write_binn->add_bool(true);                // Recursively

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        if (!$read) {
            $this->_error('Read socket error');
        }

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("server_command_gdaemon_remove_error", $results[1]);
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

        if (!$locpath) {
            $this->_error('gdaemon_locpath_not_set');
        }

        $this->_binn_free();

        $this->_write_binn->add_uint8(self::FSERV_FILESEND);
        $this->_write_binn->add_uint8(self::FSERV_DOWNLOAD_FR_SERVER);
        $this->_write_binn->add_str($rempath);

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        if (!$read) {
            $this->_error('Read socket error');
        }

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("gdaemon_download_file_error", $results[1]);
            return false;
        }

        $filesize = $results[2];
        $writed = 0;
        $readlen = 0;

        $output_file = fopen($locpath, "w");

        if (!$output_file) {
            $this->_error("fopen_error");
        }

        while($writed < $filesize) {
            if ($filesize - $writed > $this->_max_bufsize) {
                $readlen = $this->_max_bufsize;
            }
            else {
                $readlen = $filesize - $writed;
            }

            $writed += fwrite($output_file, socket_read($this->_socket, $readlen));
        }

        fclose($output_file);
        return true;
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

		if (!$this->_socket OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}

        if (!$path) {
			$this->_error('server_files_directory_no_set');
		}

		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}

        $this->_binn_free();

        $this->_write_binn->add_uint16(self::FSERV_READDIR);
        $this->_write_binn->add_str($path);     // Dir path
        $this->_write_binn->add_uint8(1);       // Mode

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("server_command_gdaemon_list_files_error", $results[1]);
            return false;
        }

        $files_list =& $results[2];

		if (empty($files_list)) {
			return array();
		}

        $return_list = array();

		foreach($files_list as &$file) {
			$pathinfo = pathinfo($file[0]);

            if (basename($file[0]) == '.' OR basename($file[0]) == '..') {
                continue;
            }

            $return_list[] = basename($file[0]);
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

        $this->_binn_free();

        $this->_write_binn->add_uint8(self::FSERV_READDIR);
        $this->_write_binn->add_str($path);     // Dir path
        $this->_write_binn->add_uint8(1);       // Mode

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("server_command_gdaemon_list_files_error", $results[1]);
            return false;
        }

        $files_list =& $results[2];

		if (empty($files_list)) {
			return array();
		}

        $return_list = array();

		foreach($files_list as &$file) {
			$pathinfo = pathinfo($file[0]);

            if (basename($file[0]) == '.' OR basename($file[0]) == '..') {
                continue;
            }

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
                                    'file_size' => $file[1],
									'file_time' => $file[2],
									'type' => ($file[3] == 1) ? 'd' : 'f',
			);
		}

        uasort($return_list, function($a, $b) {
            return ($a['file_name'] > $b['file_name']) ? 1: -1;
            // $a['file_name'] == $b['file_name'] ? Nooooo
        });

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

        $this->_binn_free();

        $this->_write_binn->add_uint8(self::FSERV_MKDIR);
        $this->_write_binn->add_str($path);
        $this->_write_binn->add_str($permissions);

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        if (!$read) {
            $this->_error('Read socket error');
        }

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("server_command_gdaemon_mkdir_error", $results[1]);
            return false;
        }

        return true;
	}

	// -----------------------------------------------------------------

	/**
	 * Переименование файла/директории
	 */
	public function rename($old_file, $new_file)
	{
		if (!$old_file OR !$new_file) {
			$this->_error('server_command_gdaemon_empty_parameters');
		}

		if (!$this->_connection OR !$this->_auth) {
			$this->_error('server_command_not_connected');
		}

        $this->_binn_free();

        $this->_write_binn->add_uint8(self::FSERV_MOVE);
        $this->_write_binn->add_str($old_file);
        $this->_write_binn->add_str($new_file);
        $this->_write_binn->add_bool(false);            // Copy

        socket_write($this->_socket, $this->_write_binn->get_binn_val() . "\xFF\xFF\xFF\xFF");
        $read = $this->_read();

        if (!$read) {
            $this->_error('Read socket error');
        }

        $this->_read_binn->binn_open($read);
        $results = $this->_read_binn->get_binn_arr();

        if ($results[0] != 100) {
            // Error
            $this->_error("server_command_gdaemon_rename_error", $results[1]);
            return false;
        }

        return true;
	}

	// -----------------------------------------------------------------

	/**
	 * Перемещение файла/директории
	 */
	public function move($old_file, $new_file)
	{
        $this->rename($old_file, $new_file);
	}

	// -----------------------------------------------------------------

	/**
	 * Выкидывание исключения
	 *
	 * @access	private
	 * @param	string
	 */
	function _error($msg, $dscr = "")
	{
		throw new Exception(lang($msg) . ' ' . $dscr . ' (GDaemon)');
	}
}
