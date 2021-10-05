<?php
/* 
 * class db 
 * Author: M.N.
 */
 
class db {
    private static $conn;
    
    public function __construct($file = 'database.ini') {
        if (!$settings = parse_ini_file($file, TRUE)) {
            throw new exception('Unable to open ' . $file . '.');
        }
        
        $driver = $settings['database']['driver'];
        $host = $settings['database']['host'];
        $username = $settings['database']['username'];
        $password = $settings['database']['password'];
        $dbname = $settings['database']['dbname'];
        $charset = $settings['database']['charset'];
        
        $dsn = "$driver:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            if(empty($conn)) {
                $dbh = new PDO($dsn, $username, $password, $options);
                self::$conn = $dbh;
            }
        }
        catch (PDOException $e){
            echo('Unable to connect to database. Error: ' . $e . '</br>');
            die();
        }
    }
    
    // gets userdata given from matnr
    public function getUserData($matnr) {
        $query = 'SELECT id, matnr, sex, vorname, name, strasse, ort, statusorig, email, telefon, password, last_seen FROM users WHERE matnr = :matnr LIMIT 1';
        $stmt = self::$conn->prepare($query);
        $stmt->execute(['matnr' => $matnr]);
        $userData = $stmt->fetch();
        
        return $userData;
    }
    
    // checks pwd and returns bool
    public function getLogin($matnr, $pwd) {
        $stmt = self::$conn->prepare("SELECT * FROM users WHERE matnr = :matnr");
        $stmt->execute(['matnr' => $matnr]);
        $user = $stmt->fetch();

        //user login 1234
        if ($user && password_verify($pwd, $user['pwd_login']))
        {
            return true;
        } else {
            return false;
        }
    }
    
    public function getAllSlots() { // is this function still needed?
        $stmt = self::$conn->prepare("SELECT * FROM slots");
        $stmt->execute();
        $slots = $stmt->fetchAll();
        
        return $slots;
    }

    public function getDistinctRooms() {
        $stmt = self::$conn->prepare("SELECT DISTINCT room_name, room_name_short FROM slots");
        $stmt->execute();
        $rooms = $stmt->fetchAll();
        
        return $rooms;
    }

    public function getSlotsFromRoom($room_name) {
        $stmt = self::$conn->prepare("SELECT * FROM slots WHERE room_name = :room_name");
        $stmt->execute(['room_name' => $room_name]);
        $slots = $stmt->fetchAll();
        
        return $slots;
    }
    
    public function updateUser($id, $data) {
        $query = "UPDATE users SET matnr = :matnr, vorname = :vorname, name = :name, strasse = :strasse, ort = :ort, statusorig = :statusorig, email = :email, telefon = :telefon, password = :password WHERE id = :id";
        $stmt = self::$conn->prepare($query);
        $stmt->execute(['matnr' => $data['matnr'], 'vorname' => $data['vorname'], 'name' => $data['name'], 'strasse' => $data['strasse'], 'ort' => $data['ort'], 'statusorig' => $data['statusorig'], 'email' => $data['email'], 'telefon' => $data['telefon'], 'password' => $data['password'], 'id' => $id]);
        $result = $stmt->rowCount();
        
        return $result;
    } 

    public function createBooking($kursId, $matnr, $lastBooking) {
        # create entry in db if it doesnt exist yet, otherwise just update days
        $query = "INSERT INTO bookings (bs_kursId, matnr, last_booking) VALUES (:bs_kursId, :matnr, :lastBooking)";
        $stmt = self::$conn->prepare($query);
        $stmt->execute(['bs_kursId' => $kursId, 'matnr' => $matnr, 'lastBooking' => $lastBooking]);

        $result = $stmt->rowCount();
        
        return $result;
    }
    
    public function deleteBooking($kursId, $matnr) {
        $query = "DELETE FROM bookings WHERE bs_kursId = :bs_kursId AND matnr = :matnr";
        $stmt = self::$conn->prepare($query);
        $stmt->execute(['bs_kursId' => $kursId, 'matnr' => $matnr]);

        $result = $stmt->rowCount();
        
        return $result;
    }
    
    # returns days which were booked for slot in question
    public function getBookingStatusForSlot($kursId, $matnr) {
        $stmt = self::$conn->prepare("SELECT * FROM bookings WHERE matnr = :matnr AND bs_kursId = :bs_kursId");
        $stmt->execute(['bs_kursId' => $kursId, 'matnr' => $matnr]);
        $bookingStatus = $stmt->fetchAll();        

        return $bookingStatus;
    }    
}

?>