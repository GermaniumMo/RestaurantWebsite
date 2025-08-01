<?php
require_once 'config/database.php';
require_once 'classes/MenuItem.php';
require_once 'includes/pagination.php';

function getMenuItems($category = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $menuItem = new MenuItem($db);
    $stmt = $menuItem->read($category);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMenuItemsPaginated($category = null, $page = 1, $items_per_page = 12) {
    $database = new Database();
    $db = $database->getConnection();
    
    $menuItem = new MenuItem($db);
    
    // Get total count
    $total_count = $menuItem->getTotalCount($category);
    
    // Create pagination object
    $pagination = new Pagination($total_count, $items_per_page, $page);
    
    // Get paginated results
    $stmt = $menuItem->readPaginated($category, $pagination->getLimit(), $pagination->getOffset());
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'items' => $items,
        'pagination' => $pagination
    ];
}

function getMenuItemsByCategory() {
    $categories = ['starters', 'main_course', 'desserts', 'beverages'];
    $menuByCategory = [];
    
    foreach ($categories as $category) {
        $menuByCategory[$category] = getMenuItems($category);
    }
    
    return $menuByCategory;
}
?>
