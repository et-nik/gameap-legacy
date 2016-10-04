<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        include_once FCPATH . '../../../install_gameap/db.php';

        $this->load->library('migration');
        if (!$this->migration->latest()) {
			show_error($this->migration->error_string());
		}
    }

    public function index()
    {
        $this->output->append_output("DB Schema loaded!");
    }
}
