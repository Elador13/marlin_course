<?php
session_start();
require_once 'functions.php';

$id = $_GET['id'];

if (is_admin()) {
    delete($id);
    set_flash_message('edit_success', 'Пользователь успешно удален!');
    redirect_to('page_users.php');
}
delete($id);
set_flash_message('delete', 'Ваш аккаунт успешно удален!');
redirect_to("page_register.php");
