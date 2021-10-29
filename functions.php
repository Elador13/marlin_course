<?php
function db_connect()
{
    $connect = mysqli_connect("localhost", "root", "root", "marlin_course");
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_set_charset($connect, "utf8");
    return $connect;
}

function get_user_by_email($email)
{
    $connect = db_connect();

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $sql);

    if (!$result) return false;

    $data = mysqli_fetch_assoc($result);
    $connect->close();
    return $data;

}

function get_user_by_id($id)
{
    $connect = db_connect();

    $sql = "SELECT * FROM users WHERE user_id = '$id'";
    $result = mysqli_query($connect, $sql);

    if (!$result) return false;

    $data = mysqli_fetch_assoc($result);
    $connect->close();
    return $data;

}

function add_user($email, $password)
{
    $connect = db_connect();

    $sql = "INSERT INTO users (email, password, role) VALUES ('$email', '$password', 'user')";

    mysqli_query($connect, $sql);

    $user = get_user_by_email($email);
    mysqli_close($connect);
//    $connect->close();
    return (int)$user['user_id'];
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
    $connect = db_connect();

    $sql = "UPDATE users SET username = '$username', job = '$job', tel = '$tel', address = '$address' WHERE user_id = '$id'";

    mysqli_query($connect, $sql);
    $connect->close();
}

function set_status($id, $status = 'Онлайн')
{
    $connect = db_connect();

    $sql = "UPDATE users SET status = '$status' WHERE user_id = '$id'";

    mysqli_query($connect, $sql);
    $connect->close();
}

function edit_user_socials($id, $vk = null, $telegram = null, $instagram = null)
{
    $connect = db_connect();

    $sql = "UPDATE users SET vk = '$vk', telegram = '$telegram', instagram = '$instagram' WHERE user_id = '$id'";

    mysqli_query($connect, $sql);
    $connect->close();
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
    $connect = db_connect();
    $sql = "SELECT * FROM users";
    $result = mysqli_query($connect, $sql);
    $connect->close();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
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
    $getMime = explode('.', $file['name']);
    $mime = strtolower(end($getMime));
    $types = array('jpg', 'png', 'gif', 'bmp', 'jpeg');

    // если расширение не входит в список допустимых - return
    if(!in_array($mime, $types))
        return 'Недопустимый тип файла.';

    return true;
}

function make_upload($file){
    //Уникальное имя аватара
    $name = mt_rand(0, 10000) . $file['name'];
    copy($file['tmp_name'], 'img/' . $name);
}