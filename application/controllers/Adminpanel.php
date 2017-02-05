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

use \Myth\Controllers\BaseController;

/**
 * Информационные функции АдминПанели
 *
 * Проверка обновлений, отправка информации об ошибках, информация об админпанели
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.6
 * 
*/
class Adminpanel extends BaseController {
	
	// -----------------------------------------------------------------
	
	function __construct()
    {
        parent::__construct();

		$this->load->database();
        $this->load->model('users');
        $this->lang->load('ap');

        if($this->users->check_user()){
			
			//Base Template
			$this->tpl['title'] 	= lang('ap_title_index');
			$this->tpl['heading'] 	= lang('ap_heading_index');
			$this->tpl['content']	= '';
			
			$this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
			$this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
        
        } else {
			redirect('auth');
        }
    }
    
    // -----------------------------------------------------------------
    
    // Отображение информационного сообщения
    function _show_message($message = false, $link = false, $link_text = false)
    {
        
        if (!$message) {
			$message = lang('error');
		}
		
        if (!$link) {
			$link = 'javascript:history.back()';
		}
		
		if (!$link_text) {
			$link_text = lang('back');
		}

        $local_tpl['message'] = $message;
        $local_tpl['link'] = $link;
        $local_tpl['back_link_txt'] = $link_text;
        $this->tpl['content'] = $this->parser->parse('info.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }
    
    // -----------------------------------------------------------------
    
    private function _check_modules_updates()
    {
		$tpl = array();
		//~ print_r($this->gameap_modules->modules_data);
		
		$i = 0;
		foreach($this->gameap_modules->modules_data as $module) {
			
			$tpl[$i] = $module;
			$tpl[$i]['available_version'] 		= '-';
			$tpl[$i]['download_url'] 			= '#';
			$tpl[$i]['available_url'] 			= '#';
			
			if (!isset($module['update_info']) OR !$module['update_info']) {
				$i++;
				continue;
			}
			
			$version_info = $this->_get_available_version($module['update_info']);
			
			if ($version_info) {
				$tpl[$i]['download_url'] 		= $version_info['download_url'];
				$tpl[$i]['available_version'] 	= $version_info['available_version'];
				$tpl[$i]['available_url'] 		= $version_info['available_url'];
				
				if (version_compare($module['version'], $version_info['available_version']) == -1) {
					$tpl[$i]['available_version'] = '<strong><font color="green">' . $tpl[$i]['available_version'] . '</font></strong>';
				}
			}
			
			$i++;
		}
		
		return $tpl;
	}
	
	// -----------------------------------------------------------------
	
	private function _get_available_version($url = false)
	{
		if (!$url) {
			return false;
		}
		
		if (in_array('curl', get_loaded_extensions())) {
			/* Имеется расширение curl, через него лучше */
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$file_version = curl_exec($ch);
			curl_close($ch);
		} else {
			$file_version = @file_get_contents($url);
		}
		
		if (!$file_version) {
			return false;
		}
		
		$version_info = array();
		
		$strings = explode("\n", $file_version);
			
		$available_version = explode(": ", $strings[0]);
		@$version_info['available_version'] = $available_version[1];
		
		$download_url = explode(": ", $strings[1]);
		@$version_info['download_url'] = $download_url[1];
		
		$available_url = explode(": ", $strings[2]);
		@$version_info['available_url'] = $available_url[1];
		
		return $version_info;
	}
	
	// -----------------------------------------------------------------
	
	function index()
	{
		$this->parser->parse('main.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------
	
	function help()
	{
		$this->tpl['title'] 	= lang('ap_title_help');
		$this->tpl['heading'] 	= lang('ap_heading_help');
										
		$this->tpl['content'] = lang('ap_help_msg') . '<p align="center"><strong>Yandex Money:</strong> 410011199602260<br />
			<strong>WebMoney WMR:</strong> R133031427608<br />
			<strong>WebMoney WMZ</strong>:</strong> Z140088475851</p>' . lang('ap_help_translator');
		
		
					
		$this->parser->parse('main.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Страница обновления панели
	 * 
	 * @param str - обновление (auto|manial|back), если строка отсутствует, то отображаются сведения о версии
	*/
	function update($type = false, $confirm = false)
	{
		$this->load->library('migration');
		
		/* Есть ли у пользователя права */
		if(!$this->users->auth_data['is_admin']) {
			redirect('admin');
		}
		
		$this->tpl['title'] = lang('ap_title_update');
		$this->tpl['heading'] = lang('ap_heading_update');
		
		/* Получение информации о новой версии */
		$version_info = $this->_get_available_version('http://www.gameap.ru/gameap_version.txt');
		
		if (!$version_info OR !isset($version_info['available_version'])) {
			$this->_show_message('<p align="center">' . lang('ap_error_check_version') . '</p>');
			return false;
		}

		switch($type) {
			//~ case 'auto':
					//~ 
				//~ break;
				
			case 'manual':
			
				
				/* Если было подтверждение */
				if ($confirm == $this->security->get_csrf_hash()) {
					
					if (!$this->migration->latest()) {
						show_error($this->migration->error_string());
					} else {
						$this->_show_message('Update successful', site_url('adminpanel/update'), lang('next'));
						return true;
					}
					
				} else {
					/* Пользователь не подвердил намерения */
					$confirm_tpl['message'] = 'Загрузите последнюю версию по адресу <a href="'. $version_info['download_url'] . '">' . $version_info['download_url'] . '</a>, распакуйте архив в каталог с панелью и нажмите "Да"';
					$confirm_tpl['confirmed_url'] = site_url('adminpanel/update/manual/' . $this->security->get_csrf_hash());
					$this->tpl['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
				}
				
				break;
				
			case 'back':
				/* Откат */	
				
				/* Если было подтверждение */
				//~ if ($confirm == $this->security->get_csrf_hash()) {
					//~ 
					//~ if (!$this->migration->version()) {
						//~ show_error($this->migration->error_string());
					//~ }
					//~ 
				//~ } else {
					//~ /* Пользователь не подвердил намерения */
					//~ $confirm_tpl['message'] = 'Откатить к выбранной версии?';
					//~ $confirm_tpl['confirmed_url'] = site_url('adminpanel/update/manual/' $this->security->get_csrf_hash());
					//~ $this->tpl['content'] .= $this->parser->parse('confirm.html', $confirm_tpl, true);
				//~ }
				
				break;
				
			default:

				$this->tpl['content'] = '<p align="center"><strong>' . lang('ap_you_version') . ': </strong>' . AP_VERSION . ' <strong>' . lang('ap_actual_version') . ': </strong>' . $version_info['available_version'] . '</p>';
				
				if(version_compare(AP_VERSION, $version_info['available_version']) == -1) {
					$this->tpl['content'] .= '<p align="center"><font color="red">' . lang('ap_you_version_old') . '</font></p>';
					//~ $this->tpl['content'] .= '<p align="center"><a class="small awesome" href="#">' . lang('ap_autoupdate') . '</a></p>';
					$this->tpl['content'] .= '<p align="center"><a class="small awesome" href="' . site_url('/adminpanel/update/manual') . '">Manual Update</a></p>';
				} elseif(version_compare(AP_VERSION, $version_info['available_version']) == 0) {
					$this->tpl['content'] .= '<p align="center"><font color="green">' . lang('ap_you_version_actual') . '</font></p>';
					//~ $this->tpl['content'] .= '<p align="center"><a class="small awesome" href="' . site_url('/adminpanel/update/back') . '">Откат к предыдущей версии</a></p>';
				} elseif(version_compare(AP_VERSION, $version_info['available_version']) == 1) {
					$this->tpl['content'] .= '<p align="center"><font color="goldenrod">' . lang('ap_you_version_dev') . '</font></p>';
					$this->tpl['content'] .= '<p align="center"><a class="small awesome" href="' . site_url('/adminpanel/update/manual') . '">Manual Update</a></p>';
				}

				$this->tpl['content'] .= '<p align="center"><a class="small awesome" href="' . $version_info['download_url'] . '">' . lang('ap_goto_download_page') . '</a></p>';
				
				// Проверка модулей
				$local_tpl['modules_list'] = $this->_check_modules_updates();
				$this->tpl['content'] 		.= $this->parser->parse('adminpanel/update.html', $local_tpl, true);
				
				break;
		}

		$this->parser->parse('main.html', $this->tpl);
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Отправка сообщения разработчику
	 */
	function send_error()
	{
		$this->tpl['title'] = lang('ap_title_report_error');
		$this->tpl['heading'] = lang('ap_heading_report_error');
		
		$local_tpl = array();
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('description', 'описание', 'trim|required|min_length[3]');
		$this->form_validation->set_rules('actions', 'действия', 'trim|min_length[3]');
		
		if ($this->form_validation->run() == false) {
			
			if (validation_errors()) {
				$this->_show_message(validation_errors());
				return false;
			}
			
			$this->tpl['content'] = $this->parser->parse('adminpanel/send_error.html', $local_tpl, true);
		} else {
			$upload_config['upload_path'] = sys_get_temp_dir();
			$upload_config['overwrite'] = true;
			$upload_config['max_filename'] = 64;
			$upload_config['max_size']	= '2048';
			$upload_config['allowed_types'] = 'gif|jpg|png|zip|rar|7z';
			
			$this->load->library('upload', $upload_config);
			$this->load->library('email');

			$this->email->to('kronstadtsky@bk.ru');
			$this->email->from($this->users->auth_data['email'], $this->users->auth_data['login']);
			
			$this->email->subject('Сообщение об ошибке в АдминПанели');
			
			$this->email->message('GameAP version: ' . AP_VERSION . 
									"\nЛогин пользователя: " . $this->users->auth_data['login'] .
									"\nEmail пользователя: " . $this->users->auth_data['email'] .
									"\nДомен: " . site_url() .
									"\nОписание ошибки: " . $this->input->post('description', true) . 
									"\nДействия: " . $this->input->post('actions')
								);
								
			/* Прикрепление файла */
			if ($this->upload->do_upload()) {
				$file_data = $this->upload->data();
				$this->email->attach($file_data['full_path']);
			}
								
			if($this->email->send()) {
				$this->_show_message(lang('ap_error_msg_sended'), site_url('/admin'), lang('next'));
				return true;
			} else {
				$this->_show_message(lang('ap_error_send_mail'));
				//echo $this->email->print_debugger();
				return false;
			}

		}
		
		
		$this->parser->parse('main.html', $this->tpl);
	}
}
