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

function add_user($email, $password)
{
    $connect = db_connect();

    $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";

    if(mysqli_query($connect, $sql)){
        echo "Данные успешно добавлены";
    } else{
        echo "Ошибка: " . mysqli_error($connect);
        return false;
    }
    $user = get_user_by_email($email);
    mysqli_close($connect);
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

    if (password_verify($password, $user['password'])) {
        set_flash_message('login_success', 'Авторизация успешна');

        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'email' => $email
        ];

        redirect_to('users.html');
    }else{
        set_flash_message('login_error', 'Пароль не верный');
        redirect_to('page_login.php');
        return false;
    }
    return true;
}


