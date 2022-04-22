<?php
$_SERVER['SCRIPT_NAME'] = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
if (isset( $_SERVER['SCRIPT_URI'])) {
    $_SERVER['SCRIPT_URI'] = str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['SCRIPT_URI']);
}
$_SERVER['SCRIPT_URL']  = $_SERVER['SCRIPT_NAME'];
$_SERVER['PHP_SELF']    = $_SERVER['SCRIPT_NAME'];
//$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
