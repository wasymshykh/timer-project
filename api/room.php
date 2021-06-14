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

                $filtered['config'] = ['room_name' => $room['room_name'], 'room_url' => $room['room_url'], 'room_work_time' => $room['room_work_time'], 'room_pause_time' => $room['room_pause_time'], 'room_sound_type' => $room['room_sound_type'], 'room_round' => (int)$room['room_round'], 'room_status' => $room['room_status'], 'pause_start' => $room['room_pause_start_date'], 'work_end' => $room['room_work_end_date'], 'room_configure' => $room['room_configure_date']];

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

        if (isset($_POST['work_hour']) && is_string($_POST['work_hour']) && !empty($_POST['work_hour'])) {
            $work_hour = normal_text($_POST['work_hour']);
        } else {
            $errors[] = "Work hours cannot be empty";
        }
        if (isset($_POST['work_minute']) && is_string($_POST['work_minute']) && !empty($_POST['work_minute'])) {
            $work_minute = normal_text($_POST['work_minute']);
        } else {
            $errors[] = "Work minutes cannot be empty";
        }
        if (isset($_POST['pause_hour']) && is_string($_POST['pause_hour']) && !empty($_POST['pause_hour'])) {
            $pause_hour = normal_text($_POST['pause_hour']);
        } else {
            $errors[] = "Pause hours cannot be empty";
        }
        if (isset($_POST['pause_minute']) && is_string($_POST['pause_minute']) && !empty($_POST['pause_minute'])) {
            $pause_minute = normal_text($_POST['pause_minute']);
        } else {
            $errors[] = "Pause minutes cannot be empty";
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
            $round = normal_text($_POST['round']);
        } else {
            $errors[] = "Round cannot be empty";
        }

        if (empty($errors)) {

            $work_time = $work_hour.":".$work_minute;
            $pause_time = $pause_hour.":".$pause_minute;
            
            $now = current_date();

            $work_end = date('Y-m-d H:i:s', strtotime("+$work_hour hours +$work_minute minutes", strtotime($now)));
            $pause_start = date('Y-m-d H:i:s', strtotime("+$pause_hour hours +$pause_minute minutes", strtotime($now)));

            $result = $r->configure_room($room['room_id'], $work_time, $work_end, $pause_time, $pause_start, $sound, $round);
            if ($result['status']) {
                end_response(200, ['work_time' => $work_time, 'pause_time' => $pause_time, 'sound' => $sound, 'configure_date' => $result['configure_date'], 'work_end' => $work_end, 'pause_start' => $pause_start, 'round' => 1]);
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

    // getting room
    $room_id = normal_text($_GET['status']);
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

        $result = $r->update_room_status ($room['room_id'], $status);

        if ($result['status']) {
            end_response(200, "Successfully changed");
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

        $work_hour = explode(":", $room['room_work_time'])[0];
        $work_minute = explode(":", $room['room_work_time'])[1];
        $pause_hour = explode(":", $room['room_pause_time'])[0];
        $pause_minute = explode(":", $room['room_pause_time'])[1];
        
        $now = current_date();

        $work_end = date('Y-m-d H:i:s', strtotime("+$work_hour hours +$work_minute minutes", strtotime($now)));
        $pause_start = date('Y-m-d H:i:s', strtotime("+$pause_hour hours +$pause_minute minutes", strtotime($now)));

        $result = $r->configure_room($room['room_id'], $room['room_work_time'], $work_end, $room['room_pause_time'], $pause_start, $room['room_sound_type'], $room['room_round']);
        if ($result['status']) {
            end_response(200, ['work_time' => $room['room_work_time'], 'pause_time' => $room['room_pause_time'], 'sound' => $room['room_sound_type'], 'configure_date' => $result['configure_date'], 'work_end' => $work_end, 'pause_start' => $pause_start, 'round' => (int)$room['room_round']]);
        } else {
            end_response(400, "Unable to save changes");
        }

    } else {
        end_response(404, "Invalid Room");
    }

}

end_response(403, "Invalid Request");
