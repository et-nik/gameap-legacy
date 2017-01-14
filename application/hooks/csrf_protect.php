<?php
/**
 * CSRF Protection Class
 */
class CSRF_Protection
{
	/**
	* Holds CI instance
	*
	* @var CI instance
	*/
	private $CI;
	
	/**
	* Name used to store token on session
	*
	* @var string
	*/
	private static $token_name = 'li_token';
	
	/**
	* Stores the token
	*
	* @var string
	*/
	private static $token;
	
	// -----------------------------------------------------------------------------------
	
	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	* Generates a CSRF token and stores it on session. Only one token per session is generated.
	* This must be tied to a post-controller hook, and before the hook
	* that calls the inject_tokens method().
	*
	* @return void
	* @author Ian Murray
	*/
	public function generate_token()
	{
		// Загружаем библиотеку session
		$this->CI->load->library('session');
		
		echo 'hello';
		
		if ($this->CI->session->userdata(self::$token_name) === FALSE) {
			// Генерируем слчайную строку и записываем её в сессию.
			self::$token = md5(uniqid() . microtime() . rand());
		
			$this->CI->session->set_userdata(self::$token_name, self::$token);
		} else {
			// записываем полученное значение в локальную переменную
			self::$token = $this->CI->session->userdata(self::$token_name);
		}
	}
	
	/**
	 * Validates a submitted token when POST request is made.
	 *
	 * @return void
	 * @author Ian Murray
	 */
	public function validate_tokens()
	{
	  // Если это post запрос?
	  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		// Если строка валидна
		$posted_token = $this->CI->input->post(self::$token_name);
		if ($posted_token === FALSE || $posted_token != $this->CI->session->userdata(self::$token_name)) {
		  // Если нет, формируем ошибку 400.
		  show_error('Request was invalid. Tokens did not match.', 400);
		}
	  }
	  
	}
	
	/**
	 * This injects hidden tags on all POST forms with the csrf token.
	 * Also injects meta headers in <head> of output (if exists) for easy access
	 * from JS frameworks.
	 *
	 * @return void
	 * @author Ian Murray
	 */
	public function inject_tokens()
	{
		$output = $this->CI->output->get_output();
		
		echo 'hello';

		  // Вставка в форму
		$output = preg_replace('/(<(form|FORM)[^>]*(method|METHOD)="(post|POST)"[^>]*>)/',
								 '$0<input type="hidden" name="' . self::$token_name . '" value="' . self::$token . '">',
								 $output);

		  // Вставка в <head>
		$output = preg_replace('/(<\/head>)/',
								 '<meta name="csrf-name" content="' . self::$token_name . '">' . "\n" . '<meta name="csrf-token" content="' . self::$token . '">' . "\n" . '$0',
								 $output);

		$this->CI->output->_display($output);
	}

}
