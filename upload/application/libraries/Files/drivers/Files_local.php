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
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла
	 */
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		return copy($locpath, $rempath);
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
		
		foreach($list_files as $scandir) {
			$scandir = $dir . '/' . $scandir;
			
			if (in_array(str_replace('/', '', $dir), $exclude_dirs)) {
				continue;
			}
			
			if ($found_dir = $this->search($file, $scandir, array(), $depth - 1)) {
				return $found_dir;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Удаление директории
	 */
	public function delete_dir($filepath)
	{
		return rmdir($filepath);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Удаление файла
	 */
	public function delete_file($filepath)
	{
		return unlink($filepath);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Загрузка файла с сервера
	 */
	public function download($rempath, $locpath)
	{
		return copy($rempath, $locpath);
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
	 * Создание директории
	 */
	public function mkdir($path = '', $permissions = NULL)
	{
		return mkdir($path, $permissions);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Переименование файла/директории
	 */
	public function rename($old_file, $new_file)
	{
		if (!file_exists($old_file)) {
			return false;
		}
		
		return rename($old_file, $new_file);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Перемещение файла/директории
	 */
	public function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file);
	}
}
