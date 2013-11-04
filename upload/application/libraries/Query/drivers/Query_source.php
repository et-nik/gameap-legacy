<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Query_source extends CI_Driver {
	
		// ------------------------------------------------------------------------
	
	/**
	 * Получение списка игроков на сервере
	*/
	function get_players($host, $port)
	{
		return $this->CI->query->goldsource->get_players($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение информации о сервере
	*/
	function get_info($host, $port)
	{
		return $this->CI->query->goldsource->get_info($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Получение переменных сервера
	*/
	function get_rules($host, $port)
	{
		return $this->CI->query->goldsource->get_rules($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Статус сервера
	*/
	function get_status($host, $port)
	{
		return (bool)$this->CI->query->goldsource->A2S_INFO($host, $port);
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Пинг сервера
	*/
	function ping($host, $port)
	{
		return $this->CI->query->goldsource->ping($host, $port);
	}
}
