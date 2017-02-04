<?php


class EditProfileCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/in');
        $I->fillField('user_login','admin');
        $I->fillField('user_password','fpwPOuZD');
        $I->click('Login');
        $I->canSeeCurrentUrlEquals('/admin');
    }

    public function editProfileTest(AcceptanceTester $I)
    {
        $I->amOnPage('/admin/profile/edit');
        $I->fillField('name','Admin Name');
        $I->click('Edit');

        $I->amOnPage('/admin/profile/edit');
        $I->seeInField('name','Admin Name');

        $I->amOnPage('/admin/profile');
        $I->see('Admin Name');
    }
}
