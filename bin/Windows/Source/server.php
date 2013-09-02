<?php
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource	
 */
 
 /**
 * Исходный файл исполняемого файла Windows
 * Компилируется при помощи bam compile (http://www.bambalam.se/bamcompile/)
 *
 * @package		Game AdminPanel
 * @category	Executable file source
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.4
 */
 
// ./server.exe start {dir} {name} {ip} {port} "hlds.exe -game {game} +ip {ip} +port {port} +map crossfire"
	
$command 		= $_SERVER['argv'][1];			// Команда
$dir 			= $_SERVER['argv'][2];			// Каталог
$name 			= $_SERVER['argv'][3];			// Имя
$ip 			= $_SERVER['argv'][4];			// IP
$port 			= $_SERVER['argv'][5];			// Порт
$start_command 	= $_SERVER['argv'][6];			// Дополнительные параметры

if(!$command){
	echo "----------------------------- \n";
	echo "Welcome to HLDS Console Launcher \n";
	echo "----------------------------- \n";
		
	echo "Program created by ET-NiK \n";
	echo "Site: http://hldm.org \n";
	echo "----------------------------- \n\n";
	echo "Options: \n";
	echo "server.exe <start|stop|restart|status> <dir> <name> <ip> <port> <name> <server_start_commands>\n\n";
	echo "Example: \n";
	echo "server.exe start dir hlds 127.0.0.1 27015 \"hlds.exe -game valve +map crossfire +sv_lan 0 +maxplayers 16\"\n";
		
	sleep(60);
}

/* Разъединение программы и аргумента из одной строки */
if(isset($start_command)) {
	$start_command = trim($start_command);
	$commands = explode(' ', $start_command);
	$programm = $commands[0];
	
	$i = 1;
	$count = count($commands);

	while($i < $count)
	{
		$arguments .= $commands[$i] . ' ';
		$i++;
	}
}

//echo $programm . "\n";
//echo $arguments . "\n";

//echo __FILE__;

if(file_exists('psexec.exe')) {
	$psexec = 'psexec.exe -s -i -w "' . $dir . '" -d ';
} elseif(file_exists('paexec.exe')) {
	$psexec = 'paexec.exe \\\\localhost -s -d -w "' . $dir . '" -d ';
} else {
	echo "psexec.exe and paexec.exe not found\n";
	$psexec = 'start /D "' . $dir . '" /I ';
}
	
//chdir($dir);
set_time_limit (3);
	
function server_status()
{
	global $program, $dir, $ip, $port,  $start_command, $psexec;
		
	//chdir($dir);
		
	system("netstat -ano | findstr " . $port .">" . $dir . '\\pid.txt');
	$file = file($dir . '\\pid.txt');

	//UDP    0.0.0.0:27015          *:*                                    1508
	//$file['0'] = 'Hello';
			
	$file['0'] = str_replace(' ', '', $file['0']);
	
	if(preg_match('/^UDP(\d*)\.(\d*)\.(\d*)\.(\d*)\:(\d*)\*\:\*(\d*)/xsi', $file['0'], $text)){
		$pid = $text['6'];
	}

	return $pid;
}
	
function server_start()
{
	global $programm, $arguments, $dir, $ip, $port,  $start_command, $psexec;
	pclose(popen($psexec . '"' . $dir . '\\' . $programm . '" ' . $arguments, "r" ));
	
	//echo "\n\n\n" . $psexec . '"' . $dir . '\\' . $programm . '" ' . $arguments, "r\n\n";

	sleep(2);
		
	return server_status();
}
	
function server_stop(){
	global $program, $dir, $ip, $port,  $start_command, $psexec;
		
	if($pid = server_status()) {
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
	default:
		"unknown command!" . "\n";
		break;
}
