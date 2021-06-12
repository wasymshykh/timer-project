<?php

class Members
{
    
    private $db;
    private $logs;
    private $class_name;
    private $class_name_lower;
    private $table_name;
    
    public function __construct(PDO $db) {
        $this->logs = new Logs((new DB())->connect());
        $this->db = $db;
        $this->class_name = "Members";
        $this->class_name_lower = "members_class";
        $this->table_name = "members";
    }

    public function get_members_by ($col, $val)
    {
        $q = "SELECT * FROM `{$this->table_name}` WHERE `$col` = :v";
        $s = $this->db->prepare($q);
        $s->bindParam(":v", $val);
        if (!$s->execute()) {
            $failure = $this->class_name.'.get_members_by - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        if ($s->rowCount() > 0) {
            return ['status' => true, 'type' => 'success', 'data' => $s->fetchAll()];
        }
        return ['status' => false, 'type' => 'empty'];
    }

    public function generate_unique_guest_name ()
    {
        $retries = 0;
        while (true) {
            if ($retries > 20) { return false; }
            $name = 'guest'.rand(675454, 1675454);
            $r = $this->get_members_by("member_name", $name);
            if (!$r['status']) { return $name; }
            $retries++;
        }
    }

    public function create_member ($name, $room_id, $member_type)
    {
        $q = "INSERT INTO `{$this->table_name}` (`member_name`, `member_room_id`, `member_type`, `member_last_active`, `member_created`) VALUE (:n, :i, :t, :ld, :dt)";
        $s = $this->db->prepare($q);
        $s->bindParam(":n", $name);
        $s->bindParam(":i", $room_id);
        $s->bindParam(":t", $member_type);
        $dt = current_date();
        $s->bindParam(":ld", $dt);
        $s->bindParam(":dt", $dt);
        if (!$s->execute()) {
            $failure = $this->class_name.'.create_member - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        return ['status' => true, 'type' => 'success', 'member_id' => $this->db->lastInsertId()];
    }

    public function set_member_cookie ($room_id, $member_id)
    {
        $cookie_data = md5("$room_id,$member_id");
        setcookie('__usrd', $cookie_data, time()+(86400*30), "/");
    }


}
