<?php

namespace ride\test;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverPoint;

/**
 * The SeleniumRideTestCase bridges the gap between Selenium, PHPUnit and Ride. Extending this class instead of the default
 * TestCase will initialize the Ride System upon construct and provide helper methods to work with Selenium.
 *
 * This class uses several optional configuration entries:
 *      - selenium.host: The host of the selenium server, running on `http://localhost:4444/wd/hub` by default.
 *      - selenium.browser.type: The type of browser to use; firefox, ie, edge, safari or chrome; defaults to `firefox`.
 *      - selenium.browser.timeout: The timeout in milliseconds to use, defaults to 5000.
 *      - selenium.browser.host: The base host of the browser; eg. http://belweb.local.statik.be, but has no default.
 */
abstract class SeleniumRideTestCase extends RideTestCase {

    /**
     * A test token added to URLs
     */
    const SELENIUM_TEST_TOKEN = 'SELENIUM_TEST';

    /**
     * The browser timeout to use when running Selenium tests.
     */
    const SELENIUM_BROWSER_TIMEOUT = 5000;

    /**
     * The host on which the Selenium server runs.
     */
    const SELENIUM_HOST = 'http://localhost:4444/wd/hub';

    /**
     * The browser to use when running Selenium tests.
     */
    const SELENIUM_BROWSER_TYPE = 'firefox';

    /**
     * @var RemoteWebDriver
     */
    protected $driver;

    /**
     * Create the web driver and setup a clean database.
     */
    protected function setUp() {
        parent::setUp();

        $this->createDriver();
    }

    /**
     * Quit the web driver and remove the database.
     */
    protected function tearDown() {
        $this->quitDriver();
        $this->removeDatabase();
    }

    /**
     * Create a driver to run Selenium tests on.
     *
     * @param array $capabilities
     *
     * @return RemoteWebDriver
     */
    protected function createDriver($capabilities = []) {
        $config = $this->getConfig();

        $host = $config->get('selenium.host', static::SELENIUM_HOST);
        $timeout = $config->get('selenium.timeout', static::SELENIUM_BROWSER_TIMEOUT);
        $browserType = $config->get('selenium.browser.type', static::SELENIUM_BROWSER_TYPE);
        $capabilities[WebDriverCapabilityType::BROWSER_NAME] = $browserType;

        $this->driver = RemoteWebDriver::create($host, $capabilities, $timeout);
        $this->driver->manage()->window()->setSize(new WebDriverDimension(1920 * 0.8, 1080 * 0.9));
        $this->driver->manage()->window()->setPosition(new WebDriverPoint(0, 0));

        return $this->driver;
    }

    /**
     * Quit the driver
     */
    protected function quitDriver() {
        if (!$this->driver) {
            return;
        }

        $this->driver->quit();
        unset($this->driver);
    }

    /**
     * Create a URL for Selenium tests
     *
     * @param      $url
     * @param null $host
     *
     * @return string
     */
    protected function createUrl($url, $host = null) {
        $config = $this->getConfig();

        if ($host === null && $host = $config->get('selenium.browser.host')) {
            $url = rtrim($host, '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }
}
