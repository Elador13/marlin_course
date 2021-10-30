<?php
require_once 'functions.php';
session_start();

$email = $_POST['email'];
$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_BCRYPT);

$user = get_user_by_email($email);
ob_start();
if ($user) {
    set_flash_message('user_exist', 'Этот эл. адрес уже занят другим пользователем.');
    redirect_to('page_register.php');
    exit();
}

add_user($email, $hash);
set_flash_message('register_success', 'Регистрация успешна');

redirect_to('page_login.php');
