# Ride testing

This library bridges the gap between PHPUnit, Selenium and Ride.

```sh
composer require --dev ride/test
```

## Installation

This module doesn't trigger a composer post-install script yet. So you should manually copy the `dist/` folder to your 
 project root. You should also modify the `parameters.php` file to let Ride know when to use the test environment. 
 More about that later. 

### Test environment configuration

You can use `application/config/test/` for test-specific parameters, routes and dependencies. 
  
The test environment uses a SQLite database. This database is copied from a master template for each test, 
 ensuring every test case has a clean database instance. You can use an SQLite browser
 to add data to the clean test database: `application/test/clean.test.db`. You could also make other databases and use 
 those as a template for individual test cases. This is done by extending `RideTestCase` and calling 
 `$this->cleanDatabase('path/to/my/clean/test.db')`. The current database used for a test case can be found in 
 `application/test/test.db`.
 
### Creating a clean database

This library enables the `testdb generate` command. When run, the command will take the MySQL connection and 
generate an SQLite database from it. Specific model data can be copied per model, and a file can be specified in which to
 save the SQLite database.

```sh
cli testdb generate --file=test/clean.test.db Dealer User TaxonomyTerm Mail
```

Running this command is very useful in early development, when you're still updating models.xml.

## PHPUnit tests

Your test classes should be saved in the `application/test` folder. They can either extend the normal PHPUnit TestCase,
 or extend `ride\testing\RideTestCase`. Doing this makes the Ride `System` and `DependecyInjector` available in your tests.
 Note that you should always call `parent::setUp()` and `parent::tearDown()` when you're overriding these methods.
 
Tests can be run with the following command.

```sh
vendor/bin/phpunit

# Or, if PHPUnit is installed globally
phpunit

# Or, a specified test file
phpunit application/test/statik/orm/model/MyModelTest.php
```

## Selenium tests

Like PHPUnit tests, there is a class available which you can extend Selenium test classes from: `ride\testing\SeleniumRideTestCase`

### Prerequisites

Selenium requires Java and a JDK to run; you can find download links to the current JDK here: 
[http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html](http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html).

Furthermore, you'll need a specific browser to run tests on. Certain versions of Firefox work out of the box, but some 
 don't. If you're on Mac and want to play safe, a working Chrome driver is included in the `dist/bin` of this library. If you 
 want to use another driver, you can look around here: 
 [http://www.seleniumhq.org/about/platforms.jsp](http://www.seleniumhq.org/about/platforms.jsp).
 
To tell Ride it has to use the test environment when running Selenium tests, you should add the following lines in 
 `application/config/parameters.php`, after the environment detection.
 
```php
if (file_exists(__DIR__ . '/../../.selenium')) {
 $environment = "test";
}
```

### Running tests

The Selenium server should always run in the background when running tests. The server also logs a lot of useful 
 information while testing. 

```bash
# Run Selenium in the background, with the provided Chrome driver.
./selenium-server-chrome.sh

# Or run selenium with your own driver.
sh vendor/se/selenium-server-standalone/bin/selenium-server-standalone -Dwebdriver.chrome.driver=bin/chromedriver
```

```bash
# Run all selenium tests.
phpunit --testsuite selenium

# Or run a specific test.
phpunit --testsuite selenium --filter test_inquiry_form_submit
```
