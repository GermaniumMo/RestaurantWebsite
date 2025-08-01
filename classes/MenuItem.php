<?php
require_once 'config/database.php';

class MenuItem {
    private $conn;
    private $table_name = "menu_items";

    public $id;
    public $name;
    public $description;
    public $price;
    public $category;
    public $image_url;
    public $rating;
    public $review_count;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($category = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1";
        
        if ($category && $category !== 'all') {
            $query .= " AND category = :category";
        }
        
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($category && $category !== 'all') {
            $stmt->bindParam(':category', $category);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function read_all() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_single() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->category = $row['category'];
            $this->image_url = $row['image_url'];
            $this->rating = $row['rating'];
            $this->review_count = $row['review_count'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description, price=:price, 
                      category=:category, image_url=:image_url, rating=:rating, 
                      review_count=:review_count, is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':review_count', $this->review_count);
        $stmt->bindParam(':is_active', $this->is_active);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, description=:description, price=:price, 
                      category=:category, image_url=:image_url, rating=:rating, 
                      review_count=:review_count, is_active=:is_active 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':rating', $this->rating);
        $stmt->bindParam(':review_count', $this->review_count);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function getTotalCount($category = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_active = 1";
        
        if ($category && $category !== 'all') {
            $query .= " AND category = :category";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($category && $category !== 'all') {
            $stmt->bindParam(':category', $category);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function readPaginated($category = null, $limit = 10, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1";
        
        if ($category && $category !== 'all') {
            $query .= " AND category = :category";
        }
        
        $query .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if ($category && $category !== 'all') {
            $stmt->bindParam(':category', $category);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }

    public function getTotalCountAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function readAllPaginated($limit = 10, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
?>
