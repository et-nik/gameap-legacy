<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 *
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/

use \Myth\Controllers\BaseController;

/**
 * Ajax для получения базовой информации о серверах
 * Получение статуса серверов (опрос сервера)
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		1.0
 *
*/
class Web_ftp extends BaseController {

	private $_error = "";

	var $forbidden_types = array('exe', 'com', 'dll', 'bat', 'cmd', 'bin', 'so', 'sh', 'tmp', 'dmp', 'mdmp', 'core', 'dylib', 'pid');
    var $forbidden_names = array('stdin.txt', 'stdout.txt', 'pid.txt');

	// -----------------------------------------------------------------

	public function __construct()
    {
        parent::__construct();

		$this->load->database();
        $this->load->model('users');

        if (!$this->input->is_ajax_request()) {
		   show_404();
		}

        if (!$this->users->check_user()) {
			show_404();
        }

        $this->lang->load('web_ftp');
        $this->lang->load('server_command');
        $this->lang->load('server_control');

        $this->load->library('form_validation');

        $this->load->helper('ds');
        $this->load->helper('file');
        $this->load->helper('date');
		$this->load->driver('files');
    }

    // -----------------------------------------------------------------

    private function _send_response($array)
    {
        if (empty($array)) {
            $this->_send_error('Invalid data');
        }

        $this->renderJson($array);
    }

    // -----------------------------------------------------------------

    private function _send_error($error = "")
    {
        $this->renderJson(array('status' => 0, 'error_text' => $error));
    }

	// -----------------------------------------------------------------

	/**
	 * Проверка расширений файлов
	 */
	private function _check_file_ext($file)
	{
		$pathinfo = pathinfo($file['file_name']);

		// Расширение из числа
		if ($this->servers->server_data['os'] != 'windows') {
			if (isset($pathinfo['extension']) && is_numeric($pathinfo['extension'])) {
				return false;
			}
		}

		// Проверка на расширения
		if (isset($file['type'])) {
			if ($file['type'] != 'd'
                && (!isset($pathinfo['extension'])
                    OR in_array($pathinfo['extension'], $this->forbidden_types)
                    OR in_array($pathinfo['basename'], $this->forbidden_names)
                )
            ) {
				return false;
			}
		} else {
			if (!isset($pathinfo['extension'])
                OR in_array($pathinfo['extension'], $this->forbidden_types)
                OR in_array($pathinfo['basename'], $this->forbidden_names)
            ) {
				return false;
			}
		}

		return true;
	}

	// -----------------------------------------------------------------

	private function _check_server($server_id = 0)
	{
		/*
		 * Если не указан id сервера, то перенаправляем на
		 * страницу выбора
		*/
		if(!$server_id){
			$this->_error = 'server not set';
			return false;
		}

		/* Преобразование id в числовое значение */
		$server_id = (int)$server_id;

		/* Загружаем модель работы с сервером */
		$this->load->model('servers');

		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);

		/* Проверка на права загрузки и правки конфигурационный файлов сервера */
		if(!$this->users->auth_servers_privileges['UPLOAD_CONTENTS']
		&& !$this->users->auth_servers_privileges['CHANGE_CONFIG']
		){
			$this->_error = lang('server_files_no_privileges');
			return false;
		}

		$local_tpl = array();

		/* Получение данных сервера */
		if (!$this->servers->get_server_data($server_id)) {
			$this->_error = lang('server_control_server_not_found');
			return false;
		}

		return true;
	}

	// ----------------------------------------------------------------

    /**
     * Получает путь
     *
	 * Иногда запись файлов или чтение может завершаться ошибкой
	 * причина чаще всего в путях
	 *
	 * Путь для чтения/записи файла генерируется из базы данных
	 *
	 * Локальный путь:
	 * 	this->servers->server_data['local_path'] - путь к скрипту запуск серверов относительно корня сервера, либо домашней папки пользователя
	 * 	this->servers->server_data['dir'] - директория игрового сервера относительно скрипта
	 * 	$s_cfg_files[$cfg_id]['file'] - путь к файлу взятый из json
	 *
	 * Удаленный ftp сервер
	 * 	$this->servers->server_data['ftp_path'] - путь к скрипту запуск серверов относительно корня сервера, либо домашней папки пользователя
	 * 	this->servers->server_data['dir'] - директория игрового сервера относительно скрипта
	 * 	$s_cfg_files[$cfg_id]['file'] - путь к файлу взятый из json
    */
    private function _get_path(&$server_data)
    {
		$this->load->helper('ds');
		return get_ds_file_path($server_data);
	}

	// -----------------------------------------------------------------

	private function _two_point_delete($str)
	{
		return preg_replace('/\.{2,}/si', '', $str);
	}

	// -----------------------------------------------------------------

	private function _update_last_files($dir, $file)
	{
		$ntdata['last_files'] = isset($this->users->auth_data['notices']['web_ftp']['data']['last_files'])
					? $this->users->auth_data['notices']['web_ftp']['data']['last_files']
					: array();

		$add = true;
		foreach ($ntdata['last_files'] as &$ntfile) {
			if ($ntfile['dir'] == $dir && $ntfile['name'] == $file) {
				$add = false;
				break;
			}
		}

		if ($add) {
			$ntdata['last_files'][] = array(
				'dir' => $dir,
				'name' => $file,
				'server_id' => $this->servers->server_data['id'],
				'game' => $this->servers->server_data['game'],
			);

			if (count($ntdata['last_files']) > 7) {
				$ntdata['last_files'] = array_slice($ntdata['last_files'], -7);
			}

			$this->users->set_notice('web_ftp', 'Last files', $ntdata);
		}
	}

	// -----------------------------------------------------------------

    /**
     * Получение списка последних редактируемых файлов
    */
    public function get_last_files($server_id = 0)
    {
		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$last_files =& $this->users->auth_data['notices']['web_ftp']['data']['last_files'];
		$return = array();

		if (!empty($last_files)) {
			foreach ($last_files as &$file) {
				if ($this->servers->server_data['game'] != $file['game']) {
					continue;
				}

				$return[] = array('file' => $file['name'], 'dir' => $file['dir']);
			}
		}

		$this->_send_response(array('status' => 1, 'files' => $return));
	}

	// -----------------------------------------------------------------

    /**
     * Получение списка файлов
    */
    public function get_list($server_id = 0)
    {
		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$this->form_validation->set_rules('dir', lang('directory'), 'trim');

		if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error(lang('web_ftp_form_error'));
				return false;
			}
		}

		$loc_dir = $this->_two_point_delete($this->input->post('dir', true));
		$loc_dir = empty($loc_dir) ? '/' : $loc_dir;

		$dir = get_ds_file_path($this->servers->server_data) . '/' . $loc_dir;

		// Данные для соединения
		$config = get_file_protocol_config($this->servers->server_data);

		try {
			$this->files->set_driver($config['driver']);
			$this->files->connect($config);
			$files = $this->files->list_files_full_info($dir);
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

		$return['dirs'] = array();
		$return['files'] = array();

		foreach ($files as &$file) {
			$file['file_size'] = human_size($file['file_size']);
			$file['file_time'] = unix_to_human($file['file_time'], true, 'ru');

			// Скрытие файлов (exe, dll и т.д.)
			if (!$this->_check_file_ext($file)) {
				continue;
			}

			// Директории впереди
			if ($file['type'] == 'd') {
				$file['file_size'] = '';
				$return['dirs'][] = $file;
			} else {
				$return['files'][] = $file;
			}
		}

		$this->_send_response(array('status' => 1, 'files' => array_merge($return['dirs'], $return['files'])));

	}

    // -----------------------------------------------------------------

    /**
     * Чтение файла
    */
    public function read_file($server_id = 0)
    {
		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$this->form_validation->set_rules('dir', lang('directory'), 'trim');
		$this->form_validation->set_rules('file', lang('file'), 'trim');

		if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error(lang('web_ftp_form_error'));
				return false;
			}
		}

		$loc_dir = $this->_two_point_delete($this->input->post('dir', true));
		$loc_file = $this->_two_point_delete($this->input->post('file', true));

		if (!$this->_check_file_ext(array('file_name' => $loc_file))) {
			$this->_send_error(lang('web_ftp_forbidden_type'));
			return false;
		}

		$loc_dir = empty($loc_dir) ? '/' : $loc_dir;

		$file = get_ds_file_path($this->servers->server_data) . '/' . $loc_dir . '/' . $loc_file;

		// Данные для соединения
		$config = get_file_protocol_config($this->servers->server_data);

		try {
			$this->files->set_driver($config['driver']);
			$this->files->connect($config);

			// 2Mb
			if ($this->files->file_size($file) > 2000000) {
				$this->_send_error(lang('web_ftp_file_big'));
				return;
			}

			$file_content = $this->files->read_file($file);
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

		$this->_update_last_files($loc_dir, $loc_file);

		$this->_send_response(array('status' => 1, 'file_contents' => base64_encode($file_content)));
	}

	// -----------------------------------------------------------------

    /**
     * Запись файла
    */
    public function write_file($server_id = 0)
    {
		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$this->form_validation->set_rules('dir', lang('directory'), 'trim');
		$this->form_validation->set_rules('file', lang('file'), 'trim|required');
		$this->form_validation->set_rules('contents', lang('contents'), 'trim');

		if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error(lang('web_ftp_form_error'));
				return false;
			}
		}

		$loc_dir = $this->_two_point_delete($this->input->post('dir', true));
		$loc_file = $this->_two_point_delete($this->input->post('file', true));
		$file_contents = $this->input->post('file_contents');

		if (!$this->_check_file_ext(array('file_name' => $loc_file))) {
			$this->_send_error(lang('web_ftp_forbidden_type'));
			return false;
		}

		$file = get_ds_file_path($this->servers->server_data) . '/' . $loc_dir . '/' . $loc_file;

		// Данные для соединения
		$config = get_file_protocol_config($this->servers->server_data);

		try {
			$this->files->set_driver($config['driver']);
			$this->files->connect($config);
			$file_content = $this->files->write_file($file, $file_contents);
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

		$this->_send_response(array('status' => 1));
	}

	// -----------------------------------------------------------------

    /**
     * Переименование файла
    */
    public function rename_file($server_id = 0)
    {
		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$this->form_validation->set_rules('dir', lang('directory'), 'trim');
		$this->form_validation->set_rules('file', lang('file'), 'trim|required');
		$this->form_validation->set_rules('new_name', lang('new_name'), 'trim|required');

		if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error(lang('web_ftp_form_error'));
				return false;
			}
		}

		$loc_dir = $this->_two_point_delete($this->input->post('dir', true));
		$loc_file = $this->_two_point_delete($this->input->post('file', true));
		$new_name = $this->_two_point_delete($this->input->post('new_name', true));

		$file 		= get_ds_file_path($this->servers->server_data) . '/' . $loc_dir . '/' . $loc_file;
		$new_file 	= get_ds_file_path($this->servers->server_data) . '/' . $loc_dir . '/' . $new_name;

		// Данные для соединения
		$config = get_file_protocol_config($this->servers->server_data);

		try {
			$this->files->set_driver($config['driver']);
			$this->files->connect($config);
			$file_content = $this->files->rename($file, $new_file);
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

		$this->_send_response(array('status' => 1));
	}

	// -----------------------------------------------------------------

    /**
     * Удаление файла
    */
    public function delete_file($server_id = 0)
    {
		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$this->form_validation->set_rules('dir', lang('directory'), 'trim');
		$this->form_validation->set_rules('file', lang('file'), 'trim|required');

		if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error(lang('web_ftp_form_error'));
				return false;
			}
		}

		$loc_dir = $this->_two_point_delete($this->input->post('dir', true));
		$loc_file = $this->_two_point_delete($this->input->post('file', true));

		$file 		= get_ds_file_path($this->servers->server_data) . '/' . $loc_dir . '/' . $loc_file;

		// Данные для соединения
		$config = get_file_protocol_config($this->servers->server_data);

		try {
			$this->files->set_driver($config['driver']);
			$this->files->connect($config);
			$file_content = $this->files->delete_file($file);
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

		$this->_send_response(array('status' => 1));
	}

	// -----------------------------------------------------------------

    /**
     * Загрузка файла на сервер
     *
     * @param int
     *
    */
	public function upload($server_id = false)
    {
		$this->load->helper('string');
		$this->load->helper('path');

		if (!$this->_check_server($server_id)) {
			$this->_send_error($this->_error);
			return;
		}

		$this->form_validation->set_rules('dir', lang('directory'), 'trim');

		if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error(lang('web_ftp_form_error'));
				return false;
			}
		}

		$loc_dir = $this->_two_point_delete($this->input->post('dir', true));

		$tmp_dir = $this->files->_get_tmp_dir();
		$remdir = $this->_get_path($this->servers->server_data)
						. $loc_dir
						. '/';

		$upload_config['upload_path'] 	= $tmp_dir;
		$upload_config['overwrite'] 	= true;
		$upload_config['max_filename'] 	= 64;
		$upload_config['allowed_types'] = '*';

		$this->load->library('upload', $upload_config);

		if (!$this->upload->do_upload()) {
			$this->_send_error(lang('server_files_upload_error') . '<br />' . $this->upload->display_errors());
			return;
		} else {
			/* Файл загружен, делаем необходимые дальнейшие правки */

			$file_data = $this->upload->data();

			if (!$this->_check_file_ext($file_data)) {
				$this->_send_error(lang('web_ftp_forbidden_type'));
				return false;
			}

			$config = get_file_protocol_config($this->servers->server_data);

			try {
				$this->files->set_driver($config['driver']);
				$this->files->connect($config);
				$this->files->upload($file_data['full_path'], $remdir . $file_data['orig_name']);
				unlink($file_data['full_path']);

				/* Обнуляем список кешированных карт сервера */
				$server_data['maps_list'] = '';
				$this->servers->edit_game_server($this->servers->server_data['id'], $server_data);

				$message = lang('server_files_upload_successful', $file_data['orig_name'], $loc_dir);

				/* Сохраняем логи */
				$log_data['type'] = 'server_files';
				$log_data['command'] = 'upload_file';
				$log_data['user_name'] = $this->users->auth_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = 'Upload file success';
				$log_data['log_data'] = 'Directory: ' . $remdir . ' File name: ' . $file_data['orig_name'] . "\n";
				$this->panel_log->save_log($log_data);

				$this->_send_response(array('status' => 1, 'message' => $message));

			} catch (Exception $e) {

				unlink($file_data['full_path']);

				$message = $e->getMessage();

				/* Сохраняем логи */
				$log_data['type'] = 'server_files';
				$log_data['command'] = 'upload_file';
				$log_data['user_name'] = $this->users->auth_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = $message;
				$log_data['log_data'] = 'Directory: ' . $remdir . ' File name: ' . $file_data['orig_name'] . "\n";
				$this->panel_log->save_log($log_data);

				$this->_send_error($message);
				return false;
			}
		}
	}
}
