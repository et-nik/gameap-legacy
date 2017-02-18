<?php

class OldGamesTest extends CodeIgniterTestCase
{
    private $CI;

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
        $this->CI =& get_instance();

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

        $games_list = $this->CI->games->get_games_list(array('code' => 'valve'));

        $this->assertTrue(is_array($games_list));
        $this->assertEquals('ftp://127.0.0.1/files/', $this->CI->games->games_list[0]['remote_repository']);
        $this->assertEquals('ftp://127.0.0.1/files/', $games_list[0]['remote_repository']);
    }

    public function test_get_games_list()
    {
        $games_list = $this->CI->games->get_games_list(array('code' => 'valve'));
        $this->assertTrue(is_array($games_list));

        $this->assertEquals('valve', $this->CI->games->games_list[0]['code']);
        $this->assertEquals('valve', $this->CI->games->games_list[0]['start_code']);
        $this->assertEquals('Half-Life', $this->CI->games->games_list[0]['name']);
        $this->assertEquals('GoldSource', $this->CI->games->games_list[0]['engine']);
        $this->assertEquals('1', $this->CI->games->games_list[0]['engine_version']);
        $this->assertEquals(90, $this->CI->games->games_list[0]['app_id']);
        $this->assertEquals('+game valve', $this->CI->games->games_list[0]['app_set_config']);
        $this->assertEquals('/home/ftp/files/', $this->CI->games->games_list[0]['local_repository']);
        $this->assertEquals('ftp://127.0.0.1/files/', $this->CI->games->games_list[0]['remote_repository']);

        $this->assertEquals('valve', $games_list[0]['code']);
        $this->assertEquals('valve', $games_list[0]['start_code']);
        $this->assertEquals('Half-Life', $games_list[0]['name']);
        $this->assertEquals('GoldSource', $games_list[0]['engine']);
        $this->assertEquals('1', $games_list[0]['engine_version']);
        $this->assertEquals(90, $games_list[0]['app_id']);
        $this->assertEquals('+game valve', $games_list[0]['app_set_config']);
        $this->assertEquals('/home/ftp/files/', $games_list[0]['local_repository']);
        $this->assertEquals('ftp://127.0.0.1/files/', $games_list[0]['remote_repository']);
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

        $this->assertEmpty($this->CI->games->get_games_list(array('code' => 'valve')));
        $this->assertEmpty($this->CI->games->get_games_list(array('code' => 'cstrike')));
    }
}
