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
 * Files библиотека для работы с файлам через FTP, sFTP и локально.
 *
 * @package		Game AdminPanel
 * @category	Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9
*/
class Files extends CI_Driver_Library {
	
	protected $CI;
	
	protected $driver 		= false;
	var $tmp_dir			= '';
	
	// --------------------------------------------------------------------
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('string');
		$this->CI->lang->load('server_command');
		$this->CI->lang->load('web_ftp');
		$this->CI->lang->load('ftp');
		$this->CI->lang->load('sftp');
		
		$this->valid_drivers = array('ftp', 'sftp', 'gdaemon');

		$this->_get_tmp_dir();
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Получение директорири для записи временных файлов
	 */
	public function _get_tmp_dir()
	{
		$this->tmp_dir = sys_get_temp_dir();
		
		if (@is_writable($this->tmp_dir)) {
			return $this->tmp_dir;
		} elseif (file_exists(FCPATH . 'tmp') && is_writable(FCPATH . 'tmp')) {
			$this->tmp_dir = FCPATH . 'tmp';
			return $this->tmp_dir;
		} elseif (file_exists(FCPATH . 'application/cache') && is_writable(FCPATH . 'application/cache')) {
			
			if (!file_exists(FCPATH . 'application/cache/tmp')) {
				mkdir(FCPATH . 'application/cache/tmp');
			}
			
			$this->tmp_dir = FCPATH . 'application/cache/tmp';
			return $this->tmp_dir;
		} else {
			show_error('Failed to set the tmp directory. <a target="blank" href="http://wiki.hldm.org/index.php/%D0%90%D0%B4%D0%BC%D0%B8%D0%BD%D0%9F%D0%B0%D0%BD%D0%B5%D0%BB%D1%8C:FAQ#.D0.9F.D0.BE.D1.8F.D0.B2.D0.B8.D0.BB.D0.B0.D1.81.D1.8C_.D0.BE.D1.88.D0.B8.D0.B1.D0.BA.D0.B0_.27Failed_to_set_the_tmp_directory.27">See FAQ</a>');
		}

		return $this->tmp_dir;
	}

	// ---------------------------------------------------------------------
	
	public function set_driver($driver) 
	{
		if (!in_array($driver, $this->valid_drivers)) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		$this->driver = $driver;
		$this->{$this->driver}->check();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Соединение с сервером
	 */
	public function connect($config = array())
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		// Проверка возможности работы с драйвером
		$this->{$this->driver}->check();
		
		return $this->{$this->driver}->connect($config);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла
	 */
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->upload($locpath, $rempath, $mode, $permissions);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Поиск файла/файлов
	 */
	public function search($file, $dir = '/', $exclude_dirs = array(), $depth = 4)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->search($file, $dir, $exclude_dirs, $depth);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Удаление директории
	 */
	public function delete_dir($filepath)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->delete_dir($filepath);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Удаление файла
	 */
	public function delete_file($filepath)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->delete_file($filepath);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла с сервера
	 */
	public function download($rempath, $locpath, $mode = 'auto')
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->download($rempath, $locpath, $mode);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Список файлов
	 */
	public function list_files($path = '.', $recursive = false)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->list_files($path, $recursive);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Список файлов с информацией о размере и последнем изменении
	 */
	public function list_files_full_info($path = '.', $extensions = array())
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->list_files_full_info($path, $extensions);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Создание директории
	 */
	public function mkdir($path = '', $permissions = NULL)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->mkdir($path, $permissions);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Переименование файла/директории
	 */
	public function rename($old_file, $new_file, $move = FALSE)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->rename($old_file, $new_file, $move);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Перемещение файла/директории
	 */
	public function move($old_file, $new_file)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->move($old_file, $new_file);
	}
	
	
	// --------------------------------------------------------------------
	
	/**
	 * Чтение файла
	 */
	public function file_size($remfile)
	{
		if (!$this->driver) {
			throw new Exception(lang('server_files_driver_not_set'));
		}
		
		return $this->{$this->driver}->file_size($remfile);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Чтение файла
	 */
	public function read_file($remfile)
	{
		$temp_file = tempnam($this->tmp_dir, basename($remfile));
		$this->download($remfile, $temp_file);
		
		$file_contents = file_get_contents($temp_file);
		unlink($temp_file);
		
		return $file_contents;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Запись файла
	 */
	public function write_file($remfile, $data = '')
	{
		$temp_file = tempnam($this->tmp_dir, basename($remfile));
		
		if (file_put_contents($temp_file, $data)) {
			$upload_status = $this->upload($temp_file, $remfile);
			unlink($temp_file);
			return $upload_status;
		}
	}
	
}
