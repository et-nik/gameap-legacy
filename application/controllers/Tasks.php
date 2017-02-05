<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (GameAP)
 *
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (NiK)
 * @copyright	Copyright (c) 2014-2016
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
*/

use \Myth\Controllers\BaseController;

class Tasks extends BaseController {

    public $tpl = array();

    // -----------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();

		$this->load->database();
        $this->load->model('users');
        $this->lang->load('main');

        if (!$this->users->check_user()) {
            redirect('auth');
        }

        if (!$this->users->auth_data['is_admin']) {
            redirect('admin');
        }

        $this->load->model('gdaemon_tasks');

        $this->lang->load('server_control');

        //Base Template
        $this->tpl['title'] 	= lang('gdaemon_tasks_title_index');
        $this->tpl['heading'] 	= lang('tasks_heading_index');
        $this->tpl['content'] 	= '';

        $this->tpl['menu']      = $this->parser->parse('menu.html', $this->tpl, true);
        $this->tpl['profile']   = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
    }

    // -----------------------------------------------------------------

    /**
     * Show info message
     *
     * @param string    $message
     * @param string    $link
     * @param string    $link_test
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

	public function _remap($method, $params = array())
	{
		if ($method == 'page' or $method == 'index') {
			return call_user_func_array(array($this, 'index'), $params);
		}

		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}

		show_404();
	}

    // -----------------------------------------------------------------

    /**
     * Index page. Show GDaemon tasks list
     */
    public function index($offset = 0)
	{
        $this->load->helper('form');
        $this->load->helper('date');

        $local_tpl = array();

        $filter = $this->users->get_filter('gdaemon_tasks');
        foreach ($filter as $filter_name => &$filter_val) {
            $this->gdaemon_tasks->set_filter($filter_name, $filter_val);
        }

        $config['base_url']         = site_url('tasks/page');
		$config['total_rows']       = $this->gdaemon_tasks->get_all_count();
		$config['per_page']         = 25;
		$config['full_tag_open']    = '<p id="pagination">';
		$config['full_tag_close']   = '</p>';

        $this->pagination->initialize($config);
		$local_tpl['pagination'] = $this->pagination->create_links();

        foreach ($filter as $filter_name => &$filter_val) {
            $this->gdaemon_tasks->set_filter($filter_name, $filter_val);
        }

        if (!$this->gdaemon_tasks->get_list($config['per_page'], $offset)) {
            $this->_show_message($this->gdaemon_tasks->last_error);
        }

        $local_tpl['task_names_dropdown'] = form_multiselect(
            'filter_gdaemon_tasks_name[]',
            $this->gdaemon_tasks->get_names(),
            (empty($filter['task']) ? '' : $filter['task'])
        );

        $local_tpl['task_statuses_dropdown'] = form_multiselect(
            'filter_gdaemon_tasks_status[]',
            $this->gdaemon_tasks->get_statuses(),
            (empty($filter['status']) ? '' : $filter['status'])
        );

        $local_tpl['tasks_list'] = array();
        foreach ($this->gdaemon_tasks->tasks_list as &$task) {
            $local_tpl['tasks_list'][] = array(
                'task_id'               => $task['id'],
                'task_date_create'      => unix_to_human($task['time_create'], true),
                'task_date_stchange'    => unix_to_human($task['time_stchange'], true),
                'task_task'             => $task['task'],
                'task_htask'            => $this->gdaemon_tasks->human_name($task['task']),
                'task_ds_id'            => $task['ds_id'],
                'task_ds_name'          => $task['ds_name'],
                'task_server_id'        => $task['server_id'],
                'task_server_name'      => $task['server_name'],
                'task_cmd'              => $task['cmd'],
                'task_status'           => $task['status'],
                'task_hstatus'          => $this->gdaemon_tasks->human_status($task['status']),
            );
        }

		$this->tpl['content'] .= $this->parser->parse('tasks/tasks_list.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }

    // -----------------------------------------------------------------

    /**
     * Set tasks list filter
     */
    public function set_filter()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('filter_gdaemon_tasks_task_name',     lang('name'), 'trim');
        $this->form_validation->set_rules('filter_gdaemon_tasks_ds_id',         lang('ds_id'), 'trim');
        $this->form_validation->set_rules('filter_gdaemon_tasks_server_id',     lang('server_id'), 'trim');
        $this->form_validation->set_rules('filter_gdaemon_tasks_status',        lang('server_id'), 'trim');
        // $this->form_validation->set_rules('filter_gdaemon_tasks_time_create',   lang('time_create'), 'trim');
        // $this->form_validation->set_rules('filter_gdaemon_tasks_time_stchange', lang('time_create'), 'trim');

        if ($this->form_validation->run() == false) {
			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return;
			}
		} else {
            $reset = (bool) $this->input->post('reset');

            $filter['task']        = $reset ? '' : $this->input->post('filter_gdaemon_tasks_name');
            $filter['ds_id'] 	   = $reset ? '' : $this->input->post('filter_gdaemon_tasks_ds_id');
            $filter['server_id']   = $reset ? '' : $this->input->post('filter_gdaemon_tasks_server_id');
            $filter['status']      = $reset ? '' : $this->input->post('filter_gdaemon_tasks_status');
            // $filter['time_create']    = $reset ? '' : $this->input->post('filter_gdaemon_tasks_time_create');
            // $filter['time_stchange']  = $reset ? '' : $this->input->post('filter_gdaemon_tasks_time_stchange');

            $this->users->update_filter('gdaemon_tasks', $filter);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    // -----------------------------------------------------------------

    public function view($task_id = 0)
	{
        $local_tpl = array();

        $task_id = (int)$task_id;

        if (!$this->gdaemon_tasks->get_single($task_id)) {
            $this->_show_message($this->gdaemon_tasks->last_error);
            return;
        }

        $local_tpl = array(
            'task_id'           => $this->gdaemon_tasks->single_task['id'],
            'task_ds_id'        => $this->gdaemon_tasks->single_task['ds_id'],
            'task_ds_name'      => $this->gdaemon_tasks->single_task['ds_name'],
            'task_server_id'    => $this->gdaemon_tasks->single_task['server_id'],
            'task_server_name'  => $this->gdaemon_tasks->single_task['server_name'],
            'task_task'         => $this->gdaemon_tasks->single_task['task'],
            'task_htask'        => $this->gdaemon_tasks->human_status($this->gdaemon_tasks->single_task['task']),
            'task_data'         => $this->gdaemon_tasks->single_task['data'],
            'task_cmd'          => $this->gdaemon_tasks->single_task['cmd'],
            'task_output'       => $this->gdaemon_tasks->single_task['output'],
            'task_status'       => $this->gdaemon_tasks->single_task['status'],
            'task_hstatus'      => $this->gdaemon_tasks->human_status($this->gdaemon_tasks->single_task['status']),
        );

        $this->tpl['content'] .= $this->parser->parse('tasks/task_view.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }
}
