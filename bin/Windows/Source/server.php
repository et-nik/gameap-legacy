<?php

/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package    	Game AdminPanel
 * @author    	Nikita Kuznetsov (ET-NiK)
 * @copyright  	Copyright (c) 2013-2014, Nikita Kuznetsov (http://hldm.org)
 * @license    	http://www.gameap.ru/license.html
 * @link    	http://www.gameap.ru
 * @filesource  
 */
 
 /**
 * Исходный файл исполняемого файла Windows
 * Компилируется при помощи bam compile (http://www.bambalam.se/bamcompile/)
 *
 * @package    Game AdminPanel
 * @category  Executable file source
 * @author    Nikita Kuznetsov (ET-NiK)
 * @sinse    0.4
 */

// ./server.exe start {dir} {name} {ip} {port} "hlds.exe -game {game} +ip {ip} +port {port} +map crossfire"
	
$command 		= $_SERVER['argv'][1];			// Команда
$dir 			= $_SERVER['argv'][2];			// Каталог
$name 			= $_SERVER['argv'][3];			// Имя
$ip 			= $_SERVER['argv'][4];			// IP
$port 			= $_SERVER['argv'][5];			// Порт
$start_command 	= $_SERVER['argv'][6];			// Дополнительные параметры

// Каким бы ни было значение $dir, оно будет всегда с виндовыми слешами без дублирования
// C:/windows 					-> C:\windows
// C:/windows/ 					-> C:\windows
// C:\servers\\my_server//001/ 	-> C:\servers\my_server\001
$dir = str_replace('/', '\\', $dir . '\\');
$dir = preg_replace('/\\\\{2,}/si', '\\', $dir);
$dir = substr($dir, 0, strlen($dir)-1);

if(!$command){
	echo "----------------------------- \n";
	echo "Welcome to HLDS Console Launcher \n";
	echo "----------------------------- \n";
		
	echo "Program created by ET-NiK \n";
	echo "Site: http://hldm.org \n";
	echo "----------------------------- \n\n";
	echo "Options: \n";
	echo "server.exe <start|stop|restart|status|get_console> <dir> <ip> <port> <name> <server_start_commands>\n\n";
	echo "Example: \n";
	echo "server.exe start dir hlds 127.0.0.1 27015 \"hlds.exe -game valve +map crossfire +sv_lan 0 +maxplayers 16\"\n";
		
	sleep(60);
}

/* Разъединение программы и аргумента из одной строки */
if(isset($start_command)) {
	$start_command = trim($start_command);
	$commands = explode(' ', $start_command);
	$program = $commands[0];
	
	$i = 1;
	$count = count($commands);

	while($i < $count)
	{
		$arguments .= $commands[$i] . ' ';
		$i++;
	}
}

// Узнаем CPU с наименьшей загрузкой
function get_lowload_cpu()
{
	exec("wmic CPU get LoadPercentage",$res);

	// Избавляемся от LoadPercentage столбца
	unset($res[0]);
	// Сносим отступ в конце вывода
	unset($res[count($res)]);
	
	$cpu_load = array();
	
	$i = 1;
	foreach ($res as $load) {
		$cpu_load[$i] = $load;
		$i ++;
	}
	
	if (empty($cpu_load)) {
		return 1;
	}
	
	asort($cpu_load);
	reset($cpu_load);

	return key($cpu_load);
}

if(file_exists('psexec.exe')) {
	$psexec = 'psexec.exe -s -i -d -w "' . $dir . '" -a ' . get_lowload_cpu() . ' ';
} elseif(file_exists('paexec.exe')) {
	$psexec = 'paexec.exe \\\\localhost -s -d -w "' . $dir . '" -a ' . get_lowload_cpu() . ' ';
} else {
	echo "psexec.exe and paexec.exe not found\n";
	$psexec = 'start /D "' . $dir . '" /I /affinity ' . get_lowload_cpu() . ' ';
}
	
//chdir($dir);
set_time_limit (3);

// ---------------------------------------------------------------------

/**
 * Проверка статуса сервера
 */
function server_status()
{
	global $program, $dir, $ip, $port, $start_command, $psexec;
	
	$pid = NULL;
	
	/* wmic process where description="rust_server.exe" get executablepath, processid
	 * 
	 * ExecutablePath                       ProcessId
	 * C:\servers\rust_01\rust_server.exe   2448
	 * C:\servers\rust_02\rust_server.exe  	2240
	*/
	
	exec('wmic process where description="' . $program . '" get executablepath, processid', $ex_output);
	unset($ex_output[0]);

	foreach ($ex_output as $str) {
		$str = preg_replace('/\s{2,}/i', ' ', $str);
		$ex 		= explode(' ', $str);
		$ex[0] 	= substr($ex[0], 0, strlen($ex[0])-strlen($program)-1);
		
		//~ echo "\n" . $dir . ' ' . $ex[0] . "\n";
		
		if (isset($ex[1]) && $ex[0] == $dir) {
			$pid = $ex[1];
			break;
		}
	}
	
	if ($pid) {
		system("echo " . $pid .">" . $dir . '/pid.txt');
	} else {
		system("echo NOT FOUND>" . $dir . '/pid.txt');
	}
	
	return $pid;
}

// ---------------------------------------------------------------------

/**
 * Запуск сервера
 */
function server_start()
{
	global $program, $arguments, $dir, $ip, $port,  $start_command, $psexec;
	pclose(popen($psexec . '"' . $dir . '\\' . $program . '" ' . $arguments, "r" ));
	
	//echo "\n\n\n" . $psexec . '"' . $dir . '\\' . $program . '" ' . $arguments, "r\n\n";

	sleep(2);
		
	return server_status();
}

// ---------------------------------------------------------------------
	
/**
 * Остановка сервера
 */
function server_stop() {
	global $program, $dir, $ip, $port,  $start_command, $psexec;
		
	if($pid = server_status()) {
		print_r($psexec . 'taskkill /f /pid ' . $pid);
		echo "\n";
		system($psexec . 'taskkill /f /pid ' . $pid);
	} else {
		return FALSE;
	}

	sleep(1);
	$pid = server_status();
		
	if(!$pid) {
		return TRUE;
	} else {
		return FALSE;
	}
}

switch($command) {
	case 'start':
		if(!server_status()) {
			
			if(server_start()) {
				echo 'Server started' . "\n";
			} else {
				echo 'Server not started' . "\n";
			}
				
		} else {
			echo 'Server is already running' . "\n";
		}

		break;
		
	case 'stop':
		
		if(server_stop()){
				echo 'Server stopped' . "\n";
			}else{
				echo 'Server not stopped' . "\n";
			}
				
		break;
		
	case 'restart':
		
		if(server_status()){
			server_stop();
			sleep(3);
		}
			
		if(server_start()) {
			echo 'Server restarted' . "\n";
		} else {
			echo 'Server not restarted' . "\n";
		}
			
		break;
		
	case 'status':
		$pid = server_status();
		
		if($pid) {
			echo 'Server is UP' . "\n";
		} else {
			echo 'Server is Down' . "\n";
		}
			
		break;
		
	case 'get_console':
	
		$console_file = '';
		
		if (file_exists($dir . '/qconsole.log')) {
			$console_file = 'qconsole.log';
		} else {
			$tokens = explode(' ', $start_command);
			
			$count = count($tokens);
			$i = 0;
			
			while($i < $count) {
				if ($tokens[$i] == '-game') {
					if (file_exists($dir . '/' . $tokens[ $i+1 ] . '\\' . 'console.log')) {
						$console_file = $tokens[ $i+1 ] . '\\' . 'console.log';
					}
					
					break;
				}
				
				$i ++;
				// END while($i < $count)
			}
		}
		
		
		if (!$console_file) {
			echo 'Console file not found. Add -condebug in server start parameters';
			exit;
		}
		
		$console_content = file_get_contents($dir . '/' . $console_file);
		$console_content = explode("\n", $console_content);
		
		/* Файл может быть большим, поэтому оставляем только последние 100 строк */
		
		$i = 0; // Номер строки (первая строка соответствует 0)
		foreach ($console_content as $string) {
			if ($string == '') {
				$i ++;
				continue;
			}
			
			if ((count($console_content) - $i) > 100) {
				$i ++;
				continue;
			}
			
			$final_console_content[] = $string;
			
			$i ++;
		}
		
		echo implode("\n", $final_console_content);
		
		break;
		
	default:
		"unknown command!" . "\n";
		break;
}
