<?php
$_POST['api_id'] = 2;
$_POST['excluded_operators'] = 'virtual63';
$_SESSION['token'] = 'DUMMY'; // This will cause auth.php to redirect!
include('toggle_operator_mode.php');
