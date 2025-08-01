<?php
require_once 'config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $city;
    public $postal_code;
    public $date_of_birth;
    public $is_active;
    public $email_verified;
    public $email_verification_token;
    public $password_reset_token;
    public $password_reset_expires;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, email=:email, 
                      password=:password, phone=:phone, address=:address, 
                      city=:city, postal_code=:postal_code, date_of_birth=:date_of_birth,
                      email_verification_token=:email_verification_token";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $this->email_verification_token = bin2hex(random_bytes(32));

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':email_verification_token', $this->email_verification_token);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $query = "SELECT id, first_name, last_name, email, password, is_active, email_verified 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->email = $row['email'];
                $this->is_active = $row['is_active'];
                $this->email_verified = $row['email_verified'];
                return true;
            }
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function read_single() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->postal_code = $row['postal_code'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->is_active = $row['is_active'];
            $this->email_verified = $row['email_verified'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name=:first_name, last_name=:last_name, email=:email, 
                      phone=:phone, address=:address, city=:city, 
                      postal_code=:postal_code, date_of_birth=:date_of_birth 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " SET password=:password WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    public function verifyEmail($token) {
        $query = "UPDATE " . $this->table_name . " 
                  SET email_verified=1, email_verification_token=NULL 
                  WHERE email_verification_token=:token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function generatePasswordResetToken() {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "UPDATE " . $this->table_name . " 
                  SET password_reset_token=:token, password_reset_expires=:expires 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function resetPassword($token, $new_password) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE password_reset_token=:token 
                  AND password_reset_expires > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            
            $update_query = "UPDATE " . $this->table_name . " 
                            SET password=:password, password_reset_token=NULL, 
                                password_reset_expires=NULL 
                            WHERE id=:id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':id', $this->id);
            
            return $update_stmt->execute();
        }
        return false;
    }

    public function getUserReservations($limit = 10, $offset = 0) {
        $query = "SELECT * FROM reservations WHERE user_id = :user_id 
                  ORDER BY date DESC, time DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOrders($limit = 10, $offset = 0) {
        $query = "SELECT o.*, 
                         GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id 
                  WHERE o.user_id = :user_id 
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
