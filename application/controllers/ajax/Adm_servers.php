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
class Adm_servers extends BaseController {
	
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
		$this->form_validation->set_rules('code', 'код игры', 'trim');

		if($this->form_validation->run() == false){
			show_404();
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
		$this->form_validation->set_rules('ds_id', 'id физ сервера', 'trim|integer');
		
		if($this->form_validation->run() == false){
			show_404();
		}
		
		$ds_id = (int)$this->input->post('ds_id');
		
		$this->dedicated_servers->get_ds_list(array('id' => $ds_id), 1);

        $this->output->append_output($this->dedicated_servers->ds_list[0]['work_path']);
	}
	
	// ----------------------------------------------------------------
    
	/**
	 * Проверяет выбранный порт на занятость
	*/
	public function check_port($port)
	{
		$ds_id 		= (int)$this->input->post('ds_id');
		$server_ip 	= $this->input->post('server_ip');
		
		if ($this->dedicated_servers->check_ports($ds_id, $port, $server_ip)) {
			$this->output->append_output('<img src="' . base_url('themes/system/images/warning.png') . '" />' . lang('adm_servers_port_exists'));
		}
	}
	
		// -----------------------------------------------------------------
	
	/**
	 * Получение формы со списком ip выделенного сервера
	*/
	function get_ip($ds_id = false) 
	{
		$this->load->model('servers');
		$this->load->model('servers/dedicated_servers');
		
		if (!$ds_id OR is_int($ds_id)) {
			show_404();
		}

		if (false == $this->dedicated_servers->get_ds_data($ds_id)) {
			show_404();
		}

		foreach($this->dedicated_servers->ds_list[0]['ip'] as $ip) {
			$ip_list[ $ip ] = $ip;
		}

		if (empty($ip_list)) {
			$this->output->append_output('Select other location');
		} else {
			$ip_list_dropdown = form_dropdown('server_ip', $ip_list);
			$this->output->append_output($ip_list_dropdown);
		}
	}
	
	// ----------------------------------------------------------------
    
	/**
	 * Поиск server.sh/server.exe на ftp сервере
	 * 
	 * @param integer
	*/
	public function found_ftp_path($server_id = false)
	{
		if(!$server_id){
			show_404();
		}
	}
	
	// ----------------------------------------------------------------
    
	/**
	 * Поиск server.sh/server.exe на ftp сервере
	 * 
	 * @param integer
	*/
	public function found_sftp_path($server_id = false)
	{
		if(!$server_id){
			show_404();
		}
	}
}

/* End of file adm_servers.php */
