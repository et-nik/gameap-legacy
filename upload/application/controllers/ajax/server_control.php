<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/


// ------------------------------------------------------------------------

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
class Server_control extends CI_Controller {
	
	// ----------------------------------------------------------------
    
    /**
     * Получает форму со списком игровых типов выбранной игры
    */
	public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model('users');
        $this->lang->load('server_control');
        
        if($this->users->check_user()) {

			$this->load->library('form_validation');
			$this->load->helper('form');
			
			$this->load->model('servers');
			$this->load->model('servers/dedicated_servers');
			$this->load->model('servers/games');
			$this->load->model('servers/game_types');
		} else {
			show_404();
		}
    }
    
    // ----------------------------------------------------------------
    
    /**
     * Получение статуса сервера
    */
    public function get_status($server_id = false)
    {
		if (!$server_id) {
			show_404();
		}
		
		if (false == $this->servers->get_server_data($server_id)) {
			show_404();
		}
		
		/* Проверка привилегий на сервер */
		$this->users->get_server_privileges($server_id);
		
		if (!$this->users->auth_servers_privileges['VIEW']) {
			show_404();
		}
		
		if($this->servers->server_status($this->servers->server_data['server_ip'], 
											$this->servers->server_data['query_port'], 
											$this->servers->server_data['engine'], 
											$this->servers->server_data['engine_version'])) {
			$this->output->append_output(1);
											
		} else {
			$this->output->append_output(0);
		}
		
	}
	
}

/* End of file server_control.php */
