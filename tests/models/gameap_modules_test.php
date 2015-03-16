<?php

class Gameap_modules_test extends CIUnit_TestCase
{ 
	var $new_module = array(
						'short_name' 	=> 'module',
						'name'	 		=> 'My Module',
						'description' 	=> 'This is my module',
						'version' 		=> '1.0 beta',
						'developer' 	=> 'ET-NiK',
						'site' 			=> 'http://gameap.ru',
						'show_in_menu' 	=> 1,
	);
	
	public function setUp()
    {
		$this->CI->db->db_debug = 0;
    }
    
    public function test_add_module()
	{			
		$this->assertTrue($this->CI->gameap_modules->add_module($this->new_module));
		//~ $this->assertFalse($this->CI->gameap_modules->add_module($new_module));
	}
	
	public function test_clean_modules()
	{			
		$this->assertTrue($this->CI->gameap_modules->clean_modules());
		
		$modules_list = $this->CI->gameap_modules->get_modules_list();
		$this->assertTrue(empty($modules_list));
	}
	
	public function test_get_menu_modules()
	{			
		$this->assertTrue($this->CI->gameap_modules->add_module($this->new_module));
		
		$menu = $this->CI->gameap_modules->get_menu_modules();
		$this->assertTrue(is_array($menu));
		
		$this->assertEquals($this->CI->gameap_modules->menu[0]['short_name'], 'module');
		$this->assertEquals($this->CI->gameap_modules->menu[0]['name'], 'My Module');
	}
	
	public function test_get_modules_data()
	{			
		$modules_data = $this->CI->gameap_modules->get_modules_data();
		$this->assertTrue(is_array($modules_data));
		
		$this->assertEquals($this->CI->gameap_modules->modules_data[0]['short_name'], 'module');
		$this->assertEquals($this->CI->gameap_modules->modules_data[0]['name'], 'My Module');
		$this->assertEquals($this->CI->gameap_modules->modules_data[0]['description'], 'This is my module');
		$this->assertEquals($this->CI->gameap_modules->modules_data[0]['version'], '1.0 beta');
		$this->assertEquals($this->CI->gameap_modules->modules_data[0]['developer'], 'ET-NiK');
		$this->assertEquals($this->CI->gameap_modules->modules_data[0]['site'], 'http://gameap.ru');
	}
	
	public function test_get_modules_list()
	{			
		$this->assertTrue($this->CI->gameap_modules->add_module(array(
						'short_name' 	=> 'module2',
						'name'	 		=> 'My Module 2',
						'description' 	=> 'This is my module',
						'version' 		=> '1.0 beta',
						'developer' 	=> 'ET-NiK',
						'site' 			=> 'http://gameap.ru',
						'show_in_menu' 	=> 0,
		)));

		$this->assertTrue( (count($this->CI->gameap_modules->get_modules_list(false)) == 2) );
		$this->assertFalse( (count($this->CI->gameap_modules->get_modules_list(true)) == 1) );
	}
}
