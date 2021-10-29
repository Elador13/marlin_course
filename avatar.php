<?php
session_start();
include 'functions.php';

$id = $_GET['id'];
$old_avatar = get_user_by_id($id)['avatar'];

if(!isset($_FILES['avatar'])) {
    set_flash_message('avatar_error', 'Вы не выбрали изображение');
    redirect_to('page_avatar.php' . "?id=$id");
}
// Проверка можно ли загружать изображение
$check = can_upload($_FILES['avatar']);
$file = $_FILES['avatar'];

if($check === true){
    if (file_exists("/img/avatars/$old_avatar/")) {
        unlink("/img/avatars/$old_avatar/");
    }
    $connect = db_connect();
    $sql = "UPDATE users SET avatar = NULL WHERE user_id = '$id'";

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid() . '.' . $extension;
    copy($file['tmp_name'], 'img/avatars/' . $name);

    $sql = "UPDATE users SET avatar = '$name' WHERE user_id = '$id'";
    mysqli_query($connect, $sql);
    $connect->close();

    set_flash_message('edit_success', 'Данные пользователья успешно изменены');
    redirect_to('page_users.php');
}else{
    set_flash_message('avatar_error', 'Не удалось загрузить аватар');
    redirect_to('page_avatar.php' . "?id=$id");
}