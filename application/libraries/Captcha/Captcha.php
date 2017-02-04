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
 * Работа с капчами
 *
 * @package		Game AdminPanel
 * @category	Driver Libraries
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.9.4
*/
class Captcha extends CI_Driver_Library {
	
	protected 	$driver = '';
	private 	$config = array();
	private 	$CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('string');
		$this->CI->lang->load('server_command');
		
		$this->CI->config->load('drivers');
        $drivers = $this->CI->config->item('drivers');
        $this->valid_drivers = $drivers['captcha'];

		if (!in_array($this->CI->config->item('captcha_driver'), $this->valid_drivers)) {
			$this->driver 		= 'codeigniter';
		} else {
			$this->driver 		= $this->CI->config->item('captcha_driver');
		}
	}

	// -----------------------------------------------------------------
	
	/**
	 * Получение html кода с капчей
	 */
	public function get_html()
	{
		return $this->{$this->driver}->get_html();
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Проверка капчи
	 */
	public function check()
	{
		return $this->{$this->driver}->check();
	}
	
}
	
/* End of file Captcha.php */
/* Location: ./application/libraries/Captcha/Captcha.php */
