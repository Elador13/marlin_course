<?php
session_start();
include 'functions.php';

$id = is_admin() ? $_SESSION['user']['id'] : $_GET['id'];
$user = get_user_by_id($id);
$old_avatar = get_user_by_id($id)['avatar'];



// если была произведена отправка формы
if(isset($_FILES['avatar'])) {
    // Проверка можно ли загружать изображение
    $check = can_upload($_FILES['avatar']);
    $file = $_FILES['avatar'];

    if($check === true){
        // загружаем изображение на сервер предварительно удалив существующее из БД и хранилища
        $connect = db_connect();
        $sql = "UPDATE users SET avatar = NULL WHERE user_id = '$id'";
        unlink('img/'. $old_avatar);

        $name = mt_rand(0, 10000) . $file['name'];
        copy($file['tmp_name'], 'img/' . $name);

        $sql = "UPDATE users SET avatar = '$name' WHERE user_id = '$id'";

        mysqli_query($connect, $sql);
        $connect->close();

        set_flash_message('edit_success', 'Данные пользователья успешно изменены');
        redirect_to('page_users.php');
    }
    else{
        set_flash_message('avatar_error', 'Не удалось загрузить аватар');
        redirect_to('page_users.php');
    }
}else{
    set_flash_message('avatar_error', 'Аватар не выбран');
    redirect_to('page_users.php');
}
