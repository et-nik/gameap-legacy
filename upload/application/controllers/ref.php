<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Ref extends MX_Controller {
	
	public function __construct()
    {
        parent::__construct();

        $this->load->model('users');
        $this->users->check_user();
    }
    
    public function _remap($method)
	{
		$this->_ref_register($method);
	}
    
	private function _ref_register($user_id)
	{
		$user_live = $this->users->user_live($user_id);
		
		if (!$this->input->cookie('ref') && !$this->users->auth_id && $user_live) {
			$cookie = array(
				'name'   => 'ref',
				'value'  => $user_id . '|' . time(),
				'expire' => '86500',
			);
			
			$this->input->set_cookie($cookie);
		}
		
		redirect();
	}

}
