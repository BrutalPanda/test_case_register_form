<?php

namespace app\Models;

use app\Classes\DB;
use app\Configs\ConfigDB;

class User extends AbstractModel {

    protected $tableName = 'users';

    public function fillUserDataByEmailOrLogin(string $email = null, string $login = null): bool {
        if (empty($email) && empty($login)){
            return false;
        }

        $db = new DB(new ConfigDB());
        $filter = array();
        if (!empty($email)) {
            $filter['email'] = $email;
        }
        if (!empty($login)) {
            $filter['login'] = $login;
        }

        $fields = $db->select($this->tableName, $filter);
        if (count($fields) !== 0) {
            $this->fields = max($fields);
            $this->id = $this->fields['id'];
            return true;
        }
        return false;
    }

    public function fillUserDataBySessionId(string $sessionId): bool {
        if (empty($sessionId)) {
            return false;
        }

        $db = new DB(new ConfigDB());
        $filter = array();
        if ($sessionId !== null) {
            $filter['session_id'] = $sessionId;
        }

        $fields = $db->select($this->tableName, $filter);
        if (count($fields) !== 0) {
            $this->fields = max($fields);
            $this->id = $this->fields['id'];
            return true;
        }
        return false;
    }
}