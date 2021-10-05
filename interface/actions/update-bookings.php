<?php
require '../inc.php';

# is user authenticated? check token
# Array ( [kursId] => BS_Kursid_175850 [matnr] => 980998 [enableBooking] => on ) false
print_r($_POST);
if (isset($_POST['matnr'], $_POST['kursId'])) {
    
    if ($_POST['enableBooking'] == 'on') {
        $lastBooking = 0; # 0 für alle tage
        $result = $db->createBooking($_POST['kursId'], $_POST['matnr'], $lastBooking);
    } else {
        $result = $db->deleteBooking($_POST['kursId'], $_POST['matnr']);
    }
    
    if ($result == 0) {
        sendBackMSG('Es wurden keine Daten verändert.', 'info');
    } else {
        sendBackMSG('Deine Daten wurden gespeichert.', 'success');
    }
    

} else {
    echo 'false';
}
/* if (isset($_POST['update-userinfo'])) { # make sure we come from clientdetails site
    $update = $db->updateUser($_SESSION['id'], $_POST);
} else {
    #sendBack();
    echo 'failes';
} */
?>