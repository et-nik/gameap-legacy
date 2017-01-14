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
 * Работа с локальными файлами
 *
 * Библиотека для работы с удаленными серверами
 * через SSH
 *
 * @package		Game AdminPanel
 * @category	Drivers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9
 */
class Files_local extends CI_Driver {
	
	// --------------------------------------------------------------------
	
	/**
	 * Соединение с сервером
	 */
	public function connect($config = array())
	{
		return true;
	}
	
	// ---------------------------------------------------------------------
	
	public function check()
	{
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла
	 */
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if (!file_exists($locpath)) {
			$this->_error('server_files_no_source_file');
		}
		
		$result = @copy($locpath, $rempath);
		
		if (!$result) {
			$this->_error('server_files_unable_to_upload');
		}
		
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Поиск файла/файлов
	 */
	public function search($file, $dir = '/', $exclude_dirs = array(), $depth = 4)
	{
		if (!$depth) {
			return false;
		}
		
		$dir = $dir ? $dir : '/';
		
		$list_files = $this->list_files($dir);
		$list_base_name = array();
		
		if (!is_array($list_files)) {
			return;
		}
		
		// Избавляемся от пути, оставляем лишь имя файла
		foreach($list_files as &$value) {
			$list_base_name[] = basename($value);
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
		
		foreach($list_files as &$scandir) {
			$scandir = $dir . '/' . $scandir;
			
			if (in_array(str_replace('/', '', $dir), $exclude_dirs)) {
				continue;
			}
			
			if ($found_dir = $this->search($file, $scandir, array(), $depth - 1)) {
				return $found_dir;
			}
			
			unset($scandir);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Удаление директории
	 */
	public function delete_dir($filepath)
	{
		$result = rmdir($filepath);
		
		if (!$result) {
			$this->_error('server_files_unable_to_delete');
		}
		
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Удаление файла
	 */
	public function delete_file($filepath)
	{
		$result = unlink($filepath);
		
		if (!$result) {
			$this->_error('server_files_unable_to_delete');
		}
		
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла с сервера
	 */
	public function download($rempath, $locpath)
	{
		if (!copy($rempath, $locpath)) {
			$this->_error('server_files_unable_to_download');
		}

		return true;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Scans a directory from a given path
	 *
	 * @access	private
	 * @return	array
	 */

	function _scan_directory($dir, $recursive = FALSE)
	{		
		$tempArray = array();
		
		if (!is_readable($dir)) {
			return array();
		}

		$handle = opendir($dir);

		if (!$handle) {
			return array();
		}
		
		// List all the files
		while (false != ($file = readdir($handle))) {
			if (substr("$file", 0, 1) != ".") {
				if (is_dir($file) && $recursive) {
					// If its a directory, interate again
					$tempArray[$file] = $this->_scan_directory("$dir/$file");
				} else {
					$tempArray[] = $file;
				}
			}
		}

		closedir($handle);
		return $tempArray;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Размер файла
	 */
	function file_size($file)
	{
		return filesize($file);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Список файлов
	 */
	public function list_files($path = '.', $recursive = false)
	{
		if (!is_dir($path)) {
			return false;
		}
		
		$directory = $this->_scan_directory($path, $recursive);
		sort($directory);
		
		return $directory;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Список файлов с информацией о размере, последнем изменении.
	 * 
	 * @param string
	 * @param array  список расширений файлов
	 */
	function list_files_full_info($path = '.', $extensions = array()) 
	{
		if (!file_exists($path)) {
			$this->_error('server_files_directory_not_found');
			return FALSE;
		}
		
		$list_files = $this->list_files($path);
		$return_list = array();

		foreach($list_files as &$file) {
			
			$pathinfo = pathinfo($file);
			
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
			
			$file_stat = stat($path . '/' . $file);
			
			//~ $type = is_dir($dir . '/' . $file) ? 'd' : 'f';
			
			$return_list[] = array('file_name' => basename($file),
									'file_time' => $file_stat['mtime'],
									'file_size' => $file_stat['size'],
									'type' => is_dir($path . '/' . $file) ? 'd' : 'f',
			);
		}

		return $return_list;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Создание директории
	 */
	public function mkdir($path = '', $permissions = NULL)
	{
		$result = mkdir($path, $permissions);
		
		if (!$result) {
			$this->_error('server_files_unable_to_makdir');
		}
		
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Переименование файла/директории
	 */
	public function rename($old_file, $new_file)
	{
		if (!file_exists($old_file)) {
			$this->_error('server_files_file_not_found');
		}
		
		$result = rename($old_file, $new_file);
		
		if (!$result) {
			$msg = ($move == FALSE) ? 'server_files_unable_to_rename' : 'server_files_unable_to_move';
			$this->_error($msg);
		}
		
		return true;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Перемещение файла/директории
	 */
	public function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file);
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
		throw new Exception(lang($msg) . ' (Local)');
	}
}
