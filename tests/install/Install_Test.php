<?php
/**
 * @group Controller
 */
class Install_Test extends CIUnit_TestCase
{
    public function setUp()
    {
        rename('/home/travis/build/ET-NiK/GameAP/tests/database.php', '/home/travis/build/ET-NiK/GameAP/upload/application/config/database.php');
        $this->CI = set_controller('Test');
    }

    public function testTestController()
    {
        $this->CI->index();
    }
}
