<?php

class AdmGamesCest {

    public function _before(\AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','admin');
        $I->fillField('user_password','fpwPOuZD');
        $I->click('Login');
    }

    public function addGameTest(AcceptanceTester $I)
    {
        $I->wantTo("Add new game server");
        $I->amOnPage("/adm_servers/add/games");
        $I->fillField("name", "GameName");
        $I->fillField("code", "gamename");
        $I->fillField("start_code", "gamename");
        $I->fillField("engine", "source");
        $I->fillField("engine_version", "1");
        $I->fillField("local_repository", "/rep/directory");
        $I->fillField("app_id", "90");
        $I->fillField("app_set_config", "app_set_config");
        $I->click("Add");
        $I->see("New game successfully added");
    }

    public function nonUniqueGame(AcceptanceTester $I)
    {
        $I->amOnPage("/adm_servers/add/games");
        $I->fillField("name", "GameName");
        $I->fillField("code", "gamename");
        $I->fillField("start_code", "gamename");
        $I->fillField("engine", "unknown_engine");
        $I->fillField("engine_version", "Version");
        $I->fillField("local_repository", "/rep/directory");
        $I->fillField("remote_repository", "http://ftp.yandex.ru/");
        $I->fillField("app_id", "90");
        $I->fillField("app_set_config", "app_set_config");
        $I->click("Add");

        $I->see("The Id field must contain a unique value.");
    }

    public function notValidFields(AcceptanceTester $I)
    {
        $I->amOnPage("/adm_servers/add/games");
        $I->fillField("name", "GameName 2");
        $I->fillField("code", "gamename2");
        $I->fillField("start_code", "gamename2");
        $I->fillField("engine", "unknown_engine");
        $I->fillField("engine_version", "Version");
        $I->fillField("local_repository", "/rep/directory");
        $I->fillField("app_id", "90");
        $I->fillField("app_set_config", "app_set_config");
        $I->click("Add");
        // TODO: Replace this message after update english language
        $I->see("adm_servers_unknown_engine");
        $I->moveBack();

        $I->fillField("engine", "source");
        $I->fillField("remote_repository", "/invalid/rep");
        $I->click("Add");
        // TODO: Replace this message after update english language
        $I->see("adm_servers_rep_file_not_exists");
        $I->moveBack();

        $I->fillField("app_id", "dfff");
        $I->fillField("remote_repository", "http://ftp.yandex.ru/pub/");
        $I->click("Add");

        $I->see('The app_id field must contain an integer.');
    }

    public function editGameTest(AcceptanceTester $I)
    {
        $I->amOnPage("/adm_servers/view/games");
        $I->click("Edit");
        $I->fillField("name", "GameName Edited");
        $I->fillField("code", "gamename_edited");
        $I->fillField("start_code", "gamename_edited");
        $I->fillField("engine", "goldsource");
        $I->fillField("engine_version", "2");
        $I->click("Server installation options");
        $I->fillField("local_repository", "/rep/directory_edited");
        $I->fillField("remote_repository", "http://ftp.yandex.ru/pub/");
        $I->fillField("app_id", "91");
        $I->fillField("app_set_config", "app_set_config_edited");
        $I->click("Save");
        $I->see("Game settings changed successfully");
        $I->click("Back to games list");
        $I->click("Edit");

        $I->seeInField("name", "GameName Edited");
        $I->seeInField("code", "gamename_edited");
        $I->seeInField("start_code", "gamename_edited");
        $I->seeInField("engine", "goldsource");
        $I->seeInField("engine_version", "2");
        $I->click("Server installation options");
        $I->seeInField("local_repository", "/rep/directory_edited");
        $I->seeInField("app_id", "91");
        $I->seeInField("app_set_config", "app_set_config_edited");
    }
}