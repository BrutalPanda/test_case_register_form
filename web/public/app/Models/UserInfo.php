<?php

namespace app\Models;

use app\Classes\DB;
use app\Configs\ConfigDB;

class UserInfo extends AbstractModel {

    protected $tableName = 'users_info';

    public function fillUserInfoByUserId (string $userId): bool {
        if (empty($userId)){
            return false;
        }

        $db = new DB(new ConfigDB());
        $filter = array();
        $filter['user_id'] = $userId;

        $fields = $db->select($this->tableName, $filter);
        if (count($fields) !== 0) {
            $this->fields = max($fields);
            $this->id = $this->fields['user_id'];
            return true;
        }
        return false;
    }
}