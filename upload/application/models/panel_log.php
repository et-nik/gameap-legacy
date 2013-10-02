<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource
*/
class Panel_log extends CI_Model {

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
	function save_log($data = FALSE)
    {
		if(!isset($data['type'])){
			return FALSE;
		}
		
		$data['date'] = time();
		
		if(isset($_SERVER['REMOTE_ADDR'])) {
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
		} else {
			$data['ip'] = 'localhost';
		}
		
		if($this->db->insert('logs', $data)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	
	//-----------------------------------------------------------
	
	/**
     * Получение содержимое логов
     * 
     * @param array
     * @return array
     * 
     *
    */
	function get_log($where = array(), $limit = 10, $offset = FALSE)
	{
		
		$this->db->order_by('date', 'desc'); 
		
		if(is_array($where)){
			$query = $this->db->get_where('logs', $where, $limit, $offset);
		}else{
			$query = $this->db->get('logs', $limit, $offset);
		}

		if($query->num_rows > 0){
			
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
	function get_count_all_log() {
		return $this->db->count_all('logs');
	}
	
}
