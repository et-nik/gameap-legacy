<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Game AdminPanel (АдминПанель)
 *
 * 
 *
 * @package		Game AdminPanel
 * @author		Nikita Kuznetsov (ET-NiK)
 * @copyright	Copyright (c) 2014, Nikita Kuznetsov (http://hldm.org)
 * @license		http://gameap.ru/license.html
 * @link		http://gameap.ru
 * @filesource	
 */
 
// ------------------------------------------------------------------------

/**
 * Хук заменяет языковые конструкции {lang_blablabla} на значения из 
 * языковых файлов.
 * Языковые файлы должны быть загружены в контролерах, которые
 * обрабатывают шаблоны.
 *
 * @package		Game AdminPanel
 * @category	Controllers
 * @category	Controllers
 * @author		Nikita Kuznetsov (ET-NiK)
 * @sinse		0.7
 */
class Tpl_replace
{
	/**
	* Holds CI instance
	*
	* @var CI instance
	*/
	private $CI;
	
	var $l_delim;
	var $r_delim;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		/* Получаем обрамляющие знаки. Обычно это { и } */
		$this->l_delim = $this->CI->parser->l_delim;
		$this->r_delim = $this->CI->parser->r_delim;
	}
	
	function _get_lang_line($matches)
	{
		return $this->CI->lang->line($matches[1]);
	}
	
	/* Замена языковых конструкций вида {lang_***} */
	public function parse_lang()
	{
		$output = $this->CI->output->get_output();
		
		$output = preg_replace_callback('/' . $this->l_delim . 'lang\_([a-z\_\-]*)' . $this->r_delim . '/', array($this,'_get_lang_line'), $output);
		$this->CI->output->set_output($output);
	}
	
	/* URL */
	public function parse_url()
	{
		$output = $this->CI->output->get_output();
		
		if ($this->CI->config->config['enable_query_strings']) {
			$output = str_replace($this->l_delim .  'site_url' . $this->r_delim, $this->CI->config->config['base_url'] . $this->CI->config->config['index_page'] . '?', $output);
		} else {
			$suffix = ($this->CI->config->config['url_suffix'] == FALSE) ? '' : $this->CI->config->config['url_suffix'];
			$output = str_replace($this->l_delim .  'site_url' . $this->r_delim, $this->CI->config->config['base_url'] . $suffix, $output);
		}
		
		$output = str_replace($this->l_delim .  'base_url' . $this->r_delim, $this->CI->config->config['base_url'], $output);
		

		$this->CI->output->set_output($output);
	}
	
	/* Templates */
	public function parse_template()
	{
		$output = $this->CI->output->get_output();
		
		$output = str_replace($this->l_delim . 'csrf_hash'. $this->r_delim, $this->CI->security->get_csrf_hash(), $output);
		$output = str_replace($this->l_delim . 'csrf_token_name'. $this->r_delim, $this->CI->security->get_csrf_token_name(), $output);
		
		if (isset($this->CI->config->config['template'])) {
			$output = str_replace($this->l_delim .  'template' . $this->r_delim, $this->CI->config->config['template'], $output);
		} else {
			$output = str_replace($this->l_delim .  'template' . $this->r_delim, 'default', $output);
		}
		
		if (isset($this->CI->config->config['style'])) {
			$output = str_replace($this->l_delim .  'style' . $this->r_delim, $this->CI->config->config['style'], $output);
		} else {
			$output = str_replace($this->l_delim .  'style' . $this->r_delim, 'default', $output);
		}
		
		$this->CI->output->set_output($output);
	}
}
