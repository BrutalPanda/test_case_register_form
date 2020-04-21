<?php

require_once 'autoload.php';

$sessionId = array_key_exists('session_id', $_COOKIE) ? $_COOKIE['session_id'] : null;
if (!empty($sessionId)) {
    $app = new \app\Application();
    setcookie("session_id","",time()-3600,"/");
    echo $app->logout($sessionId);
}else {
    echo json_encode(array(
        'success' => false,
        'message' => 'fail'
    ));
}