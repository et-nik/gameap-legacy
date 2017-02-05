<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 *
 *
 * @package     Game AdminPanel
 * @author      Nikita Kuznetsov (ET-NiK)
 * @copyright   Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license     http://www.gameap.ru/license.html
 * @link        http://www.gameap.ru
 * @filesource
*/

use \Myth\Controllers\BaseController;

/**
 * Управление модулями
 *
 * Контроллер управляет выделенными серверами, игровыми серверами,
 * играми и игровыми модификациями.
 * Позволяет производить следующие действия: добавление, редактирование,
 * удаление, дублирование игровой модификации.
 *
 * Установку игровых серверов производит модуль cron, adm_servers лишь
 * делает запись о том, что сервер нужно установить.
 *
 * Переустановка игровых серверов делается заданием значения 0 поля
 * installed таблицы servers.
 *
 * @package     Game AdminPanel
 * @category    Controllers
 * @author      Nikita Kuznetsov (ET-NiK)
 * @sinse       0.8
 */

class Adm_modules extends BaseController {

    var $tpl = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('users');

        $this->lang->load('adm_modules');

        if ($this->users->check_user()) {

            /* Есть ли у пользователя права */
            if (false == $this->users->auth_data['is_admin']) {
                show_404();
            }

            //Base Template
            $this->tpl['title']     = lang('adm_modules_title_index');
            $this->tpl['heading']   = lang('adm_modules_heading_index');
            $this->tpl['content']   = '';

            $this->tpl['menu'] = $this->parser->parse('menu.html', $this->tpl, true);
            $this->tpl['profile'] = $this->parser->parse('profile.html', $this->users->tpl_userdata(), true);
        } else {
            redirect('auth/in');
        }
    }

    // ---------------------------------------------------------------------

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

    // ---------------------------------------------------------------------

    function _update_list()
    {
        $this->load->helper('directory');

        if ($map = directory_map(APPPATH . 'modules', 2)) {

            /* Получение списка старых модулей */
            $old_modules_list = $this->gameap_modules->get_modules_list();

            /* Очищаем список модулей из базы */
            $this->gameap_modules->clean_modules();

            foreach($map as $mod_dir => $mod_files) {

                // Trim / from end
                $mod_dir = substr($mod_dir, 0, strlen($mod_dir)-1);

                if (!is_array($mod_files)) {
                    /* Это файл */
                    continue;
                }

                if (!is_dir(APPPATH . 'modules/' . $mod_dir)) {
                    /* Это не директория */
                    continue;
                }

                /* Если модуль небыл установлен до этого и существуют правила установки */
                if (!in_array($mod_dir, $old_modules_list) && file_exists(APPPATH . 'modules/' . $mod_dir . '/module_install.php')) {
                    /* Инклудим файл с правилами установки */
                    include_once APPPATH . 'modules/' . $mod_dir . '/module_install.php';
                }

                /* Поиск файла с информацией о модулей */
                if (file_exists(APPPATH . 'modules/' . $mod_dir . '/module_info.php')) {

                    $module_info = array();
                    $sql_data = array();

                    /* Инклудим файл с инфой */
                    include_once APPPATH . 'modules/' . $mod_dir . '/module_info.php';

                    $sql_data['short_name']     = $mod_dir;
                    $sql_data['name']           = (isset($module_info['name']))         ? $module_info['name'] : '';
                    $sql_data['description']    = (isset($module_info['description']))  ? $module_info['description'] : '';
                    $sql_data['cron_script']    = (isset($module_info['cron_script']))  ? $module_info['cron_script'] : '';
                    $sql_data['version']        = (isset($module_info['version']))      ? $module_info['version'] : '0.0.0';
                    $sql_data['update_info']    = (isset($module_info['update_info']))  ? $module_info['update_info'] : '';
                    $sql_data['show_in_menu']   = (isset($module_info['show_in_menu'])) ? (int)(bool)$module_info['show_in_menu'] : 0;
                    $sql_data['access']         = (isset($module_info['access']))       ? $module_info['access'] : '';
                    $sql_data['developer']      = (isset($module_info['developer']))    ? $module_info['developer'] : '';
                    $sql_data['site']           = (isset($module_info['site']))         ? $module_info['site'] : '';
                    $sql_data['email']          = (isset($module_info['email']))        ? $module_info['email'] : '';
                    $sql_data['copyright']      = (isset($module_info['copyright']))    ? $module_info['copyright'] : '';
                    $sql_data['license']        = (isset($module_info['license']))      ? $module_info['license'] : '';

                    $this->gameap_modules->add_module($sql_data);

                }
            }

            return true;
        } else {
            return false;
        }

    }

    // ---------------------------------------------------------------------

    public function index()
    {
        $local_tpl['modules_list'] = ($this->gameap_modules->modules_data) ? $this->gameap_modules->modules_data : array();

        $this->tpl['content'] = $this->parser->parse('adm_modules/modules_list.html', $local_tpl, true);
        $this->parser->parse('main.html', $this->tpl);
    }

    // ---------------------------------------------------------------------

    function info($module_id)
    {

        if (!$this->gameap_modules->modules_data) {
            $this->_show_message('Module not found');
            return false;
        }

        $local_tpl = array();
        $module_found = false;

        /*
         * Т.к список модулей уже получен, то
         * нужно лишь прогнать массив и найти в нем
         * нужный нам модуль
         */
        foreach($this->gameap_modules->modules_data as $module) {
            if ($module_id == $module['short_name']) {
                $module_found = true;

                $local_tpl['module_name']           = $module['name'];
                $local_tpl['module_description']    = $module['description'];
                $local_tpl['module_version']        = $module['version'];
                $local_tpl['module_copyright']  = $module['copyright'];
                $local_tpl['module_license']        = auto_link($module['license']);
                $local_tpl['module_developer']  = $module['developer'];
                $local_tpl['module_email']      = $module['email'];
                $local_tpl['module_site']           = $module['site'];

                break;
            }
        }

        if (!$module_found) {
            $this->_show_message('Module not found');
            return false;
        }

        $this->tpl['content'] = $this->parser->parse('adm_modules/module_info.html', $local_tpl, true);

        $this->parser->parse('main.html', $this->tpl);
    }

    // ---------------------------------------------------------------------

    public function install()
    {
        $this->tpl['content'] = 'Функция в разработке';
        $this->parser->parse('main.html', $this->tpl);
    }

    // ---------------------------------------------------------------------

    public function update_list()
    {
        if ($this->_update_list()) {
            $this->_show_message(lang('adm_modules_update_success'), site_url('adm_modules'));
            return true;
        } else {
            $this->_show_message(lang('adm_modules_update_failure'));
            return false;
        }
    }

}
