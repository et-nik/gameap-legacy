<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2013-2016, Nikita Kuznetsov (http://hldm.org)
 * @license		http://www.gameap.ru/license.html
 * @link		http://www.gameap.ru
 * @filesource
*/

/**
 * Помошник для работы с кешем
 *
 * @package		Game AdminPanel
 * @category	Helpers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		1.1
*/

// ---------------------------------------------------------------------

/**
 * Сохранение в кеш
 *
 * @param string    $key ключ
 * @param array     $items данные для сохранения
 * @param int       $time время хранения данных в секундах
 * 
 * @return bool
 */
if ( ! function_exists('save_to_cache'))
{
	function save_to_cache($key, $items, $time = 60)
	{
        if ($this->cache->is_supported('memcached')) {
            return $this->cache->memcached->save($key, $items, $time);
        }
        elseif ($this->cache->is_supported('apc')) {
            return $this->cache->apc->save($key, $items, $time);
        }
        else {
            return $this->cache->file->save($key, $items, $time);
        } 
	}
}

// ---------------------------------------------------------------------

/**
 * Загрузка из кеша
 *
 * @param string    $key ключ
 * 
 * @return mixed
 */
if ( ! function_exists('load_from_cache'))
{
	function load_from_cache($key)
	{
        if ($this->cache->is_supported('memcached')) {
            return $this->cache->memcached->get($key);
        }
        elseif ($this->cache->is_supported('apc')) {
            return $this->cache->apc->get($key);
        }
        else {
            return $this->cache->file->get($key);
        }
	}
}

// ---------------------------------------------------------------------

/**
 * Удалить данные в кеше
 *
 * @param string    $key ключ
 *
 * @return bool
 */
if ( ! function_exists('delete_in_cache'))
{
	function delete_in_cache($key)
	{
        if ($this->cache->is_supported('memcached')) {
            return $this->cache->memcached->delete($key);
        }
        elseif ($this->cache->is_supported('apc')) {
            return $this->cache->apc->delete($key);
        }
        else {
            return $this->cache->file->delete($key);
        }
	}
}
