<?php

class Files_gdaemon_test extends CIUnit_TestCase
{
    public function setUp()
    {
        $this->CI->load->driver('files');
    }
    
    public function test_connect()
    {
        // GDaemon Test
        $this->CI->files->set_driver('gdaemon');
        
        $config = array(
			'hostname' => '78.155.209.112:31708', // Windows
			'login' => '',
			'password' => 'bw8IO1Ukck97Balu',
		);

		$this->assertInternalType('resource', $this->CI->files->connect($config));
    }

    public function test_upload()
    {
		$this->assertTrue($this->CI->files->upload(TESTSPATH . 'upload_file.txt', 'C:\servers\gameap_unittest\upload_file.txt'));
	}
	
	public function test_read_file()
	{
		$this->assertEquals('FILE_CONTENTS', trim($this->CI->files->read_file('C:\servers\gameap_unittest\File02.txt')));
		$this->assertEquals('UPLOAD_CONTENTS', trim($this->CI->files->read_file('C:\servers\gameap_unittest\upload_file.txt')));
	}
	
	public function test_write_file()
	{
		$this->assertTrue($this->CI->files->write_file('C:\servers\gameap_unittest\File01.txt', 'WRITED'));
		$this->assertEquals('WRITED', trim($this->CI->files->read_file('C:\servers\gameap_unittest\File01.txt')));
		
		$this->assertTrue($this->CI->files->write_file('C:\servers\gameap_unittest\File01.txt', 'WRITED01'));
		$this->assertEquals('WRITED01', trim($this->CI->files->read_file('C:\servers\gameap_unittest\File01.txt')));
	}
	
	public function test_download()
	{
		$this->assertInternalType('int', $this->CI->files->download('C:\servers\gameap_unittest\upload_file.txt', TESTSPATH . 'download_file.txt'));
		$this->assertTrue(file_exists(TESTSPATH . 'download_file.txt'));
		
		$this->assertEquals(filesize(TESTSPATH . 'upload_file.txt'), filesize(TESTSPATH . 'download_file.txt'));
		
		unlink(TESTSPATH . 'download_file.txt');
	}
	
	public function test_file_size()
	{
		//~ $this->assertEquals(13, $this->CI->files->file_size('C:\servers\gameap_unittest\File02.txt'));
	}

}
 
