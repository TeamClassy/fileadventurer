<?php

session_start();
session_destroy();
$cookieParams = session_get_cookie_params();
setcookie(session_name(), '', 0, $cookieParams['path'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
session_unset();

?>