<?php
/**
 * @group Controller
 */
class AAATest_Test extends CIUnit_TestCase
{
    public function setUp()
    {
        $this->CI = set_controller('Test');
        $this->CI->index();
    }
}
