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

if (isset($_GET['j'])) {
    // passing session to index page
    $_SESSION['join-url'] = $_GET['u'];

    if (isset($_SESSION['join-url-remove'])) { unset($_SESSION['join-url-remove']); }
    go(URL.'/');
}

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

$dafault_section = "room";
$timer_configured = true;
if (empty($room['room_configure_date'])) {
    $timer_configured = false;
    if ($member['member_type'] == 'H') {
        $dafault_section = "config";
    }
}

// setting default values
$room['work_minute'] = "00";
$room['work_seconds'] = "00";
if (!empty($room['room_work_time'])) {
    $h = explode(':', $room['room_work_time']);
    $room['work_minute'] = $h[0];
    $room['work_seconds'] = $h[1];
}
$room['pause_minute'] = "00";
$room['pause_seconds'] = "00";
if (!empty($room['room_pause_time'])) {
    $h = explode(':', $room['room_pause_time']);
    $room['pause_minute'] = $h[0];
    $room['pause_seconds'] = $h[1];
}

// checking for the pause value on pause condition
if ($room['room_status'] === 'P' && !empty($room['room_pause_start'])) {
    $h = explode(':', $room['room_pause_start']);
    $room['pause_minutes_at'] = $h[0];
    $room['pause_seconds_at'] = $h[1];
}

$room_link = URL.'/'.$room_url.'&j';

require DIR.'views/layout/header.view.php';
require DIR.'views/room.view.php';
require DIR.'views/layout/footer.view.php';
