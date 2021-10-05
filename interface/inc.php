<?php
require_once 'db.php';
require_once 'functions.php';

session_start();
$db = new db();

# if page is login
if (isPage('login.php')) {
    if (isset($_POST['matnr']) && isset($_POST['password'])) {
        $greatSuccess = $db->getLogin($_POST['matnr'], $_POST['password']);
        if ($greatSuccess) {
            $_SESSION['matnr'] = $_POST['matnr'];
            redirect('clientdetails.php');
        } else {
            echo 'Wrong pass';
        }
    }
}

?>