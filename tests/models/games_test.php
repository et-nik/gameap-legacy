<?php

class Games_test extends CIUnit_TestCase { 

	var $new_game_0 = array(
					'code' 				=> 'cstrike',
					'start_code' 		=> 'cstrike',
					'name' 				=> 'Counter-Strike',
					'engine' 			=> 'GoldSource',
					'engine_version' 	=> '1',
					'app_id' 			=> 90,
					'app_set_config' 	=> '+game cstrike',
					'local_repository' 	=> '/home/ftp/files/',
					'remote_repository' => 'ftp://localhost/files/',
	);
	
	var $new_game_1 = array(
					'code' 				=> 'valve',
					'start_code' 		=> 'valve',
					'name' 				=> 'Half-Life',
					'engine' 			=> 'GoldSource',
					'engine_version' 	=> '1',
					'app_id' 			=> 90,
					'app_set_config' 	=> '+game valve',
					'local_repository' 	=> '/home/ftp/files/',
					'remote_repository' => 'ftp://localhost/files/',
	);
	
	public function setUp()
	{
		$this->CI->load->database();
		$this->CI->db->db_debug = 0;
		$this->CI->load->model('servers/games');
	}
	
	public function test_add_game()
	{
		$this->assertTrue($this->CI->games->add_game($this->new_game_0));
		$this->assertFalse($this->CI->games->add_game($this->new_game_0));
	}
	
	public function test_live()
	{
		$this->assertTrue($this->CI->games->live('cstrike'));
		$this->assertFalse($this->CI->games->live('blablablabla'));
	}
	
	public function test_edit_game()
	{
		$this->assertTrue($this->CI->games->add_game($this->new_game_1));
		
		$this->assertTrue(is_array($this->CI->games->get_games_list(array('code' => 'valve'))));
		$this->assertEquals($this->CI->games->games_list[0]['remote_repository'], 'ftp://localhost/files/');

		$this->assertTrue($this->CI->games->edit_game('valve', array('remote_repository' => 'ftp://127.0.0.1/files/')));
		
		$this->assertTrue(is_array($this->CI->games->get_games_list(array('code' => 'valve'))));
		$this->assertEquals($this->CI->games->games_list[0]['remote_repository'], 'ftp://127.0.0.1/files/');
	}
	
	public function test_get_games_list()
	{
		$this->assertTrue(is_array($this->CI->games->get_games_list(array('code' => 'valve'))));
		
		$this->assertEquals($this->CI->games->games_list[0]['code'], 'valve');
		$this->assertEquals($this->CI->games->games_list[0]['start_code'], 'valve');
		$this->assertEquals($this->CI->games->games_list[0]['name'], 'Half-Life');
		$this->assertEquals($this->CI->games->games_list[0]['engine'], 'GoldSource');
		$this->assertEquals($this->CI->games->games_list[0]['engine_version'], '1');
		$this->assertEquals($this->CI->games->games_list[0]['app_id'], 90);
		$this->assertEquals($this->CI->games->games_list[0]['app_set_config'], '+game valve');
		$this->assertEquals($this->CI->games->games_list[0]['local_repository'], '/home/ftp/files/');
		$this->assertEquals($this->CI->games->games_list[0]['remote_repository'], 'ftp://127.0.0.1/files/');
	}
	
	public function test_get_active_games_list()
	{
		
	}
	
	public function test_tpl_data_games()
	{
	}
	
	public function test_game_name_by_code()
	{
		$this->assertEquals($this->CI->games->game_name_by_code('valve'), 'Half-Life');
		$this->assertFalse($this->CI->games->game_name_by_code('valve00000'));
	}
	
	public function test_delete_game()
	{
		$this->assertTrue($this->CI->games->delete_game('valve'));
		$this->assertTrue($this->CI->games->delete_game('cstrike'));
		
		$this->assertTrue(empty($this->CI->games->get_games_list(array('code' => 'valve'))));
		$this->assertTrue(empty($this->CI->games->get_games_list(array('code' => 'cstrike'))));
	}
}
