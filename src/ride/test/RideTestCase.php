<?php

namespace ride\test;

use PHPUnit\Framework\TestCase;

use ride\application\system\System;

use ride\library\config\Config;
use ride\library\dependency\DependencyInjector;
use ride\library\orm\OrmManager;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;

/**
 * The RideTestCase bridges the gap between PHPUnit and Ride. Extending this class instead of the default
 * TestCase will initialize the Ride System upon construct.
 */
abstract class RideTestCase extends TestCase {

    /**
     * @var string
     *
     * The system environment to use when running tests. Defaults to `test`.
     *
     * @see application/config/test/parameters.json
     */
    const SYSTEM_ENVIRONMENT = 'test';

    /**
     * @var System
     */
    protected $system;

    /**
     * @var DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Setup a clean database.
     */
    public function setUp() {
        $this->createDatabase();
    }

    /**
     * Remove the database.
     */
    public function tearDown() {
        $this->removeDatabase();
    }

    /**
     * Constructing a new Ride PHPUnit test will automatically initialize a Ride System, and setting the dependency
     * injector. Default parameters are loaded from application/src/bootstrap.php.
     *
     * @see \ride\application\system\System
     */
    public function __construct() {
        parent::__construct();

        include './application/src/bootstrap.php';
        $parameters['environment'] = static::SYSTEM_ENVIRONMENT;

        $system = new System($parameters);
        $system->setTimeZone();

        $this->system = $system;
        $this->dependencyInjector = $system->getDependencyInjector();
    }

    /**
     * @return DependencyInjector
     */
    protected function getDependencyInjector() {
        return $this->dependencyInjector;
    }

    /**
     * @return OrmManager
     */
    protected function getOrmManager() {
        return $this->dependencyInjector->get(OrmManager::class);
    }

    /**
     * @return FileBrowser
     */
    protected function getFileBrowser() {
        return $this->dependencyInjector->get(FileBrowser::class);
    }

    /**
     * @return Config
     */
    protected function getConfig() {
        return $this->dependencyInjector->get(Config::class);
    }

    /**
     * Get the SQLite database file from the current DSN.
     *
     * @return File
     */
    protected function getDatabaseFile() {
        $fileBrowser = $this->getFileBrowser();
        $applicationDirectory = $fileBrowser->getApplicationDirectory();

        $path = str_replace("{$applicationDirectory->getPath()}/", '', $this->getOrmManager()->getConnection()->getDsn()->getPath());

        return $applicationDirectory->getChild($path);
    }

    /**
     * Remove the SQLite database.
     */
    protected function removeDatabase() {
        $databaseFile = $this->getDatabaseFile();

        if ($databaseFile && $databaseFile->exists()) {
            $databaseFile->delete();
        }
    }

    /**
     * Remove the current SQLite database and copy a clean file.
     *
     * @param string $cleanDatabasePath
     */
    protected function createDatabase($cleanDatabasePath = 'test/clean.test.db') {
        $fileBrowser = $this->getFileBrowser();
        $databaseFile = $this->getDatabaseFile();
        $cleanDatabaseFile = $fileBrowser->getApplicationDirectory()->getChild($cleanDatabasePath);

        $cleanDatabaseFile->copy($databaseFile);
        $databaseFile->setPermissions(0755);
    }


}

