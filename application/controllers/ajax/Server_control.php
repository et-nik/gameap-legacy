<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (GameAP)
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (NiK)
 * @copyright	Copyright (c) 2014-2016
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
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
 * @sinse		0.8.6
 * 
*/
class Server_control extends BaseController {
	
	// -----------------------------------------------------------------
    
    /**
     * Получает форму со списком игровых типов выбранной игры
    */
	public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model('users');
        $this->lang->load('server_control');

        if (!$this->input->is_ajax_request()) {
		   show_404();
		}
        
        if (!$this->users->check_user()) {
            show_404();
        }

        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->helper('ds');
        
        $this->load->model('servers');
        $this->load->model('servers/dedicated_servers');
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
     * Получение статуса сервера
    */
    public function get_status($server_id = false)
    {
		if (!$server_id) {
			$this->_send_error("Empty server id");
            return;
		}

		if (false == $this->servers->get_server_data($server_id)) {
			$this->_send_error("Server not found");
            return;
		}
		
		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		if (!$this->users->auth_servers_privileges['VIEW']) {
			$this->_send_error("Access denied");
            return;
		}

        $this->_send_response(array(
            'status' => 1,
            'data' => array(
                'query_active'          => (int)$this->servers->server_status(),
                'process_active'        => (int)$this->servers->server_data['process_active'],
                'last_process_check'    => (int)$this->servers->server_data['last_process_check']
            )
        ));
	}
	
	// -----------------------------------------------------------------
    
    /**
     * Получение содержимого консоли
    */
    public function get_console($server_id = false)
    {
        $this->load->driver('files');
        
        if (!$server_id) {
			$this->_send_error("Empty server id");
            return;
		}

		if (false == $this->servers->get_server_data($server_id)) {
			$this->_send_error("Server not found");
            return;
		}
		
		// Получение прав на сервер
		$this->users->get_server_privileges($this->servers->server_data['id']);
		
		if (!$this->users->auth_data['is_admin'] && !$this->users->auth_servers_privileges['CONSOLE_VIEW']) {
            $this->_send_error("Access denied");
            return;
		}

        $console_content = "";
        $fconfig = get_file_protocol_config($this->servers->server_data);

        try {
			$this->files->set_driver($fconfig['driver']);
			$this->files->connect($fconfig);
			
			$console_content = $this->files->read_file(get_ds_file_path($this->servers->server_data) . "stdout.txt");
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

        $this->_send_response(array(
            'status' => 1,
            'data' => array(
                'console' => $console_content
            )
        ));
	}
	
	// -----------------------------------------------------------------
    
    /**
     * Отправка ркон команды на сервер
    */
    public function send_command($server_id = false)
    {
		$this->load->driver('files');
        
        if (!$server_id) {
			$this->_send_error("Empty server id");
            return;
		}

		if (false == $this->servers->get_server_data($server_id)) {
			$this->_send_error("Server not found");
            return;
		}
		
		// Получение прав на сервер
		$this->users->get_server_privileges($this->servers->server_data['id']);
		
		if (!$this->users->auth_data['is_admin'] && !$this->users->auth_servers_privileges['CONSOLE_VIEW']) {
            $this->_send_error("Access denied");
            return;
		}

        $this->form_validation->set_rules('command', 'rcon command', 'trim|required|max_length[64]|min_length[1]');

        if($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_send_error("Command error");
				return;
			}
		}
		
        $fconfig = get_file_protocol_config($this->servers->server_data);

        try {
			$this->files->set_driver($fconfig['driver']);
			$this->files->connect($fconfig);
			
			$console_content = $this->files->write_file(get_ds_file_path($this->servers->server_data) . "stdin.txt", $this->input->post('command'));
		} catch (exception $e) {
			$this->_send_error($e->getMessage());
			return;
		}

        $this->_send_response(array(
            'status' => 1
        ));
	}
	
}

/* End of file server_control.php */
