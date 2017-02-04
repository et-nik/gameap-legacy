<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class Captcha_none extends CI_Driver {

    public function get_html()
    {
        return '';
    }

    public function check()
    {
        return true;
    }
}