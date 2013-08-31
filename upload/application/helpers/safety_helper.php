<?php
/*
 * Фукнции для обеспечения безопасности
 * @param array/string
*/

function safesql($string)
{
	if(is_array($string)){
		
		foreach($string as $key => $value){
			$return[$key] = mysql_real_escape_string($string[$key]);
		}
	}else{
		$return = mysql_real_escape_string($string);
	}
	
	return $return;
}


function generate_code($length = 6, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789")
{
    $code = "";

    $clen = strlen($chars) - 1;  
    while (strlen($code) < $length) {

            $code .= $chars[mt_rand(0,$clen)];  
    }

    return $code;
}
