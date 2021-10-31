<?php
session_start();
require_once 'functions.php';

$user_id = $_GET['id'];

$email = $_POST['email'];
$password = $_POST['password'];
$password_confirmation = $_POST['password_confirmation'];


edit_security($user_id, $email, $password, $password_confirmation);
