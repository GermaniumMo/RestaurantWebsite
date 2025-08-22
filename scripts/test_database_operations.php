<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

class DatabaseTestSuite {
    private $db;
    private $test_results = [];
    private $test_count = 0;
    private $passed_tests = 0;
    
    public function __construct() {
        $this->db = db();
        echo "<h1>Database Operations Test Suite</h1>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; margin: 10px;'>\n";
    }
    
    public function runAllTests() {
        echo "<h2>Starting Database Tests...</h2>\n";
        
        // Core database connection tests
        $this->testDatabaseConnection();
        $this->testDatabaseFunctions();
        
        // CRUD operation tests
        $this->testUserCRUD();
        $this->testCategoryCRUD();
        $this->testMenuItemCRUD();
        $this->testReservationCRUD();
        $this->testContactMessageCRUD();
        $this->testCartOperations();
        
        // Security and validation tests
        $this->testInputSanitization();
        $this->testSQLInjectionPrevention();
        $this->testTransactionHandling();
        
        // Performance tests
        $this->testQueryPerformance();
        
        $this->displayResults();
    }
    
    private function test($description, $callback) {
        $this->test_count++;
        echo "<div style='margin: 5px 0;'>";
        echo "<strong>Test {$this->test_count}:</strong> {$description} ... ";
        
        try {
            $result = $callback();
            if ($result) {
                echo "<span style='color: green;'>✓ PASSED</span>";
                $this->passed_tests++;
                $this->test_results[] = ['test' => $description, 'status' => 'PASSED', 'message' => ''];
            } else {
                echo "<span style='color: red;'>✗ FAILED</span>";
                $this->test_results[] = ['test' => $description, 'status' => 'FAILED', 'message' => 'Test returned false'];
            }
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</span>";
            $this->test_results[] = ['test' => $description, 'status' => 'ERROR', 'message' => $e->getMessage()];
        }
        
        echo "</div>\n";
        flush();
    }
    
    private function testDatabaseConnection() {
        echo "<h3>Database Connection Tests</h3>\n";
        
        $this->test("Database connection established", function() {
            $connection = $this->db->getConnection();
            return $connection && $connection->ping();
        });
        
        $this->test("Database charset is UTF-8", function() {
            $result = db_fetch_one("SELECT @@character_set_connection as charset");
            return $result && in_array($result['charset'], ['utf8', 'utf8mb4']);
        });
        
        $this->test("Required tables exist", function() {
            $required_tables = ['users', 'categories', 'menu_items', 'reservations', 'cart_items'];
            foreach ($required_tables as $table) {
                $result = db_fetch_one("SHOW TABLES LIKE ?", [$table]);
                if (!$result) {
                    throw new Exception("Table {$table} does not exist");
                }
            }
            return true;
        });
    }
    
    private function testDatabaseFunctions() {
        echo "<h3>Database Function Tests</h3>\n";
        
        $this->test("db_fetch_one function works", function() {
            $result = db_fetch_one("SELECT 1 as test_value");
            return $result && $result['test_value'] == 1;
        });
        
        $this->test("db_fetch_all function works", function() {
            $result = db_fetch_all("SELECT 1 as test_value UNION SELECT 2 as test_value");
            return is_array($result) && count($result) == 2;
        });
        
        $this->test("Prepared statements work with parameters", function() {
            $result = db_fetch_one("SELECT ? as test_value", ['test_param']);
            return $result && $result['test_value'] == 'test_param';
        });
    }
    
    private function testUserCRUD() {
        echo "<h3>User CRUD Operations</h3>\n";
        
        $test_email = 'test_user_' . time() . '@example.com';
        $user_id = null;
        
        $this->test("Create user", function() use ($test_email, &$user_id) {
            $user_id = db_insert(
                "INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                ['Test User', $test_email, password_hash('testpass', PASSWORD_DEFAULT), 'customer', 1],
                'ssssi'
            );
            return $user_id > 0;
        });
        
        $this->test("Read user", function() use ($user_id) {
            $user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user_id]);
            return $user && $user['email'] == $GLOBALS['test_email'];
        });
        
        $this->test("Update user", function() use ($user_id) {
            $affected = db_execute("UPDATE users SET name = ? WHERE id = ?", ['Updated Test User', $user_id]);
            return $affected > 0;
        });
        
        $this->test("Delete user", function() use ($user_id) {
            $affected = db_execute("DELETE FROM users WHERE id = ?", [$user_id]);
            return $affected > 0;
        });
    }
    
    private function testCategoryCRUD() {
        echo "<h3>Category CRUD Operations</h3>\n";
        
        $category_id = null;
        
        $this->test("Create category", function() use (&$category_id) {
            $category_id = db_insert(
                "INSERT INTO categories (name, description, display_order, is_active, created_at) VALUES (?, ?, ?, ?, NOW())",
                ['Test Category', 'Test Description', 1, 1],
                'ssii'
            );
            return $category_id > 0;
        });
        
        $this->test("Read category", function() use ($category_id) {
            $category = db_fetch_one("SELECT * FROM categories WHERE id = ?", [$category_id]);
            return $category && $category['name'] == 'Test Category';
        });
        
        $this->test("Update category", function() use ($category_id) {
            $affected = db_execute("UPDATE categories SET name = ? WHERE id = ?", ['Updated Category', $category_id]);
            return $affected > 0;
        });
        
        $this->test("Delete category", function() use ($category_id) {
            $affected = db_execute("DELETE FROM categories WHERE id = ?", [$category_id]);
            return $affected > 0;
        });
    }
    
    private function testMenuItemCRUD() {
        echo "<h3>Menu Item CRUD Operations</h3>\n";
        
        $menu_item_id = null;
        
        $this->test("Create menu item", function() use (&$menu_item_id) {
            $menu_item_id = db_insert(
                "INSERT INTO menu_items (name, description, price, is_available, is_featured, display_order, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                ['Test Item', 'Test Description', 9.99, 1, 0, 1],
                'ssdiii'
            );
            return $menu_item_id > 0;
        });
        
        $this->test("Read menu item", function() use ($menu_item_id) {
            $item = db_fetch_one("SELECT * FROM menu_items WHERE id = ?", [$menu_item_id]);
            return $item && $item['name'] == 'Test Item';
        });
        
        $this->test("Update menu item price", function() use ($menu_item_id) {
            $affected = db_execute("UPDATE menu_items SET price = ? WHERE id = ?", [12.99, $menu_item_id]);
            return $affected > 0;
        });
        
        $this->test("Delete menu item", function() use ($menu_item_id) {
            $affected = db_execute("DELETE FROM menu_items WHERE id = ?", [$menu_item_id]);
            return $affected > 0;
        });
    }
    
    private function testReservationCRUD() {
        echo "<h3>Reservation CRUD Operations</h3>\n";
        
        // First create a test user
        $test_user_id = db_insert(
            "INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            ['Reservation Test User', 'reservation_test@example.com', password_hash('testpass', PASSWORD_DEFAULT), 'customer', 1],
            'ssssi'
        );
        
        $reservation_id = null;
        
        $this->test("Create reservation", function() use ($test_user_id, &$reservation_id) {
            $reservation_id = db_insert(
                "INSERT INTO reservations (user_id, name, email, phone, reservation_date, reservation_time, number_of_guests, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$test_user_id, 'Test Reservation', 'test@example.com', '555-1234', '2024-12-31', '19:00:00', 4, 'pending'],
                'isssssss'
            );
            return $reservation_id > 0;
        });
        
        $this->test("Update reservation status", function() use ($reservation_id) {
            $affected = db_execute("UPDATE reservations SET status = ? WHERE id = ?", ['confirmed', $reservation_id]);
            return $affected > 0;
        });
        
        $this->test("Delete reservation", function() use ($reservation_id) {
            $affected = db_execute("DELETE FROM reservations WHERE id = ?", [$reservation_id]);
            return $affected > 0;
        });
        
        // Clean up test user
        db_execute("DELETE FROM users WHERE id = ?", [$test_user_id]);
    }
    
    private function testContactMessageCRUD() {
        echo "<h3>Contact Message Operations</h3>\n";
        
        $this->test("Create contact messages table if not exists", function() {
            db_execute("CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('unread', 'read', 'replied') DEFAULT 'unread'
            )");
            return true;
        });
        
        $message_id = null;
        
        $this->test("Insert contact message", function() use (&$message_id) {
            $message_id = db_insert(
                "INSERT INTO contact_messages (first_name, last_name, email, message) VALUES (?, ?, ?, ?)",
                ['Test', 'User', 'test@example.com', 'This is a test message'],
                'ssss'
            );
            return $message_id > 0;
        });
        
        $this->test("Read contact message", function() use ($message_id) {
            $message = db_fetch_one("SELECT * FROM contact_messages WHERE id = ?", [$message_id]);
            return $message && $message['first_name'] == 'Test';
        });
        
        $this->test("Update message status", function() use ($message_id) {
            $affected = db_execute("UPDATE contact_messages SET status = ? WHERE id = ?", ['read', $message_id]);
            return $affected > 0;
        });
        
        $this->test("Delete contact message", function() use ($message_id) {
            $affected = db_execute("DELETE FROM contact_messages WHERE id = ?", [$message_id]);
            return $affected > 0;
        });
    }
    
    private function testCartOperations() {
        echo "<h3>Cart Operations</h3>\n";
        
        // Create test user and menu item
        $test_user_id = db_insert(
            "INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            ['Cart Test User', 'cart_test@example.com', password_hash('testpass', PASSWORD_DEFAULT), 'customer', 1],
            'ssssi'
        );
        
        $test_item_id = db_insert(
            "INSERT INTO menu_items (name, price, is_available, created_at) VALUES (?, ?, ?, NOW())",
            ['Test Cart Item', 15.99, 1],
            'sdi'
        );
        
        $cart_item_id = null;
        
        $this->test("Add item to cart", function() use ($test_user_id, $test_item_id, &$cart_item_id) {
            $cart_item_id = db_insert(
                "INSERT INTO cart_items (user_id, menu_item_id, quantity, created_at) VALUES (?, ?, ?, NOW())",
                [$test_user_id, $test_item_id, 2],
                'iii'
            );
            return $cart_item_id > 0;
        });
        
        $this->test("Update cart quantity", function() use ($cart_item_id) {
            $affected = db_execute("UPDATE cart_items SET quantity = ? WHERE id = ?", [3, $cart_item_id]);
            return $affected > 0;
        });
        
        $this->test("Get cart items with menu details", function() use ($test_user_id) {
            $items = db_fetch_all(
                "SELECT ci.*, mi.name, mi.price FROM cart_items ci JOIN menu_items mi ON ci.menu_item_id = mi.id WHERE ci.user_id = ?",
                [$test_user_id]
            );
            return count($items) > 0 && $items[0]['name'] == 'Test Cart Item';
        });
        
        $this->test("Remove item from cart", function() use ($cart_item_id) {
            $affected = db_execute("DELETE FROM cart_items WHERE id = ?", [$cart_item_id]);
            return $affected > 0;
        });
        
        // Clean up
        db_execute("DELETE FROM users WHERE id = ?", [$test_user_id]);
        db_execute("DELETE FROM menu_items WHERE id = ?", [$test_item_id]);
    }
    
    private function testInputSanitization() {
        echo "<h3>Input Sanitization Tests</h3>\n";
        
        $this->test("sanitize_input function exists and works", function() {
            if (!function_exists('sanitize_input')) {
                require_once __DIR__ . '/../includes/security.php';
            }
            $dirty_input = '<script>alert("xss")</script>Test & "quotes"';
            $clean_input = sanitize_input($dirty_input);
            return $clean_input !== $dirty_input && !strpos($clean_input, '<script>');
        });
        
        $this->test("Array sanitization works", function() {
            $dirty_array = ['<script>alert("xss")</script>', 'normal text', ['nested' => '<b>bold</b>']];
            $clean_array = sanitize_input($dirty_array);
            return is_array($clean_array) && !strpos($clean_array[0], '<script>');
        });
    }
    
    private function testSQLInjectionPrevention() {
        echo "<h3>SQL Injection Prevention Tests</h3>\n";
        
        $this->test("Prepared statements prevent SQL injection", function() {
            $malicious_input = "'; DROP TABLE users; --";
            $result = db_fetch_one("SELECT ? as test_value", [$malicious_input]);
            return $result && $result['test_value'] === $malicious_input;
        });
        
        $this->test("Parameter type validation works", function() {
            try {
                // This should work fine
                $result = db_fetch_one("SELECT ? as test_value", [123], 'i');
                return $result && $result['test_value'] == 123;
            } catch (Exception $e) {
                return false;
            }
        });
    }
    
    private function testTransactionHandling() {
        echo "<h3>Transaction Handling Tests</h3>\n";
        
        $this->test("Transaction begin/commit works", function() {
            $this->db->beginTransaction();
            $user_id = db_insert(
                "INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                ['Transaction Test', 'transaction@example.com', 'hash', 'customer', 1],
                'ssssi'
            );
            $this->db->commit();
            
            $user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user_id]);
            db_execute("DELETE FROM users WHERE id = ?", [$user_id]); // cleanup
            
            return $user && $user['name'] == 'Transaction Test';
        });
        
        $this->test("Transaction rollback works", function() {
            $this->db->beginTransaction();
            $user_id = db_insert(
                "INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                ['Rollback Test', 'rollback@example.com', 'hash', 'customer', 1],
                'ssssi'
            );
            $this->db->rollback();
            
            $user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user_id]);
            return !$user; // Should not exist after rollback
        });
    }
    
    private function testQueryPerformance() {
        echo "<h3>Query Performance Tests</h3>\n";
        
        $this->test("Simple query executes quickly", function() {
            $start_time = microtime(true);
            db_fetch_one("SELECT 1");
            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
            return $execution_time < 100; // Should execute in less than 100ms
        });
        
        $this->test("Complex join query executes reasonably", function() {
            $start_time = microtime(true);
            db_fetch_all("
                SELECT u.name, COUNT(r.id) as reservation_count 
                FROM users u 
                LEFT JOIN reservations r ON u.id = r.user_id 
                GROUP BY u.id 
                LIMIT 10
            ");
            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time) * 1000;
            return $execution_time < 500; // Should execute in less than 500ms
        });
    }
    
    private function displayResults() {
        echo "</div>\n";
        echo "<h2>Test Results Summary</h2>\n";
        echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px; border-left: 5px solid #4caf50;'>\n";
        echo "<strong>Tests Passed:</strong> {$this->passed_tests} / {$this->test_count}<br>\n";
        echo "<strong>Success Rate:</strong> " . round(($this->passed_tests / $this->test_count) * 100, 2) . "%<br>\n";
        
        if ($this->passed_tests == $this->test_count) {
            echo "<span style='color: green; font-weight: bold;'>🎉 ALL TESTS PASSED!</span>\n";
        } else {
            echo "<span style='color: orange; font-weight: bold;'>⚠️ Some tests failed - check details above</span>\n";
        }
        
        echo "</div>\n";
        
        // Show failed tests
        $failed_tests = array_filter($this->test_results, function($test) {
            return $test['status'] !== 'PASSED';
        });
        
        if (!empty($failed_tests)) {
            echo "<h3>Failed Tests Details:</h3>\n";
            echo "<div style='background: #ffe8e8; padding: 15px; margin: 10px; border-left: 5px solid #f44336;'>\n";
            foreach ($failed_tests as $test) {
                echo "<strong>{$test['test']}:</strong> {$test['status']}";
                if ($test['message']) {
                    echo " - " . htmlspecialchars($test['message']);
                }
                echo "<br>\n";
            }
            echo "</div>\n";
        }
        
        echo "<p><em>Database operations testing completed at " . date('Y-m-d H:i:s') . "</em></p>\n";
    }
}

// Run the tests if accessed directly
if (basename($_SERVER['PHP_SELF']) == 'test_database_operations.php') {
    echo "<!DOCTYPE html><html><head><title>Database Test Results</title></head><body>";
    
    $test_suite = new DatabaseTestSuite();
    $test_suite->runAllTests();
    
    echo "</body></html>";
}
?>
