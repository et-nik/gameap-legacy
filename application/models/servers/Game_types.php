<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Game_types extends CI_Model {
	
	var $game_types_list = array();	// Список типов игр
	
	private $_fields = array();
	
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
	
	// -----------------------------------------------------------------
	
	public function select_fields($fields)
	{
		$this->_fields = $fields;
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение списка имён игровых модификаций
	 * 
	 * @return array
	 */
	public function get_names($variant = 1)
	{
		if (empty($this->game_types_list)) {
			return array();
		}
		
		$names = array();
		
		switch ($variant) {
			default:
			case 1:
				foreach ($this->game_types_list as &$game_type) {
					$names[$game_type['id']] = $game_type['name'];
				}
				break;
			
			case 2:
				foreach ($this->game_types_list as &$game_type) {
					$names[] = array('id' => $game_type['id'], 'name' => $game_type['name']);
				}
				break;
				
			case 3:
				foreach ($this->game_types_list as &$game_type) {
					$names[] = array('game_type_id' => $game_type['id'], 'game_type_name' => $game_type['name']);
				}
				break;
		}
		
		return $names;
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
		
		// Выбор полей
		!$this->_fields OR $this->db->select($this->_fields);
		
		if(is_array($where)) {
			$query = $this->db->get_where('game_types', $where, $limit);
		} else {
			$query = $this->db->get('game_types', $limit);
		}

		if($query->num_rows() > 0) {
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
            
			if($script_param = TRUE) {
				
				$tpl_data[$num]['local_repository']		= $game_types['local_repository'];
				$tpl_data[$num]['remote_repository']	= $game_types['remote_repository'];
				
				// Заменяем двойные кавычки на html символы
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
	
	// ----------------------------------------------------------------
    
    /**
     * Проверяет, существует ли мод с данным id
     * Параметру id может быть передан id мода, либо массив where
     * 
     * 
     * @param int|array
     * @return bool
    */  
    function live($id = false) 
    {
		if (false == $id) {
			return false;
		}

		if (is_array($id)) {
			$this->db->where($id);
		} else {
			$this->db->where(array('id' => $id));
		}
		
		return (bool)($this->db->count_all_results('game_types') > 0);
    }

}
