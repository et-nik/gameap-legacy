<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

use \GameQ\GameQ;

/**
 * Query библиотека для опроса серверов
 *
 * @package		Game AdminPanel
 * @category	Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9
*/
class Query
{
	private $gameq          = null;
	
	private $request 	    = false;
	private $request_data   = array();
	
	// ---------------------------------------------------------------------
	
	public function __construct()
    {
		$this->_load();
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Загрузка GameQ библиотеки
	 * https://github.com/Austinb/GameQ
	 * 
	 */
	private function _load()
	{
		$this->gameq = new GameQ;
		$this->gameq->setOption('timeout', 5);
	}
	
	// ---------------------------------------------------------------------
	
	public function set_option($var, $value)
	{
		return $this->gameq->setOption($var, $value);
	}
	
	// ---------------------------------------------------------------------
	
	public function set_data($server_info)
	{
		if (isset($server_info['id']) && array_key_exists($server_info['id'], $this->request_data)) {
			return;
		}
		
		$this->request = false;
				
		if (isset($server_info['port'])) {
			// Костыль для удобства
			$explode = explode(':', $server_info['host']);
			$server_info['host'] = $explode[0];
			unset($explode);
			
			$server_info['host'] = "{$server_info['host']}:{$server_info['port']}";
		}
		
		$server_info['options'] = array('timeout' => 5.0);
		
		try {
			return $this->gameq->addServer($server_info);
		} catch(Exception $e) {
			
		}
	}
	
	// ---------------------------------------------------------------------
	
	private function _request()
	{
		if ($this->request) {
			return $this->request_data;
		}

        $this->request_data = $this->gameq->process();
		
		$this->request = (bool)$this->request_data;
		return $this->request_data;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Получение базовых кваров сервера -- текущая карта, количество игроков онлайн,
	 * хостнейм и пр.
	 */
	public function get_base_cvars()
	{
		$info = array();

		if ($this->_request()) {
			foreach($this->request_data as $key => $array) {
				$info[$key]['hostname'] 	= htmlspecialchars($array['gq_hostname']);
				$info[$key]['map'] 			= htmlspecialchars($array['gq_mapname']);
				$info[$key]['game'] 		= htmlspecialchars($array['gq_gametype']);
				$info[$key]['game_code'] 	= htmlspecialchars($array['gq_mod']);
				$info[$key]['players'] 		= (int)$array['gq_numplayers'];
				$info[$key]['maxplayers'] 	= (int)$array['gq_maxplayers'];
				$info[$key]['version'] 		= isset($array['version']) ? $array['version'] : null;
				$info[$key]['password'] 	= $array['gq_password'];
				$info[$key]['joinlink']		= $array['gq_joinlink'];
			}
		}
		
		return $info;
	}
	
	// ---------------------------------------------------------------------
	
	private function _remove_gq_cvars($cvars)
	{
		$return_cvars = array();

		foreach ($cvars as $key => $value) {
			if (is_array($value)) {
				continue;
			}
			
			if (strpos($key, 'gq') !== false) {
				continue;
			}
			
			$return_cvars[$key] = $value;
		}
		
		return $return_cvars;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Получение кваров сервера
	 */
	public function get_cvars()
	{
		$cvars = array();

		if ($this->_request()) {
			foreach($this->request_data as $key => $array) {
				$cvars[$key]		= $this->_remove_gq_cvars($array);
			}
		}

		return $cvars;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Извлекает данные игроков из массива
	 */
	private function _extract_players($query_data)
	{
		$players = array();
		
		if (array_key_exists('players', $query_data)) {
			/* Удобная штука */
			$query_data = $query_data['players'];
		}
		
		foreach ($query_data as $array) {
			$players[] = array('name' => htmlspecialchars($array['gq_name']), 
								'score' => (int)$array['gq_score'],
								);
		}
		
		return $players;
	}

	// ---------------------------------------------------------------------
	
	/**
	 * Получение игроков на сервере
	 */
	public function get_players()
	{
		if ($this->_request()) {
			foreach($this->request_data as $key => $array) {
				$players[$key] = $this->_extract_players($array);
			}
		}
		
		return $players;
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Получение статуса сервера (онлайн/офлайн)
	 */
	public function get_status()
	{
		if ($this->_request()) {
			foreach($this->request_data as $key => $array) {
				$status[$key] = (bool)$array['gq_online'];
			}
		}
		
		return $status;
	}
	
}
