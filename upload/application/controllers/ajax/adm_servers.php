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
 * Ajax для администрирования серверов
 *
 * Обновление данных, получение типов определенной игры
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.6
 * 
*/
class Adm_servers extends CI_Controller {
	
	public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->model('users');
        $this->lang->load('adm_servers');
        
        if($this->users->check_user()) {
			
			/* Есть ли у пользователя права */
			if(!$this->users->auth_privileges['srv_global']) {
				show_404();
				return false;
			}
        
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
     * Получает форму со списком игровых типов выбранной игры
    */
    public function get_gametypes()
    {
		$this->form_validation->set_rules('code', 'код игры', 'trim|xss_clean');

		if($this->form_validation->run() == false){
			header("HTTP/1.0 404 Not Found");
			return false;
		}
		
		$default = false;
		$game_code = $this->input->post('code');

		if($game_code) {
			$where = array('game_code' => $game_code);
		} else {
			$where = false;
		}
		
		$gametypes_list = $this->game_types->get_gametypes_list($where);
		
		if(!$gametypes_list) {
			$this->output->append_output(lang('adm_servers_no_game_types_for_selected_game'));
			return false;
		}

		foreach($gametypes_list as $list) {
			$options[$list['id']] = $list['name'];
		}
		
		// Выводим готовую форму
		$this->output->append_output(form_dropdown('game_type', $options, $default));
	}
	
	// ----------------------------------------------------------------
    
	/**
	 * Получает путь к игровому серверу и выводит строку
	*/
	public function get_ds_path()
	{
		$this->form_validation->set_rules('ds_id', 'id физ сервера', 'trim|integer|xss_clean');
		
		if($this->form_validation->run() == false){
			header("HTTP/1.0 404 Not Found");
			return false;
		}
		
		$ds_id = (int)$this->input->post('ds_id');
		
		if(!$ds_id) {
			$this->output->append_output($this->config->config['local_script_path']);
		} else {
			$this->dedicated_servers->get_ds_list(array('id' => $ds_id), 1);
			
			if(strtolower($this->dedicated_servers->ds_list[0]['control_protocol']) == 'ssh') {
				$this->output->append_output($this->dedicated_servers->ds_list[0]['ssh_path']);
			} elseif(strtolower($this->dedicated_servers->ds_list[0]['control_protocol']) == 'telnet') {
				$this->output->append_output($this->dedicated_servers->ds_list[0]['telnet_path']);
			} elseif(strtolower($this->dedicated_servers->ds_list[0]['os']) == 'windows') {
				$this->output->append_output($this->dedicated_servers->ds_list[0]['telnet_path']);
			} else {
				$this->output->append_output($this->dedicated_servers->ds_list[0]['ssh_path']);
			}
		}
	}
	
	// ----------------------------------------------------------------
    
	/**
	 * Проверяет выбранный порт на занятость
	*/
	public function check_port($port)
	{
		$ds_id = (int)$this->input->post('ds_id');
		
		if (!$this->dedicated_servers->check_ports($ds_id, $port)) {
			$this->output->append_output('<img src="' . site_url('themes/system/images/warning.png') . '" />' . lang('adm_servers_port_exists'));
		}
	}
}

/* End of file adm_servers.php */
