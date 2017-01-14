<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource	
 */

/**
 * Мастер установки GameAP
 *
 * Производит необходимые проверки перед установкой. Дает советы, в 
 * случае необходимости. Подготавливает панель для дальнейшей работы.
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.7
 */

$this->lang->load('install');

$template				= file_get_contents('install_gameap/template.html');
$title 					= '';
$content 				= '';

$this->tpl_data['title'] 	=	lang('install_title');
$this->tpl_data['heading'] 	=	lang('install_title');

switch($page) {
	case 'license_agreement':
		if($language == 'russian') {
			$license_agreement = file_get_contents('install_gameap/license_agreement.html');
		} else {
			$license_agreement = file_get_contents('install_gameap/license_agreement_eng.html');
		}
		
		$this->output->set_output($license_agreement);
		return TRUE;
	case 'select_language':
		/* Получение списка доступных языков */
		$dirs = scandir('application/language/');
		$options = '';
		foreach ($dirs as $dir) {
			/* Если это директория и в ней присутствует install_lang.php */
			if (is_dir('application/language/' . $dir) && file_exists('application/language/' . $dir . '/install_lang.php')) {
				$options .= '<option value="' . $dir . '">' . $dir . '</option>' . "\n";
			}
			
		}
		
		/* Языки отсутствуют */
		if ($options == '') {
			redirect('install/page/start');
		}
		
		$options = '<option value="">-select language-</option>' . "\n" . $options;
		
		$title = 'Install Game AdminPanel '. AP_VERSION .' [' . AP_DATE . ']';
		$content .= '<p align="center">Select language:<br />
		<select name="language">' . $options . '</select>
		</p>';
		
		$content .= '
		<script type="text/javascript">
		var language;
		
		$(document).ready(function() {
			$("select[name=\'language\']").change(function() {
				language = $("select[name=\'language\']").val();
				$("a#link").attr("href", "' . site_url('install/page/start') . '/" + language);
			});
			
		});

		</script>';
		
		$content .= '<p align="center"><a id="link" class="awesome" href="' . site_url('install/page/start/') . '">Next</a></p>';
		
		break;
		
	case 'start':
		$title = lang('install_title');
		$content .= '<p>' . lang('install_welcome') . ' ' . AP_VERSION .' [' . AP_DATE . ']</p>';
		
		$content .= lang('install_welcome_description');
		
		$content .= '<p align="center"><iframe src="' . site_url('install/page/license_agreement/' . $language) . '" width="600" height="400"></iframe></p>';
		
		$content .= '<p align="right"><a class="large awesome" href="' . site_url('install/page/1/' . $language) . '">' . lang('next') . '</a></p>';
		
		break;
		
	case '1':
		$ext_list = get_loaded_extensions();
		
		$title = lang('install_title');
		
		$content = '<h2>' . lang('install_php_version') . '</h2>';
		
		$content .= '<table width="100%" class="zebra">';
		
		$content .= '<th>' . lang('install_php_version_on_server') . '</th>';
		$content .= '<th>' . lang('install_php_recomended_version') . '</th>';
		
		$content .=  '<tr><td>' . phpversion() . '</td><td>' . lang('install_php_recomended_version_info') . '</td></tr>';
		
		$content .= '</table>';
		
		$content .= '<h2>' . lang('install_php_ext') . '</h2>';
		
		$content .= '<table width="100%" class="zebra">';
		
		$content .= '<th>' . lang('install_module') . '</th>';
		$content .= '<th>' . lang('install_status') . '</th>';
		
		$content .= '<tr>';
		$content .= '<td width="30%">' . lang('install_module_ftp') . ':</td>';
		$content .= (in_array('ftp', $ext_list)) ? '<td><font color="green">OK</font></td>' : '<td><font color="red">' . lang('install_not_module') . '</font></td>';
		$content .= '</tr>';
		
		$content .= '<tr>';
		$content .= '<td>' . lang('install_module_json') . ':</td>';
		$content .= (in_array('json', $ext_list)) ? '<td><font color="green">OK</font></td>' : '<td><font color="red">' . lang('install_not_module') . '</font></td>';
		$content .= '</tr>';
		$content .= '<tr>';
		
		$content .= '<td>' . lang('install_module_gd') . ':</td>';
		$content .= (in_array('gd', $ext_list)) ? '<td><font color="green">OK</font></td>' : '<td><font color="red">' . lang('install_not_module') . '</font></td>';
		$content .= '</tr>';
		$content .= '<tr>';
		
		$content .= '<td>' . lang('install_module_ssh') . ':</td>';
		$content .= (in_array('ssh2', $ext_list)) ? '<td><font color="green">OK</font></td>' : '<td><font color="red">' . lang('install_not_module') . '</font></td>';
		$content .= '</tr>';
		
		$content .= '</table>';

		$content .= '<h2>' . lang('install_php_settings') . '</h2>';
		
		$content .= '<table width="100%" class="zebra">';
		
		$content .= '<th>' . lang('install_setting') . '</th>';
		$content .= '<th>' . lang('install_status') . '</th>';
		
		/* Загрузка файлов */
		$content .= '<tr>';
		$content .= '<td width="30%">' . lang('install_upload_files') . ':</td>';
		$content .= (ini_get('file_uploads')) ? '<td><font color="green">' . lang('yes') . '</font></td>' : '<td><font color="red">' . lang('no') . '</font></td>';
		$content .= '</tr>';

		$content .= '</table>';
		
		$content .= '<p>' . lang('install_modules_information') . '</p>';
		
		$content .= '<p align="right"><a class="large awesome" href="' . site_url('install/page/2/' . $language) . '">' . lang('next') . '</a></p>';
		
		break;
		
	case '2':
		$title = lang('install_title');
		$content = '<h2>' . lang('install_dir_chmod') . '</h2>';
		
		$writeble_directories = array(
			'uploads/security/',
			'application/cache/',
			'application/config/',
			'application/logs/',
		);
		
		$content .= '<table width="100%" class="zebra">';
		
		$content .= '<th>' . lang('directory') . '</th>';
		$content .= '<th>' . lang('status') . '</th>';
		
		foreach($writeble_directories as $dir) {
			
			$content .= '<tr><td>' . $dir . '</td>';
			
			if(!file_exists($dir)) {
				$content .= '<td><font color="red">' . lang('install_dir_not_found') . '</font></td>';
			} elseif(is_writable($dir)) {
				$content .= '<td><font color="green">' . lang('install_dir_writable') . '</font></td>';
			} else {
				$content .= '<td><font color="red">' . lang('install_dir_not_writable') . '</font></td>';
			}
			
			$content .= '</tr>';
		}
		
		$content .= '</table>';
		
		$content .= '<p>' . lang('install_dirs_information') . '</p>';
		
		$content .= '<p align="right"><a class="large awesome" href="' . site_url('install/page/3/' . $language) . '">' . lang('next') . '</a></p>';
		
		break;
		
	case '3':
		$title = lang('install_title');
		$content = '<h2>' . lang('install_configuration') . '</h2>';

		$content .= '<form action="' . site_url('install/page/4/' . $language)  . '" method="post" accept-charset="utf-8">';
		$content .= '<input type="hidden" name="' . $this->security->get_csrf_token_name() .'" value="' . $this->security->get_csrf_hash() . '" />';
		
		$content .= '<table class="zebra" width="100%">';
		
		$content .= '<p class="hr"><strong>' . lang('install_data_base') . '</strong></p>';
		
		$db_driver_options = array();
		!extension_loaded('mysql') 		OR ($db_driver_options['mysql'] = 'MySQL' & $db_driver_default = 'mysql');
		!extension_loaded('mysqli')  	OR ($db_driver_options['mysqli'] = 'MySQLi' & $db_driver_default = 'mysqli');
		!extension_loaded('pdo') 		OR $db_driver_options['pdo'] = 'PDO';
		!extension_loaded('postgre') 	OR $db_driver_options['postgre'] = 'Postgre';
		!extension_loaded('cubrid')		OR $db_driver_options['cubrid'] = 'CUBRID';
		!extension_loaded('mssql') 		OR $db_driver_options['mssql'] = 'Microsoft SQL';
		!extension_loaded('oci8') 		OR $db_driver_options['oci8'] = 'oci8';
		!extension_loaded('odbc')  		OR $db_driver_options['odbc'] = 'ODBC';
		!extension_loaded('sqlite') 	OR $db_driver_options['sqlite'] = 'SQLite';
		//~ !extension_loaded('sqlite3')	OR $db_driver_options['sqlite3'] = 'SQLite3';
		!extension_loaded('sqlsrv') 	OR $db_driver_options['sqlsrv'] = 'SQLSRV';
		
		// строим список доступных PDO драйверов
		if($db_driver_options['pdo']){
			$pdoDrv = PDO::getAvailableDrivers();
			
			$pdoDriverNames = array(
				'mysql' => 'MySQL',
				'sqlite' => 'SQLite'
			);
			
			foreach(PDO::getAvailableDrivers() as $pdoDriver){
				if(!isset($pdoDriverNames[$pdoDriver]))
					continue;
				
				$pdoDrivers[$pdoDriver] = $pdoDriverNames[$pdoDriver];
			}
		}
		
		$content .= '
					<tr>
						<td>' . lang('install_db_dbdriver') . ':</td>
						<td>' . form_dropdown('dbdriver', $db_driver_options, $db_driver_default, 'id="dbdrivers"') . '</td>
					</tr>';
		
		if(isset($db_driver_options['pdo'])){ // выводим этот список
			$content .= '
						<tr id="pdodrivers_row" hidden>
							<td>' . lang('install_db_pdo') . ':</td>
							<td>' . form_dropdown('pdodriver', $pdoDrivers,array(),'id="pdodrivers"') . '</td>
						</tr>';
						
			$content .= '<script src="{base_url}/gameap/themes/system/js/install.js"></script>';
		}
	
		$content .= '
					<tr class="noForSqlite">
						<td>' . lang('install_db_hostname') . ':</td>
						<td width="40%"><input type="text" name="hostname" value="localhost"/></td>
					</tr>
					<tr class="noForSqlite">
						<td>' . lang('install_db_username') . ':</td>
						<td><input value="root" type="text" name="username" /></td>
					</tr>
					<tr class="noForSqlite">
						<td>' . lang('install_db_password') . ':</td>
						<td><input type="text" name="password" /></td>
					</tr>
					<tr>
						<td>' . lang('install_db_database') . ':</td>
						<td><input type="text" name="database" /></td>
					</tr>
					<tr>
						<td>' . lang('install_db_dbprefix') . ':</td>
						<td><input type="text" name="dbprefix" value="gameap_" /></td>
					</tr>
					';	
							
		$content .= '</table>';	
		
		$content .= '<p class="hr"><strong>' . lang('install_configuration') . '</strong></p>';
		$content .= '<table class="zebra" width="100%">';
		$content .= '<tr>
						<td>' . lang('install_site_url') . '*:</td>
						<td width="40%"><input type="text" name="base_url" value="' . base_url() . '"/></td>
					</tr>
					<tr>
						<td>' . lang('install_secret_key') . '*:</td>
						<td><input value="' . generate_code(12) . '" type="text" name="encryption_key" /></td>
					</tr>
					<tr>
						<td>' . lang('install_system_email') . '*:</td>
						<td><input type="text" name="system_email" /></td>
					</tr>
					<tr>
						<td>' . lang('install_local_os') . ':</td>
						<td><select name="local_os">
								<option value="Linux">Linux</option>
								<option value="Windows">Windows</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>' . lang('install_script_path') . ':</td>
						<td><input value="/home/servers" type="text" name="local_script_path" /></td>
					</tr>
					<tr>
						<td>' . lang('install_steamcmd_path') . ':</td>
						<td><input type="text" name="local_steamcmd_path" /></td>
					</tr>
					';
							
		$content .= '</table>';
		
		$content .= '<h2>' . lang('install_create_admin') . '</h2>';
		$content .= '<table class="zebra" width="100%">';
		$content .= '<tr>
						<td>' . lang('login') . ':</td>
						<td width="40%"><input type="text" name="admin_login" /></td>
					</tr>
					<tr>
						<td>' . lang('email') . ':</td>
						<td><input type="text" name="admin_email" /></td>
					</tr>
					<tr>
						<td>' . lang('password') . ':</td>
						<td><input value="' . generate_code(8) . '" type="text" name="admin_password" /></td>
					</tr>';			
		$content .= '</table>';
		
		
		$content .= '<p align="right"><input class="large awesome" type="submit" name="submit" value="' . lang('next') . '" /></p>';
		$content .= '</form>';
		break;
		
	case '4':
		// Определяем SQLite драйвер.	
		$isSqlite = $this->input->post('dbdriver') == 'pdo' && $this->input->post('pdodriver') == 'sqlite';

		$title = lang('install_title');
		$content = '<h2>' . lang('install_end_stage') . '</h2>';
		
		// Для SQLite базы некоторые данные можно опустить.
		$isSqlite OR $this->form_validation->set_rules('hostname', lang('install_db_hostname'), 'trim|required|xss_clean');
		$isSqlite OR $this->form_validation->set_rules('username', lang('install_db_username'), 'trim|required|xss_clean');
		$isSqlite OR $this->form_validation->set_rules('password', lang('install_db_password'), 'trim|xss_clean');
		$this->form_validation->set_rules('database', lang('install_db_database'), 'trim|required|xss_clean');
		$this->form_validation->set_rules('dbdriver', lang('install_db_dbdriver'), 'trim|required|xss_clean');
		$this->input->post('dbdriver') != 'pdo' OR $this->form_validation->set_rules('pdodriver', lang('install_pdo_dbdriver'), 'trim|required|xss_clean');
		$this->form_validation->set_rules('dbprefix', lang('install_db_dbprefix'), 'trim|xss_clean');
		
		$this->form_validation->set_rules('base_url', lang('install_site_url'), 'trim|required|xss_clean');
		$this->form_validation->set_rules('encryption_key', lang('install_secret_key'), 'trim|required|min_length[6]|xss_clean');
		$this->form_validation->set_rules('system_email', lang('install_system_email'), 'trim|required|valid_email|xss_clean');
		$this->form_validation->set_rules('local_os', lang('install_local_os'), 'trim|required|xss_clean');
		$this->form_validation->set_rules('local_script_path', lang('install_script_path'), 'trim|xss_clean');
		$this->form_validation->set_rules('local_steamcmd_path', lang('install_steamcmd_path'), 'trim|xss_clean');
		
		$this->form_validation->set_rules('admin_login', lang('login'), 'trim|required|max_length[64]|xss_clean');
		$this->form_validation->set_rules('admin_email', lang('email'), 'trim|required|valid_email|xss_clean');
		$this->form_validation->set_rules('admin_password', lang('password'), 'trim|required|min_length[6]|max_length[64]|xss_clean');
		
		if ($this->form_validation->run() == FALSE)
		{
			$this->_show_message(validation_errors());
			return FALSE;
		}
		
		/*--------------*/
		/* DATABASE.PHP */
		/*--------------*/
		
		/* Проверяем, правильно ли указаны данные БД */
		
		$db_cfg['hostname'] = $this->input->post('hostname');
		$db_cfg['username'] = $this->input->post('username');
		$db_cfg['password'] = $this->input->post('password');
		$db_cfg['database'] = $this->input->post('database');
		$db_cfg['dbdriver'] = $this->input->post('dbdriver');
		$db_cfg['dbprefix'] = $this->input->post('dbprefix');
		$db_cfg['db_debug'] = TRUE;
		
		// Обработка PDO данных
		if($this->input->post('dbdriver') == 'pdo'){
			switch($this->input->post('pdodriver')){
				case 'mysql':
					$db_cfg['hostname'] = 'mysql:host='.$db_cfg['hostname'].';dbname='.$db_cfg['database'];
					//$db_cfg['database'] = '';
				break;
				case 'sqlite':
					$db_cfg['hostname'] = 'sqlite:'.APPPATH.'db/'.$db_cfg['database'].'.db3';
					$db_cfg['username'] = '';
					$db_cfg['password'] = '';
					$db_cfg['database'] = '';
				break;
			}
		}
		
		if (!$this->load->database($db_cfg)) {
			$this->_show_message(lang('install_db_error'));
			return FALSE;
		} else {
			$file_strings = explode("\n", file_get_contents('install_gameap/database_install.tmp'));
		}

		$i = 0;
		$count_fstr = count($file_strings);
		$db_variables = array('hostname', 'username', 'password',
								'database', 'dbdriver', 'dbprefix',
		);
		$new_file_data = '';
		while($i < $count_fstr) {
			
			foreach($db_variables as $variable) {
				preg_match('/([\s]*)\$db\[\'default\'\]\[\'' . $variable . '\'\]([\s]*)([\']?)(.*)(\'?)(\\\\?)(.*)/si', $file_strings[$i], $matches);
				
				// ???
				$value = $db_cfg[$variable]; //$this->input->post($variable);
				
				$value = str_replace('\\', '\\\\', $value);
				$value = str_replace('\'', '\\\'', $value);
				$value = str_replace('"', '\\"', $value);
				
				/* Найдены совпадения */
				if(!empty($matches)){
					$file_strings[$i] = '$db[\'default\'][\'' . $variable . '\'] = \'' . $value . '\';';
					$string_found = TRUE; // Строка rcon_password найдена
				}
			}
	
			
			/* Записываем данные в переменную, которую потом запишем как новый конфиг */
			$new_file_data .= $file_strings[$i] . "\n";
			$i++;
		}
		
		
		if(@file_put_contents('application/config/database.php', $new_file_data)) {
			/*
			if(!unlink('../application/config/config_install.tmp')) {
				$content .= '<p>Удалите файл "application/config/config_install.tmp"</p>';
			}
			*/
		
			$content .= '<p>' . lang('install_database_saved') . '</p>';
		} else {
			//~ $content .= '<p>' . lang('install_manual_database') . ':</p>';
			//~ $content .= '<textarea>' . $new_file_data . '</textarea>';
			$this->_show_message('File application/config/database.php create error');
			return FALSE;
		}

		/* 
		 * КОСТЫЛЬ 
		 * 
		 * ХЗ почему, но при загрузке базы данных с ручной конфигурацией
		 * он не хочет грузить $CI->db->dbforce();
		*/
		//~ $this->load->database();

		/* Структура базы данных */
		
		require_once 'install_gameap/db.php';
		
		/* Демо данные */
		require_once 'install_gameap/demo_data.php';


		// If the migrations table is missing, make it
		if (!$this->db->table_exists('migrations')) {
			$this->dbforge->add_field(array(
				'version' => array('type' => 'INT', 'constraint' => 3),
			));

			$this->dbforge->create_table('migrations', TRUE);
		}
		
		include_once 'application/config/migration.php';
		
		if (isset($config['migration_version'])) {
			$m_version = $config['migration_version'];
		} else {
			$m_version = 0;
		}
		
		$this->db->insert('migrations', array('version' => $m_version));
		
		/*-------------------------------*/
		/* Создание выделенного сервера  */
		/*-------------------------------*/
		$this->load->model('servers/dedicated_servers');
		
		$ds_data['name'] 				= 'Local server';
		$ds_data['os'] 					= $this->input->post('local_os');
		$ds_data['control_protocol'] 	= 'local';
		$ds_data['location'] 			= 'GameAP';
		$ds_data['provider'] 			= 'GameAP';
		
		$ds_data['ip'] = json_encode(array('127.0.0.1'));
		
		$ds_data['steamcmd_path'] = $this->input->post('local_steamcmd_path');
		$ds_data['ssh_path'] 	= $this->input->post('local_script_path');
		$ds_data['telnet_path'] = $this->input->post('local_script_path');

		$this->dedicated_servers->add_dedicated_server($ds_data);
		
		/*------------*/
		/* ADMIN ADD  */
		/*------------*/
		
		$user_data['email'] 	= $this->input->post('admin_email');
		$user_data['reg_date'] 	= time();
			 
        $user_data['login'] 	= $this->input->post('admin_login');
        $user_data['password'] 	= hash_password($this->input->post('admin_password'));

		$user_data['privileges'] = json_encode(array(
													'srv_global' 			=> TRUE,
													'srv_start' 			=> TRUE,
													'srv_stop' 				=> TRUE,
													'srv_restart' 			=> TRUE,
													'usr_create' 			=> TRUE,
													'usr_edit' 				=> TRUE,
													'usr_edit_privileges' 	=> TRUE,
													'usr_delete' 			=> TRUE,
		));
		
		$user_data['is_admin'] = 1;
		
		$this->users->add_user($user_data);

		/*-------------------*/
		/* GAMEAP_CONFIG.PHP */
		/*-------------------*/
		
		$file_strings = explode("\n", file_get_contents('install_gameap/gameap_config_install.tmp'));
		
		$i = 0;
		$count_fstr = count($file_strings);
		$cfg_variables = array('base_url', 'language', 'encryption_key',
								'local_os', 'local_script_path', 'local_steamcmd_path',
								'system_email',
		);
		$new_file_data = '';
		while($i < $count_fstr) {
			
			foreach($cfg_variables as $variable) {
				preg_match('/([\s]*)\$config\[\'' . $variable . '\'\]([\s]*)([\']?)(.*)(\'?)(\\\\?)(.*)/si', $file_strings[$i], $matches);

				/* Язык */
				if ($variable == 'language') {
					$value = $language;
				} else {
					$value = $this->input->post($variable);
				}
				
				$value = str_replace('\\', '\\\\', $value);
				$value = str_replace('\'', '\\\'', $value);
				$value = str_replace('"', '\\"', $value);
				
				/* Найдены совпадения */
				if(!empty($matches)){
					$file_strings[$i] = '$config[\'' . $variable . '\'] = \'' . $value . '\';';
					$string_found = TRUE; // Строка rcon_password найдена
				}
			}
	
			
			/* Записываем данные в переменную, которую потом запишем как новый конфиг */
			$new_file_data .= $file_strings[$i] . "\n";
			$i++;
		}
		
		if(@file_put_contents('application/config/gameap_config.php', $new_file_data)) {
			/*
			if(!unlink('../application/config/config_install.tmp')) {
				$content .= '<p>Удалите файл "application/config/config_install.tmp"</p>';
			}
			*/
			
			$content .= '<p>' . lang('install_configuration_saved') . '</p>';
		} else {
			$content .= '<p>' . lang('install_manual_configuration') . ':</p>';
			$content .= '<textarea>' . $new_file_data . '</textarea>';
		}
		
		$content .= '<p align="right"><a class="large awesome" href="' . site_url('install/page/end/' . $language) . '">' . lang('next') . '</a></p>';
		break;
		
	case 'end':
		$this->load->library('migration');
		
		$title = lang('install_title_end');
		
		/* Обновление базы */
		if (!$this->migration->latest()) {
			show_error($this->migration->error_string());
		}
		
		$content = lang('install_end');
		$content .= '<p align="center"><a class="large awesome" href="' . base_url() . '">' . lang('install_goto_adminpanel') . '</a></p>';
		break;

	default:
		redirect('install/page/start');
		break;

}

$template = str_replace('{title}', $title, $template);
$template = str_replace('{content}', $content, $template);
$template = str_replace('{base_url}', base_url(), $template);

$this->output->set_output($template);
