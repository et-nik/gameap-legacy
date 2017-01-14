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

/**
 * Помошник для работы с играми
 *
 * @package		Game AdminPanel
 * @category	Helpers
 * @author		Nikita Kuznetsov (ET-NiK)
*/

// ---------------------------------------------------------------------

/**
 * Вставляет данные сервера в массив с играми
 *
 * @param array
 * @param array
 * @return array
*/
if ( ! function_exists('game_server_insert'))
{
	function game_server_insert($game_server_data, $games_array)
	{
		$CI =& get_instance();
		$CI->load->model('servers/games');

		$i = 0;
		while($i < count($games_array)){

			if(isset($games_array[$i]['game_code']) && $games_array[$i]['game_code'] == $game_server_data['server_game']){
				$games_array[$i]['servers_list'][] = $game_server_data;
				$i++;
				break;
			} elseif(isset($games_array[$i]['server_game_code']) && $games_array[$i]['server_game_code'] == $game_server_data['server_game']){
				$games_array[$i]['servers_list'][] = $game_server_data;
				$i++;
				break;
			}

			$i ++;
		}

		return $games_array;
	}
}

// ---------------------------------------------------------------------

/**
 * Очищает из массива список игр, где отсутствуют серверы
 */
if ( ! function_exists('clean_games_list'))
{
	function clean_games_list($games_array)
	{

		$i = 0;

		$count_array = count($games_array);

		while($i < $count_array){

			//echo $i . '<br />';

			if(empty($games_array[$i]['servers_list'])){
				unset($games_array[$i]);
			}

			$i ++;
		}

		return $games_array;

	}
}

// ---------------------------------------------------------------------

/**
 * Обычный список серверов сортирует в список игр, каждая игра
 * содержит лишь свои серверы
 */
if ( ! function_exists('servers_list_to_games_list'))
{
	function servers_list_to_games_list($servers_list = array())
	{
		$CI =& get_instance();
		$CI->load->model('servers/games');

		$games_list = $CI->games->tpl_data_games();

		foreach ($servers_list as $server) {
			$gs_data =  array('server_id' => $server['id'],
								'server_name' => $server['name'],
								'server_game' => $server['game'],
								'server_ip' => $server['server_ip'],
								'server_port' => $server['server_port'],
			);

			$games_list = game_server_insert($gs_data, $games_list);
		}

		return clean_games_list($games_list);
	}
}

// ---------------------------------------------------------------------

/**
 * Преобразует SteamID64 в SteamID
 * Основа взята с http://facepunch.com/showthread.php?t=1238157
 */
if ( ! function_exists('steamid64_to_steamid'))
{
	function steamid64_to_steamid($steamid)
	{
		$steamY = $steamid - 76561197960265728;
		$steamX = (int)($steamY%2 == 1);

		$steamY = (($steamY - $steamX) / 2);
		$steamID = "STEAM_0:" . $steamX . ":" . $steamY;
		return $steamID;
	}
}

// ---------------------------------------------------------------------

/**
 * Преобразует SteamID в SteamID64
 */
if ( ! function_exists('steamid_to_steamid64'))
{
	function steamid_to_steamid64($steamid)
	{
		list( , $m1, $m2) = explode(':', $steamid, 3);
		list($steam_cid, ) = explode('.', bcadd((((int) $m2 * 2) + $m1), '76561197960265728'), 2);
		return $steam_cid;
	}
}
