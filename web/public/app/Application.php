<?php

namespace app;

use app\Classes\Files;
use app\Models\User;
use app\Models\UserInfo;

class Application {

    private $requiredFields = array(
        'login', 'email', 'pass', 'pass_confirm', 'first_name', 'second_name', 'last_name', 'phone', 'userpic_filename'
    );

    private $baseFields = array(
        'login', 'email', 'pass', 'pass_confirm', 'first_name', 'phone'
    );

    public function __construct(){
    }

    public function loginByEmailOrLoginAndPassword(?string $email, ?string $login, ?string $password): string {
        if (empty($email) && empty($login)) {
            return $this->_error('login data is empty');
        }
        if (empty($password)){
            return $this->_error('password is empty');
        }
        try {
            $user = new User();
            $user->fillUserDataByEmailOrLogin($email, $login);
        } catch (\Exception $ex) {
            return $this->_error($ex->getMessage());
        }

        $encodedPassword = $this->_encodePassword($password);
        if ($user->getId() !== null && $user->get('pass_hash') == $encodedPassword) {
            $result = array(
                'sessionId' => $user->get('session_id'),
                'user' => $this->_getUserInfoByUser($user),
            );
            return $this->_success($result, 'login success');
        }
        $result = array(
            'sessionId' => $this->_sessionId($email)
        );
        return $this->_success($result, 'login fail');
    }

    public function loginBySessionId(string $sessionId): string {
        try {
            $user = new User();
            $user->fillUserDataBySessionId($sessionId);
        } catch (\Exception $ex) {
            return $this->_error($ex->getMessage());
        }

        if ($user->getId() !== null) {
            $result = array(
                'sessionId' => $user->get('session_id'),
                'user' => $this->_getUserInfoByUser($user),
            );
            return $this->_success($result, 'login success');
        }
        $result = array(
            'sessionId' => $sessionId,
            'user' => array()
        );
        return $this->_success($result, 'login fail');
    }

    public function logout(string  $sessionId): string {
        try {
            $user = new User();
            $user->fillUserDataBySessionId($sessionId);
        }catch (\Exception $ex) {
            return $this->_error($ex->getMessage());
        }

        if ($user->getId() !== null) {
            $sessionId = $this->_sessionId($user->get('email'));
            $user->set('session_id', $sessionId);
        }
        return $this->_success(array(),'logout success');
    }

    public function registerUser(array $requestData): string {
        try {
            $requestData['userpic_filename'] = $this->_loadFile($requestData['userpic_file']);
            $data = $this->_validateData($requestData);
            if($data['pass'] !== $data['pass_confirm']){
                return $this->_error('password are not equal');
            }
            if ($this->_isUserExist($data['email'], $data['login'])) {
                return $this->_success(array(), 'user exist');
            }

            $user = new User();
            $user->set('login', $data['login']);
            $user->set('email', $data['email']);
            $user->set('pass_hash', $this->_encodePassword($data['pass']));
            $user->set('session_id', $this->_sessionId($data['email']));
            $user->save();
        } catch (\Exception $ex) {
            return $this->_error($ex->getMessage());
        }

        if ($user->getId() !== null) {
            try {
                $userInfo = new UserInfo();
                $userInfo->set('user_id', $user->getId());
                $userInfo->set('first_name', $data['first_name']);
                $userInfo->set('second_name', $data['second_name']);
                $userInfo->set('last_name', $data['last_name']);
                $userInfo->set('phone', $data['phone']);
                $userInfo->set('userpic_filename', $data['userpic_filename']);
                $userInfo->save();
                if ($userInfo->getId() !== null) {
                    $result = array(
                        'sessionId' => $user->get('session_id')
                    );
                    return $this->_success($result, 'register successfully');
                }
            } catch (\Exception $ex) {
                $user->delete();
                return $this->_error($ex->getMessage());
            }
        }
        return $this->_error('Problems with user registration');
    }

    private function _getUserInfoByUser(User $user): array {
        $userInfo = new UserInfo();
        $userInfo->fillUserInfoByUserId($user->getId());
        $result = array();
        if ($userInfo->getId() !== null) {
            $result = array(
                    'login'            => $user->get('login'),
                    'email'            => $user->get('email'),
                    'first_name'       => $userInfo->get('first_name'),
                    'second_name'      => $userInfo->get('second_name'),
                    'last_name'        => $userInfo->get('last_name'),
                    'phone'            => $userInfo->get('phone'),
                    'userpic_filename' => $userInfo->get('userpic_filename'),
            );
        }
        return $result;
    }

    private function _isUserExist(string $email, string $login){
        try {
            $user = new User();
            $user->fillUserDataByEmailOrLogin($email);
            if ($user->getId() === null){
                $user->fillUserDataByEmailOrLogin(null, $login);
            }
        } catch (\Exception $ex) {
            return $this->_error($ex->getMessage());
        }

        return $user->getId() !== null;
    }

    private function _loadFile(array $userFile): string {
        if (count($userFile) !== 0){
            $file = new Files($userFile);
            return $file->load() ? $file->getName() : '';
        }
        return '';
    }

    private function _validateData($data): array {
        $result = array();
        foreach ($this->requiredFields as $requiredField){
            $result[$requiredField] = array_key_exists($requiredField, $data) ? $data[$requiredField] : '';
        }
        foreach ($this->baseFields as $baseField){
            if (empty($result[$baseField])) {
                throw new \Exception("field {$baseField} is empty");
            }
        }
        return $result;
    }

    private function _encodePassword(string $password): string {
        return md5(md5($password));
    }

    private function _sessionId(string $email): string {
        return md5(microtime().$email);
    }

    private function _success(array $data, string $message): string {
        return json_encode(array(
            'success' => true,
            'message' => $message,
            'data'    => $data
        ));
    }

    private function _error(string $message): string {
        return json_encode(array(
            'success' => false,
            'message' => $message
        ));
    }
}