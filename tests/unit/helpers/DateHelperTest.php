<?php

class DateHelperTest extends CodeIgniterTestCase {

    protected function _before()
    {
        date_default_timezone_set("Greenwich");
        $this->load->helper('date');
    }

    public function test_add_month_to_unix_time()
    {
        $this->assertEquals(1437816012, add_month_to_unix_time(1435224012)); // On month
        $this->assertEquals(1440408012, add_month_to_unix_time(1435224012, 2)); // Two month
    }

    public function test_add_year_to_unix_time()
    {
        $this->assertEquals(1466760093, add_year_to_unix_time(1435224093));
        $this->assertEquals(1530004954, add_year_to_unix_time(1498468954)); // leap-year
    }

    public function test_unix_to_human()
    {
        $this->assertEquals('25-06-2015 09:20', unix_to_human(1435224012));
        $this->assertEquals('25-06-2015 09:20:12', unix_to_human(1435224012, true));
    }

    public function test_human_to_unix()
    {
        $this->assertEquals('1435224012', human_to_unix('25-06-2015 09:20:12'));
        $this->assertEquals('1435224000', human_to_unix('25-06-2015 09:20'));
    }
}