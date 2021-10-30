<?php
session_start();
require_once 'functions.php';

$user_id = $_GET['id'];

$email = $_POST['email'];
$password = $_POST['password'];
$password_confirmation = $_POST['password_confirmation'];

//Если изменили только Имейл
if ($password === '') {
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
    $statement->execute(array($email, $user_id));

    set_flash_message('edit_success', 'Данные пользователья успешно изменены');
    redirect_to('page_users.php');
}

if ($password !== $password_confirmation) {
    set_flash_message('password_error', 'Пароли не совпадают!');
    redirect_to("page_security.php?id=$user_id");
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
    [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$statement = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE user_id = ?");
$statement->execute(array($email, $hash, $user_id));

set_flash_message('edit_success', 'Данные пользователья успешно изменены');
redirect_to('page_users.php');
