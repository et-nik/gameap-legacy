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
class Games extends CI_Model {
	
	var $games_list = array();			// Список игр
	var $name_games = array();
	
	//-----------------------------------------------------------	
	/*
     * Добавление новой игры
     * 
     *
    */
    function add_game($data)
    {
		if($this->db->insert('games', $data)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------	
	/*
     * Удаление игры
     * 
     *
    */
    function delete_game($code)
    {
		if($this->db->delete('games', array('code' => $code))){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------	
	/*
     * Редактирование игры
     * 
     *
    */
	function edit_game($code, $data)
    {
		$this->db->where('code', $code);
		
		if($this->db->update('games', $data)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------
	/**
     * Получение списка игр
     * 
     *
    */
    function get_games_list($where = FALSE, $limit = 10000, $offset = 0)
    {
		$this->db->order_by('name', 'asc'); 
		
		if (is_array($where)) {
			$query = $this->db->where($where);
		}
		
		$this->db->limit($limit, $offset);
		$query = $this->db->get('games');

		if($query->num_rows > 0){
			
			$this->games_list = $query->result_array();
			return $this->games_list;
			
		}else{
			return NULL;
		}
	}
	
	//-----------------------------------------------------------
	/**
     * Получение данных игр для шаблона
     * (вырезаны ненужные данные - пароли и пр.)
     * 
     *
    */
	function tpl_data_games($where = FALSE, $limit = FALSE)
    {
		$num = -1;
		
		if(!$this->games_list){
			$this->get_games_list($where, $limit);
		}
		
		if($this->games_list){
		
			foreach ($this->games_list as $games){
				$num++;
				
				$tpl_data[$num]['game_name'] 			= $games['name'];
				$tpl_data[$num]['game_code'] 			= $games['code'];
				$tpl_data[$num]['game_start_code'] 		= $games['start_code'];
				$tpl_data[$num]['game_engine'] 			= $games['engine'];
				$tpl_data[$num]['game_engine_version'] 	= $games['engine_version'];
				
				$tpl_data[$num]['app_id']				= (isset($games['app_id'])) ? $games['app_id'] : '';
				$tpl_data[$num]['app_set_config']		= (isset($games['app_set_config'])) ? $games['app_set_config'] : '';
				
				$tpl_data[$num]['local_repository']		= (isset($games['local_repository'])) ? $games['local_repository'] : '';
				$tpl_data[$num]['remote_repository']	= (isset($games['remote_repository'])) ? $games['remote_repository'] : '';
				
			}
			
			return $tpl_data;
			
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------
	/**
     * Получение названия игры по ее коду
     *
    */
	function game_name_by_code($code){
		
		if(!$this->games_list){
			$this->get_games_list();
		}
		
		if(!empty($this->name_games[$code])){
			return $this->name_games[$code];
		}
		
		$count_games = count($this->games_list);
		$i = 0;
		
		while($i < $count_games){
			
			$this->name_games[$this->games_list[$i]['code']] = $this->games_list[$i]['name'];
			
			if($code == $this->games_list[$i]['code']){
				$return = $this->games_list[$i]['name'];
			}
			
			$i++;
		}
		
		if(isset($return)){
			return $return;
		}
		
		return FALSE;
	}
	
}
