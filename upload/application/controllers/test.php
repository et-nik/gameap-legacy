<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('adm_servers');
        $this->lang->load('server_control');
        $this->lang->load('main');
        
        $this->load->model('servers');
        $this->load->model('servers/dedicated_servers');
		$this->load->model('servers/games');
		$this->load->model('servers/game_types');
		
		$this->load->helper('string');
		$this->load->helper('form');

		$games_list = $this->games->get_games_list();
		$game_types_list = $this->game_types->get_gametypes_list();

        if ($this->users->check_user()) {
			
			//Base Template
			$this->tpl_data['title'] 	= lang('adm_servers_title_index');
			$this->tpl_data['heading'] 	= lang('adm_servers_heading_index');
			$this->tpl_data['content'] 	= '';
			
			/* Есть ли у пользователя права */
			if(!$this->users->auth_privileges['srv_global']) {
				redirect('admin');
			}
			
			$this->load->model('servers');
			$this->load->library('form_validation');
			$this->load->helper('form');
			
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, true);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
			
			
        
        } else {
			redirect('auth');
        }
    }
    
    function index()
    {
		$this->servers->get_server_data(17);
		
		print_r($this->servers->command("./add_ftp_user.sh test 123 /home/test", $this->servers->server_data));
		//~ print_r($this->servers->command('echo "Текст" >> /etc/proftpd/ftpd.passwd', $this->servers->server_data));
		print_r($this->servers->commands);
	}

}
