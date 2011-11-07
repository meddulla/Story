<?php
class demotwo extends Story
{
    public function __construct()
    {
        parent::__construct();
    }

    public function Sceneario_ItShouldHaveBlood()
    {
        $this->Given('Variable Blood is where the heart is')
        ->Then('I should see Blood')
        ->Also('I should not see error');
    }

    //fix this test to pass all
    public function Sceneario_ItShouldGiveErrors()
    {
        $this->Given('Variable Home is where the heart is')
        ->Then('I should see Blood') //fix this test so "I should not see Blood"
        ->Also('I should not see error');
    }
}

