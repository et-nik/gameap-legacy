<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (GameAP)
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014-2017, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
 */

use \Myth\Controllers\BaseController;

/**
 * Main page
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.1
 * 
 */
class Main extends BaseController {

    public $tpl = array();

	public function index()
	{
        $this->load->database();
        $this->load->model('users');
        
        $this->lang->load('auth');

        $this->tpl['code'] = '';

        if (!$this->users->check_user()){
			redirect('auth/in');
		} else {
            redirect('admin');
        }
	}
}
