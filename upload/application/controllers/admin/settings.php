<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller {
	
	//Template
	var $tpl_data = array();
	
	var $user_data = array();
	var $server_data = array();
	
	public function __construct()
    {
        parent::__construct();
		
		$this->load->database();
        $this->load->model('users');
        $this->lang->load('settings');

        if($this->users->check_user()){
			
			 $this->load->model('servers');
			 $this->load->model('servers/game_types');
			 $this->load->library('form_validation');
			
			//Base Template
			$this->tpl_data['title'] 		= lang('settings_title_index');
			$this->tpl_data['heading'] 		= lang('settings_heading_index');
			$this->tpl_data['content'] 		= '';
			$this->tpl_data['menu'] 		= $this->parser->parse('menu.html', $this->tpl_data, TRUE);
			$this->tpl_data['profile'] 		= $this->parser->parse('profile.html', $this->users->tpl_userdata(), TRUE);
        
        }else{
            redirect('auth');
        }
    }
    
    // Отображение информационного сообщения
    private function show_message($message = FALSE, $link = FALSE, $link_text = FALSE)
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

        $local_tpl_data['message'] = $message;
        $local_tpl_data['link'] = $link;
        $local_tpl_data['back_link_txt'] = $link_text;
        $this->tpl_data['content'] = $this->parser->parse('info.html', $local_tpl_data, TRUE);
        $this->parser->parse('main.html', $this->tpl_data);
    }
    
    // ----------------------------------------------------------------

    /**
     * Главная страница
    */
    public function index()
    {
		
		$this->tpl_data['content'] = 'Функция в разработке';
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	
	// ----------------------------------------------------------------

    /**
     * Настройки сервера
    */
    public function server($server_id = FALSE)
    {

		if(!$server_id) {
			$this->show_message('Сервер не найден');
			return FALSE;
		}
		
		$server_id = (int)$server_id;
		
		/* Получение данных сервера и привилегий на сервер */
		$this->servers->server_data = $this->servers->get_server_data($server_id);
		$this->users->get_server_privileges($server_id);
		
		if(!$this->servers->server_data) {
			$this->show_message(lang('settings_server_not_found'));
			return FALSE;
		}
		
		/* Пользователь должен быть админом либо иметь привилегии настройки */			
		if(!$this->users->auth_data['is_admin'] && !$this->users->servers_privileges['SERVER_SETTINGS']) {
			$this->show_message(lang('settings_not_privileges_for_server'));
			return FALSE;
		}
		
		$server_settings = $this->servers->get_server_settings($server_id);

		if (!$this->input->post('save')){
			
			/* Отображение формы */
			$num = -1;
			foreach ($server_settings as $sett_id => $value) {
				$num++;
		
				$local_tpl_data['settings'][$num]['input_field'] = form_checkbox($sett_id, '1', $value);
				$local_tpl_data['settings'][$num]['human_name'] = $this->servers->all_settings[$sett_id];
			}
			
			/* Допустимые алиасы */
			$allowable_aliases = json_decode($this->servers->server_data['aliases_list'], TRUE);
			
			/* Значения алиасов на сервере */
			$server_aliases = json_decode($this->servers->server_data['aliases'], TRUE);
			
			/* Отображение алиасов */
			if($allowable_aliases && !empty($allowable_aliases)) {
				foreach ($allowable_aliases as $alias) {

					if(!$this->users->user_privileges['srv_global'] && $alias['only_admins']) {
						/* Алиас могут редактировать только администраторы */
						continue;
					}
					
					$num++; // Отсчет продолжаем, не сбрасываем
					
					// Задаем правила проверки для алиаса
					$this->form_validation->set_rules('alias_' . $alias['alias'], $alias['desc'], 'trim|max_length[32]|xss_clean');
					
					if(isset($server_aliases[$alias['alias']])) {
						$value_alias = $server_aliases[$alias['alias']];
					} else {
						$value_alias = '';
					}
					
					$data = array(
						  'name'        => 'alias_' . $alias['alias'],
						  'value'       => $value_alias,
						  'maxlength'   => '32',
						  'size'        => '30',
						);

					$local_tpl_data['settings'][$num]['input_field'] =  form_input($data);
					$local_tpl_data['settings'][$num]['human_name'] = $alias['desc'];
				}
			}
			
			$local_tpl_data['server_id'] = $server_id;

			$this->tpl_data['content'] .= $this->parser->parse('settings/server.html', $local_tpl_data, TRUE);
		} else {
			/* Сохранение настроек */
			
			$log_data['log_data'] = '';
			

            foreach ($this->servers->all_settings as $sett_id => $value) {

				$value = (bool)$this->input->post($sett_id, TRUE);
				$this->servers->set_server_settings($sett_id, $value, $server_id);
				
				$log_data['log_data'] .= $sett_id . ' : ' . (int)$value . "\n";
            }
            
            /* Допустимые алиасы */
			$allowable_aliases = json_decode($this->servers->server_data['aliases_list'], TRUE);
			
			/* Значения алиасов на сервере */
			$server_aliases = json_decode($this->servers->server_data['aliases'], TRUE);
			
			/* Прогон по алиасам */
			if($allowable_aliases && !empty($allowable_aliases)) {
				foreach ($allowable_aliases as $alias) {

					if(!$this->users->user_privileges['srv_global'] && $alias['only_admins']) {
						/* Алиас могут редактировать только администраторы */
						continue;
					}
					
					/* Для безопасности запрещаем пробелы, табы и кавычки */
					$alias_arr = explode(' ', $this->input->post('alias_' . $alias['alias'], TRUE));
					$server_aliases[$alias['alias']] = $alias_arr[0];
					$server_aliases[$alias['alias']] = str_replace('\'', '', $server_aliases[$alias['alias']]);
					$server_aliases[$alias['alias']] = str_replace('"', '', $server_aliases[$alias['alias']]);
					$server_aliases[$alias['alias']] = str_replace('	', '', $server_aliases[$alias['alias']]);
					
					$log_data['log_data'] .= 'alias_' . $alias['alias'] . ' : ' . $server_aliases[$alias['alias']] . "\n";
				}
			}
			
			// Отправляем алиасы на сервер
			$sql_data['aliases'] = json_encode($server_aliases);
			$this->servers->edit_game_server($server_id, $sql_data);

            // Сохраняем логи
			$log_data['type'] = 'server_settings';
			$log_data['command'] = 'edit_settings';
			$log_data['user_name'] = $this->users->user_login;
			$log_data['server_id'] = $server_id;
			$log_data['msg'] = 'Edit settings';
			$this->panel_log->save_log($log_data);
            
            $this->show_message(lang('settings_saved'), site_url('admin/server_control/main/' . $server_id), lang('next'));
            return TRUE;
            
		}
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
	
	
	// ----------------------------------------------------------------

    /**
     * Персональные настройки
    */
	public function personal()
    {
		
		$this->tpl_data['content'] = 'Функция в разработке';
		
		
		$this->parser->parse('main.html', $this->tpl_data);
	}
    
}
