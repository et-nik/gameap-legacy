<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * Модель работы с модулями
 *
 * Производит установку модулей и ...
 *
 * @package		Game AdminPanel
 * @category	Models
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.8
 */
 
class Gameap_modules extends CI_Model {

	var $modules_data 	= array();	// Массив со всеми данными модулей
	var $modules_list 	= array();	// Массив с именами модулей
	var $menu 			= array();	// Меню

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();

        $this->load->helper('cache');

        /* 
         * Получение списка модулей 
         * 
         * Когда база данных не инициализирована, получать список модулей нет необходимости 
         * Это может быть, например при установке панели.
         * 
        */
        if (file_exists(APPPATH . 'config/database.php')) {
			
			if (!isset($this->db)) {
				$this->load->database();
			}
			
			$this->get_modules_data();
			//~ $this->get_modules_list();
		}
    }
    
    // ----------------------------------------------------------
    
    /**
     * Новый модуль
     * 
     * @param arr
     * @return bool
     * 
     * Example:
     * 
     * $new_module = array(
     * 						'name'	 		=> 'My Module',
     * 						'file' 			=> 'module',
     * 						'version' 		=> '1.0 beta',
     * 						'developer' 	=> 'ET-NiK',
     * 						'site' 			=> 'http://hldm.org',
     * 						'information' 	=> 'This is my module',
     * );
     * 
     * $this->modules->add_module($new_module);
    */
    function add_module($data)
    {
        delete_in_cache('modules_data');

		if ($this->db->insert('modules', $data)) {
		    $this->modules_data[] = $data;
		    return true;
		}

		return false;
	}
	
	// ----------------------------------------------------------
	
	/**
	 * Очищает список модулей из базы данных
	*/
	function clean_modules()
    {
        delete_in_cache('gameap_hooks_callbacks');
        delete_in_cache('modules_data');

        $this->modules_data = array();
		$this->modules_list = array();
		$this->menu = array();
		return (bool)$this->db->empty_table('modules');
	}
	
	// ----------------------------------------------------------

    /**
     * Get All Modules data
     *
     * @param bool $where
     * @param int $limit
     * @return array
     */
    function get_modules_data($where = FALSE, $limit = 10000)
    {
		if ($modules_data = load_from_cache('modules_data')) {
            $this->modules_data = $modules_data;
            return $this->modules_data;
        }

        if (!$this->db->table_exists('modules')) {
            return [];
        }

        $this->db->order_by('name', 'asc');
		
		if (is_array($where)) {
			$query = $this->db->get_where('modules', $where, $limit);
		} else {
			$query = $this->db->get('modules');
		}
		
		if ($query->num_rows() > 0) {
			$this->modules_data = $query->result_array();
			save_to_cache('modules_data', $this->modules_data, 600);
			return $this->modules_data;
		} else {
			return [];
		}
	}
	
	// ----------------------------------------------------------
	
	/*
	 * Получает список модулей из базы данных
	 * 
	 * @return array
	 */
	function get_modules_list($for_menu = FALSE, $access = '')
    {
		if (!empty($this->modules_list)) {
			return $this->modules_list;
		}
		
		if (empty($this->modules_data)) {
			$this->get_modules_data();
		}

		foreach ($this->modules_data as $module) {

			if ($for_menu) {
				if ($module['show_in_menu']) {
					$this->modules_list[] = str_replace(' ', '_', strtolower($module['short_name']));
				}
				
			} else {
				$this->modules_list[] = str_replace(' ', '_', strtolower($module['short_name']));
			}
		}
		
		return $this->modules_list;
	}
	
	/**
     * Получение меню из модулей
     * 
    */
	function get_menu_modules()
    {
		if (empty($this->modules_data)) {
			$this->get_modules_data();
		}
		
		/* Определение прав пользователя */
		if (isset($this->users->auth_data) && $this->users->auth_data['is_admin']) {
			$access_level = 100;
		} elseif (isset($this->users->auth_data) && $this->users->auth_privileges['srv_global']) {
			$access_level = 90;
		} else {
			$access_level = 1;
		}
		
		$i = 0;
		foreach ($this->modules_data as $module) {
			
			
			if ($module['show_in_menu']) {
				
				if (strtolower($module['access']) == 'user' && $access_level < 1) {
					$i ++;
					continue;
				} elseif (strtolower($module['access']) == 'srv_global' && $access_level < 90)  {
					$i ++;
					continue;
				} elseif (strtolower($module['access']) == 'admin' && $access_level < 100)  {
					$i ++;
					continue;
				}
				
				
				$this->menu[$i]['short_name'] 	= strtolower($module['short_name']);	
				$this->menu[$i]['name'] 		= $module['name'];	
			}
			
			$i++;
		}

		return $this->menu;
		
	}
}
