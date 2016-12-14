<?php
/**
 * @group Controller
 */
class Install_Test extends TestCase
{
    public function setUp()
    {
        $this->CI =& get_instance();
        rename('/home/travis/build/ET-NiK/GameAP/tests/database.php', '/home/travis/build/ET-NiK/GameAP/upload/application/config/database.php');
    }

    public function testTestController()
    {
        $this->request('GET', 'test');
    }
}
