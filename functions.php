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

function set_status($id, $status = 'Онлайн')
{
    $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    $statement = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $statement->execute(array($status, $id));
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
        case 'Не беспокоить':
            echo "status-danger";
            break;
        case 'Отошел':
            echo "status-warning";
            break;
        case 'Онлайн':
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

function upload_avatar($id)
{
    if(!isset($_FILES['avatar'])) {
        set_flash_message('avatar_error', 'Вы не выбрали изображение');
        redirect_to('page_avatar.php' . "?id=$id");
    }
    // Проверка можно ли загружать изображение
    $check = can_upload($_FILES['avatar']);
    $file = $_FILES['avatar'];

    if($check === true){
        $old_avatar = get_user_by_id($id)['avatar'];
        //Удаляю старый аватар из БД и хранилища
        if (file_exists("/img/avatars/$old_avatar/")) {
            unlink("/img/avatars/$old_avatar/");
        }

        $pdo = new PDO('mysql:dbname=marlin_course;host=localhost;charset=utf8', 'root', 'root',
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $statement = $pdo->prepare("UPDATE users SET avatar = NULL WHERE user_id = ?");
        $statement->execute(array($id));
        //Создаю имя файла
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '.' . $extension;
        copy($file['tmp_name'], 'img/avatars/' . $name);

        $statement = $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $statement->execute(array($name, $id));

        set_flash_message('edit_success', 'Данные пользователья успешно изменены');
        return true;
    }else{
        set_flash_message('avatar_error', 'Не удалось загрузить аватар');
        return false;
    }
}