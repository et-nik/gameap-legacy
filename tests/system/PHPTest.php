<?php

/**
 * @group System
 */

class PHPTest extends TestCase
{
    function setUp()
    {
        // Setup
    }

    public function testFunctionJsonEncode()
    {
        $this->assertTrue(function_exists('json_encode'));
    }

    public function testPhpVersion()
    {
        $this->assertTrue(phpversion() > 5.1);
    }
}
