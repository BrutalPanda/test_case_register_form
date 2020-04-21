<?php

namespace app\Models;

use app\Classes\DB;
use app\Configs\ConfigDB;
use http\Exception;

class AbstractModel {

    public const VALUE_TYPE_EMAIL = 'email';
    public const VALUE_TYPE_PHONE = 'phone';
    public const VALUE_TYPE_NAME  = 'name';

    protected $fields;

    protected $tableName = '';

    protected $id = null;

    public function __construct(string $_id = null) {
        if ($_id !== null){
            $this->id = $_id;
            $this->fields = $this->_getFieldsData();
        } else {
            $this->fields = $this->_getFieldsInfo();
        }
    }

    public function getId(): ?string {
        return $this->id;
    }

    public function get(string $param): ?string{
        return array_key_exists($param, $this->fields) ? (string)$this->fields[$param] : null;
    }

    public function set(string $param, string $value, string $valueType = 'text'): bool {
        if (array_key_exists($param, $this->fields)) {
            $filteredValue = $this->_filter($value, $valueType);
            $this->fields[$param] = $filteredValue;
            return true;
        }
        return false;
    }

    public function save() {
        if (array_key_exists('updated', $this->fields) && array_key_exists('created', $this->fields)) {
            $now = new \DateTime();
            $nowFormatted = $now->format('Y-m-d H:i:s');

            $this->fields['updated'] = $nowFormatted;
            if ($this->id === null) {
                $this->fields['created'] = $nowFormatted;
            }
        }

        $db = new DB(new ConfigDB());
        if ($this->id === null) {
            $this->id = $db->insert($this->tableName, $this->fields);
        } else {
            $filter = array(
                'id' => $this->id
            );
            $db->update($this->tableName, $this->fields, $filter);
        }
    }

    public function delete() {
        if ($this->id !== null) {
            $db = new DB(new ConfigDB());
            $filter = array(
                'id' => $this->id
            );
            $db->delete($this->tableName, $filter);
        }
    }

    private function _filter(string $value, string $valueType = 'text'): ?string {
        switch ($valueType) {
            case self::VALUE_TYPE_EMAIL:
                $regExp = '/^[A-Z0-9._%+-]+@[A-Z0-9-]+.+.[A-Z]{2,4}$/i';
                break;
            case self::VALUE_TYPE_PHONE:
                $value = preg_replace('/[^0-9]/', '', $value);
                $regExp = '^[0-9]{6,15}';
                break;
            case self::VALUE_TYPE_NAME:
                $regExp = '^[a-zA-Z0-9_]{3,20}$/';
                break;
            default:
                $regExp = null;
                break;
        }
        if ($regExp !== null){
            if (preg_match($regExp, $value, $match)) {
                return $match[0];
            }
            else {
                throw new \Exception($valueType.' bad value');
            }
        } else {
            return $value;
        }
    }

    private function _getFieldsInfo(): array {
        $db = new DB(new ConfigDB());
        $fieldList = $db->getFieldList($this->tableName);
        $result = array();
        foreach ($fieldList as $field){
            $result[$field] = null;
        }
        return $result;
    }

    private function _getFieldsData(): array {
        $db = new DB(new ConfigDB());

        $filter = array(
            'id' => $this->id,
        );
        return $db->select($this->tableName, $filter);
    }

}