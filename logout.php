<?php
ob_start();
session_start();
require_once 'functions.php';

logout();
header('Location: page_login.php');
