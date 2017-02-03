<?php

class SigninCest
{
    public function signinTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','incorrect_login');
        $I->fillField('user_password','incorrect_password');
        $I->click('Login');
        $I->see('Authorization failed');
    }
}