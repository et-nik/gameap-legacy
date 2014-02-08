<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Главная страница
 *
 * Отображение поля входа, либо перенаправление, если пользователь авторизован
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.1
 * 
 */
class Main extends CI_Controller {

    //Отображение
	public function index()
	{
        /* Загрузка модели проверки пользователей */
        $this->load->model('users');
        $this->load->database();
        
        $this->lang->load('auth');

        $this->tpl_data = array();
        $this->tpl_data['code'] = '';

        /* 
         * Проверяем пользователя 
         * если все ок, то перенаправляем в панель
        */
        if($this->users->check_user()){
			redirect('admin');
		}
		
		/* Пользователь не авторизован, показываем ему форму авторизации */
		$this->tpl_data['menu'] 		= '';
        $this->tpl_data['profile'] 		= '';
        $this->tpl_data['content'] 		= '';
        $this->tpl_data['title'] 		= lang('auth_title_index');
		$this->tpl_data['heading'] 		= lang('auth_heading');


        $this->parser->parse('login.html', $this->tpl_data);
	}
}
