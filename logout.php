<?php

session_start();
ftp_close($_SESSION['ftp']);
session_destroy();
$cookieParams = session_get_cookie_params();
setcookie(session_name(), '', time()-604800, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
session_unset();
echo json_bad();

?>