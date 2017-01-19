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
 * @category    Helpers
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
        $CI =& get_instance();

        if ($CI->cache->is_supported('redis')) {
            return $CI->cache->redis->save($key, $items, $time);
        }
        elseif ($CI->cache->is_supported('memcached')) {
            return $CI->cache->memcached->save($key, $items, $time);
        }
        elseif ($CI->cache->is_supported('apc')) {
            return $CI->cache->apc->save($key, $items, $time);
        }
        else {
            return $CI->cache->file->save($key, $items, $time);
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
        $CI =& get_instance();

        if ($CI->cache->is_supported('redis')) {
            return $CI->cache->redis->get($key);
        }
        elseif ($CI->cache->is_supported('memcached')) {
            return $CI->cache->memcached->get($key);
        }
        elseif ($CI->cache->is_supported('apc')) {
            return $CI->cache->apc->get($key);
        }
        else {
            return $CI->cache->file->get($key);
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
        $CI =& get_instance();

        if ($CI->cache->is_supported('redis')) {
            return $CI->cache->redis->delete($key);
        }
        elseif ($CI->cache->is_supported('memcached')) {
            return $CI->cache->memcached->delete($key);
        }
        elseif ($CI->cache->is_supported('apc')) {
            return $CI->cache->apc->delete($key);
        }
        else {
            return $CI->cache->file->delete($key);
        }
	}
}
