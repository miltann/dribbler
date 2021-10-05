<?php

function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    die();
}

function sendBack() {
    $ref = $_SERVER['HTTP_REFERER'];
    redirect($ref);
}

function sendBackMSG($msg, $code) {
    $ref = $_SERVER['HTTP_REFERER'];
    if (isset($msg)) {
        if ($code == 'success') {
            $_SESSION['message'] = '<div class="alert alert-success d-flex align-items-center alert-dismissible fade show" role="alert"><svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg><div>' .$msg. '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        } elseif ($code == 'warning') {
            $_SESSION['message'] = '<div class="alert alert-warning d-flex align-items-center alert-dismissible fade show" role="alert"><svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Warning:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>' .$msg. '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        } elseif ($code == 'danger') {
            $_SESSION['message'] = '<div class="alert alert-danger d-flex align-items-center alert-dismissible fade show" role="alert"><svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg><div>' .$msg. '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        } elseif ($code == 'info') {
            $_SESSION['message'] = '<div class="alert alert-primary d-flex align-items-center alert-dismissible fade show" role="alert"><svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg><div>' .$msg. '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'; 
        }
    }
    redirect($ref);
}

function isPage($pageToCheck) {
    $page = basename($_SERVER['PHP_SELF']);
    return $page === $pageToCheck;
}
?>





