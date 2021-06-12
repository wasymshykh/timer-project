<?php

require 'app/start.php';

$m = new Members($db);
$r = new Rooms($db);

if (!isset($_GET['u']) || empty($_GET['u']) || !is_string($_GET['u']) || empty(normal_text($_GET['u']))) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Room link cannot be empty'];
    go(URL);
}

$room_url = normal_text($_GET['u']);

// checking if url is valid

$room = $r->get_room_by('room_url', $room_url);
if (!$room['status']) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Room link is invalid'];
    go(URL);
}
$room = $room['data'];

// checking if the user have access to room

$session = $m->get_cookie_data ();
if (!$session['status']) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Session is invalid'];
    go(URL);
}

if ($room['room_id'] !== $session['room_id']) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'You dont have access to this room'];
    go(URL);
}

// checking if the member id is correct
$member = $m->get_members_by('member_id', $session['member_id']);
if (!$member['status']) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'You dont have access to this room'];
    go(URL);
} else {
    $member = $member['data'][0];
    if ($member['member_room_id'] != $room['room_id']) {
        $_SESSION['message'] = ['type' => 'error', 'data' => 'You dont have access to this room'];
        go(URL);
    }
}



$room_link = URL.'/r.php?u='.$room_url;

require DIR.'views/layout/header.view.php';
require DIR.'views/room.view.php';
require DIR.'views/layout/footer.view.php';
