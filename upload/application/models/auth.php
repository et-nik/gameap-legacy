<?php 

// Аутентификация пользователей


if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Model {
	
	var $user_id = false;
    var $user_login;
    var $user_password;
    var $user_servers = array();        // Серверы пользователя
    var $user_privileges = array();     // Привилегии пользователя
    var $servers_privileges = array();  // Привилегии на отдельные серверы
    var $user_data = array();           // Данные пользователя
	
	
}
