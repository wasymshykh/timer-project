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
        $cookie_data = $this->encrypt_string ("$room_id,$member_id");
        setcookie('__usrd', $cookie_data, time()+(86400*30), "/");
    }

    public function unset_member_cookie ()
    {
        setcookie('__usrd', '', time()-(86400*30), '/');
    }

    public function get_cookie_data ()
    {
        
        if (!isset($_COOKIE['__usrd'])) {
            return ['status' => false];
        }

        $str = $this->decrypt_string($_COOKIE['__usrd']);
        
        if (!empty($str)) {
            $str = explode(",", $str);
            if (count($str) !== 2) {
                $this->unset_member_cookie();
                return ['status' => false];
            } 
            
        } else {
            $this->unset_member_cookie();
            return ['status' => false];
        }

        return ['status' => true, 'room_id' => $str[0], 'member_id' => $str[1]];

    }

    public function encrypt_string($plaintext, $password = "secret", $encoding = null) {
        $iv = openssl_random_pseudo_bytes(16);
        $ciphertext = openssl_encrypt($plaintext, "AES-256-CBC", hash('sha256', $password, true), OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext.$iv, hash('sha256', $password, true), true);
        return $encoding == "hex" ? bin2hex($iv.$hmac.$ciphertext) : ($encoding == "base64" ? base64_encode($iv.$hmac.$ciphertext) : $iv.$hmac.$ciphertext);
    }
    
    public function decrypt_string($ciphertext, $password = "secret", $encoding = null) {
        $ciphertext = $encoding == "hex" ? hex2bin($ciphertext) : ($encoding == "base64" ? base64_decode($ciphertext) : $ciphertext);
        if (!hash_equals(hash_hmac('sha256', substr($ciphertext, 48).substr($ciphertext, 0, 16), hash('sha256', $password, true), true), substr($ciphertext, 16, 32))) return null;
        return openssl_decrypt(substr($ciphertext, 48), "AES-256-CBC", hash('sha256', $password, true), OPENSSL_RAW_DATA, substr($ciphertext, 0, 16));
    }

    public function update_last_active ($member_id)
    {
        $q = "UPDATE `members` SET `member_last_active` = :dt WHERE `member_id` = :m";
        $s = $this->db->prepare($q);
        $dt = current_date();
        $s->bindParam(":dt", $dt);
        $s->bindParam(":m", $member_id);
        if (!$s->execute()) {
            $failure = $this->class_name.'.update_last_active - E.02: Failure';
            $this->logs->create($this->class_name_lower, $failure, json_encode($s->errorInfo()));
            return ['status' => false, 'type' => 'query', 'data' => $failure];
        }
        return ['status' => true, 'type' => 'success'];
    }

}
