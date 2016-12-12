<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 *
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (NiK)
 * @copyright	Copyright (c) 2014-2016
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
*/
class Gdaemon_tasks extends CI_Model {

    public $last_error     = "";

    public $tasks_list     = array();
    public $single_task    = array();

    private $_filter_list    = array();

    private $_task_human_names = array(
        'gsinst'    => 'gdaemon_tasks_gsinst',
        'gsupd'     => 'gdaemon_tasks_gsupd',
        'gsstart'   => 'gdaemon_tasks_gsstart',
        'gsstop'    => 'gdaemon_tasks_gsstop',
        'gsrest'    => 'gdaemon_tasks_gsrest',
        'gsdel'     => 'gdaemon_tasks_gsdel',
        'cmdexec'   => 'gdaemon_tasks_cmdexec',
    );

    private $_task_human_statuses = array(
        'waiting'   => 'gdaemon_tasks_waiting',
        'working'   => 'gdaemon_tasks_working',
        'error'     => 'gdaemon_tasks_error',
        'success'   => 'gdaemon_tasks_success',
    );

    public function __construct()
    {
        parent::__construct();

        $this->lang->load('gdaemon_tasks');

        foreach ($this->_task_human_names as &$task_name) {
            $task_name = lang($task_name);
        }

        foreach ($this->_task_human_statuses as &$task_status) {
            $task_status = lang($task_status);
        }
    }

    // -----------------------------------------------------------------

    /**
     * Get all available task names
     *
     * @return array
     */
    public function get_names()
    {
        return $this->_task_human_names;
    }

    // -----------------------------------------------------------------

    /**
     * Get all available task statuses
     *
     * @return array
     */
    public function get_statuses()
    {
        return $this->_task_human_statuses;
    }

    // -----------------------------------------------------------------

    public function set_filter($fname, $fvalue)
    {
        if (empty($fvalue)) {
            return;
        }

        switch ($fname) {
            case 'ds_id':
                $this->_filter_list[$fname] = $fvalue;
                break;

            case 'server_id':
                $this->_filter_list[$fname] = $fvalue;
                break;

            case 'task':
                $this->_filter_list[$fname] = $fvalue;
                break;

            case 'status':
                $this->_filter_list[$fname] = $fvalue;
                break;

            case 'time_create':
            case 'time_create >':
            case 'time_create <':
                $this->_filter_list[$fname] = $fvalue;
                break;

            case 'time_stchange':
            case 'time_stchange >':
            case 'time_stchange <':
                $this->_filter_list[$fname] = $fvalue;
                break;


            default:
                // Unknown filter
                break;
        }
    }

    // -----------------------------------------------------------------

    /**
     * Get task list
     *
     * @param int   $limit 0 - unlimit
     * @param int   $offset
     *
     * @return array
     */
    public function get_list($limit = 0, $offset = 0)
    {
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        if (!empty($this->_filter_list)) {
            foreach ($this->_filter_list as $fname => &$fval) {
                if (is_array($fval)) {
                    $this->db->where_in('gdaemon_tasks.' . $fname, $fval);
                }
                else {
                    $this->db->where('gdaemon_tasks.' . $fname, $fval);
                }
            }
        }
        $this->_filter_list = array();

        $this->db->select('
            gdaemon_tasks.id,
            gdaemon_tasks.time_create,
            gdaemon_tasks.time_stchange,
            gdaemon_tasks.ds_id,
            dedicated_servers.name AS ds_name,
            gdaemon_tasks.server_id,
            servers.name AS server_name,
            gdaemon_tasks.task,
            gdaemon_tasks.cmd,
            gdaemon_tasks.status'
        );

        $this->db->from('gdaemon_tasks');

        $this->db->join('dedicated_servers', 'dedicated_servers.id = gdaemon_tasks.ds_id');
        $this->db->join('servers', 'servers.id = gdaemon_tasks.server_id');
        $this->db->order_by('gdaemon_tasks.id', 'desc');
        $query = $this->db->get();

        if ($query == false) {
            return false;
        }

        $this->tasks_list = $query->result_array();

        return true;
    }

    // -----------------------------------------------------------------

    public function get_single($task_id = 0)
    {
        if (!$task_id) {
            $this->last_error = lang('task_empty');
            return false;
        }

        $this->db->select('
            gdaemon_tasks.id,
            gdaemon_tasks.ds_id,
            dedicated_servers.name AS ds_name,
            gdaemon_tasks.server_id,
            servers.name AS server_name,
            gdaemon_tasks.task,
            gdaemon_tasks.data,
            gdaemon_tasks.cmd,
            gdaemon_tasks.output,
            gdaemon_tasks.status'
        );

        $this->db->from('gdaemon_tasks');
        $this->db->where('gdaemon_tasks.id', $task_id);

        $this->db->join('dedicated_servers', 'dedicated_servers.id = gdaemon_tasks.ds_id');
        $this->db->join('servers', 'servers.id = gdaemon_tasks.server_id');

        $query = $this->db->get();
        $this->single_task = $query->row_array();

        if (empty($this->single_task)) {
            $this->last_error = lang('task_not_found');
            return false;
        }

        return true;
    }

    // -----------------------------------------------------------------

    public function get_all_count()
    {
        if (!empty($this->_filter_list)) {
            foreach ($this->_filter_list as $fname => &$fval) {
                if (is_array($fval)) {
                    $this->db->where_in($fname, $fval);
                }
                else {
                    $this->db->where($fname, $fval);
                }
            }
        }
        $this->_filter_list = array();
		return $this->db->count_all_results('gdaemon_tasks');
    }

    // -----------------------------------------------------------------

	/**
     * Add new task
     *
     * @param array $data
     * @return bool
     *
     * @return int
     *
    */
	public function add($data)
	{
        $this->load->helper('date');
        $this->load->helper('cache');

        $this->gameap_hooks->run('pre_gtask_add', array('task_data' => &$data));

        if (empty($data['ds_id'])) {
            return 0;
        }

        if (!isset($data['server_id'])) {
            $data['server_id'] = 0;
        }

        if ($data['server_id'] != 0) {
            delete_in_cache('server_status_' . $data['server_id']);
        }

        if ($data['task'] == 'cmdexec' && empty($data['cmd'])) {
            $this->last_error = lang('gdaemon_tasks_cmd_empty');
            return 0;
        }

        $insert_data = [
            'ds_id'         => $data['ds_id'],
            'server_id'     => $data['server_id'],
            'time_create'   => !empty($data['time_create']) ? $data['time_create'] : now(),
            'time_stchange' => !empty($data['time_stchange']) ? $data['time_stchange'] : now(),
            'task'          => $data['task'],
            'data'          => !empty($data['data']) ? $data['data'] : '',
            'cmd'           => !empty($data['cmd']) ? $data['cmd'] : '',
            'status'        => !empty($data['status']) ? $data['status'] : 'waiting',
        ];

        if ((bool)$this->db->insert('gdaemon_tasks', $insert_data)) {
            $task_id = $this->db->insert_id();

            $this->panel_log->save_log(array(
                'type'          => 'gdaemon_task_add',
                'command'       => $data['task'],
                'user_name'     => isset($this->users->auth_login) ? $this->users->auth_login : '',
                'server_id'     => $data['server_id'],
                'msg'           => 'Task successfully added',
                'log_data'      => "TaskID: {$task_id}",
            ));

            $this->gameap_hooks->run('post_gtask_add', array('task_data' => &$data, 'task_id' => $task_id));
            return $task_id;
        }
        else {
            return 0;
        }
	}

	// -----------------------------------------------------------------

	/**
     * Delete task
     *
     * @param id $id
     * @return bool
     *
    */
	public function delete($id)
	{
		return (bool)$this->db->delete('gdaemon_tasks', array('id' => $id));
	}

    // -----------------------------------------------------------------

	/**
     * Update task
     *
     * @param int $id
     * @param array $data
     * @return bool
     *
    */
	public function update($id, $data)
	{
		if (is_array($id)) {
			$this->db->where($id);
		}
		else {
			$this->db->where('id', $id);
		}

		return (bool)$this->db->update('gdaemon_tasks', $data);
	}

    // -----------------------------------------------------------------

    /**
     * Task human name
     *
     * @param string $task_code
     * @return string
     */
    public function human_name($task_code)
    {
        if (array_key_exists($task_code, $this->_task_human_names)) {
            return $this->_task_human_names[$task_code];
        } else {
            return $task_code;
        }
    }

    // -----------------------------------------------------------------

    /**
     * Human status
     *
     * @param string $task_status
     * @return string
     */
    public function human_status($task_status)
    {
        if (array_key_exists($task_status, $this->_task_human_statuses)) {
            return $this->_task_human_statuses[$task_status];
        } else {
            return $task_status;
        }
    }

}
