<?php

/**
 * @group Helper
 */
class Modules_helper_test extends TestCase
{
    public function setUp()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper('modules');

        $this->CI->gameap_modules->add_module([
            'short_name'    => 'cron',
            'name'          => 'Cron',
            'description'   => 'Cron',
            'version'       => '1.0',
        ]);
    }

    public function test_module_exists()
    {
        $this->assertTrue(module_exists('cron'));
        $this->assertFalse(module_exists('unknown_module'));
    }
}