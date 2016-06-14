<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Template extends CI_Controller {
	
	//Template
	var $tpl = array();
	
	var $user_data = array();
	var $server_data = array();
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $check = $this->users->check_user();
        
        if($check){
			//Base Template
			$this->tpl['title'] = 'Настройки :: АдминПанель';
			$this->tpl['heading'] = 'Настройки';
			$this->tpl['content'] = '';
			$this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
			$this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
        
        }else{
            header("Location: /auth");
			exit;
        }
    }
    
    
    // ----------------------------------------------------------------

    /**
     * Редактирование пользователя
     * 
    */
    public function index($user_id = false)
    {
		
		$this->tpl['content'] = 'Функция в разработке';
		
		
		$this->parser->parse('main.html', $this->tpl);
	}
    
}
