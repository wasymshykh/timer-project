<?php

require 'app/start.php';

$m = new Members($db);

if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['name']) && !empty($_POST['name']) && is_string($_POST['name']) && !empty(normal_text($_POST['name']))) {
        $name = normal_text($_POST['name']);
    } else {
        $errors[] = "Name cannot be empty!";
    }

    if (empty($errors) && isset($_POST['create'])) {

        $_SESSION['create_name'] = $name;
        go(URL.'/create-room.php');
        
    } else if (empty($errors) && isset($_POST['join'])) {

        $_SESSION['join_name'] = $name;
        go(URL.'/join-room.php');

    }


} else {
    $random_username = $m->generate_unique_guest_name();
}


require DIR.'views/layout/header.view.php';
require DIR.'views/home.view.php';
require DIR.'views/layout/footer.view.php';
