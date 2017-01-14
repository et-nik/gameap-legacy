<?php

class UserSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('users');

        $this->db->insert('users', [
            'login'         => 'admin',
            'password'      => '$2a$10$Fke.QmsyW3p0hEyCGXbIaeh3xkKEQwjyxH7syHdVxl68FRlho5KVq', // fpwPOuZD
            'is_admin'      => 1,
            'group'         => 100,
            'reg_date'      => time(),
            'email'         => 'admin@yousite.local',
            'privileges'    => '{"srv_global":true,"srv_start":true,"srv_stop":true,"srv_restart":true,"usr_create":true,"usr_edit":true,"usr_edit_privileges":true,"usr_delete":true}',
        ]);
    }
}