<?php

class SignCest
{
    public function signInFailTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','incorrect_login');
        $I->fillField('user_password','incorrect_password');
        $I->click('Login');

        // TODO: Replace authorization to authentication
        // $I->see('Authentication failed');
        $I->see('Authorization failed');
    }

    public function signInSuccessTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','admin');
        $I->fillField('user_password','fpwPOuZD');
        $I->click('Login');
        $I->canSeeCurrentUrlEquals('/admin');
    }

    public function signUpFailTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/register');
        $I->fillField('login','user');
        $I->fillField('password','user_password');
        $I->fillField('passconf','user_password2');
        $I->fillField('email','user@example.com');
        $I->click('input[type="submit"]');
        $I->see('field does not match the');

        $I->amOnPage('/auth/register');
        $I->fillField('login','user');
        $I->fillField('password','user_password');
        $I->fillField('passconf','user_password');
        $I->fillField('email','incorrect_mail');
        $I->click('input[type="submit"]');
        $I->see('field must contain a valid email address.');
    }

    public function signUpTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/register');

        $I->fillField('login','user');
        $I->fillField('password','user_password');
        $I->fillField('passconf','user_password');
        $I->fillField('email','user@example.com');
        // $I->fillField('captcha','1009');

        // TODO: Fix english language
        $I->click('Регистрация');
        $I->see('Registration successful, now you can login with your account data');
    }

    public function recoveryPasswordTest(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/recovery_password');
    }
}