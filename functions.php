<?php
function get_user_by_email($email)
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $statement->execute(array($email));

    $result = $statement->fetch();
    if (!$result) return false;

    return $result;
}

function get_user_by_id($id)
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $statement->execute(array($id));

    $result = $statement->fetch();
    if (!$result) return false;

    return $result;

}

function add_user($email, $password, $role = 'user')
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $statement->execute(array($email, $password, $role));

    return $pdo->lastInsertId();
}

function set_flash_message($key, $message) {
    $_SESSION['_flash'][$key] = $message;
}

function display_flash_message($key)
{
    if (isset($_SESSION['_flash'][$key])) {
        echo $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
    }
}

function redirect_to($path)
{
    header('Location: ' . $path);
    exit();
}

function login($email, $password)
{
    $user = get_user_by_email($email);
    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        set_flash_message('login_error', 'Пароль не верный');
        redirect_to('page_login.php');
        return false;
    }
    set_flash_message('login_success', 'Авторизация успешна');

    $_SESSION['user'] = [
        'id' => $user['user_id'],
        'email' => $email,
        'role' => $user['role']
    ];

    redirect_to('page_users.php');

    return true;
}

function edit_user_info($id, $username = null, $job = null, $tel = null, $address = null)
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("UPDATE users SET username = ?, job = ?, tel = ?, address = ? WHERE user_id = ?");
    $statement->execute(array($username, $job, $tel, $address, $id));
}

function set_status($id, $status = 'online')
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $statement->execute(array($status, $id));
}

function edit_security($user_id, $email, $password, $password_confirmation)
{
    $user_id = $_GET['id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    //1. Был лы изменен Имейл
    $edit_user_email = get_user_by_id($user_id)['email'];
    if ($email !== $edit_user_email) {
        if (get_user_by_email($email)) {  //2. Не занят ли он другим
            set_flash_message('security_error', 'Этот Email занят другим пользователем!');
            redirect_to("page_security.php?id=$user_id");
        }
    }
    //Если пароль не изменяли
    if ($password === '') {
        $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

        $statement = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        $statement->execute(array($email, $user_id));

        set_flash_message('edit_success', 'Данные пользователья успешно изменены');
        redirect_to('page_users.php');
    }
    //Если изменили и пароль и Имейл
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE user_id = ?");
    $statement->execute(array($email, $hash, $user_id));

    set_flash_message('edit_success', 'Данные пользователья успешно изменены');
    redirect_to('page_users.php');
}

function edit_user_socials($id, $vk, $telegram, $instagram)
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("UPDATE users SET vk = ?, telegram = ?, instagram = ? WHERE user_id = ?");
    $statement->execute(array($vk, $telegram, $instagram, $id));
}

function is_not_logged_in()
{
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        return false;
    }
    return true;
}

function is_admin()
{
    if (isset($_SESSION['user']['role']) && ($_SESSION['user']['role'] == 'admin')) {
        return true;
    }
    return false;
}

function get_all_users()
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
    return $statement->fetchAll();
}

function get_status_class($status)
{
    switch ($status) {
        case 'not_disturb':
            echo "status-danger";
            break;
        case 'away':
            echo "status-warning";
            break;
        case 'online':
            echo "status-success";
            break;
    }
}

function can_upload($file){

    if($file['name'] == ''){
        set_flash_message('avatar_error', 'Вы не выбрали аватар');
        redirect_to('page_users.php');
    }

    if($file['size'] == 0){
        set_flash_message('avatar_error', 'Файл слишком большой');
        redirect_to('page_users.php');
    }

    //Валидация расширения
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $types = array('jpg', 'png', 'gif', 'bmp', 'jpeg');

    // если расширение не входит в список допустимых - return
    if(!in_array($extension, $types))
        return 'Недопустимый тип файла.';

    return true;
}

function upload_avatar($id, $image)
{
    if(!isset($image)) {
        set_flash_message('avatar_error', 'Вы не выбрали изображение');
        redirect_to('page_avatar.php' . "?id=$id");
    }
    // Проверка можно ли загружать изображение
    $check = can_upload($image);

    if($check === true){
        $old_avatar = get_user_by_id($id)['avatar'];
        //Удаляю старый аватар из БД и хранилища
        if (file_exists("img/avatars/$old_avatar")) {
            unlink("img/avatars/$old_avatar");
        }

        //Создаю уникальное имя файла
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '.' . $extension;

        $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $statement = $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $statement->execute(array($name, $id));

        copy($image['tmp_name'], 'img/avatars/' . $name);

        set_flash_message('edit_success', 'Данные пользователья успешно изменены');
        return true;
    }

    set_flash_message('avatar_error', 'Не удалось загрузить аватар');
    return false;
}

function is_author($logged_user_id, $edit_user_id)
{
    if ($logged_user_id != $edit_user_id) return false;
    return true;
}

function get_current_status($id)
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->query("SELECT status FROM users WHERE user_id = $id");
    return $statement->fetch()['status'];
}

function has_image($user_id)
{
    $user = get_user_by_id($user_id);
    if (isset($user['avatar']) && file_exists('img/avatars/' . $user['avatar'])) {
        return true;
    };
    return false;
}

function logout()
{
    $_SESSION = array();
    session_destroy();
}

function delete($user_id)
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->query("DELETE FROM users WHERE user_id = $user_id");
    return true;
}