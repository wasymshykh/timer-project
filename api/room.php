<?php

require '../app/start.php';

$m = new Members($db);
$r = new Rooms($db);

if (isset($_GET['info']) && !empty($_GET['info']) && is_numeric($_GET['info'])) {

    // getting room
    $room_id = normal_text($_GET['info']);
    
    $room = $r->get_room_by('room_id', $room_id);

    if ($room['status']) {
        $room = $room['data'];

        // checking the token
        $session = $m->get_cookie_data ();
        if (!$session['status']) {
            end_response(403, "Session is invalid");
        }
        if ($room['room_id'] !== $session['room_id']) {
            end_response(403, "You dont have access to this room");
        }
    
        // checking if the member id is correct
        $member_id = $session['member_id'];

        $found_member = false;

        // getting members of the room
        $members = $m->get_members_by('member_room_id', $room['room_id']);

        if ($members['status']) {

            $members = $members['data'];

            $filtered = ['members' => [], 'config' => []];
            
            foreach ($members as $member) {
                $d = [];

                if ($member['member_id'] === $member_id) {
                    $found_member = true;
                }

                $d['member_name'] = $member['member_name'];
                $d['is_host'] = $member['member_type'] === 'M' ? false : true;
                // checking if member was last active at 20 seconds ago

                $active_time = strtotime($member['member_last_active']);
                $now = time();
                
                $d['diff'] = round(($now - $active_time));

                if ($d['diff'] > 20 && $member['member_id'] !== $member_id) {
                    $d['online'] = false;
                } else {
                    $d['online'] = true;
                }

                $filtered['members'][] = $d;
            }

            if ($found_member) {
                // updating last active of requestee
                $m->update_last_active($member_id);

                $filtered['config'] = ['room_name' => $room['room_name'], 'room_url' => $room['room_url'], 'room_work_time' => $room['room_work_time'], 'room_pause_time' => $room['room_pause_time'], 'room_sound_type' => $room['room_sound_type'], 'room_round' => (int)$room['room_round'], 'room_round_limit' => (int)$room['room_round_limit'], 'room_status' => $room['room_status'], 'pause_start' => $room['room_pause_start'], 'work_end' => $room['room_work_end_date'], 'room_configure' => $room['room_configure_date'], 'pause_end' => $room['room_pause_end']];

                end_response(200, $filtered);
            } else {
                end_response(403, "You dont have access to this room");
            }

        } else {
            end_response(404, "No members");
        }

    } else {
        end_response(404, "Invalid Room");
    }

} else if (isset($_GET['config']) && !empty($_GET['config']) && is_numeric($_GET['config'])) {

    // getting room
    $room_id = normal_text($_GET['config']);
    $room = $r->get_room_by('room_id', $room_id);
    $allowed_sound = ['bip bip bip', 'buzzer', 'dring'];

    if ($room['status']) {
        $room = $room['data'];

        // checking the token
        $session = $m->get_cookie_data ();
        if (!$session['status']) {
            end_response(403, "Session is invalid");
        }
        if ($room['room_id'] !== $session['room_id']) {
            end_response(403, "You dont have access to this room");
        }

        if (isset($_POST['work_minute']) && is_string($_POST['work_minute']) && !empty($_POST['work_minute'])) {
            $work_minute = normal_text($_POST['work_minute']);
        } else {
            $errors[] = "Work time minutes cannot be empty";
        }
        if (isset($_POST['work_seconds']) && is_string($_POST['work_seconds']) && !empty($_POST['work_seconds'])) {
            $work_seconds = normal_text($_POST['work_seconds']);
        } else {
            $errors[] = "Work time seconds cannot be empty";
        }
        if (isset($_POST['pause_minute']) && is_string($_POST['pause_minute']) && !empty($_POST['pause_minute'])) {
            $pause_minute = normal_text($_POST['pause_minute']);
        } else {
            $errors[] = "Pause time minutes cannot be empty";
        }
        if (isset($_POST['pause_seconds']) && is_string($_POST['pause_seconds']) && !empty($_POST['pause_seconds'])) {
            $pause_seconds = normal_text($_POST['pause_seconds']);
        } else {
            $errors[] = "Pause time seconds cannot be empty";
        }
        if (isset($_POST['sound']) && is_string($_POST['sound']) && !empty($_POST['sound'])) {
            $sound = normal_text($_POST['sound']);
            if (!in_array($sound, $allowed_sound)) {
                $errors[] = "Sound is not allowed";
            }
        } else {
            $errors[] = "Sound cannot be empty";
        }
        if (isset($_POST['round']) && is_string($_POST['round']) && !empty($_POST['round'])) {
            $round_limit = normal_text($_POST['round']);
            $round = '1'; // starting from round 1
        } else {
            $errors[] = "Round cannot be empty";
        }

        if (empty($errors)) {

            $work_time = $work_minute.":".$work_seconds;
            $pause_time = $pause_minute.":".$pause_seconds;
            
            $work_end = date('Y-m-d H:i:s', strtotime("+$work_minute minutes +$work_seconds seconds", time()));

            $result = $r->configure_room($room['room_id'], $work_time, $work_end, $pause_time, $sound, $round, $round_limit);
            if ($result['status']) {
                end_response(200, ['work_time' => $work_time, 'pause_time' => $pause_time, 'sound' => $sound, 'configure_date' => $result['configure_date'], 'work_end' => $work_end, 'round' => 1, 'pause_end' => $room['room_pause_end']]);
            } else {
                end_response(400, "Unable to save changes");
            }

        } else {
            end_response(402, $errors);
        }

    } else {
        end_response(404, "Invalid Room");
    }

} else if (isset($_GET['status']) && !empty($_GET['status']) && is_numeric($_GET['status'])) {

    if (isset($_GET['pause'])) {
        $status = "P";
    } else if (isset($_GET['resume'])) {
        $status = "A";
    } else {
        end_response(403, "Invalid Request");
    }

    $at_minutes = $_POST['at_minute'] ?? '00';
    $at_seconds = $_POST['at_seconds'] ?? '00';

    $pause_time = $at_minutes.':'.$at_seconds;

    // getting room
    $room_id = normal_text($_GET['status']);
    $room = $r->get_room_by('room_id', $room_id);

    if ($room['status']) {
        $room = $room['data'];

        $work_end = date('Y-m-d H:i:s', strtotime("+$at_minutes minutes +$at_seconds seconds", time()));
        
        // checking the token
        $session = $m->get_cookie_data ();
        if (!$session['status']) {
            end_response(403, "Session is invalid");
        }
        if ($room['room_id'] !== $session['room_id']) {
            end_response(403, "You dont have access to this room");
        }

        $result = $r->update_room_status ($room['room_id'], $status, $pause_time, $work_end);

        if ($result['status']) {
            end_response(200, ['work_end' => $work_end]);
        } else {
            end_response(400, "Unable to save changes");
        }

    } else {
        end_response(404, "Invalid Room");
    }

} else if (isset($_GET['reset']) && !empty($_GET['reset']) && is_numeric($_GET['reset'])) {

    // getting room
    $room_id = normal_text($_GET['reset']);
    $room = $r->get_room_by('room_id', $room_id);

    if ($room['status']) {
        $room = $room['data'];
        
        // checking the token
        $session = $m->get_cookie_data ();
        if (!$session['status']) {
            end_response(403, "Session is invalid");
        }
        if ($room['room_id'] !== $session['room_id']) {
            end_response(403, "You dont have access to this room");
        }

        $work_minute = explode(":", $room['room_work_time'])[0];
        $work_seconds = explode(":", $room['room_work_time'])[1];
        
        $work_end = date('Y-m-d H:i:s', strtotime("+$work_minute minutes +$work_seconds seconds", time()));

        $result = $r->configure_room($room['room_id'], $room['room_work_time'], $work_end, $room['room_pause_time'], $room['room_sound_type'], $room['room_round'], $room['room_round_limit']);
        if ($result['status']) {
            end_response(200, ['work_time' => $room['room_work_time'], 'pause_time' => $room['room_pause_time'], 'sound' => $room['room_sound_type'], 'configure_date' => $result['configure_date'], 'work_end' => $work_end, 'round' => (int)$room['room_round'], 'round_limit' => (int)$room['room_round_limit'], 'pause_end' => $room['room_pause_end']]);
        } else {
            end_response(400, "Unable to save changes");
        }

    } else {
        end_response(404, "Invalid Room");
    }

} else if (isset($_GET['change_round']) && !empty($_GET['change_round']) && is_numeric($_GET['change_round'])) {

// getting room
    $room_id = normal_text($_GET['change_round']);
    $room = $r->get_room_by('room_id', $room_id);

    if ($room['status']) {
        $room = $room['data'];
        
        // checking the token
        $session = $m->get_cookie_data ();
        if (!$session['status']) {
            end_response(403, "Session is invalid");
        }
        if ($room['room_id'] !== $session['room_id']) {
            end_response(403, "You dont have access to this room");
        }
        
        $pause_minute = explode(":", $room['room_pause_time'])[0];
        $pause_seconds = explode(":", $room['room_pause_time'])[1];

        $pause_end = date('Y-m-d H:i:s', strtotime("+$pause_minute minutes +$pause_seconds seconds", time()));

        $result = $r->room_round_change ($room['room_id'], ($room['room_round']+1), $room['room_work_time'], $pause_end);
        if ($result['status']) {
            end_response(200, ['pause_end' => $pause_end]);
        } else {
            end_response(400, "Unable to save changes");
        }

    } else {
        end_response(404, "Invalid Room");
    }

}

end_response(403, "Invalid Request");
