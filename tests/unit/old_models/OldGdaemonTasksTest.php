<?php

class OldGdaemonTasksTest extends CodeIgniterTestCase
{
    private $CI;

    public function setUp()
    {
        $this->CI =& get_instance();

        $this->CI->load->database();
        $this->CI->db->db_debug = 0;

        $this->CI->load->model('users');
        $this->CI->load->library('gameap_hooks');
        $this->CI->load->model('gdaemon_tasks');
    }

    public function test_add()
    {
        $this->assertEquals(1, $this->CI->gdaemon_tasks->add([
            'server_id' => 1,
            'ds_id' 	=> 2,
            'task' 		=> 'gsinst',
        ]));

        $this->assertEquals(2, $this->CI->gdaemon_tasks->add([
            'server_id' => 2,
            'ds_id' 	=> 2,
            'task' 		=> 'gsstart',
        ]));
    }

    public function test_get_all_count()
    {
        $this->assertEquals(2, $this->CI->gdaemon_tasks->get_all_count());
    }

    public function test_get_single()
    {
        /*
        $task = $this->CI->gdaemon_tasks->get_single(1);

        $this->assertEquals(1, $task['server_id']);
        $this->assertEquals(2, $task['ds_id']);
        $this->assertEquals('gsinst', $task['task']);
        */
    }

    public function test_get_list()
    {
        /*
        $tasks_list = $this->CI->gdaemon_tasks->get_list();
        $this->assertEquals(2, count($tasks_list));

        $this->CI->gdaemon_tasks->set_filter('server_id', 1);
        $tasks_list = $this->CI->gdaemon_tasks->get_list();
        $this->assertEquals(1, count($tasks_list));

        $this->CI->gdaemon_tasks->set_filter('task', array('gsinst', 'gsstart'));
        $tasks_list = $this->CI->gdaemon_tasks->get_list();
        $this->assertEquals(2, count($tasks_list));
        */
    }

    public function test_update()
    {
        /*
        $this->CI->gdaemon_tasks->update(1, ['server_id' => 5]);
        $task = $this->CI->gdaemon_tasks->get_single(1);

        $this->assertEquals(5, $task['server_id']);
        */
    }

    public function test_delete()
    {
        $this->assertTrue($this->CI->gdaemon_tasks->delete(1));
    }
}
