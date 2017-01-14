<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

// --------------------------------------

/**
 * Функция для парсинга
 * 
 * 
 * 
 * @param str - регулярное выражение
 * @param str - строка из которой необходимо выбрать нужное
 * @return arr
 * 
 * 
*/
if ( ! function_exists('get_matches')) {
	function get_matches($pattern, $string)
	{

	/*
		в string можно передавать любые данные
		например, данные полученные через ркон командой status
		
		hostname:  UMI7EPATOP CEPBEP - HLDM.ORG
		version :  48/1.1.2.1/Stdio 5787 secure  (70)
		tcp/ip  :  31.31.202.96:27015
		map     :  tau_cannon at: 0 x, 0 y, 0 z
		players :  3 active (32 max)

		#      name userid uniqueid frag time ping loss adr
		# 1 "uBaH_KpuBopyKoB" 319 BOT   6  3:15:46    0    0
		# 4  "Nitro" 305 STEAM_0:0:785980079   5 02:34   52    0 176.32.12.6:27005
		# 6 "Dima^8^zombie" 289 STEAM_0:0:225349380   8 06:19    7    0 128.71.75.49:27005
		3 users

		Регулярное выражение
		
		# 4  "Nitro" 305 STEAM_0:0:785980079   5 02:34   52    0 176.32.12.6:27005
		\#(\s*)(\d*)(\s*)"(.*?)"(\s*)(\d*)(\s*)([a-zA-Z0-9\_\:]*?)(\s*)(\d*)(\s*)([0-9\:]*)(\s*)(\d*)(\s*)(\d*)(\s*)(.*?) 

	*/

		$string_expl = explode("\n", $string);

		$mreturn = array();
		$a = 0;
		$b = 0;
		
		while ($a < count($string_expl))
		{
			
			$matches = null;

			$preg_match = preg_match($pattern, $string_expl[$a], $matches);
				
			//print_r($matches);
			
			if($preg_match)
			{
				$mreturn[$b] = $matches;
				$b++;
			}
			
			$a++;
		}
		
		return $mreturn;

	}
}

// ----------------------------------------------------------------
    
/**
 * Правка значения в xml файле
 * 
 * @param string 	$file_contents xml содержимое файла
 * @param string	$variable_name переменная
 * @param string	$variable_value новое значение переменной
 * @return string	новое содержимое файла
*/ 
if ( ! function_exists('change_value_on_xml_file')) {
	function change_value_on_xml_file($file_contents, $variable_name, $variable_value)
	{
		$xml = new SimpleXMLElement($file_contents);
		$xml->$variable_name = $variable_value;
		return $xml->asXML();
	}
}

// ----------------------------------------------------------------
    
/**
 * Правка значения в файле
 * 
 * @param string 	$file_contents содержимое файла
 * @param string	$variable_name переменная
 * @param string	$variable_value новое значение переменной
 * @return string	новое содержимое файла
*/ 
if ( ! function_exists('change_value_on_file')) {
	function change_value_on_file($file_contents, $variable_name, $variable_value) 
	{
		if (function_exists('simplexml_load_string') && @simplexml_load_string($file_contents)) {
			// XML файл
			return change_value_on_xml_file($file_contents, $variable_name, $variable_value);
		}
		
		$found = false;
		$file_contents = explode("\n", $file_contents);
		
		// Паттерн строки
		$pattern = '/\s*[\"\']?(' . $variable_name . ')[\"\']?[\s*|\=]+[\"\']?(\w*)[\"\']?/six';
		
		// Паттер для определения промежутка
		$pattern_gap = '/(\w*)\s*([\s*|\=])\s*[\"\']?(\w*)[\"\']([\;]?)?/six';
		$gap = false;	// Промежуток
		$end = false;  // Окончание (бывает, что в конце строки имеется ;)
		
		foreach($file_contents as &$string) {

			if (preg_match($pattern, $string, $matches)) {
				$key = $matches[1];
				
				// Меняем значение
				if ($key == $variable_name) {
					$found = true;
					$string = str_replace($matches[2], $variable_value, $string);
				}
			}
			
			// Определение промежутка (= или пробел)
			if (!$gap) {
				if (preg_match($pattern_gap, $string, $matches)) {
					$gap = $matches[2];
					$end = $matches[4];
				}
			}
		}

		if (!$found) {
			$gap = !$gap ? ' ' : $gap;
			// Значения в файле не найдены, добавляем
			$file_contents[] = $variable_name . $gap . $variable_value . $end . "\n";
		}
		
		return implode("\n", $file_contents);
	}
}
