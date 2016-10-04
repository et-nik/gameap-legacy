<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        include FCPATH . '../../../install_gameap/db.php';

        if (!$this->db->table_exists('migrations')) {
			$this->dbforge->add_field(array(
				'version' => array('type' => 'INT', 'constraint' => 3),
			));

			$this->dbforge->create_table('migrations', TRUE);
		}
		
		include APPPATH . 'config/migration.php';
		
		if (isset($config['migration_version'])) {
			$m_version = $config['migration_version'];
		} else {
			$m_version = 0;
		}
		
		$this->db->insert('migrations', array('version' => $m_version));

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
