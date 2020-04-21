<?php


namespace app\Configs;


class ConfigDB {
    private $dbName = 'test_case';
    private $dbUser = 'test_case';
    private $dbPass = '8QY3TZRhHNzxa8lW';
    private $dbHost = 'test_mysql';

    public function getDBName(): string {
        return $this->dbName;
    }

    public function getDBUser(): string {
        return $this->dbUser;
    }

    public function getDBPass(): string {
        return $this->dbPass;
    }

    public function getDBHost(): string {
        return $this->dbHost;
    }
}