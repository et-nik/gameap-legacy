<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Captcha_codeigniter extends CI_Driver {
	
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->library('session');
		$this->CI->load->helper('captcha');
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Получение html кода с капчей
	 */
	public function get_html()
	{
		// Слово для капчи
		$cap['word'] = rand(1000, 9999);
		
		// Создаем капчу
		$vals = array(
			'word'	 		=> $cap['word'],
			'img_path'	 	=> './uploads/security/',
			'img_url'	 	=> base_url('uploads/security') . '/',
			'font_path'	 	=> './system/fonts/DroidSans.ttf',
			'img_width'	 	=> 300,
			'img_height' 	=> 50,
			'expiration' 	=> 7200,
            'font_size'     => 25,
		);

		$captcha = create_captcha($vals);
		$this->CI->session->set_flashdata('captcha', $cap['word']);

		return $captcha['image'] . '<input type="text" id="captcha" name="captcha" />';
	}
	
	// -----------------------------------------------------------------
	
	/**
	 * Проверка капчи
	 */
	public function check()
	{
		return (bool)($this->CI->input->post('captcha') == $this->CI->session->flashdata('captcha'));
	}
}
