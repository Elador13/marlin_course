<?php
session_start();
include 'functions.php';

$id = $_GET['id'];

if (upload_avatar($id)) {
    set_flash_message('edit_success', 'Данные пользователья успешно изменены');
    redirect_to('page_users.php');
}
set_flash_message('avatar_error', 'Не удалось загрузить аватар');
redirect_to("page_avatar.php?id=$id");
