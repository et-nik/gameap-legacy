<?php

class OldDedicatedServersTest extends CodeIgniterTestCase
{
    private $CI;

    public function setUp()
    {
        $this->CI =& get_instance();

        $this->CI->load->database();
        $this->CI->load->model('servers/dedicated_servers');
    }

    function test_add_dedicated_server()
    {
        // DS 1

        $sql_data = array(
            'name' => 'ds1',
            'os' => 'linux',
            'location' => 'russia',
            'provider' => 'gameap',
            'ip' => json_encode(array('127.0.0.1', '1.3.3.7')),
        );

        $this->assertTrue($this->CI->dedicated_servers->add_dedicated_server($sql_data));


        // DS 2
        $sql_data = array(
            'name' => 'ds2',
            'os' => 'windows',
            'location' => 'russia',
            'provider' => 'gameap',
            'ip' => json_encode(array('127.0.0.5')),
        );

        $this->assertTrue($this->CI->dedicated_servers->add_dedicated_server($sql_data));

        // DS 3
        $sql_data = array(
            'name' => 'ds3',
            'os' => 'windows',
            'location' => 'russia',
            'provider' => 'gameap',
            'ip' => json_encode(array('127.0.0.10')),
        );

        $this->assertTrue($this->CI->dedicated_servers->add_dedicated_server($sql_data));
    }

    function test_get_ds_list()
    {
        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_list()) );
        $this->assertCount(3, $this->CI->dedicated_servers->ds_list);
        $this->assertEquals('ds3', $this->CI->dedicated_servers->ds_list[2]['name']);

        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_list(array('id' => 2))) );
        $this->assertCount(1, $this->CI->dedicated_servers->ds_list);
        $this->assertEquals('ds2', $this->CI->dedicated_servers->ds_list[0]['name']);
        $this->assertFalse( isset($this->CI->dedicated_servers->ds_list[1]) );

        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_list(array('name' => 'ds1'))) );
        $this->assertCount(1, $this->CI->dedicated_servers->ds_list);
        $this->assertEquals('ds1', $this->CI->dedicated_servers->ds_list[0]['name']);
        $this->assertFalse( isset($this->CI->dedicated_servers->ds_list[1]) );
    }

    function test_del_dedicated_server()
    {
        $this->assertTrue( $this->CI->dedicated_servers->del_dedicated_server(3) );

        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_list()) );
        $this->assertCount(2, $this->CI->dedicated_servers->ds_list);
    }

    function test_ds_live()
    {
        $this->assertFalse( $this->CI->dedicated_servers->ds_live() );
        $this->assertTrue( $this->CI->dedicated_servers->ds_live(1) );
        $this->assertTrue( $this->CI->dedicated_servers->ds_live(2) );
        $this->assertFalse( $this->CI->dedicated_servers->ds_live(3) );
    }

    function test_get_ds_data()
    {
        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_data(1)) );
        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_data(2)) );

        $this->assertEquals('ds2', $this->CI->dedicated_servers->ds_list[0]['name']);

        $this->assertFalse( is_array($this->CI->dedicated_servers->get_ds_data(3)) );
    }

    function test_edit_dedicated_server()
    {
        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_data(1)) );

        $this->assertEquals('ds1', $this->CI->dedicated_servers->ds_list[0]['name']);
        $this->assertEquals('linux', $this->CI->dedicated_servers->ds_list[0]['os']);

        $sql_data = array(
            'name' => 'ds1_edited',
            'os' => 'windows',
            'location' => 'russia',
            'provider' => 'gameap',
            'ip' => json_encode(array('127.0.0.1', '1.3.3.7')),
        );

        $this->assertTrue( $this->CI->dedicated_servers->edit_dedicated_server(1, $sql_data) );

        $this->assertTrue( is_array($this->CI->dedicated_servers->get_ds_data(1)) );

        $this->assertCount(1, $this->CI->dedicated_servers->ds_list);

        $this->assertEquals('ds1_edited', $this->CI->dedicated_servers->ds_list[0]['name']);
        $this->assertEquals('windows', $this->CI->dedicated_servers->ds_list[0]['os']);
    }

    function test_select_ids()
    {
        $this->assertCount(2, $this->CI->dedicated_servers->get_ds_list());

        $this->CI->dedicated_servers->select_ids(array(1, 2));
        $this->assertCount(2, $this->CI->dedicated_servers->get_ds_list());
        $this->assertEquals('ds1_edited', $this->CI->dedicated_servers->ds_list[0]['name']);
        $this->assertEquals('ds2', $this->CI->dedicated_servers->ds_list[1]['name']);

        $this->CI->dedicated_servers->select_ids(array(1));
        $this->assertCount(1, $this->CI->dedicated_servers->get_ds_list());
        $this->assertEquals('ds1_edited', $this->CI->dedicated_servers->ds_list[0]['name']);

        $this->CI->dedicated_servers->select_ids(array(2));
        $this->assertCount(1, $this->CI->dedicated_servers->get_ds_list());
        $this->assertEquals('ds2', $this->CI->dedicated_servers->ds_list[0]['name']);

        $this->CI->dedicated_servers->select_ids(array(3, 7, 8));
        $this->assertCount(0, $this->CI->dedicated_servers->get_ds_list());

        //~ $this->CI->dedicated_servers->select_ids(array(3, 7, 8));
        //~ $this->assertCount(0, $this->CI->dedicated_servers->get_ds_list(array('id' => 1)));
        //~ $this->assertCount(0, $this->CI->dedicated_servers->get_ds_list(array('id' => 2)));
    }

    function test_update_modules_data()
    {

    }

    function test_tpl_data_ds()
    {

    }

    function test_check_ports()
    {

    }
}