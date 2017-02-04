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
		return (bool)$this->db->insert('games', $data);
	}

	//-----------------------------------------------------------
	/*
     * Удаление игры
     *
     *
    */
    function delete_game($code)
    {
		return (bool)$this->db->delete('games', array('code' => $code));
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

		return (bool)$this->db->update('games', $data);
	}

	// -----------------------------------------------------------

	function select_fields($fields)
	{
		if (is_array($fields)) {
			$fields = implode(',', $fields);
		}

		return $this->db->select($fields);
	}

	// -----------------------------------------------------------

	/**
	 * Массив с кодами игр, которые нужно получить
	 */
	function select_games($games_codes)
	{
		if (empty($games_codes)) {
			return false;
		}

		return $this->db->where_in('code', $games_codes);
	}

	// -----------------------------------------------------------------

	/**
	 * Получение списка активных игр (серверы которых имеются)
	 *
	 * @param array $games_array массив со списком игр
	 */
	function get_active_games_list($games_array = array())
	{
		$query = $this->db->query("SELECT * FROM `{$this->db->dbprefix('games')}`
			WHERE `code` IN (SELECT DISTINCT(`game`) FROM `{$this->db->dbprefix('servers')}`)
		");

		$this->games_list = $query->result_array();
		return $this->games_list;
	}

	//-----------------------------------------------------------

	/**
     * Получение списка игр
    */
    function get_games_list($where = FALSE, $limit = 99999, $offset = 0)
    {
		$this->db->order_by('name', 'asc');

		if (is_array($where)) {
			$this->db->where($where);
		} else {
			$this->db->where('code', $where);
		}

		$this->db->limit($limit, $offset);
		$query = $this->db->get('games');

		if($query->num_rows() > 0){
			$this->games_list = $query->result_array();

			// Заполнение массива с именами игр
			foreach($this->games_list as &$game) {
				$this->name_games[ $game['code'] ] = $game['name'];
			}

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
     * @param array $where
     * @param int $limit
     * @return array
    */
	function tpl_data_games($where = FALSE, $limit = 99999)
    {
		$num = -1;

		if (!$this->games_list) {
			$this->get_games_list($where, $limit);
		}

		if ($this->games_list) {

			foreach ($this->games_list as $games) {
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

		} else {
			return [];
		}
	}

	//-----------------------------------------------------------

	/**
     * Получение названия игры по ее коду
     *
    */
	function game_name_by_code($code)
	{
		$get_games = false;

		if(!$this->games_list){
			$get_games = true;
			$this->get_games_list();
		}

		if (isset($this->name_games[$code])) {
			return $this->name_games[$code];
		}

		$count_games = count($this->games_list);
		$i = 0;

		foreach ($this->games_list as &$game) {
			if ($code == $game['code']) {
				return $game['name'];
			}
		}

		if (!$get_games) {
			return false;
		}

		$this->get_games_list();

		foreach ($this->games_list as &$game) {
			if ($code == $game['code']) {
				return $game['name'];
			}
		}

		return false;

	}

	// ----------------------------------------------------------------

    /**
     * Проверяет, существует ли игра с данным code
     * Параметру code может быть передан code игры, либо массив where
     *
     * @param string|array
     * @return bool
    */
    function live($code = false)
    {
		if (false == $code) {
			return false;
		}

		if (is_array($code)) {
			$this->db->where($code);
		} else {
			$this->db->where(array('code' => $code));
		}

		return (bool)($this->db->count_all_results('games') > 0);
    }

}
