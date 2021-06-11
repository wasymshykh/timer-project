<?php

class Logs
{
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create($type, $text, $raw = '') {
        $q = 'INSERT INTO `logs` (`log_text`, `log_type`, `log_raw`) VALUES (:tx, :ty, :rw)';
        $s = $this->db->prepare($q);
        $s->bindParam(':tx', $text);
        $s->bindParam(':ty', $type);
        $s->bindParam(':rw', $raw);
        if (!$s->execute()) {
            $d = ['status' => false, 'message' => 'Logs.create - E.02: Failure', 'type' => $type, 'text' => $text, 'raw' => $raw, 'created' => current_date()];
            $this->store(json_encode($d));
        }
        return ['status' => true];
    }

    /**
     * 
     * @purpose
     *      Record log to file (if query fails)
     * 
     * @return null
    */
    private function store(string $text, $file = 'system')
    {
        $log_dir_path = DIR.'logs/';
        $log_file_path = $log_dir_path.$file.'.log';
        if (!file_exists($log_dir_path)) {
            mkdir($log_dir_path);
        }
        $f = fopen($log_file_path, "a") or die("E.03: Failure!");
        $d = $text."\n";
        fwrite($f, $d);
        fclose($f);
    }

}
