<?php
ob_start();
include 'functions.php';
session_start();

$email = $_POST['email'];

//Как вариант - сделать функцию для получения только email с базы, без остальных данных. Ускорит запрос
if (get_user_by_email($email)) {
    set_flash_message('user_exist', 'Этот Email же занят!');
    redirect_to('page_create_user.php');
    return false;
}

$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_BCRYPT);

$id = add_user($email, $hash);

$username = $_POST['username'];
$job = $_POST['job'];
$tel = $_POST['tel'];
$address = $_POST['adress'];
$status = $_POST['status'];
$vk = $_POST['vk'];
$telegram = $_POST['telegram'];
$instagram = $_POST['instagram'];

edit_user_info($id, $username, $job, $tel, $address);

set_status($id, $status);

edit_user_socials($id, $vk, $telegram, $instagram);

set_flash_message('edit_success', 'Данные пользователья успешно изменены');

redirect_to('page_users.php');
