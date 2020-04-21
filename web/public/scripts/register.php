<?php
require_once 'autoload.php';

$app = new \app\Application();
$data = array(
    'email'        => $_POST['inputRegEmail'],
    'login'        => $_POST['inputRegLogin'],
    'pass'         => $_POST['inputRegPass'],
    'pass_confirm' => $_POST['inputRegPassConfirm'],
    'phone'        => $_POST['inputRegPhone'],
    'first_name'   => $_POST['inputRegFirstName'],
    'last_name'    => $_POST['inputRegLastName'],
    'second_name'  => $_POST['inputRegSecondName'],
    'userpic_file' => array()
);
if (is_uploaded_file($_FILES['inputFile']['tmp_name'])){
    $data['userpic_file'] = $_FILES['inputFile'];
    if ($_FILES['inputFile']['size'] > 5000000) {
        die (json_encode(array(
            'success' => false,
            'message' => 'file too large'
        )));
    }
}
echo $app->registerUser($data);