<?php

class DedicatedServersCest
{
    public function _before(\AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','admin');
        $I->fillField('user_password','fpwPOuZD');
        $I->click('Login');
    }

    public function addServerTest(AcceptanceTester $I)
    {
        $I->wantTo("Add new dedicated server");
        $I->amOnPage("/adm_servers/add/dedicated_servers");
        $I->fillField("name", "DS #1");
        $I->fillField("provider", "GameAP");
        $I->fillField("ip", "127.0.0.1");
        $I->fillField("work_path", "/home/travis/gdaemon");
        $I->fillField("gdaemon_host", "127.0.0.1");
        $I->fillField("gdaemon_login", "gdaemon_login");
        $I->fillField("gdaemon_password", "gdaemon_password");
        $I->fillField("gdaemon_privkey", "application/keys/privkey.pem");
        $I->fillField("gdaemon_keypass", "keypass");
        $I->click("Add");
        $I->canSee("Server added successfully");

        $I->wantTo("See new col with dedicated server");
        $I->amOnPage("/adm_servers/view/dedicated_servers");
        $I->see("DS #1", "td");
    }
}