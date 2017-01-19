<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * @package     Game AdminPanel
 * @author      Nikita Kuznetsov (ET-NiK)
 * @copyright   Copyright (c) 2013-2016, Nikita Kuznetsov (http://hldm.org)
 * @license     http://www.gameap.ru/license.html
 * @link        http://www.gameap.ru
 * @filesource
*/

/**
 * Библиотека для работы с хуками GameAP
 *
 * @package     Game AdminPanel
 * @category    Libraries
 * @author      Nikita Kuznetsov (ET-NiK)
 * @sinse       1.1
*/
class Gameap_hooks {

    private $_hooks_callbacks = array();
    private $_files_loaded = array();

    public static $registry;
    
    // -----------------------------------------------------------------
    
    public function __construct()
    {
        $CI =& get_instance();

        $CI->load->driver('cache');
        $CI->load->helper('directory');

        $this->_init_hooks();
    }

    // -----------------------------------------------------------------

    private function _init_hooks()
    {
        $cache_hooks_callbacks = load_from_cache('gameap_hooks_callbacks');
        
        if ($cache_hooks_callbacks) {
            $this->_hooks_callbacks = $cache_hooks_callbacks;
            return true;
        }
        
        if ($map = directory_map(APPPATH . 'modules')) {
            foreach($map as $modname => $dircontent) {
                if (!is_array($dircontent)) {
                    /* Это файл */
                    continue;
                }
                
                if (!is_dir(APPPATH . 'modules/' . $modname)) {
                    /* Это не директория */
                    continue;
                }

                $modhooks = false;
                if (file_exists(APPPATH . 'modules/' . $modname . '/hooks.json')) {
                    $modhooks = json_decode(file_get_contents(APPPATH . 'modules/' . $modname . '/hooks.json'), true);
                }

                if (!$modhooks) {
                    continue;
                }

                foreach ($modhooks as $single_hook) {
                    // $this->_hooks[] = array('module' => $modname, 'hook_id' => $single_hook[0], 'callback' => $single_hook[1]);
                    $this->_hooks_callbacks[$single_hook[0]][] = $modname . '/' . $single_hook[1];
                }
            }
        }
        
        save_to_cache('gameap_hooks_callbacks', $this->_hooks_callbacks, 600);
        return true;
    }

    // -----------------------------------------------------------------

    /**
     * Загрузка хука
     */
    private function _load_file($file)
    {
        if (in_array($file, $this->_files_loaded)) {
            // already loaded
            return true;
        }

        if (!file_exists($file)) {
            return false;
        }

        $this->_files_loaded[] = $file;
        include $file;

        return true;
    }
    
    // -----------------------------------------------------------------

    /**
     * Выполнение хука
     *
     * @param string    $hook_id    Название хука
     * @param array     $params     Параметры
     */
    public function run($hook_id, $params = array())
    {
        if (!isset($this->_hooks_callbacks[$hook_id])) {
            return;
        }

        if (count($this->_hooks_callbacks[$hook_id]) <= 0) {
            return;
        }

        foreach ($this->_hooks_callbacks[$hook_id] as &$hook) {
            $exp = explode('/', $hook);

            $module_name        = $exp[0];
            $file_name          = str_replace('.php', '', $exp[1]);
            $alias              = strtolower($file_name);
            $class_name         = 'GH_' . ucfirst($alias);
            $method_name        = $exp[2];

            $file = APPPATH . 'modules' . '/' . $module_name . '/hooks/' . $file_name . '.php';
        
            if (!$this->_load_file($file)) {
                continue;
            }

            if (!class_exists($class_name)) {
                continue;
            }
            
            self::$registry[$alias] = new $class_name();

            if (method_exists(self::$registry[$alias], $method_name)) {
                return self::$registry[$alias]->$method_name($params);
            }
        }
    }
}
