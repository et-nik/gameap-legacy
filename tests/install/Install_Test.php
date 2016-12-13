<?php
/**
 * @group Controller
 */
class Install_Test extends TestCase
{
    public function setUp()
    {
        $this->CI =& get_instance();
        
        rename('/GameAP/tests/database.php', '/home/travis/build/ET-NiK/GameAP/upload/application/config/database.php');
        $this->request('GET', 'test');
    }

    public function testTestController()
    {
        $this->CI->index();
    }
}
