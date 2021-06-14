<?php

require 'app/start.php';

$m = new Members($db);
$r = new Rooms($db);

$rooms = $r->get_rooms();

if ($rooms['status']) {

    $rooms = $rooms['data'];

    foreach ($rooms as $room) {
        
        $members = $m->get_members_by('member_room_id', $room['room_id']);

        
        if ($members['status']) {

            $members = $members['data'];
            
            $online = [];
            foreach ($members as $member) {
                $d = [];

                // checking if member was last active at 60 seconds ago
                $active_time = strtotime($member['member_last_active']);
                $now = time();
                
                $d['diff'] = round(($now - $active_time));

                if ($d['diff'] > 60) {
                    continue;
                } else {
                    $online[] = $member['member_name'];
                }
            }

            if (empty($online)) {
                // remove room
                $r->delete_room($room['room_id']);
            }

        }

    }

}
