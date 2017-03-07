<?php

$autoloader = './vendor/autoload.php';

if (file_exists($autoloader)) {
    include_once $autoloader;
}

require_once __DIR__ . '/ride/testing/RideTestCase.php';
require_once __DIR__ . '/ride/testing/SeleniumRideTestCase.php';

$bootstrap = new PHPUnitBootstrap();

class PHPUnitBootstrap {

    const SELENIUM_TOKEN = './.selenium';

    public function __construct() {
        if (!file_exists(self::SELENIUM_TOKEN)) {
            touch(self::SELENIUM_TOKEN);
            echo "Creating Selenium Token.\n";
        }

        register_shutdown_function(function () {
            if (file_exists(self::SELENIUM_TOKEN)) {
                unlink(self::SELENIUM_TOKEN);
                echo "\nRemoving Selenium Token.\n";
            }
        });
    }

}
