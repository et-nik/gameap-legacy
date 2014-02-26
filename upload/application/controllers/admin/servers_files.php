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
class Servers_files extends CI_Controller {
	
	//Template
	var $tpl_data = array();
	
	var $user_data = array();
	var $server_data = array();
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('server_files');
        $this->lang->load('server_command');

        if($this->users->check_user()){
			//Base Template
			$this->tpl_data['title'] 		= lang('server_files_title_index');
			$this->tpl_data['heading']		= lang('server_files_header_index');
			$this->tpl_data['content'] 		= '';
			$this->tpl_data['menu'] 		= $this->parser->parse('menu.html', $this->tpl_data, true);
			$this->tpl_data['profile'] 		= $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
        
        }else{
            redirect('auth');
        }
    }
    
    // Отображение информационного сообщения
    function _show_message($message = false, $link = false, $link_text = false)
    {
        
        if (!$message) {
			$message = lang('error');
		}
		
        if (!$link) {
			$link = 'javascript:history.back()';
		}
		
		if (!$link_text) {
			$link_text = lang('back');
		}

        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, true);
        $this->parser->parse('main.html', $this->tpl_data);
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
    
    // ----------------------------------------------------------------

    /**
     * Главная страница
     * 
    */
    public function index()
    {
		/* Загружаем модель */
		$this->load->model('servers');
		$this->load->model('servers/games');
		$this->load->helper('games');
		
		$this->servers->get_servers_list($this->users->auth_id);
		
		$local_tpl_data['url'] 			= site_url('admin/servers_files/server');
		$local_tpl_data['games_list'] = servers_list_to_games_list($this->servers->servers_list);
			
		$this->tpl_data['content'] .= $this->parser->parse('servers/select_server.html', $local_tpl_data, true);
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Просмотр доступных конфигурационных файлов и контент директорий
     * 
     * @param int
     * 
    */
	public function server($server_id = false)
    {
		/* 
		 * Если не указан id сервера, то перенаправляем на
		 * страницу выбора 
		*/
		if(!$server_id){
			redirect('admin/servers_files');
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
			$this->_show_message(lang('server_files_no_privileges'), site_url('admin/servers_files'));
			return false;
		}
		
		/* Получение данных сервера */
		$this->servers->get_server_data($server_id);
		
		/* Если сервер не локальный и не настроен FTP, то выдаем ошибку */
		//~ if ($this->servers->server_data['ds_id'] 
			//~ && !$this->servers->server_data['ftp_host']
			//~ && !$this->servers->server_data['ssh_host']
			//~ ) {
			//~ $this->_show_message(lang('server_files_ftp_not_set'), site_url('admin/servers_files'));
			//~ return false;
		//~ }
		
		/* Получение данных сервера для шаблона */
		$local_tpl_data['server_id'] = $this->servers->server_data['id'];
		
		
		/*
		 * Следующие два условия проверяют права на правку
		 * конфигурации и загрузку контента по отдельности
		 * 
		 * Если права имеются, то загружаются данные json из таблиц 
		 * config_files - конфигурационные файлы
		 * content_dirs - контент директории
		 * 
		 * и вставляются в шаблоны
		 * servers/servers_files_cfg.html - конфиг файлы
		 * servers/servers_files_content_dirs.html - контент
		 * 
		*/
		if($this->users->auth_servers_privileges['CHANGE_CONFIG']) {
			$cfg_files = json_decode($this->servers->server_data['config_files'], true);
			
			if($cfg_files) {
				$i = -1;
				$local_tpl_data['cfg_files'] = $cfg_files;
				foreach($cfg_files as $array) {
					$i ++;
					$local_tpl_data['cfg_files'][$i]['id_cfg'] = $i;
				}
			
			} else {
				$local_tpl_data['cfg_files'] = array();
			}

			$this->tpl_data['content'] .= $this->parser->parse('servers/servers_files_cfg.html', $local_tpl_data, true);
		}

		if($this->users->auth_servers_privileges['UPLOAD_CONTENTS']){
			/* Чтобы шоткод отображался нормально*/

			$content_dirs = json_decode($this->servers->server_data['content_dirs'], true);
			
			if($content_dirs) {
				$i = -1;
				$local_tpl_data['content_dirs'] = $content_dirs;
				foreach($content_dirs as $array) {
					$i ++;
					$local_tpl_data['content_dirs'][$i]['id_dir'] = $i;
				}
			
			} else {
				$local_tpl_data['content_dirs'] = array();
			}
			
			$this->tpl_data['content'] .= $this->parser->parse('servers/servers_files_content_dirs.html', $local_tpl_data, true);
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Редактирование конфигурации
     * 
     * @param int
     * @param int
     * 
    */
	public function edit_config($server_id = false, $cfg_id = false)
    {
		$this->load->helper('ds');

		/* 
		 * Если не указан id сервера, то перенаправляем на
		 * страницу выбора 
		*/
		if(!$server_id){
			redirect('admin/servers_files');
		}
		
		/* Преобразование id в числовое значение */
		$server_id 		= (int)$server_id;
		$cfg_id  		= (int)$cfg_id;
		
		
		/* Загружаем необходимые модули */
		$this->load->model('servers');
		$this->load->helper('path');
		
		/* Получение привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		/* Проверка привилегий на правку конфигов */
		if (!$this->users->auth_servers_privileges['CHANGE_CONFIG']) {
			$this->_show_message(lang('server_files_no_cfg_privileges'));
			return false;
		}
		
		/* Получение данных сервера */
		$this->servers->get_server_data($server_id);
		
		$s_cfg_files = json_decode($this->servers->server_data['config_files'], true);
		
		/* Проверяем, правильно ли указан ID конфигурационного файла */
		if (!array_key_exists($cfg_id, $s_cfg_files)) {
			$this->_show_message(lang('server_files_cfg_not_found'));
			return false;
		}

		$file =& $s_cfg_files[$cfg_id]['file'];
		$dir = $this->_get_path($this->servers->server_data);
		
		if(!$this->input->post('submit')) {
			
			/* 
			 * Форма не отправлена
			 * в этом случае показываем содержимое конфигурационного файла
			 * в textarea 
			*/
			try {
				$file_contents = read_ds_file($dir . $file, $this->servers->server_data);
				
				$local_tpl_data['file_contents'] 		= $file_contents;
				$local_tpl_data['file_name'] 			= basename($file);
				$local_tpl_data['id_cfg'] 				= (int)$cfg_id;
				$local_tpl_data['cfg_id']				= (int)$cfg_id;
				$local_tpl_data['server_id'] 			= $server_id;
				
				$this->tpl_data['content'] .= $this->parser->parse('servers/edit_file.html', $local_tpl_data, true);
				
			} catch (Exception $e) {
				$message = $e->getMessage();
				
				/* Сохраняем логи */
				$log_data['type'] = 'server_files';
				$log_data['command'] = 'edit_config';
				$log_data['user_name'] = $this->users->auth_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = 'Read file failed';
				$log_data['log_data'] = 'File: ' . $dir . $file . "\n";
				$this->panel_log->save_log($log_data);
				
				// Отображаем админу дополнительную информацию
				if ($this->users->auth_data['is_admin']) {
					$message .= '<p align="center">' . lang('file') . ': <strong>"' . $dir . $file . '"</strong></p>';
				}
				
				$this->_show_message($message);
				return false;
			}
			
		} else {
			/*
			 * Форма отправлена
			 * сохраняем содержимое конфига на сервере
			*/
			
			$cfg_data = $this->input->post('file_contents', true);

			try {
				$file_contents = write_ds_file($dir . $file, $cfg_data, $this->servers->server_data);
				
				$this->_show_message(lang('server_files_data_writed'), site_url('admin/servers_files/server/' . $server_id), lang('next'));
				
				$log_data['type'] = 'server_files';
				$log_data['command'] = 'edit_config';
				$log_data['user_name'] = $this->users->auth_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = 'Config edit success';
				$log_data['log_data'] = 'Config file: ' . $dir . '/' . $file  . "\n";
				$this->panel_log->save_log($log_data);
				
				return true;
				
			} catch (Exception $e) {
				
				$message = $e->getMessage();
				
				// Отображаем админу дополнительную информацию
				if ($this->users->auth_data['is_admin']) {
					$message .= '<p align="center">' . lang('file') . ': <strong>"' . $dir . $file . '"</strong></p>';
				}
				
				$this->_show_message($message);
				
				/* Сохраняем логи */
				$log_data['type'] = 'server_files';
				$log_data['command'] = 'edit_config';
				$log_data['user_name'] = $this->users->auth_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = 'Edit file failed';
				$log_data['log_data'] = 'File: ' . $dir . $file  . ' Error: ' . $this->servers->errors . "\n";
				$this->panel_log->save_log($log_data);
				
				return false;
			}
			
		}
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Загрузка файла на сервер
     * 
     * @param int
     * @param int
     * 
    */
	public function upload($server_id = false, $dir_id = false)
    {
		$this->load->helper('ds');
		$this->load->helper('string');
		$this->load->driver('files');
		
		/* 
		 * Если не указан id сервера, то перенаправляем на
		 * страницу выбора 
		*/
		if(!$server_id){
			redirect('admin/servers_files');
		}
		
		/* Преобразование id в числовое значение */
		$server_id = (int)$server_id;
		$dir_id = (int)$dir_id;
		
		/* Загружаем необходимые модули */
		$this->load->model('servers');
		$this->load->helper('path');
		
		/* Получение привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		/* Проверка привилегий на правку конфигов */
		if (!$this->users->auth_servers_privileges['UPLOAD_CONTENTS']) {
			$this->_show_message(lang('server_files_no_cfg_privileges'));
			return false;
		}
		
		/* Получение данных сервера */
		$this->servers->get_server_data($server_id);
		
		$s_content_dirs = json_decode($this->servers->server_data['content_dirs'], true);
		
		/* Проверяем, правильно ли указан ID контент директории */
		if(!array_key_exists($dir_id, $s_content_dirs)){
			$this->_show_message(lang('server_files_content_dir_not_found'));
			return false;
		}
		
		$tmp_dir = sys_get_temp_dir();
		$remdir = $this->_get_path($this->servers->server_data) . $s_content_dirs[$dir_id]['path'] . '/';

		$upload_config['upload_path'] 	= $tmp_dir;
		$upload_config['overwrite'] 	= true;
		$upload_config['max_filename'] 	= 64;
		$upload_config['allowed_types'] = $s_content_dirs[$dir_id]['allowed_types'];
		
		$this->load->library('upload', $upload_config);

		if (!$this->upload->do_upload()) {
			$this->tpl_data['content'] .= lang('server_files_upload_error') . '<br />' . $this->upload->display_errors();
		} else {
			/* Файл загружен, делаем необходимые дальнейшие правки */
			
			$file_data = $this->upload->data();
			
			$config = get_file_protocol_config($this->servers->server_data);
			
			try {
				$this->files->set_driver($config['driver']);
				$this->files->connect($config);
				$this->files->upload($file_data['full_path'], $remdir . $file_data['orig_name']);
				unlink($file_data['full_path']);
				
				/* Обнуляем список кешированных карт сервера */
				$server_data['maps_list'] = '';
				$this->servers->edit_game_server($this->servers->server_data['id'], $server_data);
				
				$message = lang('server_files_upload_successful', $file_data['orig_name'], $s_content_dirs[$dir_id]['path']);
				$this->_show_message($message, site_url('admin/servers_files/server/' . $server_id), lang('next'));
				
				/* Сохраняем логи */
				$log_data['type'] = 'server_files';
				$log_data['command'] = 'upload_file';
				$log_data['user_name'] = $this->users->auth_login;
				$log_data['server_id'] = $this->servers->server_data['id'];
				$log_data['msg'] = 'Upload file success';
				$log_data['log_data'] = 'Directory: ' . $remdir . ' File name: ' . $file_data['orig_name'] . "\n";
				$this->panel_log->save_log($log_data);
				
				return true;
				
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
				
				$this->_show_message($message);
				return false;
			}
			
		}
			
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ----------------------------------------------------------------

    /**
     * Загрузка zip архива
     * 
     * @param int
     * 
    */
	public function upload_zip($server_id = false)
    {
		/* 
		 * Если не указан id сервера, то перенаправляем на
		 * страницу выбора 
		*/
		if (!$server_id) {
			redirect('admin/servers_files');
		}
		
		$server_id = (int)$server_id;

		$this->parser->parse('main.html', $this->tpl_data);
	}
    
}

/* End of file server_files.php */
/* Location: ./application/controllers/admin/server_files.php */
