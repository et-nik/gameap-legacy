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

class Tasks extends CI_Controller {

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

        //Base Template
        $this->tpl['title'] 	= lang('tasks_title_index');
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
    
    public function index()
	{
        $local_tpl = array();

        $config['base_url']         = site_url('tasks/page');
		$config['total_rows']       = $this->gdaemon_tasks->get_all_count();
		$config['per_page']         = 25;
		$config['full_tag_open']    = '<p id="pagination">';
		$config['full_tag_close']   = '</p>';

        $this->pagination->initialize($config); 
		$local_tpl['pagination'] = $this->pagination->create_links();

        if (!$this->gdaemon_tasks->get_list($config['per_page'])) {
            $this->_show_message($this->gdaemon_tasks->last_error);
        }

        $local_tpl['tasks_list'] = array();
        foreach ($this->gdaemon_tasks->tasks_list as &$task) {
            $local_tpl['tasks_list'][] = array(
                'task_id'           => $task['id'],
                'task_task'         => $task['task'],
                'task_htask'        => $this->gdaemon_tasks->human_name($task['task']),
                'task_ds_id'        => $task['ds_id'],
                'task_ds_name'      => $task['ds_name'],
                'task_server_id'    => $task['server_id'],
                'task_server_name'  => $task['server_name'],
                'task_cmd'          => $task['cmd'],
                'task_status'       => $task['status'],
                'task_hstatus'      => $this->gdaemon_tasks->human_status($task['status']),
            );
        }

		$this->tpl['content'] .= $this->parser->parse('tasks_list.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
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

        $this->tpl['content'] .= $this->parser->parse('task_view.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }
}
