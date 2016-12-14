<?php

class TestCase extends CIPHPUnitTestCase
{
    private static $migrate = false;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Run migrations once
        if (! self::$migrate)
        {
            $CI =& get_instance();
            $CI->load->database();

            include FCPATH . "install_gameap/db.php";
            $gameapInstall = new Gameap_install();
            $gameapInstall->dbSetUp();

            // include FCPATH . "install_gameap/demo_data.php";

            /*
            $CI->load->library('migration');
            if ($CI->migration->current() === false) {
                throw new RuntimeException($CI->migration->error_string());
            }
            */

            self::$migrate = true;
        }
    }
}
