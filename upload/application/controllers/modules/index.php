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

class Index extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('users');

		if ($this->users->check_user()) {
			
			/* Есть ли у пользователя права */
			if(!$this->users->auth_data['is_admin']) {
				redirect('admin');
			}
			
			$this->load->model('modules');
			$this->modules->get_modules_list();
			
			//Base Template
			$this->tpl_data['title'] 	= lang('ap_title');
			$this->tpl_data['heading'] 	= lang('ap_header');
			$this->tpl_data['content'] = '';
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, TRUE);
			$this->tpl_data['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), TRUE);
			
		
		} else {
			redirect('auth');
		}
	}
	
	//Главная
	public function index()
	{
		$this->tpl_data['content'] = 'Функция в разработке';
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
}
