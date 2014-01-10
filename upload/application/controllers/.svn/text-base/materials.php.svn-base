<?php if (!defined('BASEPATH')) exit('No direct script access allowed');



class Materials extends MX_Controller {
	
	public function __construct()
    {
        parent::__construct();

        $this->load->model('users');
        $this->users->check_user();
    }
    
	function index()
	{
		echo modules::run('commercial/materials_comm/index');
	}
	
	function view($param = false)
	{
		echo modules::run('commercial/materials_comm/view', $param);
	}
	
	function category($param = false)
	{
		echo modules::run('commercial/materials_comm/category', $param);
	}
	
}
