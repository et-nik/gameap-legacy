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
    private $_error = "";

    // -----------------------------------------------------------------
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->load->model('servers/dedicated_servers');
        $this->load->model('mds_stats');

        $this->load->helper('date');

        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!$this->users->check_user()) {
			show_404();
        }
    }

    // -----------------------------------------------------------------
    
    private function _send_error($error = "")
    {
        $this->renderJson(array('status' => 0, 'error_text' => $error));
	}

    // -----------------------------------------------------------------

    public function get_stats($ds_id = 0)
    {
        $ds_id = (int)$ds_id;

        if (!$ds_id) {
            $this->_send_error("Empty ds id");
            return;
        }

        if (!$this->dedicated_servers->ds_live($ds_id)) {
            $this->_send_error("Ds not found");
            return;
        }

        $timestart = human_to_unix($this->input->post('datestart'));
        $timeend = human_to_unix($this->input->post('dateend'));

        $now = now();
        $timestart = $timestart > 0 ? $timestart : $now-(6*3600);
        $timeend = $timeend > 0 ? $timeend : $now;
        
        $this->mds_stats->time_between($timestart, $timeend);
        $ds_stats = $this->mds_stats->get_stats($ds_id);

        $this->renderJson(array('status' => 1, 'data' => $ds_stats));
    }

    // -----------------------------------------------------------------

    public function get_stats_about_all()
    {
        $stats = $this->mds_stats->get_all_stats();
        $this->renderJson(array('status' => 1, 'data' => $stats));
    }

}
