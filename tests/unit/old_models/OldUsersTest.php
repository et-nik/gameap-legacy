<?php

/**
 * @group Model
 */

class OldUsersTest extends CodeIgniterTestCase
{
    private $CI;

    public function setUp()
    {
        $this->CI =& get_instance();

        $this->CI->load->database();
        $this->CI->load->model('users');
    }

    public function test_add_user()
    {
        // User 1
        $sql_data['reg_date'] 	= time();
        $sql_data['login'] 		= 'test';
        $sql_data['password'] 	= hash_password('password');

        $this->assertTrue($this->CI->users->add_user($sql_data));

        // User 2
        $sql_data['reg_date'] 	= time();
        $sql_data['login'] 		= 'test2';
        $sql_data['password'] 	= hash_password('password2');

        $this->assertTrue($this->CI->users->add_user($sql_data));
    }

    public function test_update_user()
    {
        $sql_data['password'] 	= hash_password('new_password');
        $sql_data['name'] 		= 'username';
        $sql_data['email'] 		= 'nikita.hldm@gmail.com';

        $this->assertTrue($this->CI->users->update_user($sql_data, 1));
    }

    public function test_get_users_list()
    {
        $this->CI->users->get_users_list();
        //~ var_dump($this->CI->users->users_list);
    }

    public function test_get_user_data()
    {
        $this->CI->users->get_user_data(1);

        $this->assertEquals('test', $this->CI->users->user_data['login']);
        $this->assertEquals('username', $this->CI->users->user_data['name']);
        $this->assertEquals('nikita.hldm@gmail.com', $this->CI->users->user_data['email']);
        $this->assertEquals(hash_password('new_password', $this->CI->users->user_data['password']), $this->CI->users->user_data['password']);
    }

    public function test_user_live()
    {
        $this->assertTrue($this->CI->users->user_live(1));
        $this->assertFalse($this->CI->users->user_live(99990));

        $this->assertTrue($this->CI->users->user_live('test', 'login'));
        $this->assertFalse($this->CI->users->user_live('false_user', 'login'));

        $this->assertTrue($this->CI->users->user_live('nikita.hldm@gmail.com', 'email'));
        $this->assertFalse($this->CI->users->user_live('1234@gmail.com', 'email'));
    }

    public function test_user_auth()
    {
        $this->assertEquals( 1, $this->CI->users->user_auth('test', 'new_password'));
        $this->assertFalse( $this->CI->users->user_auth('test', 'new_password2') );
        $this->assertFalse( $this->CI->users->user_auth('test2', 'new_password') );
    }

    public function test_check_user()
    {
        $_COOKIE['user_id'] 	= '1';
        $_COOKIE['hash'] 		= 'hash_test';

        $sql_data['hash'] 		= 'hash_testd41d8cd98f00b204e9800998ecf8427e';

        $this->assertTrue($this->CI->users->update_user($sql_data, 1));
        $this->assertTrue($this->CI->users->check_user());

        $sql_data['hash'] 		= 'unknown_hashd41d8cd98f00b204e9800998ecf8427e';
        $this->assertTrue($this->CI->users->update_user($sql_data, 1));
        $this->assertFalse($this->CI->users->check_user());
    }

    public function test_set_filter()
    {
        $this->CI->users->clear_filter();
        $this->CI->users->set_filter(array('login' => 'test2'));
        $this->CI->users->get_users_list();
        $this->assertCount(1, $this->CI->users->users_list);
        $this->assertTrue( ($this->CI->users->users_list[0]['login'] == 'test2') );

        $this->CI->users->clear_filter();
        $this->CI->users->get_users_list();
        $this->assertCount(2, $this->CI->users->users_list);

        $this->CI->users->clear_filter();
        $this->CI->users->set_filter(array('login' => '', 'register_before' => (time()-1337) ));
        $this->CI->users->get_users_list();
        $this->assertCount(0, $this->CI->users->users_list);

        //~ $this->CI->users->set_filter(array('login' => 'test2'));
        //~ $this->CI->users->get_users_list();
        //~ $this->assertTrue( ($this->CI->users->users_list[0]['login'] == 'test2') );
    }

    public function test_count_all_users()
    {
        $this->CI->users->clear_filter();
        $this->assertTrue( ($this->CI->users->count_all_users() === 2) );

        $this->CI->users->set_filter(array('login' => 'test2'));
        $this->assertTrue( ($this->CI->users->count_all_users() === 1) );

        $this->CI->users->set_filter(array('login' => 'unknown'));
        $this->assertTrue( ($this->CI->users->count_all_users() === 0) );
    }

    public function test_users_privileges()
    {
        $user_id = 2;	// NonAuth
        $server_id = 1;

        foreach ($this->CI->users->all_privileges as $key => &$privilege) {
            // True
            $this->CI->users->set_server_privileges($key, true, $server_id,  $user_id);
            $this->CI->users->update_server_privileges($user_id, $server_id);
            $privileges = $this->CI->users->get_server_privileges($server_id, $user_id);

            $this->assertFalse( ($this->CI->users->auth_servers_privileges[$key] == 1) ); 	// AUTH, UserID = 1
            $this->assertTrue($this->CI->users->servers_privileges[$key]);					// UserID = 2
            $this->assertTrue($privileges[$key]);

            // False
            $this->CI->users->set_server_privileges($key, false, $server_id,  $user_id);
            $this->CI->users->update_server_privileges($user_id, $server_id);
            $privileges = $this->CI->users->get_server_privileges($server_id, $user_id);

            $this->assertFalse($this->CI->users->servers_privileges[$key]);
            $this->assertFalse($privileges[$key]);
        }
    }
}