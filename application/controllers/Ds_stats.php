<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2016, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/

use \Myth\Controllers\BaseController;

class Ds_stats extends BaseController {

    var $tpl = array();

    // -----------------------------------------------------------------
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->load->model('servers/dedicated_servers');
        $this->load->model('mds_stats');
        
        // $this->lang->load('server_command');
        // $this->lang->load('server_control');

        if (!$this->users->check_user()) {
            redirect('auth');
        }

        if ($this->users->auth_data['group'] < 50) {
            redirect('admin');
        }
        
        //Base Template
        $this->tpl['title'] 		= "Ds stats";
        $this->tpl['heading']		= "Ds stats";
        $this->tpl['content'] 		= '';
        $this->tpl['menu'] 		= $this->parser->parse('menu.html', $this->tpl, true);
        $this->tpl['profile'] 		= $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);

    }

    // -----------------------------------------------------------------

    /**
     * Отображение информационного сообщения
     *
     * @param string    $message    Сообщение об ошибке
     * @param string    $link       Ссылка
     * @param string    $link_test  Текст ссылки
    */ 
    private function _show_message($message = false, $link = false, $link_text = false)
    {
        $message 	OR $message = lang('error');
		$link 		OR $link = 'javascript:history.back()';
		$link_text 	OR $link_text = lang('back');

        $local_tpl['message'] = $message;
        $local_tpl['link'] = $link;
        $local_tpl['back_link_txt'] = $link_text;
        $this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }

    // -----------------------------------------------------------------

    /**
     * @param int   $ds_id  ID выделенного сервера
     */
    public function index()
    {
        $local_tpl = array();
        $this->tpl['content'] .= $this->parser->parse('ds_stats_list.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }

    // -----------------------------------------------------------------

    /**
     * @param int   $ds_id  ID выделенного сервера
     */
    public function full($ds_id = 0)
    {
        $this->load->helper('date');
        
        $ds_id = (int)$ds_id;

        if (!$ds_id) {
            $this->_show_message("Empty ds id");
            return;
        }

        if (!$this->dedicated_servers->ds_live($ds_id)) {
            $this->_show_message("Ds not found");
            return;
        }

        $local_tpl = array(
            'ds_id' => $ds_id,
            'default_datestart' => unix_to_human(now()-(6*3600)),
            'default_dateend' => unix_to_human(now()),
        );

        // print_r($this->mds_stats->get_stats($ds_id));

        $this->tpl['content'] .= $this->parser->parse('ds_stats.html', $local_tpl, true);
		$this->parser->parse('main.html', $this->tpl);
    }
}
