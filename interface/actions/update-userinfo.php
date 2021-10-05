<?php
require '../inc.php';

# is user authenticated? check token
# make sure we come from clientdetails site
#print_r($_POST);
if (isset($_POST['update-userinfo'])) { 
    $update = $db->updateUser($_SESSION['id'], $_POST);
    if ($update == 0) {
        sendBackMSG('Es wurden keine Daten verändert.', 'info');
    } else {
        sendBackMSG('Deine Daten wurden gespeichert.', 'success');
    }
} else {
    #sendBack();
    echo 'failes';
}
?>