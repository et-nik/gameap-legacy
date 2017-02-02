<?php

/**
 * @group Helper
 */
class ModulesHelperTest extends CodeIgniterTestCase
{
    public function _before()
    {
        $this->load->helper('modules');

        $this->gameap_modules->add_module([
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