<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

/**
 * Управление модулями
 *
 * Контроллер управляет выделенными серверами, игровыми серверами,
 * играми и игровыми модификациями.
 * Позволяет производить следующие действия: добавление, редактирование,
 * удаление, дублирование игровой модификации.
 * 
 * Установку игровых серверов производит модуль cron, adm_servers лишь
 * делает запись о том, что сервер нужно установить.
 * 
 * Переустановка игровых серверов делается заданием значения 0 поля
 * installed таблицы servers.
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8
 */
 
class Adm_modules extends CI_Controller {
	
	var $tpl_data = array();
	
	public function __construct()
    {
        parent::__construct();
        $this->load->model('users');
        
        if ($this->users->check_user()) {
			
			/* Есть ли у пользователя права */
			if (FALSE == $this->users->auth_data['is_admin']) {
				show_404();
			}
			
			//Base Template
			$this->tpl_data['title'] 	= '';
			$this->tpl_data['heading'] 	= '';
			$this->tpl_data['content'] 	= '';
			
			$this->tpl_data['menu'] = $this->parser->parse('menu.html', $this->tpl_data, TRUE);
		}
	}
	
	// ---------------------------------------------------------------------
	
	// Отображение информационного сообщения
    function __show_message($message = FALSE, $link = FALSE, $link_text = FALSE)
    {
        
        if (!$message) {
			$message = lang('error');
		}
		
        if (!$link) {
			$link = 'javascript:history.back()';
		}
		
		if (!$link_text) {
			$link_text = lang('back');
		}

        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
        $this->parser->parse('main.html', $this->tpl_data);
    }
	
	// ---------------------------------------------------------------------
	
	function _update_list()
	{

	}
	
	// ---------------------------------------------------------------------
	
	public function index()
	{
		$this->tpl_data['content'] = 'Функция в разработке';
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ---------------------------------------------------------------------
	
	public function install()
	{
		$this->tpl_data['content'] = 'Функция в разработке';
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	// ---------------------------------------------------------------------
	
	public function update_list()
	{
		$this->tpl_data['content'] = 'Функция в разработке';
		$this->parser->parse('main.html', $this->tpl_data);
	}

}
