<?php

class Rooms
{
    
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_name;
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Rooms";
        $this->class_name_lower = "rooms_class";
        $this->table_name = "rooms";
    }

    public function get_rooms_by ($col, $val)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `$col` = :v";
        $s = $this->db->prepare($q);
        $s->bindParam(":v", $val);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_rooms_by - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        if ($s->rowCount() > 0) {
            return ['status' => true, 'type' => 'success', 'data' => $s->fetchAll()];
        }
        return ['status' => false, 'type' => 'empty'];
    }

    public function get_room_by ($col, $val)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `$col` = :v";
        $s = $this->db->prepare($q);
        $s->bindParam(":v", $val);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_room_by - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        if ($s->rowCount() > 0) {
            return ['status' => true, 'type' => 'success', 'data' => $s->fetch()];
        }
        return ['status' => false, 'type' => 'empty'];
    }

    public function delete_room ($room_id)
    {
        $q = "DELETE FROM `{$this->table_name}` WHERE `room_id` = :i";
        $s = $this->db->prepare($q);
        $s->bindParam(":i", $room_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.delete_room - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        return ['status' => true];
    }

    public function get_rooms ()
    {
        $q = "SELECT * FROM `{$this->table_name}`";
        $s = $this->db->prepare($q);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_rooms - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        if ($s->rowCount() > 0) {
            return ['status' => true, 'type' => 'success', 'data' => $s->fetchAll()];
        }
        return ['status' => false, 'type' => 'empty'];
    }

    public function create_room ($name, $url)
    {
        $q = "INSERT INTO `{$this->table_name}` (`room_name`, `room_url`, `room_created`) VALUE (:n, :u, :dt)";
        $s = $this->db->prepare($q);
        $s->bindParam(":n", $name);
        $s->bindParam(":u", $url);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        if (!$s->execute()) {
            $failure = $this->class_name.'.create_room - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        return ['status' => true, 'type' => 'success', 'room_id' => $this->db->lastInsertId()];
    }

    public function configure_room ($room_id, $work_time, $work_end, $pause_time, $pause_start, $sound, $round)
    {
        $q = "UPDATE `rooms` SET `room_work_time` = :w, `room_work_end_date` = :we, `room_pause_time` = :p, `room_pause_start_date` = :ps, `room_sound_type` = :s, `room_configure_date` = :dt, `room_round` = :ro, `room_status` = 'A' WHERE `room_id` = :i";
        $s = $this->db->prepare($q);
        $s->bindParam(":w", $work_time);
        $s->bindParam(":we", $work_end);
        $s->bindParam(":p", $pause_time);
        $s->bindParam(":ps", $pause_start);
        $s->bindParam(":s", $sound);
        $s->bindParam(":ro", $round);
        $s->bindParam(":i", $room_id);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        if (!$s->execute()) {
            $failure = $this->class_name.'.configure_room - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        return ['status' => true, 'type' => 'success', 'configure_date' => $dt];
    }

    public function update_room_status ($room_id, $status)
    {
        $q = "UPDATE `rooms` SET `room_status` = :s WHERE `room_id` = :i";
        $s = $this->db->prepare($q);
        $s->bindParam(":s", $status);
        $s->bindParam(":i", $room_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.update_room_status - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        return ['status' => true, 'type' => 'success'];
    }


}
