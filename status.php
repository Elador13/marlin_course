<?php
session_start();
include 'functions.php';

$user_id = $_GET['id'];
$status = $_POST['status'];

set_status($user_id, $status);

set_flash_message('edit_success', 'Данные пользователья успешно изменены');
redirect_to('page_users.php');
