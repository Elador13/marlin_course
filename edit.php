<?php
session_start();
require_once 'functions.php';

$id = is_admin() ? $_SESSION['user']['id'] : $_GET['id'];
$username = $_POST['username'];
$job = $_POST['job'];
$tel = $_POST['tel'];
$address = $_POST['address'];

edit_user_info($id, $username, $job, $tel, $address);

set_flash_message('edit_success', 'Данные пользователья успешно изменены');
redirect_to('page_users.php');
