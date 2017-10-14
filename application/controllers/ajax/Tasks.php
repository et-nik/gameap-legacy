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

    private $_avaliable_tasks = array('gsinst', 'gsstart', 'gsstop', 'gsrest');

    // -----------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('users');
        $this->lang->load('main');

        $this->load->helper('date');

        // if (!$this->input->is_ajax_request()) {
		   // show_404();
		// }

        if(!$this->users->check_user()) {
            show_404();
        }

        $this->load->model('gdaemon_tasks');
    }

    // -----------------------------------------------------------------

    private function _send_response($array)
    {
        if (empty($array)) {
            $this->_send_error('Invalid data');
        }

        $this->renderJson($array);
    }

    // -----------------------------------------------------------------

    private function _send_error($error = "")
    {
        $this->renderJson(array('status' => 0, 'error_text' => $error));
    }

    // -----------------------------------------------------------------

    public function add_task()
    {
        $this->load->model('servers');

        $server_id = (int)$this->input->post('server_id');
        $task = $this->input->post('task');

        if (!in_array($task, $this->_avaliable_tasks)) {
            $this->_send_error("Unknown task");
            return;
        }

        if (!$this->servers->get_server_data($server_id)) {
            $this->_send_error("Server not found");
            return;
        }

        // Check privileges
        if (!$this->users->auth_data['is_admin']) {

            $this->users->get_server_privileges($server_id);

            switch ($task) {
                case 'gsstart':
                    if (!$this->users->auth_servers_privileges['SERVER_START']) {
                        $this->_send_error("Access denied");
                        return;
                    }

                    break;

                case 'gsstop':
                    if (!$this->users->auth_servers_privileges['SERVER_STOP']) {
                        $this->_send_error("Access denied");
                        return;
                    }

                    break;

                case 'gsrest':
                    if (!$this->users->auth_servers_privileges['SERVER_RESTART']) {
                        $this->_send_error("Access denied");
                        return;
                    }

                    break;

                case 'gsinst':
                    if (!$this->users->auth_servers_privileges['SERVER_UPDATE']) {
                        $this->_send_error("Access denied");
                        return;
                    }

                    break;

                default:
                    $this->_send_error("Unknown task");
                    return;

                    break;

            }
        }

        // Check doubles
        $this->gdaemon_tasks->set_filter('server_id', $server_id);
        $this->gdaemon_tasks->set_filter('task', $task);
        $this->gdaemon_tasks->set_filter('status', array('waiting', 'working'));

        // if ($this->gdaemon_tasks->get_all_count() > 0) {
            // $this->_send_error("This task exists. Please wait.");
            // return;
        // }

        if ($this->gdaemon_tasks->get_list()) {
            $count_tasks = count($this->gdaemon_tasks->tasks_list);

            if ($count_tasks == 1) {
                $this->_send_response(array('status' => 1, 'message' => "Task exists", 'task_id' => $this->gdaemon_tasks->tasks_list[0]['id']));
                return;
            } else if ($count_tasks > 1) {
                $this->_send_error("This task exists. Please wait.");
                return;
            }
        }

        $task_id = $this->gdaemon_tasks->add(array(
            'ds_id'         => $this->servers->server_data['ds_id'],
            'server_id'     => $server_id,
            'time_create'   => now(),
            'time_stchange' => now(),
            'task'          => $task,
            'status'        => 'waiting',
        ));

        if ($task_id) {
            $this->_send_response(array('status' => 1, 'message' => "Task added", 'task_id' => $task_id));
        } else {
            $this->_send_error("DB error");
        }
    }

    // -----------------------------------------------------------------

    public function get_info($task_id = 0)
    {
        $task_id = (int)$task_id;

        if (!$this->gdaemon_tasks->get_single($task_id)) {
            $this->_send_error($this->gdaemon_tasks->last_error);
            return;
        }

        if (!$this->users->auth_data['is_admin']) {
            // Check user privileges
            if ($this->gdaemon_tasks->single_task['server_id'] == 0) {
                $this->_send_error("Access denied");
                return;
            }

            $this->users->get_server_privileges($this->gdaemon_tasks->single_task['server_id']);

            if (!$this->users->auth_servers_privileges['VIEW']) {
                $this->_send_error("Access denied");
                return;
            }
        }

        $task_info = array(
            'id'           => $this->gdaemon_tasks->single_task['id'],
            'ds_id'        => $this->gdaemon_tasks->single_task['ds_id'],
            'ds_name'      => $this->gdaemon_tasks->single_task['ds_name'],
            'server_id'    => $this->gdaemon_tasks->single_task['server_id'],
            'server_name'  => $this->gdaemon_tasks->single_task['server_name'],
            'task'         => $this->gdaemon_tasks->single_task['task'],
            'htask'        => $this->gdaemon_tasks->human_status($this->gdaemon_tasks->single_task['task']),
            'data'         => $this->gdaemon_tasks->single_task['data'],
            'cmd'          => $this->gdaemon_tasks->single_task['cmd'],
            'status'       => $this->gdaemon_tasks->single_task['status'],
            'hstatus'      => $this->gdaemon_tasks->human_status($this->gdaemon_tasks->single_task['status']),
        );

        if ($this->users->auth_data['is_admin']) {
            $task_info['ds_id']     = $this->gdaemon_tasks->single_task['ds_id'];
            $task_info['ds_name']   = $this->gdaemon_tasks->single_task['ds_name'];
            $task_info['output']    = $this->gdaemon_tasks->single_task['output'];
        }

        $this->_send_response(array('status' => 1, 'data' => $task_info));
    }
}
