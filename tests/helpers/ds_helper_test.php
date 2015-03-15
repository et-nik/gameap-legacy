<?php

/**
 * @group Helper
 */
class Ds_helper_test extends CIUnit_TestCase
{
    public function setUp()
    {
        $this->CI->load->helper('ds');
    }

    public function test_replace_shotcodes()
    {
		$server_data = array(
			'server_ip' => '127.0.0.1',
			'screen_name' => 'gameap',
		);
		
		$command = '{ip} {name}';
        $this->assertEquals('127.0.0.1 gameap', replace_shotcodes($command, $server_data));
    }
}
