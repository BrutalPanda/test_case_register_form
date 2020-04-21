<?php

namespace app\Classes;

use app\Configs\ConfigDB;
use http\Exception;
use mysqli;

class DB {

    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;

    private $ignoringFields = array(
        'id'
    );

    public function __construct(ConfigDB $_configDB) {
        $this->dbHost = $_configDB->getDBHost();
        $this->dbName = $_configDB->getDBName();
        $this->dbUser = $_configDB->getDBUser();
        $this->dbPass = $_configDB->getDBPass();
    }

    public function select (string $table, array $filter = array(), array $order = array(), array $columns = array()): ?array {
        $query = 'SELECT ';
        if (count($columns) == 0) {
            $query .= '* ';
        }
        else {
            $query .= '(';
            foreach ($columns as $column){
                $query .= "`{$column}`, ";
            }
            $query = substr($query,0, -2);
        }

        $query .= " FROM `{$table}` ";
        if (count($filter) !== 0){
            $query .= 'WHERE ';
            foreach ($filter as $key => $value){
                $query .= "`{$key}` = '{$value}' OR ";
            }
            $query = substr($query,0, -3);
        }

        if (count($order) !== 0){
            $query .= 'ORDER BY ';
            foreach ($filter as $key => $value){
                $query .= "`{$key}` {$value} AND ";
            }
            $query = substr($query,0, -4);
        }

        $mysql = $this->_mysqlUp();
        $result = $mysql->query($query);
        $rows = array();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        } else {
            throw new \Exception('DB select error');
        }
        $this->_mysqlDown($mysql);
        return $rows;
    }

    public function update (string $table, array $values, array $filter): ?int {
        $mysql = $this->_mysqlUp();
        $data = $this->_validateData($values, $mysql);
        if (count($data) === 0 || count($filter) === 0) {
            return null;
        }
        $query = "UPDATE `{$table}` SET ";
        foreach ($data as $key => $value){
            $query .= "`{$key}` = '{$value}', ";
        }
        $query = substr($query,0, -2);

        $query.= ' WHERE ';
        foreach ($filter as $key => $value){
            $query .= "`{$key}` = '{$value}' AND ";
        }
        $query = substr($query,0, -4);

        $result = $mysql->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $this->_mysqlDown($mysql);
                return $row['id'];
            }
        } else {
            throw new \Exception('DB update error');
        }
        $this->_mysqlDown($mysql);
        return null;
    }

    public function insert (string $table, array $values): ?string {
        $mysql = $this->_mysqlUp();
        $data = $this->_validateData($values, $mysql);
        if (count($data) === 0) {
            return null;
        }

        $columnNames  = array_keys($data);
        $columnValues = array_values($data);

        $query = "INSERT INTO `{$table}` (";
        foreach ($columnNames as $columnName) {
            $query .= "`{$columnName}`, ";
        }
        $query = substr($query,0, -2);

        $query .= ') VALUES (';
        foreach ($columnValues as $columnValue) {
            $query .= "'{$columnValue}', ";
        }
        $query = substr($query,0, -2);
        $query .= ')';

        $result = $mysql->query($query);
        $insertId = null;
        if ($result) {
            if ($result) {
                $insertId = (string) $mysql->insert_id;
            }
        } else {
            throw new \Exception('DB insert error');
        }
        $this->_mysqlDown($mysql);
        return $insertId;
    }

    public function delete(string $table, array $data): bool {
        $mysql = $this->_mysqlUp();
        if (count($data) === 0) {
            return false;
        }
        $query = "DELETE FROM `{$table}` WHERE ";

        foreach ($data as $key => $value){
            $query .= "`{$key}` = '{$value}' AND ";
        }
        $query = substr($query,0, -4);

        $result = $mysql->query($query);
        $this->_mysqlDown($mysql);
        return $result;

    }

    public function getFieldList(string $table): array {
        $mysql = $this->_mysqlUp();

        $result = $mysql->query("DESCRIBE `{$table}`;");
        $fields = array();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $fields[] = $row['Field'];
            }
        } else {
            throw new \Exception('Getting field list error');
        }

        $this->_mysqlDown($mysql);
        return $fields;
    }

    private function _validateData(array $data, mysqli $mysql): array {
        $result = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $this->ignoringFields)) {
                continue;
            }
            $editedValue = get_magic_quotes_gpc() === 1 ? stripslashes($value) : trim($value);
            $result[$key] = mysqli_real_escape_string($mysql, $editedValue);
        }
        return $result;
    }

    private function _mysqlUp(): ?mysqli {
        $link = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
        if (!$link) {
            throw new \Exception('DB connection error');
        }
        $link->query('SET NAMES \'utf8\';');
        mysqli_set_charset($link, "utf8");
        return $link;
    }

    private function _mysqlDown(mysqli $link) {
        mysqli_close($link);
    }
}