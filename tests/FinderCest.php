<?php

class FinderCest
{
    public function _before(AcceptanceTester $I)
    {

    }

    public function tryToTest(AcceptanceTester $I)
    {
        if(getenv('CALLBACK')) {
            $I->amOnUrl(getenv('URL'));
            $class = unserialize(getenv('CALLBACK'));
            $class->callable($I);
        } else {
            if(getenv('URL')) {
                $I->amOnUrl(getenv('URL'));
                $I->fillField(getenv('EMAIL_PLACEHOLDER'), getenv('EMAIL'));
                $I->click(getenv('BUTTON'));
                $I->waitForJS("return $.active == 0;", 60);
                try {
                    $I->see(getenv('PASSWORD_SUCCESSFUL_TEXT'));
                    $file = fopen(getenv('EXIST_PATH'), "w") or die("Unable to open file!");
                    fwrite($file, "");
                    fclose($file);
                } catch (Exception $e) {
                    unlink(getenv('EXIST_PATH'));
                }
            } else {
                $path = "EXIST";
                $I->amOnUrl('https://iforgot.apple.com/password/verify/appleid');
                $I->fillField(["class" => "iforgot-apple-id"], 'joemires20@yandex.com');
                $I->click('Continue');
                $I->waitForJS("return $.active == 0;", 60);
                
                try {
                    $I->see('password');
                    $file = fopen($path, "w") or die("Unable to open file!");
                    fwrite($file, "");
                    fclose($file);
                } catch (Exception $e) {
                    unlink($path);
                }
            }
        }


    }
}
