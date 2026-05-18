<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
use Helpers\Auth;
Auth::logout();
header('Location: login.php');
exit;
