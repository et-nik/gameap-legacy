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
 * Помошник для работы с выделенными серверами.
 * В функции помошника входит отправка комманд, чтение и загрузка файлов.
 *
 * @package		Game AdminPanel
 * @category	Helpers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9-dev3
*/

// ---------------------------------------------------------------------

/**
 * Замена шоткодов в команде
 * 
 * @param string
 * @param array
 * 
 * @return array
*/
if ( ! function_exists('replace_shotcodes'))
{
	function replace_shotcodes($command, &$server_data)
    {
		$CI =& get_instance();
		$CI->load->helper('string');

		/* В случае использования Windows значение может быть пустым
		 * и параметры собьются */
		if(empty($server_data['screen_name'])) {
			$server_data['screen_name'] = 'null';
		}
		
		isset($server_data['start_command']) 	OR $server_data['start_command'] 	= '';
		isset($server_data['id']) 				OR $server_data['id'] 				= '';
		isset($server_data['script_path']) 		OR $server_data['script_path'] 		= '';
		isset($server_data['dir']) 				OR $server_data['dir'] 				= '';
		isset($server_data['script_path']) 		OR $server_data['script_path']	 	= '';
		isset($server_data['screen_name']) 		OR $server_data['screen_name'] 		= '';
		isset($server_data['server_ip']) 		OR $server_data['server_ip'] 		= '';
		isset($server_data['server_port']) 		OR $server_data['server_port'] 		= '';
		isset($server_data['start_code']) 		OR $server_data['start_code'] 		= '';
		isset($server_data['su_user']) 			OR $server_data['su_user'] 			= '';
		
		isset($server_data['query_port']) 		OR $server_data['query_port'] 		= '';
		isset($server_data['rcon_port']) 		OR $server_data['rcon_port'] 		= '';
		
		isset($server_data['cpu_limit']) 		OR $server_data['cpu_limit'] 		= '';
		isset($server_data['ram_limit']) 		OR $server_data['ram_limit'] 		= '';
		isset($server_data['net_limit']) 		OR $server_data['net_limit'] 		= '';
		
		// Команда запуска игрового сервера (напр. "hlds_run -game valve +ip 127.0.0.1 +port 27015 +map crossfire")
		$command = str_replace('{command}', 	$server_data['start_command'] , $command);
		// ID сервера
		$command = str_replace('{id}', 			strip_quotes($server_data['id']) 			, $command);
		$command = str_replace('{script_path}', strip_quotes($server_data['work_path']) 	, $command);
		$command = str_replace('{work_path}',   strip_quotes($server_data['work_path']) 	, $command);
		// Директория с игрой
		$command = str_replace('{game_dir}', 	strip_quotes($server_data['dir'])  			, $command);
		// Корневая директория (где скрипт запуска)
		$command = str_replace('{dir}', 		strip_quotes($server_data['work_path'] . '/' . $server_data['dir'])  , $command);
		// Имя скрина
		$command = str_replace('{name}', 		strip_quotes($server_data['screen_name']) 	, $command);
		// IP сервера для коннекта (может не совпадать с ip дедика)
		$command = str_replace('{ip}', 			strip_quotes($server_data['server_ip']) 	, $command);
		// Порт сервера для коннекта
		$command = str_replace('{port}', 		strip_quotes($server_data['server_port']) 	, $command);
		
		$command = str_replace('{query_port}', 	strip_quotes($server_data['query_port']) 	, $command);
		$command = str_replace('{rcon_port}', 	strip_quotes($server_data['rcon_port']) 	, $command);
		
		// Игра
		$command = str_replace('{game}', 		strip_quotes($server_data['start_code']) 	, $command);
		// Пользователь
		$command = str_replace('{user}', 		strip_quotes($server_data['su_user']) 		, $command);
		
		$command = str_replace('{cpu_limit}', 	strip_quotes($server_data['cpu_limit']) 	, $command);
		$command = str_replace('{ram_limit}', 	strip_quotes($server_data['ram_limit']) 	, $command);
		$command = str_replace('{net_limit}', 	strip_quotes($server_data['net_limit']) 	, $command);

		/*-------------------*/
		/* Замена по алиасам */
		/*-------------------*/
		
		/* Допустимые алиасы */
		if (isset($server_data['aliases_list']) && isset($server_data['aliases'])) {
			$allowable_aliases 	= json_decode($server_data['aliases_list'], true);

			/* Прогон по алиасам */
			if($allowable_aliases && !empty($allowable_aliases)){
				foreach ($allowable_aliases as $alias) {
					if(isset($server_data['aliases_values'][$alias['alias']])) {
						$command = str_replace('{' . $alias['alias'] . '}', $server_data['aliases_values'][$alias['alias']] , $command);	
					}
				}
			}
		}
		
		return $command;
	}
}

// ---------------------------------------------------------------------

/**
 * Соединяется с выделенным сервером, производит авторизацию
 * и отправляет заданную команду
 * 
 * @param string or array
 * @param array
 * @param bool
 * 
 * @return string
*/
if ( ! function_exists('send_command'))
{
	function send_command($command, &$server_data, $path = false)
    {
		$CI =& get_instance();
		$CI->load->driver('control');

		if (isset($server_data['enabled']) && !$server_data['enabled']) {
			throw new Exception(lang('server_command_gs_disabled'));
		}
		
		if (isset($server_data['ds_disabled']) && $server_data['ds_disabled']) {
			throw new Exception(lang('server_command_ds_disabled'));
		}
		
		$command = replace_shotcodes($command, $server_data);
		
        $CI->load->model('gdaemon_tasks');
        
        if (is_array($command)) {
            $command = implode("\n", $command);
        }
        
        $task_id = $CI->gdaemon_tasks->add(array(
            'ds_id'     => $server_data['ds_id'],
            'server_id' => $server_data['id'],
            'task' => 'cmdexec',
            'cmd' => $command,
        ));
		
		return "Task {$task_id} created";
	}
}

// ---------------------------------------------------------------------

/**
 * Список отправленных команд на выделенный сервер
 * 
 * @return array
*/
if ( ! function_exists('get_sended_commands'))
{
	function get_sended_commands()
    {
		$CI =& get_instance();
		return $CI->control->get_sended_commands();
	}
}

// ---------------------------------------------------------------------

/**
 * Последняя отправленная команда на выделенный сервер
 * 
 * @return array
*/
if ( ! function_exists('get_last_command'))
{
	function get_last_command()
    {
		$CI =& get_instance();
		return $CI->control->get_last_command();
	}
}

// ---------------------------------------------------------------------

/**
 * Получение названия протокола передачи данных
 * 
 * @param array
 * @return string
 */
if ( ! function_exists('get_file_protocol'))
{
	function get_file_protocol(&$server_data)
    {
		return 'gdaemon';
	}
}

// ---------------------------------------------------------------------

/**
 * Получение данных для соединения с sftp, ftp
 * 
 * @param array
 * @return array
 */
if ( ! function_exists('get_file_protocol_config'))
{
	function get_file_protocol_config(&$server_data)
    {
		// Данные для соединения
		$config = array();
		
		$config['driver'] = 'gdaemon';

        $explode = explode(':', $server_data['gdaemon_host']);
			
        $config['hostname'] = $explode[0];
        $config['port'] = isset($explode[1]) ? $explode[1] : 31707;
			
        $config['username']         = $server_data['gdaemon_login'];;
        $config['password']         = $server_data['gdaemon_password'];;
        $config['privkey_path']     = $server_data['gdaemon_privkey'];
        $config['privkey_pass']     = $server_data['gdaemon_keypass'];

		return $config;
	}
}

// ---------------------------------------------------------------------

/**
 * Чтение файла на удаленном сервере
 * Функция хорошо подходит лишь для единоразового чтения, т.к. 
 * при каждом выполнении производит соединение
 * 
 * @param string
 * @param array
 * 
 * @return bool
*/
if ( ! function_exists('read_ds_file'))
{
	function read_ds_file($file, &$server_data)
    {
		$CI =& get_instance();
		$CI->load->driver('files');
		
		// Данные для соединения
		$config = get_file_protocol_config($server_data);
		
		$CI->files->set_driver($config['driver']);
		
		$CI->files->connect($config);
		return $CI->files->read_file($file);
	}
}

// ---------------------------------------------------------------------

/**
 * Запись файла на удаленном сервере
 * Функция хорошо подходит лишь для единоразовой записи, т.к. 
 * при каждом выполнении производит соединение
 * 
 * @param string
 * @param string
 * @param array
 * 
 * @return bool
*/
if ( ! function_exists('write_ds_file'))
{
	function write_ds_file($file, $contents, &$server_data)
    {
		$CI =& get_instance();
		$CI->load->driver('files');
		
		// Данные для соединения
		$config = get_file_protocol_config($server_data);
		
		$CI->files->set_driver($config['driver']);
		
		$CI->files->connect($config);
		return $CI->files->write_file($file, $contents);
	}
}

// ---------------------------------------------------------------------

/**
 * Получает путь к файлу
 * 
 * Иногда запись файлов или чтение может завершаться ошибкой
 * причина чаще всего в путях
 * 
 * Путь для чтения/записи файла генерируется из базы данных
 * 
 * Локальный путь:
 * 	this->servers->server_data['local_path'] - путь к скрипту запуск серверов относительно корня сервера, либо домашней папки пользователя
 * 	this->servers->server_data['dir'] - директория игрового сервера относительно скрипта
 * 	$s_cfg_files[$cfg_id]['file'] - путь к файлу взятый из json
 * 
 * Удаленный ftp сервер
 * 	$this->servers->server_data['ftp_path'] - путь к скрипту запуск серверов относительно корня сервера, либо домашней папки пользователя
 * 	this->servers->server_data['dir'] - директория игрового сервера относительно скрипта
 * 	$s_cfg_files[$cfg_id]['file'] - путь к файлу взятый из json
*/
if ( ! function_exists('get_ds_file_path'))
{
	function get_ds_file_path(&$server_data)
    {
		$CI =& get_instance();
		$CI->load->helper('string');
		return reduce_double_slashes($server_data['work_path'] . '/' . $server_data['dir'] . '/');
	}
}

// ---------------------------------------------------------------------

/**
 * Список файлов
 * 
 * @param string	директория
 * @param array		данные сервера
 * @param bool		полная информация о файлах (размер, привилегии)
 * @param array		массив с расширениями		
 * 
 * @return array
*/
if ( ! function_exists('list_ds_files'))
{
	function list_ds_files($dir, &$server_data, $full_info = false, $extension = array())
    {
		$CI =& get_instance();
		$CI->load->helper('string');
		$CI->load->driver('files');
		
		$dir = reduce_double_slashes($dir);
		
		// Данные для соединения
		$config = get_file_protocol_config($server_data);
		
		$CI->files->set_driver($config['driver']);
		
		$CI->files->connect($config);
		
		if ($full_info) {
			return $CI->files->list_files_full_info($dir, $extension);
		} else {
			return $CI->files->list_files($dir);
		}
	}
}

// -----------------------------------------------------------------

/**
 * Конвертирует linux слеши в windows
 * 
 * echo linux_slash_to_windows('C:/Windows/system32'); // C:\Windows\system32
 * echo linux_slash_to_windows('C:/Windows/system32/'); // C:\Windows\system32
 * 
 * @param string
 * @return string
 */
if ( ! function_exists('linux_slash_to_windows'))
{	
	function linux_slash_to_windows($string)
	{
		$string = str_replace('/', '\\', $string . '\\');
		$string = preg_replace('/\\\\+/', '\\', $string);
		$string = substr($string, 0, strlen($string)-1);
		return $string;
	}
}

// -----------------------------------------------------------------

/**
 * Конвертирует windows слеши в linux
 * 
 * echo windows_slash_to_linux('\home\servers'); // /home/servers
 * echo windows_slash_to_linux('/home\\//servers\\'); // /home/servers
 * 
 * @param string
 * @return string
 */
if ( ! function_exists('windows_slash_to_linux'))
{	
	function windows_slash_to_linux($string)
	{
		$string = str_replace('\\', '/', $string . '/');
		$string = preg_replace('/\/+/', '/', $string);
		$string = substr($string, 0, strlen($string)-1);
		return $string;
	}
}

// -----------------------------------------------------------------

/**
 * Проверяет наличие файла на ftp, sftp, http или https сервере
 * 
 * exist_remote_file('https://example.com/file.zip');
 * exist_remote_file('ftp://user:password@example.com/file.zip');
 * 
 * @param string
 * @return bool
 */
if ( ! function_exists('remote_file_exists'))
{	
	function remote_file_exists($file = '')
	{
		$expl 		= explode("://", $file);
		$protocol 	= strtolower($expl[0]);
		
		$cl = curl_init($file);
		curl_setopt($cl, CURLOPT_NOBODY, true);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
		curl_exec($cl);
		
		switch ($protocol) {
			case 'ftp':
			case 'sftp':
				$retcode = curl_getinfo($cl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
				$result = ($retcode != -1);
				break;
			
			case 'http':
			case 'https':
			default:
				$retcode = curl_getinfo($cl, CURLINFO_HTTP_CODE);
				$result = ($retcode == 200);
				break;
		}
		
		curl_close($cl); 
		
		return $result;
	}
}
