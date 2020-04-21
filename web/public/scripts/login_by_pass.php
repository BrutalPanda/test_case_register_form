<?php
require_once 'autoload.php';

$app = new \app\Application();
echo $app->loginByEmailOrLoginAndPassword($_GET['email'], $_GET['login'], $_GET['password']);
