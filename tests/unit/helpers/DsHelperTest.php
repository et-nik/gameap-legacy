<?php

/**
 * @group Helper
 */
class DsHelperTest extends CodeIgniterTestCase
{
    public function _before()
    {
        $this->load->helper('ds');
    }

    private function _get_ds_file_path($server = array())
    {
        return get_ds_file_path($server);
    }

    private function _get_file_protocol($server = array())
    {
        return get_file_protocol($server);
    }

    public function test_replace_shotcodes()
    {
        $server_data = array(
            'server_ip' 	=> '127.0.0.1',
            'server_port' 	=> '27015',
            'screen_name' 	=> 'gameap',
            'query_port' 	=> '27016',
            'rcon_port' 	=> '27017',

            'start_command' => 'start',
            'id' 			=> 1337,
            'dir' 			=> 'my_server',
            'work_path' 	=> '/home/servers',
            'start_code' 	=> 'cstrike',
            'su_user' 		=> 'nik',

            'cpu_limit' 		=> '50',
            'ram_limit' 		=> '256',
            'net_limit' 		=> '1000',

            'aliases'	=> array('maxplayers'),
            'aliases_list'	=> json_encode(array(array('alias' => 'maxplayers'))),
            'aliases_values'	=> array('maxplayers' => 32),
        );

        $this->assertEquals('start', replace_shotcodes('{command}', $server_data));
        $this->assertEquals('1337', replace_shotcodes('{id}', $server_data));
        $this->assertEquals('/home/servers', replace_shotcodes('{script_path}', $server_data));
        $this->assertEquals('/home/servers', replace_shotcodes('{work_path}', $server_data));
        $this->assertEquals('my_server', replace_shotcodes('{game_dir}', $server_data));
        $this->assertEquals('/home/servers/my_server', replace_shotcodes('{dir}', $server_data));
        $this->assertEquals('gameap', replace_shotcodes('{name}', $server_data));
        $this->assertEquals('127.0.0.1', replace_shotcodes('{ip}', $server_data));
        $this->assertEquals('27015', replace_shotcodes('{port}', $server_data));
        $this->assertEquals('27016', replace_shotcodes('{query_port}', $server_data));
        $this->assertEquals('27017', replace_shotcodes('{rcon_port}', $server_data));
        $this->assertEquals('nik', replace_shotcodes('{user}', $server_data));

        $this->assertEquals('32', replace_shotcodes('{maxplayers}', $server_data));

        $command = '{dir} {ip} {name} {query_port} {rcon_port} {maxplayers}';
        $this->assertEquals('/home/servers/my_server 127.0.0.1 gameap 27016 27017 32', replace_shotcodes($command, $server_data));
    }

    public function test_get_file_protocol()
    {
        $this->assertEquals('gdaemon', $this->_get_file_protocol(array('gdaemon_host' => 'localhost')));
    }

    public function test_get_ds_file_path()
    {
        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01',
            'dir' 		=> 'cstrike',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01//',
            'dir' 		=> '/cstrike/',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01',
            'dir' 		=> 'cstrike',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01//',
            'dir' 		=> '/cstrike/',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01',
            'dir' 		=> 'cstrike',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01//',
            'dir' 		=> '/cstrike/',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01',
            'dir' 		=> 'cstrike',
        )));

        $this->assertEquals('/home/serv01/cstrike/', $this->_get_ds_file_path(array(
            'work_path' => '/home/serv01//',
            'dir' 		=> '/cstrike/',
        )));
    }

    public function test_remote_file_exists()
    {
        $this->assertTrue(remote_file_exists('http://mirror.yandex.ru/debian/README'));
        $this->assertFalse(remote_file_exists('http://mirror.yandex.ru/debian/README_NOT_FOUND'));

        $this->assertTrue(remote_file_exists('ftp://mirror.yandex.ru/debian/README'));
        $this->assertFalse(remote_file_exists('ftp://mirror.yandex.ru/debian/README_NOT_FOUND'));
    }

    public function test_linux_slash_to_windows()
    {
        $this->assertEquals('C:\\servers', linux_slash_to_windows('C:/servers'));
        $this->assertEquals('C:\\servers', linux_slash_to_windows('C:/servers/'));
        $this->assertEquals('C:\\servers', linux_slash_to_windows('C:///servers//'));
        $this->assertEquals('C:\\servers', linux_slash_to_windows('C:///servers\\//'));
    }

    public function test_windows_slash_to_linux()
    {
        $this->assertEquals('/home/servers', windows_slash_to_linux('\\home\\servers'));
        $this->assertEquals('/home/servers', windows_slash_to_linux('\\home\\servers\\'));
        $this->assertEquals('/home/servers', windows_slash_to_linux('/home//\\servers\\//'));
    }
}
