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

use \Myth\Controllers\BaseController;

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
class Main extends BaseController {

    public $tpl = array();

    //Отображение
	public function index()
	{
        /* Загрузка модели проверки пользователей */
        $this->load->database();
        $this->load->model('users');
        
        $this->lang->load('auth');

        $this->tpl['code'] = '';

        /* 
         * Проверяем пользователя 
         * если все ок, то перенаправляем в панель
        */
        if(!$this->users->check_user()){
			redirect('auth/in');
		}
		
		/* Пользователь не авторизован, показываем ему форму авторизации */
		// $this->tpl['menu'] 		= '';
        // $this->tpl['profile'] 		= '';
        // $this->tpl['content'] 		= '';
        // $this->tpl['title'] 		= lang('auth_title_index');
		// $this->tpl['heading'] 		= lang('auth_heading');

        $local_tpl = array();

        //Base Template
        $this->tpl['title'] 	= lang('tasks_title_index');
        $this->tpl['heading'] 	= lang('tasks_heading_index');
        $this->tpl['content'] 	= '';
        
        $this->tpl['menu']      = $this->parser->parse('menu.html', $this->tpl, true);
        $this->tpl['profile']   = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);

        $this->tpl['content'] .= $this->parser->parse('main_page.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
	}
}
