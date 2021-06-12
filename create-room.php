<?php

require 'app/start.php';

$m = new Members($db);
$r = new Rooms($db);

if (!isset($_SESSION['create_name'])) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Enter your name.'];
    go(URL);
}

$host_name = $_SESSION['create_name'];

if (isset($_POST) && !empty($_POST)) {

    if (isset($_POST['room_name']) && !empty($_POST['room_name']) && is_string($_POST['room_name']) && !empty(normal_text($_POST['room_name']))) {
        $room_name = normal_text($_POST['room_name']);
    } else {
        $errors[] = "Room name cannot be empty.";
    }

    if (isset($_POST['room_url']) && !empty($_POST['room_url']) && is_string($_POST['room_url']) && !empty(normal_text($_POST['room_url']))) {
        $room_url = normal_text($_POST['room_url']);
    
        // checking if the url is available
        $result = $r->get_rooms_by('room_url', $room_url);
        if ($result['status']) {
            $errors[] = "Room URL already exists";
        }

    } else {
        $errors[] = "Room URL cannot be empty.";
    }

    if (empty($errors)) {

        try {
            $db->beginTransaction();
            
            // creating a room
            $result = $r->create_room ($room_name, $room_url);
            if (!$result['status']) {
                throw new Exception;
            }

            $room_id = $result['room_id'];
            
            $result = $m->create_member ($host_name, $room_id, 'H');
            if (!$result['status']) {
                throw new Exception;
            }

            $member_id = $result['member_id'];

            $db->commit();

            // setting cookie
            $m->set_member_cookie($room_id, $member_id);

            go(URL.'/r.php?u='.$room_url);

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = "Unable to start a room";
        }

    }
    
}

require DIR.'views/layout/header.view.php';
require DIR.'views/create-room.view.php';
require DIR.'views/layout/footer.view.php';
