<?php

require 'app/start.php';

$m = new Members($db);
$r = new Rooms($db);

if (!isset($_SESSION['join_name'])) {
    $_SESSION['message'] = ['type' => 'error', 'data' => 'Enter your name.'];
    go(URL);
}

$member_name = $_SESSION['join_name'];

if (isset($_POST) && !empty($_POST)) {
    
    if (isset($_POST['room_url']) && !empty($_POST['room_url']) && is_string($_POST['room_url']) && !empty(normal_text($_POST['room_url']))) {
        $room_url = normal_text($_POST['room_url']);
    
        // checking if the url is available
        $result = $r->get_room_by('room_url', $room_url);
        if (!$result['status']) {
            $errors[] = "Room does not exists";
        } else {
            $result = $result['data'];
        }

    } else {
        $errors[] = "Room URL cannot be empty.";
    }

    if (empty($errors)) {

        try {
            $db->beginTransaction();

            $room_id = $result['room_id'];
            
            $result = $m->create_member ($member_name, $room_id, 'M');
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
            $errors[] = "Unable to join the room";
        }

    }
    
}



require DIR.'views/layout/header.view.php';
require DIR.'views/join-room.view.php';
require DIR.'views/layout/footer.view.php';