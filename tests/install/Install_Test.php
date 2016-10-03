<?php
/**
 * @group Controller
 */
class Install_Test extends CIUnit_TestCase
{
    public function setUp()
    {
        $this->CI = set_controller('Test');
    }

    public function testTestController()
    {
        $this->CI->index();
    }
}
