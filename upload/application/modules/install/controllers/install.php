<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 
class Install extends MX_Controller {
	
	//Template
	var $tpl_data = array();
	
	var $user_data = array();
	var $server_data = array();
	
	var $autoload = array(
        'helper'    => array('url', 'form', 'safety'),
        'libraries' => array('form_validation'),
        'model' => array('users', 'servers', 'servers/dedicated_servers', 'servers/games', 'servers/game_types'),
    );
	
	public function __construct()
    {
        parent::__construct();
		
		/* Если директория install или install/install.php отсутствуют,
		 * то показываем кукишь =)
		*/
        if(!file_exists('install_gameap') OR !file_exists('install_gameap/install.php') OR !file_exists('install_gameap/db.php')) {
			show_404();
		}
    }
    
    // Отображение информационного сообщения
    private function _show_message($message = FALSE, $link = FALSE, $link_text = FALSE)
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
    
    public function index() 
    {
		/* Если присутствует конфиг и база данных, то показываем конечную страницу */
		if(file_exists('application/config/gameap_config.php') OR file_exists('application/config/database.php')) {
			redirect('install/page/end');
		}
		
		redirect('install/page/select_language');
	}
    
    public function page($page = FALSE, $language = 'russian') 
    {
		$this->config->config['language'] = $language;
		/* Если присутствует конфиг и база данных, то показываем конечную страницу */
		if(file_exists('application/config/gameap_config.php') OR file_exists('application/config/database.php')) {
			$page = 'end';
		}
		
		include_once('install_gameap/install.php');
	}

}
