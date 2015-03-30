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
			'hostname' => 'localhost',
			'port' => 31708,
			'login' => '',
			'password' => '1234567890123456',
		);

		$this->assertTrue($this->CI->files->connect($config));
    }

    public function test_upload()
    {
		$this->assertTrue($this->CI->files->upload(TESTSPATH . 'upload_file.txt', 'Files/upload_file.txt'));
	}
	
	public function test_read_file()
	{
		$this->assertEquals('FILE_CONTENTS', trim($this->CI->files->read_file('Files/File02.txt')));
		$this->assertEquals('UPLOAD_CONTENTS', trim($this->CI->files->read_file('Files/upload_file.txt')));
	}
	
	public function test_write_file()
	{
		$this->assertTrue($this->CI->files->write_file('Files/File01.txt', 'WRITED'));
		$this->assertEquals('WRITED', trim($this->CI->files->read_file('Files/File01.txt')));
		
		$this->assertTrue($this->CI->files->write_file('Files/File01.txt', 'WRITED01'));
		$this->assertEquals('WRITED01', trim($this->CI->files->read_file('Files/File01.txt')));
	}
	
	public function test_download()
	{
		$this->assertInternalType('bool', $this->CI->files->download('Files/upload_file.txt', TESTSPATH . 'download_file.txt'));
		$this->assertTrue(file_exists(TESTSPATH . 'download_file.txt'));
		
		$this->assertEquals(filesize(TESTSPATH . 'upload_file.txt'), filesize(TESTSPATH . 'download_file.txt'));
		
		unlink(TESTSPATH . 'download_file.txt');
	}
	
	public function test_file_size()
	{
		//~ $this->assertEquals(13, $this->CI->files->file_size('Files/File02.txt'));
	}

}
 
