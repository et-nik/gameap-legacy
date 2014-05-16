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
    function get_gametypes_list($where = FALSE, $limit = 99999)
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
			$this->game_types_list = array();
			return array();
		}
	}
	
	//-----------------------------------------------------------
	/**
     * Получение данных игр для шаблона
     * (вырезаны ненужные данные - пароли и пр.)
     * 
     *
    */
	function tpl_data_game_types($where = FALSE, $limit = 99999, $script_param = FALSE)
    {
		$tpl_data = array();
		
		if(!$this->game_types_list OR $where){
			$this->get_gametypes_list($where, $limit);
		}
		
		$num = 0;
		foreach ($this->game_types_list as $game_types) {
			$tpl_data[$num]['gt_id'] 	= $game_types['id'];
			//~ $tpl_data[$num]['gt_code'] 	= $game_types['game_code'];
			$tpl_data[$num]['gt_name'] 	= $game_types['name'];
			$tpl_data[$num]['gt_size'] 	= $game_types['disk_size'];
			
			if($script_param = TRUE) {
				
				$tpl_data[$num]['execfile_linux'] 		= $game_types['execfile_linux'];
				$tpl_data[$num]['execfile_windows'] 	= $game_types['execfile_windows'];
				
				$tpl_data[$num]['local_repository']		= $game_types['local_repository'];
				$tpl_data[$num]['remote_repository']	= $game_types['remote_repository'];
				
				// Заменяем двойные кавычки на html символы
				$tpl_data[$num]['script_start'] 		= str_replace('"', '&quot;', $game_types['script_start'] );
				$tpl_data[$num]['script_stop'] 			= str_replace('"', '&quot;', $game_types['script_stop'] );
				$tpl_data[$num]['script_restart'] 		= str_replace('"', '&quot;', $game_types['script_restart'] );
				$tpl_data[$num]['script_status'] 		= str_replace('"', '&quot;', $game_types['script_status'] );
				$tpl_data[$num]['script_update'] 		= str_replace('"', '&quot;', $game_types['script_update'] );
				$tpl_data[$num]['script_get_console'] 	= str_replace('"', '&quot;', $game_types['script_get_console'] );
				$tpl_data[$num]['script_send_command'] 	= str_replace('"', '&quot;', $game_types['script_send_command'] );
				
				$tpl_data[$num]['kick_cmd'] 		= str_replace('"', '&quot;', $game_types['kick_cmd'] );
				$tpl_data[$num]['ban_cmd'] 			= str_replace('"', '&quot;', $game_types['ban_cmd'] );
				$tpl_data[$num]['chname_cmd'] 		= str_replace('"', '&quot;', $game_types['chname_cmd'] );
				$tpl_data[$num]['srestart_cmd'] 	= str_replace('"', '&quot;', $game_types['srestart_cmd'] );
				$tpl_data[$num]['chmap_cmd'] 		= str_replace('"', '&quot;', $game_types['chmap_cmd'] );
				$tpl_data[$num]['sendmsg_cmd'] 		= str_replace('"', '&quot;', $game_types['sendmsg_cmd'] );
				$tpl_data[$num]['passwd_cmd'] 		= str_replace('"', '&quot;', $game_types['passwd_cmd'] );
			}
			
			$num++;
			
		}
		
		return $tpl_data;

	}


}
