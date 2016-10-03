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
        'gsinst'    => 'Установка сервера',
        'gsstart'   => 'Запуск сервера',
        'gsstop'    => 'Остановка сервера',
        'gsrest'    => 'Перезапуск сервера',
    );

    private $_task_human_statuses = array(
        'waiting'   => 'Ожидает',
        'working'   => 'В процессе',
        'error'     => 'Ошибка',
        'success'   => 'Завершено',
    );

    // -----------------------------------------------------------------

    function set_filter($fname, $fvalue)
    {
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
    function get_list($limit = 0, $offset = 0)
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

    function get_single($task_id = 0)
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

    function get_all_count()
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
	function add($data)
	{
        $this->gameap_hooks->run('pre_gtask_add', array('task_data' => &$data));

        if (empty($data['ds_id'])) {
            return 0;
        }

        if (!isset($data['server_id'])) {
            $data['server_id'] = 0;
        }

        if ((bool)$this->db->insert('gdaemon_tasks', $data)) {
            $task_id = $this->db->insert_id();
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
	function delete($id)
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
	function update($id, $data)
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

    function human_name($task_code)
    {
        if (array_key_exists($task_code, $this->_task_human_names)) {
            return $this->_task_human_names[$task_code];
        } else {
            return $task_code;
        }
    }

    // -----------------------------------------------------------------

    function human_status($task_status)
    {
        if (array_key_exists($task_status, $this->_task_human_statuses)) {
            return $this->_task_human_statuses[$task_status];
        } else {
            return $task_status;
        }
    }
    
}
