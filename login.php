<?php
session_start();
include 'functions.php';

$email = $_POST['email'];
$password = $_POST['password'];

$user = get_user_by_email($email);
ob_start();

if ($user) {
    login($email, $password);
    redirect_to();
}else{
    set_flash_message('login_error', 'Такой пользователь не найден');
    redirect_to('page_login.php');
}
redirect_to('page_login.php');
