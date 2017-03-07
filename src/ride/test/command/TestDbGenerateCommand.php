<?php

namespace ride\test\command;

use ride\library\cli\command\AbstractCommand;
use ride\library\database\Dsn;
use ride\library\orm\entry\Entry;
use ride\library\orm\exception\ModelException;
use ride\library\orm\OrmManager;
use ride\library\system\file\browser\FileBrowser;

class TestdbGenerateCommand extends AbstractCommand {

    const ARGUMENT_FILE = 'file';

    const ARGUMENT_MODELS = 'models';

    const CONNECTION_TEST = 'test';

    const DEFAULT_FILE = 'test/generated.test.db';

    /**
     * @var \ride\library\system\file\browser\FileBrowser
     */
    private $fileBrowser;

    /**
     * @var OrmManager
     */
    private $ormManager;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * TestdbGenerateCommand constructor.
     *
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @param \ride\library\orm\OrmManager                  $ormManager
     */
    public function __construct(FileBrowser $fileBrowser, OrmManager $ormManager) {
        parent::__construct('testdb generate', 'Generate a clean test database');

        $this->addFlag(self::ARGUMENT_FILE, 'Specify a file to save the generated database in');
        $this->addArgument(self::ARGUMENT_MODELS, 'Specify which models to copy data from', false, true);

        $this->fileBrowser = $fileBrowser;
        $this->ormManager = $ormManager;
        $this->defaultConnection = $this->ormManager->getDatabaseManager()->getDefaultConnectionName();
    }

    /**
     * Execute the command
     * @return null
     */
    public function execute() {
        try {
            $filePath = $this->input->getFlag(self::ARGUMENT_FILE, self::DEFAULT_FILE);
            $models = array_filter(explode(' ', $this->input->getArgument(self::ARGUMENT_MODELS)), function ($value) {
                return $value !== '';
            });

            $this->output->writeLine('Creating new SQLite test database');
            $this->deleteTestDatabaseFile($filePath);
            $this->createTestConnection($filePath);
            $this->connectTest();
            $this->ormManager->defineModels();

            if ($models) {
                $this->output->writeLine('Copying model data from to the test database: ' . implode(', ', $models));
                $this->copyModels($models);
            }

        } catch (\Exception $e) {
            $this->output->writeError($e);
            $this->output->writeErrorLine('Something went wrong, check the logs for more info.');
        } catch (\Error $e) {
            $this->output->writeErrorLine('Something went wrong, check the logs for more info.');
        }

        $this->connectDefault();

        $this->output->writeLine('Done');
    }

    /**
     * Connect to the default connection
     */
    private function connectDefault() {
        $this->ormManager->getDatabaseManager()->setDefaultConnectionName($this->defaultConnection);
    }

    /**
     * Connect to the test connection
     */
    private function connectTest() {
        $this->ormManager->getDatabaseManager()->setDefaultConnectionName(self::CONNECTION_TEST);
    }

    /**
     * Create the test connection and add it to the DBM
     *
     * @param $filePath
     */
    private function createTestConnection($filePath) {
        $applicationDirectory = $this->fileBrowser->getApplicationDirectory();
        $applicationDirectoryPath = trim($applicationDirectory->getPath(), '/');

        $dsn = new Dsn('sqlite://' . $applicationDirectoryPath . '/' . $filePath);

        $this->ormManager->getDatabaseManager()->registerConnection(self::CONNECTION_TEST, $dsn);
    }

    /**
     * Delete the test database file
     *
     * @param $filePath
     */
    private function deleteTestDatabaseFile($filePath) {
        $databaseFile = $this->fileBrowser->getApplicationDirectory()->getChild($filePath);
        if ($databaseFile && $databaseFile->exists()) {
            $databaseFile->delete();
        }
    }

    /**
     * Copy model data from the default connection to the test database.
     *
     * @param $models
     *
     * @todo: localized models
     */
    private function copyModels($models) {
        foreach ($models as $modelName) {
            try {
                $this->connectDefault();
                $model = $this->ormManager->getModel($modelName);
                $modelData = $model->find();

                $entryString = 'entries';
                if (count($modelData) === 1) {
                    $entryString = 'entry';
                }

                $this->output->writeLine("\tCopying data for model `" . $modelName . '` (' . count($modelData) . ' ' . $entryString . ' found)');
                $this->connectTest();

                /** @var Entry $entry */
                foreach ($modelData as $entry) {
                    $entry->setEntryState(Entry::STATE_NEW);
                }

                $model->save($modelData);
            } catch (ModelException $e) {
                $this->output->writeLine("\tCould not load model `" . $modelName . '`');

                continue;
            }
        }
    }
}
