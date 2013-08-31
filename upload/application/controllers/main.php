<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Главная страница
 *
 * Отображение поля входа, либо перенаправление, если пользователь авторизован
 *
 * @package		Game AdminPanel
 * @category	Controllers
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

        $data = array();
        $data['code'] = '';

        /* 
         * Проверяем пользователя 
         * если все ок, то перенаправляем в панель
        */
        if($this->users->check_user()){
			redirect('admin');
		}
		
		/* Пользователь не авторизован, показываем ему форму авторизации */
		$data['menu'] = '';
        $data['title'] = '';
        $data['profile'] = '';
        $data['content'] = '';
        $data['heading'] = 'Вход в админпанель';


        $this->parser->parse('login.html', $data);
	}
}
