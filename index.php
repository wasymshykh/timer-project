<?php

require 'app/start.php';

$m = new Members($db);

$session = $m->get_cookie_data ();
if ($session['status']) {
    $member = $m->get_members_by('member_id', $session['member_id']);
    if ($member['status']) {
        $member = $member['data'][0];
    } else {
        $member = false;
    }
}

if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['name']) && !empty($_POST['name']) && is_string($_POST['name']) && !empty(normal_text($_POST['name']))) {
        $name = normal_text($_POST['name']);
    } else {
        $errors[] = "Name cannot be empty!";
    }

    if (empty($errors) && isset($_POST['create'])) {

        if (isset($_SESSION['join-url-remove'])) { unset($_SESSION['join-url-remove']); }
        $_SESSION['create_name'] = $name;
        go(URL.'/create-room.php');
        
    } else if (empty($errors) && isset($_POST['join'])) {

        if (isset($_SESSION['join-url-remove'])) { unset($_SESSION['join-url-remove']); }
        $_SESSION['join_name'] = $name;
        go(URL.'/join-room.php');

    }

} else {

    // checking if the name is set in the cookie
    if ($session['status']) {
        if ($member) {
            $random_username = $member['member_name'];
        }
    }

    if (empty($random_username)) {
        $random_username = $m->generate_unique_guest_name();
    }

    if (isset($_SESSION['join-url'])) {
        if (isset($_SESSION['join-url-remove'])) {
            unset($_SESSION['join-url']);
        } else {
            $_SESSION['join-url-remove'] = true;
        }
    }

}

require DIR.'views/layout/header.view.php';
require DIR.'views/home.view.php';
require DIR.'views/layout/footer.view.php';
