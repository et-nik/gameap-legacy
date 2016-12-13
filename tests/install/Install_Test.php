<?php
/**
 * @group Controller
 */
class Install_Test extends TestCase
{
    public function setUp()
    {
        $this->CI =& get_instance();
        
        rename('/GameAP/tests/database.php', '/GameAP/upload/application/config/database.php');
        $this->CI = set_controller('Test');
    }

    public function testTestController()
    {
        $this->CI->index();
    }
}
