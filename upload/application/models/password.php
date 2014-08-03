<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password extends CI_Model {

	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	function encryption($user_password, $user_data)
    {
		$password_md5 = md5($user_password . $user_data['login']);
		$password_md5 = md5($user_password . $password_md5);
		$password_md5 = md5($user_password . $user_data['login'] . $password_md5);
		$password_md5 = md5($user_password . $user_data['reg_date'] . $password_md5);
		$password_md5 = md5($user_password . $user_data['login'] . $user_data['login'] . $user_data['reg_date'] . $password_md5);
		
		return $password_md5;		
	}
	
}
