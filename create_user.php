<?php
ob_start();
include 'functions.php';
session_start();

$email = $_POST['email'];

if (get_user_by_email($email)) {
    set_flash_message('user_exist', 'Этот Email же занят!');
    redirect_to('page_create_user.php');
    return false;
}

$hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

$role = $_POST['create_admin'] === 'on' ? 'admin' : 'user';

$id = add_user($email, $hash, $role);

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

upload_avatar($id);

edit_user_socials($id, $vk, $telegram, $instagram);

set_flash_message('edit_success', 'Данные пользователья успешно изменены');

redirect_to('page_users.php');
