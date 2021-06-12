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

                $filtered['config'] = ['room_name' => $room['room_name'], 'room_url' => $room['room_url'], 'room_work_time' => $room['room_work_time'], 'room_pause_time' => $room['room_pause_time'], 'room_sound_type' => $room['room_sound_type'], 'room_round' => $room['room_round'], 'room_status' => $room['room_status']];

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



}

end_response(403, "Invalid Request");
