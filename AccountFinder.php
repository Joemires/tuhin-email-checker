<?php
require_once 'vendor/codeception/codeception/autoload.php';

class AccountFinder {
    
    private $Codecept;
    private $checked = false;
    private $required_options = ['URL', 'EMAIL', 'BUTTON', 'PASSWORD_SUCCESSFUL_TEXT', 'EMAIL_PLACEHOLDER', 'CALLBACK'];


    public function __construct(Array $options = [])
    {
        $this->Codecept = new \Codeception\Codecept(array(
            'steps' => true,
            'verbosity' => 1,
            'seed' => 2,
            // some other options (see Codeception docs/sources)
        ));

        putenv('EXIST_PATH='.getcwd()."/data/EXIST");
        putenv('ERROR_PATH='.getcwd()."/data/ERROR");

        @unlink(getenv('EXIST_PATH'));
        @unlink(getenv('ERROR_PATH'));

    }
    
    public function set(Array $options)
    {
        foreach ($options as $key => $option) {
            $index = strtoupper($key);
            if (in_array($index, $this->required_options)) {
                putenv("$index=$option");
            }
        }
        return $this;
    }

    public function run()
    {
        if(!filter_var(getenv('EMAIL'), FILTER_VALIDATE_EMAIL)) return -1;
        return $this->Codecept->run('acceptance');
        $this->checked = true;
        return $this;
    }

    public function check()
    {
        if(!filter_var(getenv('EMAIL'), FILTER_VALIDATE_EMAIL)) return -1;
        if($this->checked == false) $this->run();

        if(file_exists(getenv('ERROR_PATH'))) return 0;
        
        return file_exists(getenv('EXIST_PATH'));
    }

    public function setErrors()
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);

        return $this;
    }

    public function yandex($email)
    {
        $yandex = new Yandex;
        $yandex->email = $email;
        $options = [
            'url' => 'https://passport.yandex.com/registration/mail',
            'email' => $email,
            'callback' => serialize($yandex),
        ];
        $this->set($options);
        return $this;
    }

    public function apple($email)
    {
        $apple = new Apple;
        $apple->email = $email;
        $options = [
            'url' => 'https://iforgot.apple.com/password/verify/appleid',
            'email' => $email,
            'callback' => serialize($apple),
        ];
        $this->set($options);
        return $this;
    }
}

class Yandex {

    public $email;

    public function callable($I)
    {
        $mail = explode("@", getenv('EMAIL'));
        $I->fillField('#login', $mail[0]);
        $I->waitForJS("return $.active == 0;", 60);
        try {
            $I->see('Username available');
        } catch (Exception $e) {
            try {
                $this->write();
                $I->see('Sorry, but this username is already taken');
            } catch (Exception $e) {
                $this->write(getenv('ERROR_PATH'));
            }
        }

    }
    public function write($path = NULL)
    {
        $path = is_null($path) ? getenv('EXIST_PATH') : $path;
        $file = fopen($path, "w") or die("Unable to open file!");
        fwrite($file, "");
        fclose($file);
    }
}

class Apple {
    public function callable($I)
    {
        $I->fillField('.iforgot-apple-id', getenv('EMAIL'));
        $I->click('Continue');
        $I->waitForJS("return $.active == 0;", 60);
        try {
            $I->see('valid or not supported');
        } catch (Exception $e) {
            try {
                $I->see('Confirm your phone number');
                $this->write();
            } catch (Exception $e) {
                try {
                    $I->see('Select how you want to unlock your account:');
                    $this->write();
                } catch (Exception $e) {
                    $this->write(getenv('ERROR_PATH'));
                }
            }
        }
    }
    public function write($path = NULL)
    {
        $path = is_null($path) ? getenv('EXIST_PATH') : $path;
        $file = fopen($path, "w") or die("Unable to open file!");
        fwrite($file, "");
        fclose($file);
    }
}