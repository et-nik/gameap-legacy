<?php

class Game_types_test extends TestCase { 
	
	public function setUp()
    {
        $this->CI =& get_instance();
        
        $this->CI->load->database();
        $this->CI->load->model('servers/game_types');
    }
    
    public function test_add_game_type()
	{
	}
	
    public function test_delete_game_type()
	{
	}
	
    public function test_edit_game_type()
	{
	}
	
    public function test_get_gametypes_list()
	{
	}
	
    public function test_tpl_data_game_types()
	{
	}
	
    public function test_live()
	{
	}

}
