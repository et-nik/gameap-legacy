<?php
/*
 * Хелпер для работы с играми
 * 
*/


/*
 * Вставляет данные сервера в массив с играми
 * 
 * @param array
 * @param array
 * @return array
*/
function game_server_insert($game_server_data, $games_array)
{
	$i = 0;
	while($i < count($games_array)){
		if($games_array[$i]['game_code'] == $game_server_data['server_game']){
			$games_array[$i]['servers_list'][] = $game_server_data;
			break;
		}
		
		$i ++;
	}
	
	return $games_array;
}

/*
 * Очищает из массива список игр, где отсутствуют серверы
 * 
 */
function clean_games_list($games_array){
	
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
