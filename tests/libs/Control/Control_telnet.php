<?php

class Control_telnet_test extends CIUnit_TestCase
{
    public function setUp()
    { 
        $this->CI->load->driver('control');
        
        $this->CI->control->set_data(array('os' => 'linux', 'path' => '/home'));
		$this->CI->control->set_driver('telnet');
	}
	
	public function test_connect()
	{
		$this->assertInternalType('resource', $this->CI->control->connect('127.0.0.1', 23));
	}
	
	public function test_auth()
	{
		$this->assertTrue($this->CI->control->auth('travis', '1234'));
	}
	
	public function test_command()
	{
		//~ $this->assertEquals('travis', trim($this->CI->control->command('whoami')));
		//~ $this->assertEquals('travis', trim($this->CI->control->command('whoami', '/home')));
		
		//~ $this->assertEquals('/', trim($this->CI->control->command('pwd', '/')));
		//~ $this->assertEquals('/home',  trim($this->CI->control->command('pwd', '/home')));
		//~ $this->assertEquals('/home/travis/build/ET-NiK/GameAP',  trim($this->CI->control->command('pwd', '/home/travis/build/ET-NiK/GameAP')));
	}
}
