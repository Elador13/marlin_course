<?php
session_start();
include 'functions.php';

$email = $_POST['email'];
$password = $_POST['password'];

//$user = get_user_by_email($email);
$user = get_user_by_email($email);
ob_start();

if (!$user) {
    set_flash_message('login_error', 'Такой пользователь не найден');
    redirect_to('page_login.php');
}
login($email, $password);
redirect_to('page_users.php');
