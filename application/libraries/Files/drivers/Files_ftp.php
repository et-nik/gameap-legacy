<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 * ET-NiK Modify
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * FTP Class
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Drivers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/ftp.html
 */
class Files_ftp extends CI_Driver {

	var $hostname	= '';
	var $username	= '';
	var $password	= '';
	var $port		= 21;
	var $passive	= TRUE;
	var $debug		= FALSE;
	var $conn_id	= FALSE;
	
	// Месяцы без нулября, обязательно нужно прибавить единичку
	var $raw_months 	= array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

	/**
	 * Constructor - Sets Preferences
	 *
	 * The constructor can be passed an array of config values
	 */
	public function __construct($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		log_message('debug', "FTP Class Initialized");
	}
	
	// ---------------------------------------------------------------------
	
	public function check()
	{
		return true;
	}

	// --------------------------------------------------------------------

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

	// --------------------------------------------------------------------

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	 the connection values
	 * @return	bool
	 */
	function connect($config = array())
	{
		if ($this->conn_id && $config['hostname'] == $this->hostname) {
			// Уже соединен с этим сервером, повторно соединяться не требуется
			return;
		}
		
		if (count($config) > 0) {
			$this->initialize($config);
		}
		
		if (FALSE === ($this->conn_id = @ftp_connect($this->hostname, $this->port))) {
			$this->_error('ftp_unable_to_connect');
			return FALSE;
		}

		if ( ! $this->_login()) {
			$this->_error('ftp_unable_to_login');
			return FALSE;
		}

		// Set passive mode if needed
		if ($this->passive == TRUE) {
			ftp_pasv($this->conn_id, TRUE);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Login
	 *
	 * @access	private
	 * @return	bool
	 */
	function _login()
	{
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	// --------------------------------------------------------------------

	/**
	 * Validates the connection ID
	 *
	 * @access	private
	 * @return	bool
	 */
	function _is_conn()
	{
		if ( ! is_resource($this->conn_id))
		{
			$this->_error('ftp_no_connection');
			return FALSE;
		}
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Размер файла
	 */
	function file_size($file)
	{
		if (!$this->_is_conn())
		{
			return FALSE;
		}

		return ftp_size($this->conn_id, $file);
	}

	// --------------------------------------------------------------------


	/**
	 * Change directory
	 *
	 * The second parameter lets us momentarily turn off debugging so that
	 * this function can be used to test for the existence of a folder
	 * without throwing an error.  There's no FTP equivalent to is_dir()
	 * so we do it by trying to change to a particular directory.
	 * Internally, this parameter is only used by the "mirror" function below.
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function changedir($path = '', $supress_debug = FALSE)
	{
		if ($path == '' OR ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_chdir($this->conn_id, $path);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_changedir');
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Create a directory
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function mkdir($path = '', $permissions = NULL)
	{
		if ($path == '' OR ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_mkdir($this->conn_id, $path);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_makdir');
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($path, (int)$permissions);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Upload a file to the server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		if ( ! file_exists($locpath))
		{
			$this->_error('ftp_no_source_file');
			return FALSE;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($locpath);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_put($this->conn_id, $rempath, $locpath, $mode);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_upload');
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($rempath, (int)$permissions);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Download a file from a remote server to the local server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function download($rempath, $locpath, $mode = 'auto')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->_getext($rempath);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_get($this->conn_id, $locpath, $rempath, $mode);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_download');
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function rename($old_file, $new_file, $move = FALSE)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_rename($this->conn_id, $old_file, $new_file);

		if ($result === FALSE)
		{
			$msg = ($move == FALSE) ? 'ftp_unable_to_rename' : 'ftp_unable_to_move';
			$this->_error($msg);
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Move a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_file($filepath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_delete($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_delete');
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * containted within it.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_dir($filepath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Add a trailing slash to the file path if needed
		$filepath = preg_replace("/(.+?)\/*$/", "\\1/",  $filepath);

		$list = $this->list_files($filepath);

		if ($list !== FALSE AND count($list) > 0)
		{
			foreach ($list as $item)
			{
				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()
				if ( ! @ftp_delete($this->conn_id, $item))
				{
					$this->delete_dir($item);
				}
			}
		}

		$result = @ftp_rmdir($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_delete');
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set file permissions
	 *
	 * @access	public
	 * @param	string	the file path
	 * @param	string	the permissions
	 * @return	bool
	 */
	function chmod($path, $perm)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Permissions can only be set when running PHP 5
		if ( ! function_exists('ftp_chmod'))
		{
			$this->_error('ftp_unable_to_chmod');
			return FALSE;
		}

		$result = @ftp_chmod($this->conn_id, $perm, $path);

		if ($result === FALSE)
		{
			$this->_error('ftp_unable_to_chmod');
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP List files in the specified directory
	 *
	 * @access	public
	 * @return	array
	 */
	function list_files($path = '.')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		return ftp_nlist($this->conn_id, $path);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Список файлов с информацией о размере, последнем изменении.
	 */
	function list_files_full_info($path = '.', $extensions = array()) 
	{
		$CI =& get_instance();
		$CI->load->helper('date');
		
		if (!ftp_chdir($this->conn_id, $path)) {
			$this->_error('server_files_directory_not_found');
			return false;
		}
		
		$list_files = ftp_rawlist($this->conn_id, $path);
		
		if (empty($list_files)) {
			return array();
		}
		
		$return_list = array();

		foreach($list_files as &$file) {

			$expl = preg_split("/[\s,]+/", $file, 9);
			$pathinfo = pathinfo($expl[8]);
			
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

			$return_list[] = array('file_name' => basename($expl[8]),
									'file_time' => human_to_unix($expl[6] . '-' . (array_search($expl[5], $this->raw_months)+1) . '-' . date('Y') . ' ' . $expl[7]),
									'file_size' => $expl[4],
									'type' => substr($expl[0], 0, 1) == 'd' ? 'd' : 'f',
			);
		}

		return $return_list;
	}

	// ------------------------------------------------------------------------

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access	public
	 * @param	string	path to source with trailing slash
	 * @param	string	path to destination - include the base folder with trailing slash
	 * @return	bool
	 */
	function mirror($locpath, $rempath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Open the local file path
		if ($fp = @opendir($locpath))
		{
			// Attempt to open the remote file path.
			if ( ! $this->changedir($rempath, TRUE))
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( ! $this->mkdir($rempath) OR ! $this->changedir($rempath))
				{
					return FALSE;
				}
			}

			// Recursively read the local directory
			while (FALSE !== ($file = readdir($fp)))
			{
				if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.')
				{
					$this->mirror($locpath.$file."/", $rempath.$file."/");
				}
				elseif (substr($file, 0, 1) != ".")
				{
					// Get the file extension so we can se the upload type
					$ext = $this->_getext($file);
					$mode = $this->_settype($ext);

					$this->upload($locpath.$file, $rempath.$file, $mode);
				}
			}
			return TRUE;
		}

		return FALSE;
	}


	// --------------------------------------------------------------------

	/**
	 * Extract the file extension
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _getext($filename)
	{
		if (FALSE === strpos($filename, '.'))
		{
			return 'txt';
		}

		$x = explode('.', $filename);
		return end($x);
	}


	// --------------------------------------------------------------------

	/**
	 * Set the upload type
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _settype($ext)
	{
		$text_types = array(
							'txt',
							'text',
							'php',
							'phps',
							'php4',
							'js',
							'css',
							'htm',
							'html',
							'phtml',
							'shtml',
							'log',
							'xml'
							);


		return (in_array($ext, $text_types)) ? 'ascii' : 'binary';
	}

	// ------------------------------------------------------------------------

	/**
	 * Close the connection
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	string	path to destination
	 * @return	bool
	 */
	function close()
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		@ftp_close($this->conn_id);
	}
	
	// ------------------------------------------------------------------------

	/**
	 * Рекурсивный поиск файла или директории
	 * 
	 * @param string or array
	 * @param string
	 * @param array
	 * @param integer
	 * @return string
	 *
	 */
	function search($file, $dir = '/', $exclude_dirs = array(), $depth = 4)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}
		
		$dir = $dir ? $dir : '/';
		
		$list_files = $this->list_files($dir);
		$list_base_name = array();
		
		if (!is_array($list_files)) {
			return;
		}
		
		// Избавляемся от пути, оставляем лишь имя файла
		foreach($list_files as &$ftp_file) {
			$list_base_name[] = basename($ftp_file);
		}
		
		if (is_array($file)) {
			foreach($file as $value) {
				if (in_array($value, $list_base_name)) {
					return $dir;
				}
			}
		} else {
			if (in_array($file, $list_base_name)) {
				return $dir;
			}
		}
		
		foreach($list_files as &$ftp_dir) {
			if (!$this->changedir($ftp_dir)) {
				continue;
			}
			
			if (in_array(str_replace('/', '', $dir), $exclude_dirs)) {
				continue;
			}
			
			if ($found_dir = $this->search($file, $ftp_dir, array(), $depth - 1)) {
				return $found_dir;
			}
			
			unset($ftp_dir);
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Выкидывание исключения
	 *
	 * @access	private
	 * @param	string
	 */
	function _error($msg)
	{
		throw new Exception(lang($msg) . ' (FTP)');
	}


}
// END FTP Class

/* End of file Ftp.php */
/* Location: ./system/libraries/Ftp.php */
