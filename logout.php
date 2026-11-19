<?php
require_once 'includes/functions.php';

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

setFlash('info', 'You have been logged out.');
redirect('login.php');
