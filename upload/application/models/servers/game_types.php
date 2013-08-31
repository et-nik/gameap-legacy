<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Game_types extends CI_Model {
	
	var $game_types_list = array();	// Список типов игр
	
	//-----------------------------------------------------------	
	/*
     * Добавление новой игровой модификации
     * 
     *
    */
    function add_game_type($data)
    {
		if($this->db->insert('game_types', $data)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------	
	/*
     * Удаление модификации игры
     * 
     *
    */
    function delete_game_type($id)
    {
		if($this->db->delete('game_types', array('id' => $id))){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	
	//-----------------------------------------------------------	
	/*
     * Редактирование модификации
     * 
     *
    */
	function edit_game_type($id, $data)
    {
		$this->db->where('id', $id);
		
		if($this->db->update('game_types', $data)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	//-----------------------------------------------------------
	/**
     * Получение списка игровых модификаций
     * 
     * @param array - условия для выборки
     * @param int
     * 
     * @return array
     *
    */
    function get_gametypes_list($where = FALSE, $limit = 10000)
    {
		
		/*
		 * В массиве $where храняться данные для выборки.
		 * Например:
		 * 		$where = array('id' => 1);
		 * в этом случае будет выбран сервер id которого = 1
		 * 
		*/
		
		if(is_array($where)) {
			$query = $this->db->get_where('game_types', $where, $limit);
		} else {
			$query = $this->db->get('game_types', $limit);
		}

		if($query->num_rows > 0) {
			
			$this->game_types_list = $query->result_array();
			return $this->game_types_list;
			
		} else {
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
	function tpl_data_game_types($where = FALSE, $limit = FALSE, $script_param = FALSE)
    {
		$num = -1;
		
		if(!$this->game_types_list){
			$this->get_gametypes_list($where, $limit);
		}
		
		if($this->game_types_list){
		
			foreach ($this->game_types_list as $game_types){
				$num++;
				
				$tpl_data[$num]['gt_id'] 	= $game_types['id'];
				//~ $tpl_data[$num]['gt_code'] 	= $game_types['game_code'];
				$tpl_data[$num]['gt_name'] 	= $game_types['name'];
				$tpl_data[$num]['gt_size'] 	= $game_types['disk_size'];
				
				if($script_param = TRUE) {
					
					$tpl_data[$num]['execfile_linux'] 	= $game_types['execfile_linux'];
					$tpl_data[$num]['execfile_windows'] = $game_types['execfile_windows'];
					
					// Заменяем двойные кавычки на html символы
					$tpl_data[$num]['script_start'] 	= str_replace('"', '&quot;', $game_types['script_start'] );
					$tpl_data[$num]['script_stop'] 		= str_replace('"', '&quot;', $game_types['script_stop'] );
					$tpl_data[$num]['script_restart'] 	= str_replace('"', '&quot;', $game_types['script_restart'] );
					$tpl_data[$num]['script_status'] 	= str_replace('"', '&quot;', $game_types['script_status'] );
					$tpl_data[$num]['script_update'] 	= str_replace('"', '&quot;', $game_types['script_update'] );
					$tpl_data[$num]['script_get_console'] 	= str_replace('"', '&quot;', $game_types['script_get_console'] );
				}
				
			}
			
			return $tpl_data;
			
		}else{
			return FALSE;
		}
	}


}
