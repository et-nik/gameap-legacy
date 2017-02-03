<?php

class SigninCest
{
    public function signinFailTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','incorrect_login');
        $I->fillField('user_password','incorrect_password');
        $I->click('Login');
        $I->see('Authorization failed');
    }

    public function signinSuccessTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','admin');
        $I->fillField('user_password','fpwPOuZD');
        $I->click('Login');
        $I->canSeeCurrentUrlEquals('/admin');
    }
}