<?php if (!defined('BASEPATH')) exit('No direct script access allowed');



class Materials extends MX_Controller {
	
	
	function index()
	{
		modules::run('commercial/materials_comm/index');
	}
	
	function view($param = FALSE)
	{
		modules::run('commercial/materials_comm/view', $param);
	}
	
	function category($param = FALSE)
	{
		modules::run('commercial/materials_comm/category', $param);
	}
	
}
