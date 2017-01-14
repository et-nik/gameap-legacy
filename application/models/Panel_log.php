<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/
class Panel_log extends CI_Model {
	
	private $_filter = array('type' => null, 'command' => null, 'user_name' => null, 'contents' => null, 'server_id' => null);
	
	
	//-----------------------------------------------------------
	
	/**
     * Задать фильтр списка логов
    */
	public function set_filter($filter)
	{
		if (is_array($filter)) {
			$this->_filter['type'] 		= (isset($filter['type']) && $filter['type']) ? $filter['type'] : null;
			$this->_filter['command'] 	= (isset($filter['command']) && $filter['command']) ? $filter['command'] : null;
			$this->_filter['user_name'] = (isset($filter['user_name']) && $filter['user_name']) ? $filter['user_name'] : null;
			$this->_filter['contents'] 	= (isset($filter['contents']) && $filter['contents']) ? $filter['contents'] : null;
		}
	}

	//-----------------------------------------------------------
	
	/**
     * Запись лога
     * 
     * $data['type'] 		- тип логов (по контролеру)
     * $data['user_name'] 	- имя пользователя
     * $data['server_id']	- id сервера
     * $data['msg']			- инфо сообщение
     * $data['log_data']	- подробные данные
     * 
     * @param array
     * @return bool
     * 
     *
    */
	public function save_log($data = false)
    {
		if(!isset($data['type'])){
			return false;
		}
		
		isset($data['log_data']) 	OR $data['log_data'] = '';
		isset($data['msg']) 		OR $data['msg'] = '';
		isset($data['server_id']) 	OR $data['server_id'] = 0;
		
		$data['log_data'] = (is_array($data['log_data'])) ? json_encode($data['log_data']) : $data['log_data'];
		
		$data['date'] = time();
		
		if(isset($_SERVER['REMOTE_ADDR'])) {
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
		} else {
			$data['ip'] = 'localhost';
		}
		
		if ($this->db->insert('logs', $data)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	//-----------------------------------------------------------
	
	/**
     * Получение содержимого логов
     * 
     * @param array
     * @return array
    */
	public function get_log($where = array(), $limit = 10, $offset = false)
	{
		$this->db->order_by('date', 'desc'); 
		
		!$this->_filter['type'] 		OR $this->db->where('type', $this->_filter['type']);
		!$this->_filter['command'] 		OR $this->db->where('command', $this->_filter['command']);
		!$this->_filter['user_name'] 	OR $this->db->like('user_name', $this->_filter['user_name']);
		!$this->_filter['contents'] 	OR $this->db->like('log_data', $this->_filter['contents']);
	
		if(is_array($where)){
			$query = $this->db->get_where('logs', $where, $limit, $offset);
		}else{
			$query = $this->db->get('logs', $limit, $offset);
		}

		if($query->num_rows() > 0){
			
			$log_list = $query->result_array();
			return $log_list;
			
		}else{
			return NULL;
		}
	}
	
	//-----------------------------------------------------------
	
	/**
     * Получает количество логов в базе
    */
	public function get_count_all_log() 
	{
		!$this->_filter['type'] 		OR $this->db->where('type', $this->_filter['type']);
		!$this->_filter['command'] 		OR $this->db->where('command', $this->_filter['command']);
		!$this->_filter['user_name'] 	OR $this->db->like('user_name', $this->_filter['user_name']);
		!$this->_filter['contents'] 	OR $this->db->like('log_data', $this->_filter['contents']);
		
		return $this->db->count_all_results('logs');
	}
	
}
